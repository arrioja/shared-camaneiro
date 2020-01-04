<?php
class nueva_variable extends TPage
{
     /** CHECK si existe el código **/
 function check_codigo($sender,$param)
   {
     $param->IsValid=verificar_existencia_doble("nomina.variables","cod",$this->txt_codigo->Text,"cod_organizacion",usuario_actual('cod_organizacion'),$sender);
   }

 function guardar_variable($sender, $param)
  {
    if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
     {
     // Se capturan los datos provenientes de los Controles
        $cod = $this->txt_codigo->Text;
        $descripcion = $this->txt_descripcion->Text;
        $abreviatura = $this->txt_abreviatura->Text;
        $valor = $this->txt_valor->Text;
        
        $fecha=date("Y-m-d");
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="insert into nomina.variables(cod,descripcion,abreviatura ,valor,cod_organizacion)
values ('$cod','$descripcion','$abreviatura','$valor','$cod_org')";
       
       $resultado=modificar_data($sql,$sender);//ejecutar la consulta de insercion
        if ($resultado==1)
            {
            $this->Response->redirect($this->Service->constructUrl('nomina.variables.admin_variables'));
            }
        else
            {
                echo " Error gruardando los datos de Variables - ";
                echo mysql_error();
                echo " ----- Antes de cerrar este mensaje de error, por favor contacte al soporte t&eacute;cnico de la Direcci&oacute;n de Sistemas para tomen las previsiones y lo corrijan oportunamente";
            //enviar a la pagina de error
            }
     }
  }
  function regresar()
  {
      $this->Response->redirect($this->Service->constructUrl('nomina.variables.admin_variables'));
  }
}
?>
