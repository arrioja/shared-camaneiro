<?php
class nuevo_banco extends TPage
{
     /** CHECK si existe el código **/
 function check_codigo($sender,$param)
   {
     $param->IsValid=verificar_existencia_doble("nomina.banco","numero",$this->txt_numero->Text,"cod_organizacion",usuario_actual('cod_organizacion'),$sender);
   }

 function guardar_concepto($sender, $param)
  {
    if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
     {
     // Se capturan los datos provenientes de los Controles
        $cod = $this->txt_codigo->Text;
        $descripcion = $this->txt_descripcion->Text;
        $numero = $this->txt_numero->Text;
        $tipo = $this->cmb_tipo->Text;
        $nombre = $this->txt_nombre->Text;
        
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="insert into nomina.banco(cod,descripcion,
numero,tipo,nombre,cod_organizacion)
values ('$cod','$descripcion','$numero','$tipo','$nombre','$cod_org' )";
       
       $resultado=modificar_data($sql,$sender);//ejecutar la consulta de insercion
        if ($resultado==1)
            {
            $this->Response->redirect($this->Service->constructUrl('nomina.banco.admin_banco'));
            }
        else
            {
                echo " Error gruardando los datos de Banco - ";
                echo mysql_error();
                echo " ----- Antes de cerrar este mensaje de error, por favor contacte al soporte t&eacute;cnico de la Direcci&oacute;n de Sistemas para tomen las previsiones y lo corrijan oportunamente";
            //enviar a la pagina de error
            }
     }
  }
  function regresar()
  {
      $this->Response->redirect($this->Service->constructUrl('nomina.banco.admin_banco'));
  }
}
?>
