<?php 
/*   ****************************************************  INFO DEL ARCHIVO 
  		   Creado por: 	Pedro E. Arrioja M./ Modificado por: Ronald Salazar
  Descripci�n General:  Esta p�gina se encarga (conjuntamente con marcado.php) de marcar la entrada y salida del personal de la instituci�n, adicionalmente a lo descrito en su compa�era de AJAX, esta pagina toma la foto del personal para mostrarla cuando marque, las fotos deben estar localizadas en la carpeta /imgs/fotosfuncionarios y deben ser de 400x300 en formato jpg.
  		Modificado el: 	23/10/2012 por Ronald A. Salazar C.
  			  Versi�n: 	0.1b
     ****************************************************  FIN DE INFO
*/
$id=$_POST['cedula'];

   if (!($link=mysql_connect("localhost","root","cmm12345"))){
      echo "Error conectando a la base de datos.";
      exit();
   }

   if (!mysql_select_db("organizacion",$link)){
      echo "Error seleccionando la base de datos.";
      exit();
   }
  
  // para comprobar que lo que se escribi� fue la c�dula o el carnet, si es la c�dula, viene con puntos en partes espec�ficas, si es carnet
  // viene con puros numeros.

  if (strlen($id) <= 8){ // si viene con puntos se busca la cedula
      $buscar = $id;
	  $consulta = mysql_query("select p.cedula, p.carnet, p.nombres, p.apellidos, p.foto, s.status_asistencia 
 										 from organizacion.personas p, asistencias.personas_status_asistencias as s
										 where ((p.cedula=$buscar) and (s.status_asistencia = '1') and (p.cedula=s.cedula))", $link);
    }else{// si viene sin puntos se busca el carnet
      $consulta = mysql_query("select p.cedula, p.carnet, p.nombres, p.apellidos, p.foto, s.status_asistencia 
 										 from organizacion.personas p, asistencias.personas_status_asistencias as s
										 where ((p.carnet=$id) and (s.status_asistencia = '1') and (p.cedula=s.cedula))", $link);
	}

// para ingresar marca de auditoria.   
 // include("../db/inserta_rastreo.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Log de Marcado</title>
<style type="text/css">
<!--
 body {font-family: Helvetica, Geneva, Arial,SunSans-Regular, sans-serif;}
.style3 {color: #FF0000; font-size: 60px; }
.style6 {font-size: 36px; }
.style7 {font-size: 36px; font-weight:900; }
.style8 {font-size: 100px; color: white; font-weight:900; background: #009933; width:650px; height: 150px;-moz-border-radius:15px;
-webkit-border-radius: 15px}
.style10 {font-size: 100px; color:white ; font-weight:900; background:#FF0000; width:650px; height: 150px;-moz-border-radius:15px;
-webkit-border-radius: 15px}
-->
</style>
</head>
<body>
<div align="center" >
  <?php 
   if (mysql_num_rows($consulta) == 0) 
      { // si es cero quiere decir que la c�dula no se encuentra registrada
	   echo '<span class="style3">E R R O R</span>';
	  }
   else
      { // la c�dula si se encuentra registrada 
	  $resultado=mysql_fetch_array($consulta);
	  // Para saber si est� entrando o saliendo
      $buscar2=$resultado['cedula'];
	  $fecha_buscar=date("Y-m-d",strtotime("now"));
	  
	  $hora_insertar=date("H:i:s",strtotime("now"));
  	  $inserta = mysql_query("insert into asistencias.entrada_salida(fecha,hora,cedula) values ('$fecha_buscar','$hora_insertar','$buscar2')", $link);

	  $sql_asistencia=mysql_query("SELECT count(a.cedula) count 
		                           FROM asistencias.entrada_salida a 
						 	       WHERE (a.cedula = '$buscar2') AND (fecha = '$fecha_buscar')", $link);
	  $resultado_asis=mysql_fetch_array($sql_asistencia);
	  	  					   
	  ?>
</div>
<table width="100%" border="0"  cellpadding="3" cellspacing="0" style="text-align:left">
  <tr>
    <td width="33%" rowspan="5"><div align="center"><img src="../imagenes/fotos/funcionarios/<?php echo $resultado['foto'];?>" width="300" height="400" /></div></td>
    <td  width="67%" height="20%"><span class="style7"  ><?php echo $resultado['nombres'];?></span></td>
  </tr>
  <tr><td height="20%"><span class="style7"><?php echo $resultado['apellidos'];?></span></td></tr>
  <tr><td height="20%"><span class="style6"><?php echo $resultado['cedula'];?></span></td></tr> 
  <tr>
    <td height="20%" align="center"><br><br><br><?php $par = abs($resultado_asis['count']) % 2;
	                                                 if ($par == 0){ 
													     echo '<span class="style10">SALIDA</span>'; 
  														 $descripcion='Marcado de Asistencia / SALIDA, C&eacute;dula: '.$buscar2;
													 }else{ 
													     echo '<span class="style8">ENTRADA</span>'; 
  														 $descripcion='Marcado de Asistencia / ENTRADA, C&eacute;dula: '.$buscar2;
													 }
													 $ip = $REMOTE_ADDR; 
													// inserta_rastro($_SESSION['login'],$buscar2,'I',$descripcion,$ip);
												  ?>

                                                  <br><br><br></td>
  </tr>
  <tr><td height="20%" style="text-align:center;"><span class="style6"><?php echo date("d/m/Y",strtotime("now")); ?> - <?php echo date("h:i:s A",strtotime("now")); ?></span></td></tr>
</table>
<?php } // del else del num_rows?>
<br>
</body>
</html>
