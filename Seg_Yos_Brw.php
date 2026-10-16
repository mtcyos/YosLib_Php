<?php
# -*- coding: utf-8 -*-

function Yos_Brw() {
    global $configSistema, $scriptActual, $tabla, $columnas, $cabecera, $ancho, $pkName, $Acciones_tamano;

    // Configuración por defecto o heredada de la aplicación llamadora ({Tabla}.php)[cite: 8, 9]
    $configSistema = $configSistema ?? [
        'SvrTip'     => $_GET['SvrTip'] ?? 'sqlite',
        'SvrSisDir'  => $_GET['db'] ?? '',
        'SvrSisUsr'  => $_GET['usr'] ?? '',
        'SvrSisPas'  => $_GET['pas'] ?? '',
        'SvrSisDbf'  => $_GET['dbf'] ?? '',
        'RegPorPag'  => 20
    ];

    $dbPath = $_GET['db'] ?? $_POST['db'] ?? ($configSistema['SvrSisDir'] ?? '');
    $tabla  = $tabla ?? ($_GET['tabla'] ?? $_POST['tabla'] ?? '');
//    $scriptActual = !empty($tabla) ? ($tabla . '.php') : basename($_SERVER['SCRIPT_NAME'] ?? 'Yos_Brw.php');
//$scriptActual = '../YosLib/Yos_Brw.php';
$scriptActual = $scriptActual ?? ($_GET['scriptActual'] ?? $_POST['scriptActual'] ?? '');
//echo '$scriptActual='.$scriptActual.'<br>';
    $mensaje = '';

    $columnas = $columnas ?? [];
    $cabecera = $cabecera ?? $columnas;
    $ancho    = $ancho ?? [];

    $pkName   = $pkName ?? '';
    $Acciones_tamano = $Acciones_tamano ?? '105px';

    try {
        if ($configSistema['SvrTip'] === 'sqlite') {
            $pdo = new PDO('sqlite:' . $dbPath);
        } elseif ($configSistema['SvrTip'] === 'mysql') {
            $dsn = "mysql:host=" . $configSistema['SvrSisDir'] . ";dbname=" . $configSistema['SvrSisDbf'] . ";charset=utf8mb4";
            $pdo = new PDO($dsn, $configSistema['SvrSisUsr'], $configSistema['SvrSisPas']);
        } elseif ($configSistema['SvrTip'] === 'pgsql') {
            $dsn = "pgsql:host=" . $configSistema['SvrSisDir'] . ";dbname=" . $configSistema['SvrSisDbf'];
            $pdo = new PDO($dsn, $configSistema['SvrSisUsr'], $configSistema['SvrSisPas']);
        } elseif ($configSistema['SvrTip'] === 'sqlsrv') {
            $host = $configSistema['SvrSisDir'];
            $db   = $configSistema['SvrSisDbf'];
            $driversDisponibles = PDO::getAvailableDrivers();
            if (in_array('sqlsrv', $driversDisponibles, true)) {
                $dsn = "sqlsrv:Server=$host;Database=$db;TrustServerCertificate=true;Encrypt=false";
                $pdo = new PDO($dsn, $configSistema['SvrSisUsr'], $configSistema['SvrSisPas']);
            } elseif (in_array('dblib', $driversDisponibles, true)) {
                $dsn = "dblib:host=$host;dbname=$db;charset=utf8";
                $pdo = new PDO($dsn, $configSistema['SvrSisUsr'], $configSistema['SvrSisPas']);
            } elseif (in_array('odbc', $driversDisponibles, true)) {
                $dsn = "odbc:Driver={ODBC Driver 18 for SQL Server};Server=$host;Database=$db;TrustServerCertificate=yes;Encrypt=no;";
                $pdo = new PDO($dsn, $configSistema['SvrSisUsr'], $configSistema['SvrSisPas']);
            } else {
                $dsn = "sqlsrv:Server=$host;Database=$db;TrustServerCertificate=true;Encrypt=false";
                $pdo = new PDO($dsn, $configSistema['SvrSisUsr'], $configSistema['SvrSisPas']);
            }
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Si no se pasaron columnas configuradas, autodescubrirlas de la BD[cite: 8]
        if (empty($columnas) && !empty($tabla)) {
            if ($configSistema['SvrTip'] === 'sqlite') {
                $stmtCols = $pdo->query("PRAGMA table_info(\"$tabla\")");
                $colsRaw = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
                foreach ($colsRaw as $c) {
                    $columnas[] = $c['name'];
                    $cabecera[] = $c['name'];
                    $ancho[] = ($c['pk'] > 0 ? '70px' : '150px');
                    if ($c['pk'] > 0 && empty($pkName)) $pkName = $c['name'];
                }
            } elseif ($configSistema['SvrTip'] === 'mysql') {
                $stmtCols = $pdo->query("SHOW COLUMNS FROM `$tabla`");
                $colsRaw = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
                foreach ($colsRaw as $c) {
                    $columnas[] = $c['Field'];
                    $cabecera[] = $c['Field'];
                    $ancho[] = ($c['Key'] === 'PRI' ? '70px' : '150px');
                    if ($c['Key'] === 'PRI' && empty($pkName)) $pkName = $c['Field'];
                }
            } elseif ($configSistema['SvrTip'] === 'pgsql') {
                $stmtCols = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position ASC");
                $stmtCols->execute([$tabla]);
                $columnas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
                $cabecera = $columnas;
                $ancho = array_fill(0, count($columnas), '150px');
            } elseif ($configSistema['SvrTip'] === 'sqlsrv') {
                $stmtCols = $pdo->prepare("SELECT c.name FROM sys.columns c INNER JOIN sys.tables t ON c.object_id = t.object_id WHERE t.name = ? ORDER BY c.column_id ASC");
                $stmtCols->execute([$tabla]);
                $columnas = $stmtCols->fetchAll(PDO::FETCH_COLUMN);
                $cabecera = $columnas;
                $ancho = array_fill(0, count($columnas), '150px');
            }
        }
        if (empty($pkName) && !empty($columnas)) {
            $pkName = in_array('NumReg', $columnas, true) ? 'NumReg' : $columnas[0];
        }

        $highlightId    = $_GET['highlight'] ?? null;
        $whereClauses   = [];
        $paramsQuery    = [];
        $busquedaActiva = $_GET['accion_busqueda'] ?? 'filtrar';

        $qTbl = ($configSistema['SvrTip'] === 'mysql') ? '`' . $tabla . '`' : (($configSistema['SvrTip'] === 'sqlsrv') ? '[' . $tabla . ']' : '"' . $tabla . '"');
        $qPk  = ($configSistema['SvrTip'] === 'mysql') ? '`' . $pkName . '`' : (($configSistema['SvrTip'] === 'sqlsrv') ? '[' . $pkName . ']' : '"' . $pkName . '"');

        // Configuración de tamaño de página (20 líneas por página)[cite: 8, 9]
        $regPorPag = (int)($configSistema['RegPorPag'] ?? 20);
        if ($regPorPag <= 0) {
            $regPorPag = 20;
        }

        // Ordenación[cite: 8]
        $colOrden = $_GET['orden'] ?? $pkName;
        $dirOrden = strtoupper($_GET['dir'] ?? 'ASC');
        if (!in_array($colOrden, $columnas, true)) {
            $colOrden = $pkName;
        }
        if ($dirOrden !== 'DESC') {
            $dirOrden = 'ASC';
        }
        $qColOrden = ($configSistema['SvrTip'] === 'mysql') ? "`$colOrden`" : (($configSistema['SvrTip'] === 'sqlsrv') ? "[$colOrden]" : '"' . $colOrden . '"');

        $paginaCalculadaPorBuscar = null;

        // 1. GESTIÓN DE FILTRO Y BÚSQUEDA[cite: 8]
        if (!empty($_GET['f']) && is_array($_GET['f'])) {
            if ($busquedaActiva === 'filtrar' || $busquedaActiva === '') {
                foreach ($_GET['f'] as $colKey => $val) {
                    $valStr = trim((string)$val);
                    if ($valStr !== '' && in_array($colKey, $columnas, true)) {
                        $qCol = ($configSistema['SvrTip'] === 'mysql') ? "`$colKey`" : (($configSistema['SvrTip'] === 'sqlsrv') ? "[$colKey]" : "\"$colKey\"");
                        $whereClauses[] = "$qCol LIKE ?";
                        $paramsQuery[]  = '%' . $valStr . '%';
                    }
                }
            } elseif ($busquedaActiva === 'buscar') {
                $findWhere  = [];$findParams = [];
                foreach ($_GET['f'] as $colKey =>$val) {
                    $valStr = trim((string)$val);
                    if ($valStr !== '' && in_array($colKey,$columnas, true)) {
                        $qCol = ($configSistema['SvrTip'] === 'mysql') ? "`$colKey`" : (($configSistema['SvrTip'] === 'sqlsrv') ? "[$colKey]" : "\"$colKey\"");
                        $findWhere[]  = "$qCol LIKE ?";
                        $findParams[] = '%' . $valStr . '%';
                    }
                }

                if (!empty($findWhere)) {
                    $sqlFind = "SELECT $qPk, $qColOrden FROM$qTbl WHERE " . implode(' AND ', $findWhere) . " ORDER BY $qColOrden$dirOrden, $qPk$dirOrden";
                    if ($configSistema['SvrTip'] === 'sqlsrv') {
                        $sqlFind = "SELECT TOP 1 $qPk, $qColOrden FROM$qTbl WHERE " . implode(' AND ', $findWhere) . " ORDER BY $qColOrden$dirOrden, $qPk$dirOrden";
                    } else {
                        $sqlFind .= " LIMIT 1";
                    }

                    $stmtFind = $pdo->prepare($sqlFind);
                    $stmtFind->execute($findParams);
                    $rowFound =$stmtFind->fetch(PDO::FETCH_ASSOC);

                    if ($rowFound) {
                        $highlightId =$rowFound[$pkName];$foundSortVal = $rowFound[$colOrden];

                        if ($colOrden === $pkName) {$opComp = ($dirOrden === 'ASC') ? '<=' : '>=';$sqlPos = "SELECT COUNT(*) FROM $qTbl WHERE $qPk$opComp ?";
                            $stmtPos = $pdo->prepare($sqlPos);
                            $stmtPos->execute([$highlightId]);
                        } else {
                            if ($dirOrden === 'ASC') {$sqlPos = "SELECT COUNT(*) FROM $qTbl WHERE ($qColOrden < ? OR ($qColOrden = ? AND $qPk <= ?))";
                            } else {
                                $sqlPos = "SELECT COUNT(*) FROM $qTbl WHERE ($qColOrden > ? OR ($qColOrden = ? AND $qPk >= ?))";
                            }
                            $stmtPos = $pdo->prepare($sqlPos);
                            $stmtPos->execute([$foundSortVal, $foundSortVal,$highlightId]);
                        }
                        $filaPosicion = (int)$stmtPos->fetchColumn();$paginaCalculadaPorBuscar = max(1, (int)ceil($filaPosicion / $regPorPag));
                    } else {
                        $mensaje = '<div class="alert alert-warning">⚠️ No se encontró ningún registro que coincida con la búsqueda en toda la tabla.</div>';
                    }
                }
            }
        }

        // Total de registros sin filtrar[cite: 8]
        $stmtCountTotal = $pdo->query('SELECT COUNT(*) FROM ' .$qTbl);
        $totalRegistros = (int)$stmtCountTotal->fetchColumn();

        // Total de registros que cumplen los filtros actuales[cite: 8]
        $sqlCountFiltrados = 'SELECT COUNT(*) FROM ' .$qTbl;
        if (!empty($whereClauses)) {
            $sqlCountFiltrados .= ' WHERE ' . implode(' AND ', $whereClauses);
        }
        $stmtCountFiltrados = $pdo->prepare($sqlCountFiltrados);
        $stmtCountFiltrados->execute($paramsQuery);
        $totalFiltrados = (int)$stmtCountFiltrados->fetchColumn();

        // Cálculo de páginas[cite: 8]
        $totalPaginas = max(1, (int)ceil($totalFiltrados / $regPorPag));
        $pagina = ($paginaCalculadaPorBuscar !== null) ? $paginaCalculadaPorBuscar : max(1, (int)($_GET['pagina'] ?? 1));
        if ($pagina >$totalPaginas) {
            $pagina =$totalPaginas;
        }
        $offset = ($pagina - 1) *$regPorPag;

        // Parámetros y enlaces de paginación[cite: 8]
        $queryParams =$_GET;
        $urlFirst = '?' . http_build_query(array_merge($queryParams, ['pagina' => 1]));
        $urlPrev  = '?' . http_build_query(array_merge($queryParams, ['pagina' => max(1, $pagina - 1)]));$urlNext  = '?' . http_build_query(array_merge($queryParams, ['pagina' => min($totalPaginas, $pagina + 1)]));$urlLast  = '?' . http_build_query(array_merge($queryParams, ['pagina' =>$totalPaginas]));

        // Consulta de registros[cite: 8]
        $IncluirNumReg =$columnas;
        if (!in_array('NumReg', $IncluirNumReg, true)) {
            $IncluirNumReg = array_merge($columnas, ['NumReg']);
        }
        $sqlAll = 'SELECT ' . implode(', ', array_map(function($c) use ($configSistema) {
            return ($configSistema['SvrTip'] === 'mysql') ? "`$c`" : (($configSistema['SvrTip'] === 'sqlsrv') ? "[$c]" : "\"$c\"");
        }, $IncluirNumReg)) . ' FROM ' .$qTbl;

        if (!empty($whereClauses)) {
            $sqlAll .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $sqlAll .= " ORDER BY $qColOrden$dirOrden";

        if ($configSistema['SvrTip'] === 'sqlsrv') {
            $sqlAll .= " OFFSET $offset ROWS FETCH NEXT $regPorPag ROWS ONLY";
        } else {
            $sqlAll .= " LIMIT $regPorPag OFFSET $offset";
        }

        $stmtAll = $pdo->prepare($sqlAll);
        $stmtAll->execute($paramsQuery);
        $rows =$stmtAll->fetchAll(PDO::FETCH_ASSOC);
        $numFiltrados = count($rows);

    } catch (Exception $e) {$mensaje = '<div class="alert alert-danger"><strong>Error BD:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';$totalRegistros = 0;
        $totalFiltrados = 0;
        $totalPaginas = 1;
        $pagina = 1;
        $offset = 0;
        $numFiltrados = 0;
        $rows = [];
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yos_Brw - <?= htmlspecialchars($tabla) ?></title>
    <style>
    h1 { color: var(--primary); margin: 0; font-size: 18px; display: flex; align-items: center; justify-content: space-between; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 6px; }
    .top-actions-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 6px; padding: 4px 10px; background: #f8fafc; border: 1px solid var(--border); border-radius: 5px; }
    table { width: 100%; border-collapse: collapse; background: #fff; table-layout: fixed; }

    th, td { border: 1px solid var(--border); padding: 3px 6px; font-size: 12px; text-align: left; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    th { background: #eef2f7; color: #495057; font-weight: 600; }
    th a { color: inherit; text-decoration: none; display: flex; align-items: center; justify-content: space-between; }
    th a:hover { color: var(--primary); }
    tr:nth-child(even) { background-color: #f8fafc; }
    tr:hover { background-color: #f1f5f9; }
    .search-row td { background: #fdfdfe; padding: 2px 4px; }
    .search-row input { width: 100%; height: 22px; padding: 2px 5px; font-size: 11px; border: 1px solid #cbd5e1; border-radius: 3px; box-sizing: border-box; }
    .search-row input:focus { border-color: var(--primary); outline: none; background: #fff; }
    .highlight { background-color: #fff3cd !important; font-weight: bold; }
    .btn { padding: 4px 10px; font-size: 12px; font-weight: 600; text-decoration: none; background: var(--primary); color: #fff; border-radius: 4px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; text-align: center; }
    .btn:hover { opacity: 0.9; }
    .btn-success { background: var(--success); }
    .btn-warning { background: var(--warning); color: #212529; }
    .btn-danger { background: var(--danger); }
    .btn-secondary { background: var(--secondary); }
    .btn-info { background: var(--info); color: #fff; }
    .btn-sm { padding: 2px 5px; font-size: 11px; line-height: 1; }
    .alert { padding: 6px 12px; border-radius: 4px; margin-bottom: 8px; font-size: 12px; }
    .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    th.col-acciones,
    td.col-acciones {
        width: 140px !important;
        min-width: 140px !important;
        max-width: 140px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: clip !important;
        text-align: center !important;
    }

    td.col-acciones .btn,
    td.col-acciones button,
    td.col-acciones a {
        display: inline-block;
        margin: 0 2px;
    }
    </style>
    </head>
    <body>
    <div class="card">
        <div class="top-bar">
            <h1 style="margin: 0;">📊 <?= htmlspecialchars($tabla) ?></h1>
            <p style="margin: 0; font-size: 12px; color: #4b5563;">
                <strong>Registros :</strong> <?= $totalRegistros ?>
                <?php if (!empty($whereClauses)): ?>
                    | <span style="color: #0066cc; font-weight: bold;">Filtrados : <?= $totalFiltrados ?></span>
                <?php endif; ?>
                | Mostrando <strong><?= count($rows) ?></strong> líneas
            </p>
        </div>

        <?= $mensaje ?>

        <!-- FORMULARIO DE FILTRADO Y BÚSQUEDA -->
        <form method="GET" id="formBrowse" action="<?= htmlspecialchars($scriptActual) ?>">
            <input type="hidden" name="db" value="<?= htmlspecialchars($dbPath) ?>">
            <input type="hidden" name="tabla" value="<?= htmlspecialchars($tabla) ?>">
            <input type="hidden" name="accion_busqueda" id="accionBusquedaInput" value="filtrar">
            <input type="hidden" name="orden" value="<?= htmlspecialchars($colOrden) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dirOrden) ?>">

            <!-- BARRA SUPERIOR DE BOTONES DE ACCIÓN Y PAGINACIÓN -->
            <div class="top-actions-bar">
                <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                    <button type="button" onclick="ejecutarAccionBusqueda('filtrar')" class="btn btn-success" title="⚡ Filtrar Registros que coincidan con los criterios">
                        ⚡ Filtrar Registros
                    </button>
                    <button type="button" onclick="ejecutarAccionBusqueda('buscar')" class="btn btn-warning" title="🔍 Buscar Posición (Resalta el primer registro coincidente)">
                        🔍 Buscar Posición
                    </button>
                    <a href="<?= htmlspecialchars($scriptActual) ?>?db=<?= urlencode($dbPath) ?>&tabla=<?= urlencode($tabla) ?>" class="btn btn-secondary" title="🔄 Limpiar todos los filtros y recargar">
                        🔄 Limpiar Filtros
                    </a>
                </div>

                <?php if ($totalPaginas > 1): ?>
                <div style="display: flex; gap: 4px; align-items: center; font-size: 12px;">
                    <span style="color: #6c757d; margin-right: 2px;">Pág. <strong><?= $pagina ?></strong> de <strong><?= $totalPaginas ?></strong>:</span>
                    <a href="<?= $urlFirst ?>" class="btn btn-secondary btn-sm" style="<?= ($pagina <= 1) ? 'pointer-events:none; opacity:0.5;' : '' ?>" title="Primera Página">⏮️</a>
                    <a href="<?= $urlPrev ?>" class="btn btn-secondary btn-sm" style="<?= ($pagina <= 1) ? 'pointer-events:none; opacity:0.5;' : '' ?>" title="Página Anterior">◀️</a>
                    <span style="display: inline-flex; align-items: center; padding: 2px 6px; font-weight: bold; background: #fff; border: 1px solid var(--border); border-radius: 3px; font-size: 11px;">
                        <?= $pagina ?> / <?= $totalPaginas ?>
                    </span>
                    <a href="<?= $urlNext ?>" class="btn btn-secondary btn-sm" style="<?= ($pagina >=$totalPaginas) ? 'pointer-events:none; opacity:0.5;' : '' ?>" title="Página Siguiente">▶️</a>
                    <a href="<?= $urlLast ?>" class="btn btn-secondary btn-sm" style="<?= ($pagina >=$totalPaginas) ? 'pointer-events:none; opacity:0.5;' : '' ?>" title="Última Página">⏭️</a>
                </div>
                <?php endif; ?>
            </div>

            <div class="table-container" id="browseTableContainer">
                <table id="tablaBrowse">
                    <colgroup id="browseColGroup">
                        <col class="col-acciones" data-ancho="<?= htmlspecialchars($Acciones_tamano) ?>" style="width: <?= htmlspecialchars($Acciones_tamano) ?>; min-width: <?= htmlspecialchars($Acciones_tamano) ?>; max-width: <?= htmlspecialchars($Acciones_tamano) ?>;">
                        <?php foreach ($columnas as$i => $col):$w = trim((string)($ancho[$i] ?? '150px'));
                            if ($w === '')$w = '150px';
                        ?>
                            <col data-ancho="<?= htmlspecialchars($w) ?>" style="<?= !str_ends_with($w, '%') ? 'width: ' . htmlspecialchars($w) . ';' : '' ?>">
                        <?php endforeach; ?>
                    </colgroup>

                    <!-- FILA DE BÚSQUEDA Y FILTRADO -->
                    <tr class="search-row">
                        <td class="col-acciones">
                            <a href="<?= htmlspecialchars($tabla) ?>_CRUD.php?db=<?= urlencode($dbPath) ?>&tabla=<?= urlencode($tabla) ?>&modo=nuevo" class="btn btn-success btn-sm" style="padding: 0 6px; height: 22px; display: flex; align-items: center; justify-content: center; width: 100%; box-sizing: border-box;" title="➕ Crear Nuevo Registro">➕ Nuevo</a>
                        </td>
                        <?php foreach ($columnas as$i => $col):$valFiltro = $_GET['f'][$col] ?? '';
                        ?>
                            <td>
                                <input type="text" name="f[<?= htmlspecialchars($col) ?>]" value="<?= htmlspecialchars($valFiltro) ?>" placeholder="Filtrar..." autocomplete="off">
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- ENCABEZADOS CON ORDENACIÓN -->
                    <tr>
                        <th class="col-acciones">Acciones</th>
                        <?php foreach ($columnas as $i =>$col):
                            $cab =$cabecera[$i] ?? $col;
                            $nextDir = ($colOrden === $col &&$dirOrden === 'ASC') ? 'DESC' : 'ASC';
                            $flecha = ($colOrden === $col) ? ($dirOrden === 'ASC' ? ' 🔼' : ' 🔽') : '';
                        ?>
                            <th>
                                <a href="?db=<?= urlencode($dbPath) ?>&tabla=<?= urlencode($tabla) ?>&orden=<?= urlencode($col) ?>&dir=<?=$nextDir ?><?= !empty($_GET['f']) ? '&' . http_build_query(['f' => $_GET['f']]) : '' ?>">
                                    <?= htmlspecialchars($cab) ?><?=$flecha ?>
                                </a>
                            </th>
                        <?php endforeach; ?>
                    </tr>

                    <!-- FILAS DE DATOS -->
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?= count($columnas) + 1 ?>" style="text-align: center; padding: 20px; color: #6c757d;">
                                No se encontraron registros en esta tabla.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row):$idVal = $row[$pkName] ?? '';
                            $isHighlight = (!empty($highlightId) && (string)$idVal === (string)$highlightId);
                        ?>
                            <tr class="<?= $isHighlight ? 'highlight' : '' ?>" <?= $isHighlight ? 'id="encontrado"' : '' ?>>
                                <td class="col-acciones">
                                    <a href="<?= urlencode($tabla) ?>_CRUD.php?db=<?= urlencode($dbPath) ?>&tabla=<?= urlencode($tabla) ?>&modo=ver&id=<?= urlencode($idVal) ?>" class="btn btn-info btn-sm" title="Ver Detalle">👁️</a>
                                    <a href="<?= urlencode($tabla) ?>_CRUD.php?db=<?= urlencode($dbPath) ?>&tabla=<?= urlencode($tabla) ?>&modo=editar&id=<?= urlencode($idVal) ?>" class="btn btn-sm" title="Editar">✏️</a>
                                    <a href="<?= urlencode($tabla) ?>_CRUD.php?db=<?= urlencode($dbPath) ?>&tabla=<?= urlencode($tabla) ?>&modo=eliminar&id=<?= urlencode($idVal) ?>" class="btn btn-danger btn-sm" title="Eliminar Registro">🗑️</a>
                                </td>
                                <?php foreach ($columnas as$i => $col):$rawVal = (string)($row[$col] ?? '');
                                    $cleanVal = preg_replace('/[\r\n\t]+/', ' ',$rawVal);
                                ?>
                                    <td title="<?= htmlspecialchars($rawVal) ?>"><?= htmlspecialchars($cleanVal) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        </form>
    </div>

    <script>
    function ejecutarAccionBusqueda(accion) {
        document.getElementById('accionBusquedaInput').value = accion;
        document.getElementById('formBrowse').submit();
    }

    function ajustarAnchosBrowse() {
        const container = document.getElementById('browseTableContainer');
        const table = document.getElementById('tablaBrowse');
        const cols = document.querySelectorAll('#browseColGroup col');
        if (!container || !table || !cols.length) return;

        const availableWidth = container.clientWidth - 1;

        const accionesTamStr = '<?= htmlspecialchars($Acciones_tamano) ?>';
        const a = parseFloat(accionesTamStr) || 115;

        let fixedTotalPx = a;
        let totalPercent = 0;
        const percentCols = [];

        cols.forEach((col, idx) => {
            if (idx === 0) {
                col.style.width = a + 'px';
                col.style.minWidth = a + 'px';
                col.style.maxWidth = a + 'px';
                return;
            }

            const def = (col.getAttribute('data-ancho') || '').trim();
            if (def.endsWith('%')) {
                const pct = parseFloat(def) || 0;
                totalPercent += pct;
                percentCols.push({ col, pct, idx });
            } else {
                let px = parseFloat(def);
                if (isNaN(px) || px <= 0) {
                    px = 150;
                }
                fixedTotalPx += px;
                col.style.width = px + 'px';
                col.style.minWidth = px + 'px';
                col.style.maxWidth = px + 'px';
            }
        });

        const b = availableWidth - fixedTotalPx;

        if (percentCols.length > 0) {
            const minPorCol = 40;

            if (b > (percentCols.length * minPorCol)) {
                let allocated = 0;
                percentCols.forEach((item, i) => {
                    let w;
                    if (i === percentCols.length - 1) {
                        w = b - allocated;
                    } else {
                        w = Math.round(b * (item.pct / (totalPercent || 100)));
                        allocated += w;
                    }
                    item.col.style.width = w + 'px';
                    item.col.style.minWidth = w + 'px';
                    item.col.style.maxWidth = w + 'px';
                });
                table.style.width = availableWidth + 'px';
            } else {
                percentCols.forEach(item => {
                    item.col.style.width = '100px';
                    item.col.style.minWidth = '100px';
                    item.col.style.maxWidth = '100px';
                });
                table.style.width = (fixedTotalPx + percentCols.length * 100) + 'px';
            }
        } else {
            if (fixedTotalPx > availableWidth) {
                table.style.width = fixedTotalPx + 'px';
            } else {
                table.style.width = '100%';
            }
        }
    }
    window.addEventListener('load', ajustarAnchosBrowse);
    window.addEventListener('resize', ajustarAnchosBrowse);
    </script>
    </body>
    </html>
    <?php
}
