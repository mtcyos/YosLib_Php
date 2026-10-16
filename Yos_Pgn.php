<?php
# -*- coding: utf-8 -*-
/*
 * YosPgn - Funciones que definen la Pagina que se muestra en la web.
 */

function Yos_Pgn_Cab($AplCfg, $Msg_Hlp = NULL)
{
    // ============================================================
    // 1. VARIABLES DE CONFIGURACIÓN
    // ============================================================
    $Apl = $AplCfg['Apl'];
    $Nom = $AplCfg['Nom'];
    $Etn = $AplCfg['Etn'];
    $Dir = $AplCfg['Dir'];

    // Nombres de las imágenes (por si no están definidos en sesión)
    if (empty($_SESSION[$Apl]['Www_ImgIzq'])) {
        $_SESSION[$Apl]['Www_ImgIzq'] = "WwwImgIzq.png";
    }
    if (empty($_SESSION[$Apl]['Www_ImgDch'])) {
        $_SESSION[$Apl]['Www_ImgDch'] = "WwwImgDch.png";
    }

    // ============================================================
    // 2. CÁLCULO DEL ANCHO DE LAS IMÁGENES (altura fija 60px)
    // ============================================================
    $ancho_Izq = 60;
    $ancho_Dch = 60;

    $rutaImgIzq = $_SESSION[$Apl]['Dir_Img'] . $_SESSION[$Apl]['Www_ImgIzq'];
    if (file_exists($rutaImgIzq)) {
        list($ancho, $altura) = getimagesize($rutaImgIzq);
        if ($altura > 0) {
            $ancho_Izq = intval($ancho * 60 / $altura) + 13;
        }
    }

    $rutaImgDch = $_SESSION[$Apl]['Dir_Img'] . $_SESSION[$Apl]['Www_ImgDch'];
    if (file_exists($rutaImgDch)) {
        list($ancho, $altura) = getimagesize($rutaImgDch);
        if ($altura > 0) {
            $ancho_Dch = intval($ancho * 60 / $altura) + 13;
        }
    }

    // ¿Hay ayuda?
    $tieneAyuda = (!empty($Msg_Hlp) && !empty($Msg_Hlp['Txt']));

    // ============================================================
    // 3. CABECERA HTML
    // ============================================================
    echo '<!DOCTYPE html>';
    echo '<html lang="es">';
    echo '<head>';
    echo '<meta http-equiv="Content-Language" content="es-hn">';
    echo '<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">';
    echo '<title>';
    echo htmlspecialchars($_SESSION[$Apl]['Nom'] . ' ' . ($_SESSION[$Apl]['Vsn'] ?? ''), ENT_QUOTES, 'ISO-8859-1');
    echo '</title>';
    echo '<link rel="stylesheet" href="../YosLib/Yos_Brw.css">';

    echo '<style>';
/*
    echo ':root {';
    echo '    --primary: #0066cc;';
    echo '    --primary-hover: #004d99;';
    echo '    --success: #28a745;';
    echo '    --warning: #ffc107;';
    echo '    --danger: #dc3545;';
    echo '    --secondary: #6c757d;';
    echo '    --info: #17a2b8;';
    echo '    --bg: #f4f6f9;';
    echo '    --card: #ffffff;';
    echo '    --border: #dee2e6;';
    echo '}';
*/
    echo '* { box-sizing: border-box; }';
    echo 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; margin: 8px 12px; background: var(--bg); color: #333; }';
    echo '.card { background: var(--card); border-radius: 6px; padding: 10px 14px; box-shadow: 0 1px 6px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 8px; }';
    echo '.cab-img { height: 60px; width: auto; border: 0; }';
    echo '.cab-botones { display: inline-flex; align-items: center; gap: 10px; }';

    // --- Estilos del modal de ayuda ---
    echo '.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9999; align-items: center; justify-content: center; }';
    echo '.modal-overlay.activo { display: flex; }';
    echo '.modal-caja { background: #fff; border-radius: 8px; max-width: 700px; width: 90%; max-height: 80vh; overflow: auto; box-shadow: 0 8px 30px rgba(0,0,0,0.25); border: 1px solid #dee2e6; padding: 0; }';
    echo '.modal-cab { background: #0066cc; color: #fff; padding: 10px 16px; font-weight: bold; display: flex; justify-content: space-between; align-items: center; border-radius: 8px 8px 0 0; }';
    echo '.modal-cab button { background: transparent; border: 0; color: #fff; font-size: 22px; cursor: pointer; line-height: 1; padding: 0 4px; }';
    echo '.modal-cab button:hover { opacity: 0.8; }';
    echo '.modal-cuerpo { padding: 16px; font-size: 14px; line-height: 1.5; color: #333; }';
    echo '</style>';

echo '</head>';
    echo '<body style="text-align: left">';

    // ============================================================
    // 4. CONTENEDOR PRINCIPAL
    // ============================================================
    echo '<div class="card">';

    // Reloj
    if (!empty($_SESSION['YosLib']['Dir']) && file_exists($_SESSION['YosLib']['Dir'] . "/reloj.php")) {
        include($_SESSION['YosLib']['Dir'] . "/reloj.php");
    }

    // ============================================================
    // 5. TABLA DE CABECERA
    // ============================================================
    echo '<table style="text-align: left; width: 100%;" border="01" cellpadding="2" cellspacing="2">';
    echo '<tbody>';
    echo '  <tr>';

    // ---- Logo izquierdo ----
    echo '    <td style="width: ' . $ancho_Izq . 'px;">';
    echo '      <a href="' . htmlspecialchars($_SESSION['Prg']['WwwAplPri'] ?? '#', ENT_QUOTES) . '" target="_blank">';
    echo '        <img title="' . htmlspecialchars($_SESSION['Prg']['WwwAplPri'] ?? '', ENT_QUOTES) . '" border="0" src="Img/' . htmlspecialchars($_SESSION[$Apl]['Www_ImgIzq'], ENT_QUOTES) . '" class="cab-img">';
    echo '      </a>';
    echo '    </td>';

    // ---- Título central ----
    echo '    <td style="text-align: center;">';
    echo '      <font size="6">' . htmlspecialchars($_SESSION[$Apl]['Nom'] ?? '', ENT_QUOTES, 'ISO-8859-1') . '</font>';
    echo '      <font size="4"><br>' . htmlspecialchars($_SESSION[$Apl]['Www_Des002'] ?? '', ENT_QUOTES, 'ISO-8859-1') . '</font>';
    echo '    </td>';

    // ---- Logo derecho + botón ayuda ----
    if ($tieneAyuda) {
        $ancho_Dch += 40; // Espacio extra para el icono de ayuda
    }

    echo '    <td style="width: ' . $ancho_Dch . 'px;">';
    echo '      <div class="cab-botones">';

    // ----- Botón de Ayuda (ahora abre un modal, no recarga) -----
    if ($tieneAyuda) {
        echo '<a href="#" onclick="Yos_MostrarAyuda(); return false;" style="text-decoration: none;">';
        echo '  <img src="' . htmlspecialchars($_SESSION['YosLib']['Www'] ?? '', ENT_QUOTES) . 'Img/YosInf.png" title="Ayuda" style="width: 32px; height: 32px; display: block;" border="0">';
        echo '</a>';
    }

    // ----- Logo derecho -----
    echo '        <a href="' . htmlspecialchars($_SESSION['Prg']['WwwPat'] ?? '#', ENT_QUOTES) . '" target="_blank">';
    echo '          <img title="' . htmlspecialchars($_SESSION['Prg']['NomPat'] ?? '', ENT_QUOTES) . '" border="0" src="Img/' . htmlspecialchars($_SESSION[$Apl]['Www_ImgDch'], ENT_QUOTES) . '" class="cab-img">';
    echo '        </a>';

    echo '      </div>';
    echo '    </td>';

    echo '  </tr>';
    echo '</tbody>';
    echo '</table>';

    // ============================================================
    // 6. TABLA INFERIOR (reloj + fecha + zona de usuario)
    // ============================================================
    echo '<table border="1" width="100%" id="table6" cellpadding="1" cellspacing="1">';
    echo '  <tr>';

    // Reloj en vivo
    echo '    <td align="center" width="70">';
    echo '      <font size="2">';
    echo '        <span id="liveclock"></span>';
    echo '      </font>';
    echo '    </td>';

    // Fecha
    echo '    <td align="center" width="130">';
    echo '      <font size="2">';
    if (!empty($_SESSION['YosLib']['Dir']) && file_exists($_SESSION['YosLib']['Dir'] . "/fecha.php")) {
        include($_SESSION['YosLib']['Dir'] . "/fecha.php");
    }
    echo '      </font>';
    echo '    </td>';

    // Zona de usuario (comentada por ahora)
    echo '    <td>';
//    echo 'Aquí va tu bloque de login/logout cuando lo actives';
    // Aquí va tu bloque de login/logout cuando lo actives
    echo '    </td>';

    echo '  </tr>';
    echo '</table>';

    echo '</div>'; // Cierre del contenedor .card

    // ============================================================
    // 7. MODAL DE AYUDA (solo si hay ayuda)
    // ============================================================
    if ($tieneAyuda) {
        $tituloHtml = htmlspecialchars($Msg_Hlp['Tit'] ?? 'Ayuda', ENT_QUOTES, 'ISO-8859-1');
        $msgHtml    = $Msg_Hlp['Txt']; // HTML del mensaje (ya viene con <br>, <b>, etc.)

        echo '<div id="YosModalAyuda" class="modal-overlay" onclick="if(event.target===this)Yos_CerrarAyuda();">';
        echo '  <div class="modal-caja">';
        echo '    <div class="modal-cab">';
        echo '      <span>' . $tituloHtml . '</span>';
        echo '      <button type="button" onclick="Yos_CerrarAyuda();" title="Cerrar">&times;</button>';
        echo '    </div>';
        echo '    <div class="modal-cuerpo">' . $msgHtml . '</div>';
        echo '  </div>';
        echo '</div>';

        echo '<script>';
        echo 'function Yos_MostrarAyuda(){ document.getElementById("YosModalAyuda").classList.add("activo"); }';
        echo 'function Yos_CerrarAyuda(){ document.getElementById("YosModalAyuda").classList.remove("activo"); }';
        echo 'document.addEventListener("keydown", function(e){ if(e.key==="Escape") Yos_CerrarAyuda(); });';
        echo '</script>';
    }

    return;
}


function Yos_Pgn_Pie($AplCfg)
{
//echo 'Yos_Pgn_Pie()<br>';
    $Apl = $AplCfg['Apl'];
    $Nom = $AplCfg['Nom'];
    $Etn = $AplCfg['Etn'];
    $Dir = $AplCfg['Dir'];
//echo "Apl:=".$Apl."<br>";
//echo "Nom:=".$Nom."<br>";
//echo "Etn:=".$Etn."<br>";
//echo "Dir:=".$Dir."<br>";
//echo "Apl_Dir_Img:=".$_SESSION[$Apl]['Dir_Img']."<br>";
    echo '<table style="text-align: center; width: 100%;" border="01" cellpadding="2" cellspacing="2">';
    echo '  <tr>';
/*
    echo '      <td align="center">';
    echo '          <img border="0" src="'.$_SESSION['YosLib']['Www'].'Img/HecHon.gif">';
    echo '      </td>';
*/
    echo '    <td style="text-align: center;">';
    // Veo si es YosLib o otra Aplicacion
    if ($_SESSION[$Apl]['Nom']<>'YosLib'){
        echo $_SESSION[$Apl]['Nom'],' - Versión ',$_SESSION[$Apl]['Vsn'],' - ',$_SESSION[$Apl]['Cpy'].'<font class="footmsg"><br>';
        echo '<a rel="license" href="'.$_SESSION['YosLib']['Www'].'" target="_blank">YosLib</a>';
    }else{
        echo 'YosLib';
    }
    echo ' - Versión '.$_SESSION['YosLib']['Vsn'].' - '.$_SESSION['YosLib']['Cpy'].'<font class="footmsg"><br>';
    echo ' Esta obra está bajo una licencia '.$_SESSION[$Apl]['Lic'].'</a>';
    echo '</font>';
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    Return ;
}

function Yos_Pgn_AvsFlo($Fnc_Est, $Fnc_Msg = 'Espere, por favor.') {
        // Aviso Flotante
//     Yos_Pgn_AvsFlo('On', 'Mensaje');
//     Yos_Pgn_AvsFlo('Off');
/*
    if ($Fnc_Est === "On") {
        echo "<div id='yos-loader-bg' style='position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9998;'></div>";
        echo "<div id='yos-loader' style='position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding: 30px 40px; background: #222; color: #fff; font-size: 18px; font-family: monospace; z-index:9999; box-shadow: 0px 0px 15px rgba(0,0,0,0.5);'>";
        echo $Fnc_Msg;
        echo "</div>";
        @ob_flush();
        flush();
    } elseif ($Fnc_Est === "Off") {
        echo "<script>
            if(document.getElementById('yos-loader')) document.getElementById('yos-loader').remove();
            if(document.getElementById('yos-loader-bg')) document.getElementById('yos-loader-bg').remove();
        </script>";
        @ob_flush();
        flush();
    }
}
*/
    if ($Fnc_Est === "On") {
        echo "<div id='yos-loader-bg' style='position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9998;'></div>";
        echo "<div id='yos-loader' style='position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding: 30px 40px; background: #222; color: #fff; font-size: 18px; font-family: monospace; z-index:9999; box-shadow: 0px 0px 15px rgba(0,0,0,0.5);'>";
        echo $Fnc_Msg;
        echo "</div>";
        @ob_flush();
        flush();
    } elseif ($Fnc_Est === "Off") {
        echo "<script>
            if(document.getElementById('yos-loader')) document.getElementById('yos-loader').remove();
            if(document.getElementById('yos-loader-bg')) document.getElementById('yos-loader-bg').remove();
        </script>";
        @ob_flush();
        flush();
    }
}

function Yos_Pgn_Msg(array $FncDat): void {
    static $renderizadoBase = false;
    //Â«EnRojoÂ»
/*
    Yos_MsgMst(['Tip' => 'Tipo','Tit' => 'Titulo','Msg' => 'Mensaje']);

$FncDat = [
    'Tip' => 'Tipo',    // Tipo del Mensaje
                        // 'Std' => '#2ecc71', // Verde - Mensajes informativos del Sistema
                        // 'Inf' => '#3498db', // Azul - Mensajes informativos del programador
                        // 'Atn' => '#f1c40f', // Amarillo - Mensajes de atencion
                        // 'Err' => '#e74c3c', // Rojo - Mensajes de error que inciden ciertos procesos
                        // default => '#3498db', // Azul - Mensajes informativos del programador
    'Tit' => 'Titulo',
    'Msg' => 'Mensaje'
];

*/
    $Mem_Tip  = $FncDat['Tip'] ?? 'Std';
    $Mem_Tit  = htmlspecialchars($FncDat['Tit'] ?? 'ATENCION', ENT_QUOTES, 'UTF-8');
    $Mem_Msg  = $FncDat['Msg'] ?? 'Pulse Aceptar para continuar.';

    $colorTema = match ($Mem_Tip) {
        'Std' => '#2ecc71', // Verde - Mensajes informativos del Sistema
        'Inf' => '#3498db', // Azul - Mensajes informativos del programador
        'Atn' => '#f1c40f', // Amarillo - Mensajes de atencion
        'Err' => '#e74c3c', // Rojo - Mensajes de error que inciden ciertos procesos
        default => '#2ecc71', // Verde - Mensajes informativos del Sistema
    };
/*
    $Mem_Ing = match ($Mem_Tip) {
        'Std' => asset('../_YosLib_Img/YosInf.png'),
        'Inf' => asset($_SESSION['Svr']['Dir'].'_YosLib_Img/YosInf.png'),
        'Atn' => asset($_SESSION['Svr']['Dir'].'_YosLib_Img/YosExc.png'),
        'Err' => asset($_SESSION['Svr']['Dir'].'_YosLib_Img/YosErr.png'),
        default => asset('../_YosLib_Img/YosInf.png'),
    };
*/
    $Mem_Ing = match ($Mem_Tip) {
        'Std' => $_SESSION['YosLib']['Www'] .'Img/'. 'YosInf.png',
        'Inf' => $_SESSION['YosLib']['Www'] .'Img/'. 'YosInf.png',
        'Atn' => $_SESSION['YosLib']['Www'] .'Img/'. 'YosExc.png',
        'Err' => $_SESSION['YosLib']['Www'] .'Img/'. 'YosErr.png',
        default => $_SESSION['YosLib']['Www'] .'Img/'. 'YosInf.png',
    };

    $textoCompleto = "<strong>{$Mem_Tit}</strong><br><br>{$Mem_Msg}";

    if (!$renderizadoBase) {
        $renderizadoBase = true;
        echo '
        <div id="yos-error-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.1); z-index:9999; align-items:center; justify-content:center;">
            <div style="background:#ffffff; color:#222222; padding:25px; border-radius:8px; width:auto; max-width:90vw; min-width:320px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);" id="yos-modal-box">
                <div style="display: flex; align-items: center; gap: 15px; text-align: left; margin-bottom: 20px;">
                    <img id="yos-modal-img" src="" alt="Icono" style="width:48px; height:48px; flex-shrink: 0;">
                    <div id="yos-modal-content"></div>
                </div>
                <div style="text-align: center;">
                    <button id="yos-modal-btn" onclick="siguienteYosMensaje()" style="border:none; color:#fff; padding:8px 20px; border-radius:4px; cursor:pointer; font-weight:bold; font-family: monospace;">Aceptar</button>
                </div>
            </div>
        </div>
        <script>
            window.yosColaMensajes = window.yosColaMensajes || [];
            window.yosModalIniciado = false;

            function mostrarSiguienteYosMensaje() {
                let modal = document.getElementById("yos-error-modal");
                if (!modal) return;

                if (window.yosColaMensajes.length === 0) {
                    modal.style.display = "none";
                    window.yosModalIniciado = false;
                    return;
                }
                let m = window.yosColaMensajes.shift();
                document.getElementById("yos-modal-box").style.borderTop = "5px solid " + m.color;
                document.getElementById("yos-modal-img").src = m.img;
                document.getElementById("yos-modal-btn").style.backgroundColor = m.color;
                document.getElementById("yos-modal-content").innerHTML = m.texto;
                modal.style.display = "flex";
                window.yosModalIniciado = true;
            }

            function siguienteYosMensaje() {
                mostrarSiguienteYosMensaje();
            }

            document.addEventListener("DOMContentLoaded", function() {
                if (window.yosColaMensajes.length > 0 && !window.yosModalIniciado) {
                    mostrarSiguienteYosMensaje();
                }
            });
        </script>';
    }

    $jsonTexto = json_encode($textoCompleto);
    $jsonImg = json_encode($Mem_Ing);
    echo "<script>
        window.yosColaMensajes = window.yosColaMensajes || [];
        window.yosColaMensajes.push({
            color: '{$colorTema}',
            img: {$jsonImg},
            texto: {$jsonTexto}
        });
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            let modal = document.getElementById('yos-error-modal');
            if (modal && modal.style.display !== 'flex' && !window.yosModalIniciado) {
                mostrarSiguienteYosMensaje();
            }
        }
    </script>";
}
