<?php
# -*- coding: utf-8 -*-
function Yos_SesIni(array $AplCfg): void {
//echo "Yos_SesIni()<br>";
    $Apl = $AplCfg['Apl'];
    $Nom = $AplCfg['Nom'];
    $Etn = $AplCfg['Etn'];
    $Dir = $AplCfg['Dir'];
//echo "Apl:=".$Apl."<br>";
//echo "Nom:=".$Nom."<br>";
//echo "Etn:=".$Etn."<br>";
//echo "Dir:=".$Dir."<br>";

    //******************************************************************************************** Datos del SERVIDOR
    $_SESSION['Svr']['Sop'] = php_uname('s') . ' ' . php_uname('r');
    $_SESSION['Svr']['Sop_Def']    = "(Ro) Sistema Operativo del Servidor";

    // Definimos el entorno en la sesión según lo que leyó del .env
    $serverIp = $_SERVER['SERVER_ADDR'] ?? $_SERVER['HTTP_HOST'] ?? '';
    $_SESSION['Svr']['Amb'] = (str_contains($serverIp, '172.16.111.21')) ? 'Prg' : 'Prd';
    $_SESSION['Svr']['Amb_Def'] = "(Ro) Ambiente del Servidor";
    $_SESSION['Svr']['Amb_Nom'] = ($_SESSION['Svr']['Amb'] === 'Prg') ? 'Programacion' : 'Produccion';
    $_SESSION['Svr']['Amb_Nom_Def'] = "(Ro) Ambiente del Servidor";

    $_SESSION['Svr']['Nom']        = gethostname();
    $_SESSION['Svr']['Nom_Def']    = "(Ro) Nombre del Servidor";

    $_SESSION['Svr']['Hst'] = $_SERVER['HTTP_HOST'] ?? '';
    $_SESSION['Svr']['Hst_Def'] = '(Ro) Host o Dominio';

    $_SESSION['Svr']['Ipd'] = gethostbyname(gethostname());
    $_SESSION['Svr']['Ipd_Def'] = '(Ro) Ip del Servidor';

    $_SESSION['Svr']['Ptc']     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $_SESSION['Svr']['Ptc_Def'] = '(Ro) Protocolo';

    $_SESSION['Svr']['Puo']     = $_SERVER['SERVER_PORT']?? '';
    $_SESSION['Svr']['Puo_Def'] = '(Ro) Puerto';

    $_SESSION['Svr']['Dir'] = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') . '/';
    $_SESSION['Svr']['Dir_Def'] = '(Ro) Directorio';

    $_SESSION['Svr']['Www'] = $_SESSION['Svr']['Ptc']. $_SESSION['Svr']['Ipd'].'/';
    $_SESSION['Svr']['Www_Def'] = '(Ro) Direccion Web';

    $_SESSION['Svr']['ZonHor']     = 'America/Tegucigalpa';
    $_SESSION['Svr']['ZonHor_Def'] = "Identificador de Zona Horaria de la Base de Datos IANA. Continente/Ciudad , Busque en https://www.php.net/manual/es/timezones.php";
    date_default_timezone_set($_SESSION['Svr']['ZonHor']);

    $_SESSION['Svr']['Tmp'] = rtrim(str_replace('\\', '/', sys_get_temp_dir()), '/') . '/';
    $_SESSION['Svr']['Tmp_Def'] = '(Ro) Directorio Temporal';

    //******************************************************************************************** FIN - Datos del SERVIDOR

    //******************************************************************************************** Datos del Entorno

    // Usuario, solo se crea una vez , para no destruir si ya hizo Login
    if (!isset($_SESSION[$Etn]['Usr_Nik'])) {
        $_SESSION[$Etn]['Usr_Nik']     = "";
        $_SESSION[$Etn]['Usr_Nik_Def'] = "(Ro) Nick del Usuario";
        $_SESSION[$Etn]['Usr_Nom']     = "";
        $_SESSION[$Etn]['Usr_Nom_Def'] = "(Ro) Nombre del Usuario";
        $_SESSION[$Etn]['Usr_Pas']     = "";
        $_SESSION[$Etn]['Usr_Pas_Def'] = "(Ro) Contraseña del Usuario";
        $_SESSION[$Etn]['Usr_Niv']     = "";
        $_SESSION[$Etn]['Usr_Niv_Def'] = "(Ro) Nivel del Usuario";
        $_SESSION[$Etn]['Usr_Ipd']     = "";
        $_SESSION[$Etn]['Usr_Ipd_Def'] = "(Ro) Direccion Ip del Usuario";
        $_SESSION[$Etn]['Usr_ZonHor']      = $_SESSION['Svr']['ZonHor'];
        $_SESSION[$Etn]['Usr_ZonHor_Def']  = "Identificador de Zona Horaria de la Base de Datos IANA. Continente/Ciudad.";

echo "CREAMOS SESION USUARIO<br>";
$_SESSION[$Etn]['Usr_Nik'] ="mtcyos";
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                // IP desde internet compartido
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // IP pasada por un proxy o balanceador de carga
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                // IP directa del cliente
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        $_SESSION[$Etn]['Usr_Ipd'] =$ip;
    }

    //******************************************************************************************** FIN - Datos del Entorno

    //******************************************************************************************** Datos de la Aplicacion
    $_SESSION[$Apl]['Etn']     = $Etn;
    $_SESSION[$Apl]['Etn_Def'] = '(Ro) Entorno de la Web';

    $_SESSION[$Apl]['Apl']     = $Apl;
    $_SESSION[$Apl]['Apl_Def'] = '(Ro) Nombre de la Web';

    $_SESSION[$Apl]['Nom']     = $Nom;
    $_SESSION[$Apl]['Nom_Def'] = 'Nombre de la Aplicacion';

    $_SESSION[$Apl]['Dir']     = $Dir;
    $_SESSION[$Apl]['Dir_Def'] = '(Ro) Directorio de la Web';

    $_SESSION[$Apl]['Dbg'] = "";
    $_SESSION[$Apl]['Dbg_Def'] = 'MODO DEPURACION (S/)';


    # DATOS DE LA APLICACION ORIGINAL
//    YosAplCfg_Def["Apl"]["Ori_Cod"] = "(Ro) Codigo  REAL de la Aplicacion"
//    YosAplCfg_Def["Apl"]["Ori_Nom"] = "(Ro) Nombre  REAL de la Aplicacion"
//    YosAplCfg_Def["Apl"]["Ori_Etn"] = "(Ro) Entorno REAL de la Aplicacion"

/**********************************************************************************************************/
//    $_SESSION[$Etn . 'Etn'] = $Etn;
//    $_SESSION[$Etn . $Apl . 'Dir'] = $Dir;

    $_SESSION[$Apl]['Atv']     = "";
    $_SESSION[$Apl]['Atv_Def'] = "(Ro) Programa actual en Curso";

/* Estructura Basica de Directorios
 * Bat: Almacena los scripts de lotes para automatizaciones, respaldos y llamadas a red.
 * Bdt: Contiene las bases de datos SQLite y los archivos de importación.
 * Cfg: Aloja la configuración centralizada y las matrices de la librería.
 * Img: Almacena los iconos, recursos visuales y elementos gráficos de las interfaces.
 * Reg: Utilizado para el registro de errores, trazabilidad y alertas del sistema.
 * Sql: Almacena los scripts de consultas, esquemas de tablas y rutinas de datos.
*/

//$dirPadre = base_path('../');
//echo "dirPadre=".$dirPadre."<br>";
//$dirPadre = dirname(base_path());

    $_SESSION[$Apl]['Dir_Dat']     = rtrim(str_replace('\\', '/', dirname($_SESSION['Svr']['Dir'], 2)), '/').'/'.$Apl.'/';
    $_SESSION[$Apl]['Dir_Dat_Def'] = "(Ro) Directorio de datos";

    $_SESSION[$Apl]['Dir_Bat']     = $_SESSION[$Apl]['Dir_Dat'] .'Bat/';
    $_SESSION[$Apl]['Dir_Bat_Def'] = "(Ro) Directorio .Bat";
    $_SESSION[$Apl]['Dir_Bdt']     = $_SESSION[$Apl]['Dir_Dat'] .'Bdt/';
    $_SESSION[$Apl]['Dir_Bdt_Def'] = "(Ro) Directorio Bdt bases de datos";
    $_SESSION[$Apl]['Dir_Cfg']     = $_SESSION[$Apl]['Dir_Dat'] .'Cfg/';
    $_SESSION[$Apl]['Dir_Cfg_Def'] = "(Ro) Directorio Cfg Configuracion";
    $_SESSION[$Apl]['Dir_Img']     = $_SESSION[$Apl]['Dir'] .'Img/';
    $_SESSION[$Apl]['Dir_Img_Def'] = "(Ro) Directorio Cfg Configuracion";
    $_SESSION[$Apl]['Dir_Reg']     = $_SESSION[$Apl]['Dir_Dat'] .'Reg/';
    $_SESSION[$Apl]['Dir_Reg_Def'] = "(Ro) Directorio Cfg Configuracion";
    $_SESSION[$Apl]['Dir_Sql']     = $_SESSION[$Apl]['Dir_Dat'] .'Sql/';
    $_SESSION[$Apl]['Dir_Sql_Def'] = "(Ro) Directorio Cfg Configuracion";

    // DATOS WEB Www
    $_SESSION[$Apl]['Www_Avs']            = "";
    $_SESSION[$Apl]['Www_Avs_Def']        = "AVISO A PIE DE PAGINA";
    $_SESSION[$Apl]['Www_Des']            = "";
    $_SESSION[$Apl]['Www_Des_Def']        = "TITULO PRINCIPAL";
    $_SESSION[$Apl]['Www_Des002']         = "";
    $_SESSION[$Apl]['Www_Des002_Def']    = "SUBTÍTULO";
    $_SESSION[$Apl]['Www_ImgDch']         = "";
    $_SESSION[$Apl]['Www_ImgDch_Def']    = "IMAGEN DERECHA";
    $_SESSION[$Apl]['Www_ImgDchWww']      = "";
    $_SESSION[$Apl]['Www_ImgDchWww_Def'] = "IMAGEN DERECHA ENLACE";
    $_SESSION[$Apl]['Www_ImgIzq']         = "";
    $_SESSION[$Apl]['Www_ImgIzq_Def']    = "IMAGEN IZQUIERDA";
    $_SESSION[$Apl]['Www_ImgIzqWww']      = "";
    $_SESSION[$Apl]['Www_ImgIzqWww_Def'] = "IMAGEN IZQUIERDA ENLACE";
    $_SESSION[$Apl]['Www_Not']            = "";
    $_SESSION[$Apl]['Www_Not_Def']        = "GESTION DE NOTICIAS (S/)";

    $_SESSION[$Apl]['Vsn']         = "";
    $_SESSION[$Apl]['Vsn_Def']     = "Version de la Aplicacion";
    $_SESSION[$Apl]['Cpy']         = "";
    $_SESSION[$Apl]['Cpy_Def']     = "Copyright de la aplicacion";
    $_SESSION[$Apl]['CpyEml']      = "";
    $_SESSION[$Apl]['CpyEml_Def'] = "Email del Autor de la Aplicacion";

$_SESSION[$Apl]['Lic'] = "Mit";
$_SESSION[$Apl]['Res']         = "";
$_SESSION[$Apl]['Res_Def']     = "Documento de DE RESPONSABILIDAD";


    $_SESSION[$Apl]['Enc_AdmPas']     = '$2a$12$kXQ4P3waPzyKX9a3X4gOMu9VtqNV1ZQBHo8JH.r.vv4L6Wv0N4KaK';
    $_SESSION[$Apl]['Enc_AdmPas_Def']     = "Contraseña del Administrador";

    $_SESSION[$Apl]['DirAch']     = "";
    $_SESSION[$Apl]['DirAch_Def']     = "Directorio de Almacenaje donde se guardan los Archivos";

// Datos de Usuario
    $_SESSION[$Apl]['Usr_Acc']     = "N";
    $_SESSION[$Apl]['Usr_Acc_Def'] = "Control de Acceso por Usuario (S/N)";
    $_SESSION[$Apl]['Usr_NumMaxAcc'] = 3;
    $_SESSION[$Apl]['Usr_NumMaxAcc_Def'] = "(n) Numero de intentos de Acceso del Usuario";

    // Datos Email
    $_SESSION[$Apl]['Eml_MsgCab']       = "";
    $_SESSION[$Apl]['Eml_MsgCab_Def']   = "CABECERA DEL MENSAJE";
    $_SESSION[$Apl]['Eml_MsgPie']       = "";
    $_SESSION[$Apl]['Eml_MsgPie_Def']   = "PIE DEL MENSAJE";


    //******************************************************************************************** FIN - Datos de la Aplicacion


/*
$_SESSION['Mnu']         = "";
$_SESSION['Mnu']     = "(Ro) Menu de la Aplicacion";
$_SESSION['MnuNom']      = "";
$_SESSION['MnuNom'] = "(Ro) Nombre del menu en uso";
$_SESSION['TitSub']      = "";
$_SESSION['TitSub'] = "(Ro) Muestra TITUTLO DEL SCRIPT , nombre del Script, Es para el menu";

$_SESSION['Fnc']     = [];
$_SESSION['Fnc'] = "(Ro) Lista de las Funciones de ./Script/*.py de la Aplicacion";

 */


/**********************************************************************************************************/

    return;
}

?>
