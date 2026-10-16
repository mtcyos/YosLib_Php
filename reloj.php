<?php
    date_default_timezone_set($_SESSION['Svr']['ZonHor']);
?>
<script language="JavaScript">
<!--
// Se declara FUERA de la función para que se inicialice una sola vez al cargar la página
var Digital = new Date('<?php echo date("Y/m/d H:i:s"); ?>');

function show5(){
    if (!document.layers && !document.all && !document.getElementById)
        return

    var hours = Digital.getHours();
    var minutes = Digital.getMinutes();
    var seconds = Digital.getSeconds();

    if (hours == 0) hours = 12;
    if (minutes <= 9) minutes = "0" + minutes;
    if (seconds <= 9) seconds = "0" + seconds;

    var myclock = hours + ":" + minutes + ":" + seconds;

    if (document.layers){
        document.layers.liveclock.document.write(myclock);
        document.layers.liveclock.document.close();
    }
    else if (document.all){
        liveclock.innerHTML = myclock;
    }
    else if (document.getElementById){
        document.getElementById("liveclock").innerHTML = myclock;
    }

    // Incrementamos un segundo en cada ciclo para que el reloj avance con fluidez
    Digital.setSeconds(Digital.getSeconds() + 1);

    setTimeout("show5()", 1000);
}

window.onload = show5;
-->
</script>

