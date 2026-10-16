<script language="JavaScript">
<!--
var monthNames = new Array("Ene","Feb","Mar","Abr","May","Jun","Jul",
                           "Ago","Sep","Oct","Nov","Dic");

// Reutilizamos el objeto Digital que ya tiene la fecha del servidor
var dt = Digital;
var y = dt.getFullYear(); // getFullYear() es el estándar moderno en lugar de getYear()

document.write(dt.getDate() + " de " + monthNames[dt.getMonth()] + " de " + y);
// -->
</script>

