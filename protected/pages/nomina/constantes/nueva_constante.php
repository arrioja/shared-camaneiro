<?php
class nueva_constante extends TPage
{
     /** CHECK si existe el código **/
 function check_codigo($sender,$param)
   {
     $param->IsValid=verificar_existencia("nomina.constantes","cod",$this->txt_codigo->Text,null,$sender);
   }

 function guardar_constante($sender, $param)
  {
    if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
     {
     // Se capturan los datos provenientes de los Controles
        $codigo = $this->txt_codigo->Text;
        $descripcion = $this->txt_descripcion->Text;
        $abreviatura = $this->txt_abreviatura->Text;
        $tipo = $this->cmb_tipo->Text;
        $tipo_pago = $this->cmb_tipo_pago->Text;
        $fecha=date("Y-m-d");
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="insert into nomina.constantes(cod,descripcion,abreviatura,tipo,tipo_pago,fecha,cod_organizacion)values ('$codigo','$descripcion','$abreviatura','$tipo','$tipo_pago','$fecha','$cod_org' )";
       
       $resultado=modificar_data($sql,$sender);//ejecutar la consulta de insercion
        if ($resultado==1)
            {
            $this->Response->redirect($this->Service->constructUrl('nomina.constantes.admin_constantes'));
            }
        else
            {
                echo " Error gruardando los datos de constante - ";
                echo mysql_error();
                echo " ----- Antes de cerrar este mensaje de error, por favor contacte al soporte t&eacute;cnico de la Direcci&oacute;n de Sistemas para tomen las previsiones y lo corrijan oportunamente";
            //enviar a la pagina de error
            }
     }
  }
  function regresar()
  {
      $this->Response->redirect($this->Service->constructUrl('nomina.constantes.admin_constantes'));
  }
}
?>
