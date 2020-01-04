<?php 
/*   ****************************************************  INFO DEL ARCHIVO 
  		   Creado por: 	Pedro E. Arrioja M./ Modificado por: Ronald Salazar
  Descripci�n General:  Esta p�gina se encarga (conjuntamente con marcado_log.php) de marcar la entrada y salida del personal de la instituci�n, funciona
  						con AJAX con marcado_log y dice a qu� hora se esta marcando y si es entrada y salida.
  		Modificado el: 	23/10/2012 por Ronald A. Salazar C.*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Entrada y Salida de Personal</title>
<style type="text/css">
<!--
body {color:blue;background-color: #E7E7E7;font-family: Helvetica, Geneva, Arial,SunSans-Regular, sans-serif;text-align:center;}
.tituloes{font-size: 27px;font-weight:900;color:#D8B829;background-color:#4449D6;padding: 5px 5px 0px 5px;height:auto;}
#hora, #fecha{background-color:#000066;font-weight:900;color:white;font-size:55px;padding: 5px 5px 0px 5px;border:0px;border-color:white;height:70px;}
-->
</style>
<script language="JavaScript" src="XHConn.js"></script>
<script language="JavaScript">
var Hoy = new Date("<?php echo date("d M Y G:i:s");?>");

function Reloj(){ 
    Hora = Hoy.getHours() 
    Minutos = Hoy.getMinutes() 
    Segundos = Hoy.getSeconds() 
    if (Hora<=9) Hora = "0" + Hora 
    if (Minutos<=9) Minutos = "0" + Minutos 
    if (Segundos<=9) Segundos = "0" + Segundos 
     var mer=" AM";
	if(Hora>12){Hora=Hora-12;mer=" PM"}
    if (Hora==12)mer=" PM"
    document.getElementById('hora').innerHTML = Hora + ":" + Minutos + ":" + Segundos  + mer
    var fecha_resumida = ("<?php echo date("d/m/Y",strtotime("-30 minutes")); ?>");
    document.getElementById('fecha').innerHTML = fecha_resumida;

    Hoy.setSeconds(Hoy.getSeconds() +1)
    setTimeout("Reloj()",1000)
    document.forms["form1"]["cedula_carnet"].focus()
}

function registra_hora(target,valor)
{
  var peticion;
  //document.getElementById(target).innerHTML = 'Cargando Datos...';
  var myConn = new XHConn();
  if (!myConn) alert("XMLHTTP no esta disponible. Intentalo con un navegador mas nuevo.");
  peticion=function(oXML){document.getElementById(target).innerHTML=oXML.responseText;};
  myConn.connect("marcado_log.php", "POST", "cedula="+valor, peticion);
}

function limpia_control()
{
  document.getElementById('cedula_carnet').value = '';
}
</script>
</head>
<body onload="Reloj()">
<IMG SRC="logo.jpg" WIDTH=180 HEIGHT=180>
<div style="vertical-align:middle;">
<table width="100%" border="0" cellspacing="0" cellpadding="0"  >
  <tr >
    <td colspan="2" class="tituloes">REGISTRO DE ENTRADA Y SALIDA DEL PERSONAL</td>
  </tr>
  <tr>
   <td colspan="2">
  <table width="100%"  border="0" cellspacing="10" cellpadding="0"  >
  <tr>  <td width="50%" > <div id="fecha" ></div></td>
        <td width="50%" ><div id="hora" ></div></td>
   </tr>
  </table>
  </td>
  </tr>
  <tr>
    <td colspan="2">
    <div align="right" class="tituloes" style="font-size: 16px;">
      <form id="form1" name="form1" method="post" action="">Numero de Cedula:
      <input name="cedula_carnet" type="text" id="cedula_carnet" onkeypress="if (event.keyCode == 13){  
                                                               registra_hora('log',this.value);
															   limpia_control();	
                                							   return false;
                        									   }" maxlength="10" />
          
      </form>
     </div>
    </td>
  </tr>
  <tr>
    <td colspan="2" id="log">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" id="log2"><p><div align="center" style="font-size: 16px; color:red; font-weight:900;">NOTA:<span style="color:black;"> Para ingresar la c&eacute;dula, coloquela sin puntos y sin cero a la izquierda en las menores de diez millones.</span></div></td>
  </tr>
</table>
</div>
</body>
</html>
