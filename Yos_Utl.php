<?php
function Yos_SesIni_Jsn(string $Fnc_Jsn, string $Fnc_Pre): void {
    if (!file_exists($Fnc_Jsn)) {
        exit("Error crítico: No se encuentra el archivo de configuración obligatorio: " . htmlspecialchars($Fnc_Jsn));
    }

    $contenido_json = file_get_contents($Fnc_Jsn);
    $datos_config = json_decode($contenido_json, true);

    if ($datos_config && is_array($datos_config)) {
        // Aseguramos que el contenedor del prefijo exista en la sesión
        if (!isset($_SESSION[$Fnc_Pre]) || !is_array($_SESSION[$Fnc_Pre])) {
            $_SESSION[$Fnc_Pre] = [];
        }

        foreach ($datos_config as $clave => $valor) {
            // Verificamos si la clave del JSON existe en nuestra estructura de sesión
            if (array_key_exists($clave, $_SESSION[$Fnc_Pre])) {
                $_SESSION[$Fnc_Pre][$clave] = $valor;
            } else {
                echo 'Error : "' . htmlspecialchars($clave) . '" no está definido como Configuracion en '.$Fnc_Pre.'. SE IGNORA.<br>';
            }
        }
    }

    // Verificación de datos: comprueba si algún campo obligatorio se quedó vacío dentro del array
    if (isset($_SESSION[$Fnc_Pre]) && is_array($_SESSION[$Fnc_Pre])) {
        foreach ($_SESSION[$Fnc_Pre] as $claveSesion => $valorSesion) {
            // Omitimos únicamente las descripciones (_Def)
            if (substr($claveSesion, -4) === '_Def') {
                continue;
            }

            // Si cualquier otra variable obligatoria está vacía, detenemos la ejecución
            if (empty($valorSesion)) {
                exit("Error crítico : El parámetro de configuración '" . htmlspecialchars($Fnc_Pre . "['" . $claveSesion . "']") . "' está vacío.");
            }
        }
    }
}

function yos_ksort_recursive(&$array) {
    ksort($array);
    foreach ($array as &$value) {
        if (is_array($value)) {
            yos_ksort_recursive($value);
        }
    }
}
