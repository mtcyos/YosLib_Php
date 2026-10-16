<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 - No Encontrado</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding-top: 100px; background-color: #f4f4f4; color: #333; }
        .container { background: #fff; padding: 40px; border-radius: 8px; display: inline-block; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1 { color: #d9534f; }
        button { padding: 10px 20px; background: #d9534f; color: #fff; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; }
        button:hover { background: #c9302c; }
    </style>
</head>
<body>
    <div class="container">
        <h3>
            <p>1<!--# echo var="servidor_actual" --></p>
            <p>2<!--# echo var="servidor_actual_2" --></p>
        </h3>
        <h1 id="titulo-error">Error <!--# echo var="status" --></h1>
        <p id="mensaje-error">Procesando código de incidencia...</p>
        <button id="btn-accion" onclick="window.location.reload(true);">REINTENTAR</button>
    </div>

    <script>
        var codigo = "<!--# echo var="status" -->".trim();
        var titulo = "Error " + codigo;
        var texto = "Ha ocurrido un error inesperado en el servidor.";
        var mostrarBoton = true;
        var textoBoton = "REINTENTAR";
        var accionBoton = function() { window.location.reload(true); };

        if (codigo === "401") {
            titulo = "401 - Acceso No Autorizado";
            texto = "Esta sección está protegida y requiere credenciales válidas.";
            textoBoton = "REINTENTAR";
        } else if (codigo === "403") {
            titulo = "403 - Acceso Prohibido";
            texto = "No tienes permisos suficientes para acceder a este recurso.";
            textoBoton = "VOLVER AL INICIO";
            accionBoton = function() { window.location.href = "/"; };
        } else if (codigo === "404") {
            titulo = "404 - No Encontrado";
            texto = "La página o archivo solicitado no existe.";
            textoBoton = "IR AL INICIO";
            accionBoton = function() { window.location.href = "/"; };
        } else if (codigo === "500") {
            titulo = "500 - Error Interno";
            texto = "El servidor experimentó un error interno al procesar tu solicitud.";
            textoBoton = "REINTENTAR";
        }

        document.getElementById("titulo-error").innerText = titulo;
        document.getElementById("mensaje-error").innerText = texto;

        var btn = document.getElementById("btn-accion");
        if (mostrarBoton) {
            btn.innerText = textoBoton;
            btn.onclick = accionBoton;
        } else {
            btn.style.display = "none";
        }
    </script>
</body>
</html>
