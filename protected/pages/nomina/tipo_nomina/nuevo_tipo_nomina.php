<?php
class nuevo_tipo_nomina extends TPage
{

 function guardar_tipo_nomina($sender, $param)
  {
    if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
     {
     // Se capturan los datos provenientes de los Controles
        $tipo_nomina = $this->txt_tipo_nomina->Text;
        
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="insert into nomina.tipo_nomina(tipo_nomina,cod_organizacion)
values ('$tipo_nomina','$cod_org')";
       
       $resultado=modificar_data($sql,$sender);//ejecutar la consulta de insercion
        if ($resultado==1)
            {
            $this->Response->redirect($this->Service->constructUrl('nomina.tipo_nomina.admin_tipo_nomina'));
            }
        else
            {
                echo " Error gruardando los datos de tipo_nomina - ";
                echo mysql_error();
                echo " ----- Antes de cerrar este mensaje de error, por favor contacte al soporte t&eacute;cnico de la Direcci&oacute;n de Sistemas para tomen las previsiones y lo corrijan oportunamente";
            //enviar a la pagina de error
            }
     }
  }
  function regresar()
  {
      $this->Response->redirect($this->Service->constructUrl('nomina.tipo_nomina.admin_tipo_nomina'));
  }
}
?>
