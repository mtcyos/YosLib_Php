<?php
# -*- coding: utf-8 -*-
//function YosLibIni(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar la cookie de sesión para que expire al cerrar el navegador
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']), // true si usas HTTPS
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        session_start();

    #    require_once $_SERVER['YosLib_Dir'] . 'Yos_Utl.php';

        //******************************************************************************************** Datos de YosLib
        // Definición de la configuración base para YosLib
        $_SESSION['YosLib']['Apl']         = "";
        $_SESSION['YosLib']['Apl_Def']     = "Codigo de la Libreria Yos";

        $_SESSION['YosLib']['Nom']         = "";
        $_SESSION['YosLib']['Nom_Def']     = "Nombre de la libreria";

        $_SESSION['YosLib']['Vsn']         = "";
        $_SESSION['YosLib']['Vsn_Def']     = "Version";

        $_SESSION['YosLib']['Atr']         = "";
        $_SESSION['YosLib']['Atr_Def']     = "Autor";

        $_SESSION['YosLib']['AtrEml']      = "mtcyos@yahoo.es";
        $_SESSION['YosLib']['AtrEml_Def']  = "Email Autor";

        $Mem_DirAch_json = str_replace('\\', '/', __DIR__) . '/YosLib_Cfg.json';
        Yos_SesIni_Jsn($Mem_DirAch_json, 'YosLib');

        // Valores calculados
        $_SESSION['YosLib']['Cpy'] = '© '.substr($_SESSION['YosLib']['Vsn'], 0, 4).' - <a href="mailto:'.$_SESSION['YosLib']['AtrEml'].'">'.$_SESSION['YosLib']['Atr'].'</a>';
        $_SESSION['YosLib']['Cpy'] = '© '.substr($_SESSION['YosLib']['Vsn'], 0, 4).' - <a href="mailto:'.$_SESSION['YosLib']['AtrEml'].'" target="_blank" rel="noopener noreferrer">'.$_SESSION['YosLib']['Atr'].'</a>';
        $_SESSION['YosLib']['Cpy'] = '© '.substr($_SESSION['YosLib']['Vsn'], 0, 4).' - '.$_SESSION['YosLib']['Atr'];

        $_SESSION['YosLib']['Cpy_Def'] = "Copyright";

        // Definición de directorios basados en la ruta real del servidor
        $_SESSION['YosLib']['Dir'] = str_replace('\\', '/', __DIR__) . '/';
        $_SESSION['YosLib']['Dir_Def'] = "Directorio";

        $_SESSION['YosLib']['Www'] = $_SESSION['Svr']['Www'] .'YosLib/';
        $_SESSION['YosLib']['Www_Def'] = "Direccioon WEb";
/*
    if (PHP_SAPI !== 'cli') {
        $protocol = request()->secure() ? "https://" : "http://";
        $host = request()->getHost();
        $scriptPath = "/YosLib/";

        $_SESSION['YosLib']['DirWeb'] = $protocol . $host . $scriptPath;
        $_SESSION['YosLib']['DirWeb_Def'] = "Direccion Web";
    }
*/
        //******************************************************************************************** FIN - Datos de YosLib

    }
//}
?>

