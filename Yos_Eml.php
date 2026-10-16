<?php
# -*- coding: utf-8 -*-

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

if (!function_exists('Yos_EmlEnv')) {
    /**
     * Envía un correo electrónico utilizando un array de configuración.
     *
     * @param array $AplCfg Parámetros de configuración y datos del correo
     * @return bool|string True si se envía con éxito, 'Err' si ocurre algún fallo
     */

     /* SINTAXIS
        $AplCfg = [
            'Dst' => '',
            'Tit'       => 'MI PRIMER EMAIL DESDE LARAVEL',
            'Msg'       => 'Este es el contenido de prueba enviado desde la nueva estructura.'
            ];
        $resEnvio = Yos_EmlEnv($AplCfg);
        o
        $resEnvio = Yos_EmlEnv(['Dst' => '', 'Tit' => 'MI PRIMER EMAIL DESDE LARAVEL', 'Msg' => 'Este es el contenido de prueba enviado desde la nueva estructura.']);
        if ($resEnvio[0] === 'Err') {
            // Código a ejecutar si ocurre un error en el envío
            echo "Hubo un error al enviar el correo: " . htmlspecialchars($resEnvio[1])."<br>";
            return ['Err', 'Pomga aqui su mensaje de error.'];
        }
        // Un solo Destinatario
        // 'Dst' => '',
        // Varios Destinatarios
        // 'Dst' => ['mtcyos@yahoo.es', 'mtcyos@upnfm.edu.hn'],

*************************** para añadir al final del mensaje
Att. MIGUEL TORTOSA CIVERA
mtcyos@upnfm.edu.hn

================================================================
DIRECCIÓN DE FORMACIÓN PERMANENTE
04 - DFP
mtcyos
******** YosGes - EMAIL DE ENVIO AUTOMATICO, POR FAVOR NO RESPONDA A ESTE EMAIL ********
*/
    function Yos_EmlEnv(array $AplCfg)
    {
        // Asegurar que el entorno de Laravel y las fachadas estén inicializados si se llama desde fuera
        try {
            \Illuminate\Support\Facades\Facade::getFacadeApplication();
        } catch (\Throwable $th) {
            if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
                $app = require_once __DIR__ . '/../bootstrap/app.php';
                if (class_exists(\Illuminate\Foundation\Application::class) && $app instanceof \Illuminate\Foundation\Application) {
                    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
                    $kernel->bootstrap();
                }
            }
        }

        Yos_Pgn_AvsFlo('On','Enviando email, espere por favor.');

        $Mem_Dst = $AplCfg['Dst'] ?? null;
        $Mem_Tit = $AplCfg['Tit'] ?? '';
        $Mem_Msg = $AplCfg['Msg'] ?? '';
        $Mem_Pie =  "\n\n" .
                    "Att. «UsNom»\n" .
                    "«UsrEml»\n\n" .
                    "================================================================\n" .
                    "«EmpNom»\n" .
                    "«EmpNum» - «EmpNonAbv»\n" .
                    "«UsrNik»\n" .
                    "******** «AppNom» - EMAIL DE ENVIO AUTOMATICO, POR FAVOR NO RESPONDA A ESTE EMAIL ********";

        if (empty($Mem_Dst)) {
            Yos_Pgn_AvsFlo('Off');
            Yos_Pgn_Msg(['Tip' => 'Err','Tit' => 'ENVIO DE EMAILS','Msg' => 'El $Mem_Dst no puede estar VACIO.']);
            return ['Err', 'El $Mem_Dst no puede estar VACIO.'];
        }

        $Mem_Ori = ['«UsNom»', '«UsrEml»', '«EmpNom»', '«EmpNum»', '«EmpNonAbv»', '«UsrNik»', '«AppNom»'];
        $Mem_Fin = [
            'MIGUEL TORTOSA CIVERA',
            'mtcyos@upnfm.edu.hn',
            'DIRECCIÓN DE FORMACIÓN PERMANENTE',
            '04',
            'DFP',
            'mtcyos',
            config('app.name')
        ];

        $Mem_Pie = str_replace($Mem_Ori, $Mem_Fin, $Mem_Pie);

        $Mem_Msg = $Mem_Msg . $Mem_Pie;

        try {
            Mail::raw($Mem_Msg, function ($message) use ($Mem_Dst, $Mem_Tit) {
                $message->to($Mem_Dst)
                        ->subject($Mem_Tit);
            });
            Yos_Pgn_AvsFlo('Off');
            return ['Ok', ''];
        } catch (\Exception $e) {
            echo "Error al enviar correo: " . $e->getMessage() . "<br>";
            Yos_Pgn_AvsFlo('Off');
            return ['Err', 'Error al enviar correo: '. $e->getMessage()];
        }
    }
}
