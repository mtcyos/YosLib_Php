
<?php
/**
 * YosWwwBot - Módulo YosAchAcc (PHP directo)
 * Explorador y descarga de archivos de directorios (Multiteka / Documentos)
 * Equivalente exacto a YosAchAcc.py de Python
 *
 * Copyright (c) 2026 Miguel Tortosa
 */

// Si no está definida YOS_APP, inicializamos el entorno de forma autónoma para acceso directo
if (!defined('YOS_APP')) {
    define('YOS_APP', true);
}

// Cargar configuración global si existe el archivo config.php (ajusta el nombre si es distinto)
$config = [];
if (file_exists('config.php')) {
    include 'config.php';
} elseif (file_exists('../config.php')) {
    include '../config.php';
}

// Configuración por defecto si no se cargó desde config.php
if (!isset($config['Apl'])) {
    $config['Apl'] = [
        'Usr_Acc' => 'N',
        'DirAch' => 'd:\_DfpDatos'
    ];
}

$dirAchConfig = $config['DirAch'] ?? ($config['Apl']['DirAch'] ?? 'd:\_DfpDatos');
$usrAcc = $config['Usr_Acc'] ?? ($config['Apl']['Usr_Acc'] ?? 'N');

// -----------------------------------------------------------------------------
// CONTROL DE ACCESO
// -----------------------------------------------------------------------------
if ($usrAcc === 'S') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['autenticado'])) {
        header('Location: index.php?pgn=login');
        exit;
    }
}

// Respaldo de función de escape
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// -----------------------------------------------------------------------------
// FUNCIONES AUXILIARES DE RUTA Y ARCHIVOS
// -----------------------------------------------------------------------------
function normalizeDirPath($path) {
    if (empty($path)) return '';
    $path = trim($path);
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    return rtrim($path, DIRECTORY_SEPARATOR);
}

function formatFileSize($bytes) {
    if ($bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
}

function resolveAplDirAch($inputPath, $baseDir) {
    if (empty($inputPath)) return '';
    $clean = trim($inputPath);

    // Si trae prefijo /Apl_DirAch
    if (str_starts_with($clean, '/Apl_DirAch') || str_starts_with($clean, '\\Apl_DirAch')) {
        $clean = preg_replace('#^[/\\\\]Apl_DirAch#', $baseDir, $clean);
    }
    // Si ya es absoluta con unidad (ej. D:\...)
    elseif (preg_match('/^[a-zA-Z]:[\\\\\/]/', $clean)) {
        // Se respeta tal cual
    }
    else {
        // Si es una ruta relativa pura (ej. /YosGesDoc/), la unimos al directorio base DirAch
        $clean = $baseDir . DIRECTORY_SEPARATOR . ltrim($clean, '/\\');
    }

    return normalizeDirPath($clean);
}

// -----------------------------------------------------------------------------
// RESOLUCIÓN DE PARÁMETROS (Dir, Tit, init_dir)
// -----------------------------------------------------------------------------
$rawDir = isset($_GET['Dir']) ? trim($_GET['Dir']) : 'NoDir';
$tit = !empty($_GET['Tit']) ? trim($_GET['Tit']) : 'Archivo';

$resolvedDir = resolveAplDirAch($rawDir, $dirAchConfig);
$rawInitDir = isset($_GET['init_dir']) ? trim($_GET['init_dir']) : $rawDir;
$resolvedInitDir = resolveAplDirAch($rawInitDir, $dirAchConfig);

// -----------------------------------------------------------------------------
// PROCESAMIENTO DE DESCARGA DIRECTA DE ARCHIVO (action = download)
// -----------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'download') {
    $targetFile = isset($_GET['file']) ? trim($_GET['file']) : '';
    if (!empty($targetFile)) {
        $realFile = realpath($targetFile);
        $realInit = realpath($resolvedInitDir);

        $isSafe = $realFile && is_file($realFile) && is_readable($realFile);
        if ($isSafe && $realInit) {
            $isSafe = str_starts_with(strtolower($realFile), strtolower($realInit));
        }

        if ($isSafe) {
            $fileName = basename($realFile);
            $fileSize = filesize($realFile);
            $mimeType = 'application/octet-stream';
            if (function_exists('mime_content_type')) {
                $detected = @mime_content_type($realFile);
                if ($detected) $mimeType = $detected;
            }

            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . rawurlencode($fileName) . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $fileSize);

            readfile($realFile);
            exit;
        } else {
            http_response_code(403);
            die("Error de seguridad: Archivo no accesible o fuera del directorio permitido.");
        }
    }
    exit;
}

// -----------------------------------------------------------------------------
// PROCESAMIENTO DE DESCARGA DE CARPETA COMPRIMIDA EN ZIP (action = zip)
// -----------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'zip' && class_exists('ZipArchive')) {
    $targetDir = isset($_GET['dir']) ? trim($_GET['dir']) : '';
    if (!empty($targetDir)) {
        $realDir = realpath($targetDir);
        $realInit = realpath($resolvedInitDir);

        $isSafe = $realDir && is_dir($realDir) && is_readable($realDir);
        if ($isSafe && $realInit) {
            $isSafe = str_starts_with(strtolower($realDir), strtolower($realInit));
        }

        if ($isSafe) {
            $folderName = basename($realDir);
            $tempZip = tempnam(sys_get_temp_dir(), 'yos_zip_') . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($realDir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($realDir) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                $zip->close();

                if (file_exists($tempZip)) {
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . rawurlencode($folderName) . '.zip"');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($tempZip));
                    readfile($tempZip);
                    @unlink($tempZip);
                    exit;
                }
            }
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= e($tit) ?></title>
    <style>
        body { background-color: #ffffff; color: #000000; font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .explorer-wrapper { max-width: 1200px; margin: 0 auto; }
        .explorer-nav-bar { background: #f0f0f0; border: 1px solid #ccc; padding: 8px; margin-bottom: 10px; border-radius: 3px; display: flex; align-items: center; gap: 10px; }
        .explorer-table-card { border: 1px solid #adb5bd; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .explorer-table-header { background-color: #34495e; color: #ffffff; font-weight: bold; display: flex; padding: 8px 12px; border-bottom: 2px solid #2c3e50; }
        .th-col-name { flex: 1; }
        .th-col-type { width: 150px; text-align: right; }
        .explorer-item-row { display: flex; align-items: center; padding: 6px 12px; border-bottom: 1px solid #e9ecef; }
        .explorer-item-row:hover { background-color: #e8f4fd; }
        .explorer-item-row:nth-child(even) { background-color: #fcfcfc; }
        .item-col-main { flex: 1; display: flex; align-items: center; gap: 8px; }
        .item-col-meta { width: 150px; text-align: right; display: flex; justify-content: flex-end; align-items: center; gap: 8px; }
        .item-icon { width: 18px; height: 18px; display: inline-block; vertical-align: middle; flex-shrink: 0; }
        .folder-icon { color: #f39c12; }
        .file-icon { color: #7f8c8d; }
        .item-name { text-decoration: none; color: #0066cc; }
        .item-name:hover { text-decoration: underline; }
        .explorer-notice-box { padding: 20px; border: 1px solid #ccc; background: #fff3cd; color: #856404; border-radius: 4px; margin-top: 20px; text-align: center; }
        .explorer-notice-box.error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .badge-dir { color: #2980b9; font-weight: bold; font-size: 11px; }
        .badge-file { color: #7f8c8d; font-size: 11px; }
        .btn-zip-download, .btn-file-download { background: #f8f9fa; border: 1px solid #ccc; padding: 2px 6px; border-radius: 3px; text-decoration: none; color: #333; font-size: 12px; }
        .btn-zip-download:hover, .btn-file-download:hover { background: #e2e6ea; }
        .nav-current-path { color: #666; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>

<div class="explorer-wrapper">
    <h2 style="text-align: center; color: #2c3e50;"><?= e($tit) ?></h2>

    <?php if ($rawDir === 'NoDir'): ?>
        <div class="explorer-notice-box">
            <h3>⚠️ No se ha definido ningún Directorio</h3>
            <p>Para acceder al explorador debe proporcionar un parámetro de ruta válido en la URL.</p>
        </div>
        <script>
            setTimeout(function() { window.history.back(); }, 3000);
        </script>
    <?php else: ?>
        <?php
        $realCurrent = realpath($resolvedDir);
        $realInit = realpath($resolvedInitDir);
        $dirExists = ($realCurrent !== false && is_dir($realCurrent));
        ?>

        <?php if (!$dirExists): ?>
            <div class="explorer-notice-box error">
                <h3>📁❌ El Directorio no se encuentra disponible</h3>
                <p>La ruta especificada no existe o no tiene permisos de lectura:</p>
                <p><code><?= e($resolvedDir) ?></code></p>
                <p><small>Directorio base configurado (DirAch): <code><?= e($dirAchConfig) ?></code></small></p>
            </div>
        <?php else: ?>
            <?php
            $isRoot = (!$realInit || strtolower($realCurrent) === strtolower($realInit));
            $parentDir = dirname($realCurrent);
            $canGoBack = !$isRoot && (!empty($realInit) ? str_starts_with(strtolower($parentDir), strtolower($realInit)) || strtolower($parentDir) === strtolower($realInit) : true);

            $subPath = '';
            if (!$isRoot && $realInit) {
                $subPath = ltrim(substr($realCurrent, strlen($realInit)), DIRECTORY_SEPARATOR);
            }

            $elementos = @scandir($realCurrent);
            $carpetas = [];
            $archivos = [];

            if ($elementos !== false) {
                foreach ($elementos as $elem) {
                    if ($elem === '.' || $elem === '..' || $elem === 'desktop.ini' || $elem === 'Thumbs.db') {
                        continue;
                    }
                    $fullElemPath = $realCurrent . DIRECTORY_SEPARATOR . $elem;
                    if (is_dir($fullElemPath)) {
                        $carpetas[] = ['nombre' => $elem, 'path' => $fullElemPath, 'es_dir' => true];
                    } elseif (is_file($fullElemPath)) {
                        $archivos[] = ['nombre' => $elem, 'path' => $fullElemPath, 'es_dir' => false, 'size' => @filesize($fullElemPath)];
                    }
                }
            }

            usort($carpetas, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));
            usort($archivos, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));
            $todos = array_merge($carpetas, $archivos);
            ?>

            <!-- BARRA DE NAVEGACIÓN -->
            <div class="explorer-nav-bar">
                <a href="YosAchAcc.php?Dir=<?= urlencode($rawInitDir) ?>&init_dir=<?= urlencode($rawInitDir) ?>&Tit=<?= urlencode($tit) ?>" title="Inicio">
                    🏠 Inicio
                </a>
                <?php if ($canGoBack): ?>
                    <a href="YosAchAcc.php?Dir=<?= urlencode($parentDir) ?>&init_dir=<?= urlencode($rawInitDir) ?>&Tit=<?= urlencode($tit) ?>" title="Subir nivel">
                        ↩️ Volver atrás
                    </a>
                <?php endif; ?>
                <?php if (!empty($subPath)): ?>
                    <span class="nav-current-path">\<?= e($subPath) ?></span>
                <?php endif; ?>
            </div>

            <!-- TABLA ESTILO GRILLA -->
            <div class="explorer-table-card">
                <div class="explorer-table-header">
                    <div class="th-col-name">Nombre del Elemento</div>
                    <div class="th-col-type">Tipo</div>
                </div>

                <div class="explorer-table-body">
                    <?php if (empty($todos)): ?>
                        <div style="padding: 15px; text-align: center; color: #666;">No se encontraron elementos en esta ruta.</div>
                    <?php else: ?>
                        <?php foreach ($todos as $item): ?>
                            <div class="explorer-item-row">
                                <div class="item-col-main">
                                    <?php if ($item['es_dir']): ?>
                                        <svg class="item-icon folder-icon" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
                                        </svg>
                                        <a href="YosAchAcc.php?Dir=<?= urlencode($item['path']) ?>&init_dir=<?= urlencode($rawInitDir) ?>&Tit=<?= urlencode($tit) ?>" class="item-name">
                                            <?= e($item['nombre']) ?>
                                        </a>
                                    <?php else: ?>
                                        <svg class="item-icon file-icon" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                        </svg>
                                        <a href="YosAchAcc.php?action=download&file=<?= urlencode($item['path']) ?>&init_dir=<?= urlencode($rawInitDir) ?>" class="item-name" download>
                                            <?= e($item['nombre']) ?>
                                        </a>
                                        <?php if (isset($item['size'])): ?>
                                            <span style="font-size: 11px; color: #666;">(<?= formatFileSize($item['size']) ?>)</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="item-col-meta">
                                    <?php if ($item['es_dir']): ?>
                                        <span class="badge-dir">DIRECTORIO</span>
                                        <?php if (class_exists('ZipArchive')): ?>
                                            <a href="YosAchAcc.php?action=zip&dir=<?= urlencode($item['path']) ?>&init_dir=<?= urlencode($rawInitDir) ?>" class="btn-zip-download" title="Descargar ZIP">
                                                ZIP 📦
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge-file">ARCHIVO</span>
                                        <a href="YosAchAcc.php?action=download&file=<?= urlencode($item['path']) ?>&init_dir=<?= urlencode($rawInitDir) ?>" class="btn-file-download" title="Descargar">
                                            ⬇
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
