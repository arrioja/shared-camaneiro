<?php
           
    /*if (!($link=mysql_connect("localhost","root","admin"))){
      echo "Error conectando a la base de datos.";
      exit();
   }

   if (!mysql_select_db("intranet",$link)){
      echo "Error seleccionando la base de datos.";
      exit();
   }

     $result=mysql_query("SELECT * FROM intranet.usuarios501", $link);
     while($resultado=mysql_fetch_array($result)){
           $sql="insert into intranet.usuarios ('$resultado[cedula]','$resultado[login]','".substr(MD5($resultado[clave]), 0, 200)."','$resultado[email]','$resultado[activo]')";
           $inserta = mysql_query($sql, $link);
     }//fin mientras */

   if (!($link=mysql_connect("localhost","root","dbrootcene"))){
      echo "Error conectando a la base de datos.";
      exit();
   }

   if (!mysql_select_db("intranet",$link)){
      echo "Error seleccionando la base de datos.";
      exit();
   }


//$conexion = mysql_connect('localhost', 'root', 'dbrootcene'); // se conecta con el servidor
//mysql_select_db('intranet', $conexion); // selecciona la base de datos
$tabla = mysql_query("SELECT * FROM usuarios",$link); // selecciono todos los registros de la tabla usuarios, ordenado por nombre
$i=1;

while ($resultado = mysql_fetch_array($tabla)) { // comienza un bucle que leera todos los registros y ejecutara las ordenes que siguen
//$sql="insert into usuarios (cedula,login,clave,email,activo) values ('$resultado[cedula]','$resultado[login]','".substr(MD5($resultado[clave]), 0, 200)."','$resultado[email]','$resultado[activo]')";
//$consulta = mysql_query($sql);
$sql1="SELECT * FROM usuarios_grupos WHERE login='$resultado[login]' AND codigo='5'";
$consulta2 = mysql_query($sql1,$link); // selecciono todos los registros de la tabla usuarios, ordenado por nombre
$resultado2 = mysql_fetch_array($consulta2);

    if(empty($resultado2)){//si no esta lo incluyo en el grupo de funcionarios
        
        $sql3="insert into usuarios_grupos (login,codigo) values ('".$resultado['login']."','5')";
        $consulta3 = mysql_query($sql3,$link);
		echo "$sql3 $i - $resultado[login]<br>";$i++;
    }//fin si

} // fin del bucle de ordenes

mysql_free_result($tabla); // libera los registros de la tabla
mysql_close($link); // cierra la conexion con la base de datos

?>
