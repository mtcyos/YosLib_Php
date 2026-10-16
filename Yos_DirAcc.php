<?php
# -*- coding: utf-8 -*-
// Evitamos el acceso directo al archivo de configuración
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    exit('Acceso denegado.');
}

function Yos_DirAcc($Mem_Apl) {
//    echo 'Mem_Apl='.$Mem_Apl.'<br>';
//    echo 'Yos_DirAcc'.'<br>';

    // Si el usuario hace clic en cerrar sesión, llamamos a la función de cierre
    if (isset($_GET['logout'])) {
        Yos_DirAccCie($Mem_Apl);
    }

    // Recogemos el array de usuarios que devuelve la función de configuración
    $usuarios_permitidos = Yos_DirAccCfg($Mem_Apl);

    $usuario_ingresado = $_SERVER['PHP_AUTH_USER'] ?? '';
    $clave_ingresada   = $_SERVER['PHP_AUTH_PW']   ?? '';

    // Verificamos si el usuario existe y si la contraseña coincide con su hash
    if (!isset($usuarios_permitidos[$usuario_ingresado]) ||
        !password_verify($clave_ingresada, $usuarios_permitidos[$usuario_ingresado])) {

        header('WWW-Authenticate: Basic realm="' . $Mem_Apl . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Acceso denegado. Credenciales incorrectas.';
        exit;
    }

    if (isset($_SERVER['PHP_AUTH_USER'])) {
        $usuarioActual = htmlspecialchars($_SERVER['PHP_AUTH_USER'], ENT_QUOTES, 'UTF-8');

        echo '<div class="user-bar">';
        echo '<strong>'.$Mem_Apl.'</strong> - Conectado como: <strong>' . $usuarioActual . '</strong> | ';
        echo '<a href="?logout=1" style="color: red; text-decoration: none;">[ Cerrar sesión ]</a>';
        echo '</div>';
    //    echo $_SERVER['PHP_AUTH_PW'].'<br>';
    }
}

// Función para el cierre de sesión (Logout) corregida
// Función para el cierre de sesión (Logout) corregida para forzar el olvido en el navegador
function Yos_DirAccCie($Mem_Apl) {
    // 1. Enviamos las cabeceras para forzar la expiración/credenciales inválidas
    header('WWW-Authenticate: Basic realm="' . $Mem_Apl . '"');
    header('HTTP/1.0 401 Unauthorized');

    // 2. Truco para limpiar la caché del navegador: redirigir usando credenciales vacías en la URL (usuario falso:contraseña@dominio)
    // Esto obliga al navegador a sobrescribir las credenciales guardadas con unas erróneas.
    $protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = strtok($_SERVER['REQUEST_URI'], '?'); // Ruta limpia sin ?logout=1

    // Forzamos un usuario nulo en la URL para limpiar el almacenamiento temporal del navegador
    header('Location: ' . $protocolo . '://logout@' . $host . $uri);
    exit;
}
// FIN

function Yos_DirAccCfg($Mem_Apl) {
    $usuarios = []; // Definimos por defecto
    // para generar Bcrypt
    //echo password_hash("", PASSWORD_DEFAULT).'<br>';
    switch ($Mem_Apl) {
            case 'YosCtr':
                $usuarios = [
                    'Admin'  => '$2y$12$aEJAU0BKd2Qjx3sSgvd1f.pE0TbZmZfzVZmLRwVv8xwDyuieX4c1e',
                    'mtcyos' => '$2y$12$i898tSB0fnINDhNrZcrV6O51yLgfFv49Hlo0teRXoUQoRZcdVfHSK',
                    'ronald' => '$2y$12$dCSwbMNUctjsZIPohqPzqeODj7a/zuA7rkBx5wxUJA6b91FI/e096'
                ];
                break;

            default:
                $usuarios = [];
                break;
    }

    return $usuarios;
}

function Yos_DirAcc_Vfy($Mem_Apl) {
    // 1. Recogemos los usuarios permitidos para esta aplicación
    $usuarios_permitidos = Yos_DirAccCfg($Mem_Apl);

    // 2. Obtenemos las credenciales enviadas por el navegador
    $usuario_ingresado = $_SERVER['PHP_AUTH_USER'] ?? '';
    $clave_ingresada   = $_SERVER['PHP_AUTH_PW']   ?? '';

    // 3. Verificamos si las credenciales son incorrectas o faltan
    if (!isset($usuarios_permitidos[$usuario_ingresado]) ||
        !password_verify($clave_ingresada, $usuarios_permitidos[$usuario_ingresado])) {

        // Si no está identificado, lanzamos el reto HTTP Basic y salimos con exit
        header('WWW-Authenticate: Basic realm="' . $Mem_Apl . '"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Acceso denegado. Se requiere autenticación.';
        exit; // Detiene completamente la ejecución de la aplicación
    }

    // Si todo es correcto, la función simplemente retorna (o puedes devolver true)
    return true;
}
