<?php
class nuevo_banco extends TPage
{
     /** CHECK si existe el código **/
 function check_codigo($sender,$param)
   {
     $param->IsValid=verificar_existencia_doble("nomina.banco","numero",$this->txt_numero->Text,"cod_organizacion",usuario_actual('cod_organizacion'),$sender);
   }

 function guardar_banco($sender, $param)
  {
    if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
     {
     // Se capturan los datos provenientes de los Controles
        $nombre = $this->txt_nombre->Text;
        $info_adicional = $this->txt_info_adicional->Text;
        
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="insert into presupuesto.bancos(cod_organizacion,nombre,info_adicional)
values ('$cod_org','$nombre','$info_adicional' )";
       
       $resultado=modificar_data($sql,$sender);//ejecutar la consulta de insercion
        if ($resultado==1)
            {

            $this->mensaje->setSuccessMessage($sender, "Banco guardado!!", 'grow');
            //$this->Response->redirect($this->Service->constructUrl('nomina.banco.admin_banco'));
            }
        else
            {                
                $this->mensaje->setErrorMessage($sender, "Oooppss, Error gruardando los datos de Banco!!", 'grow');
            }
     }
  }
  function cancelar($sender,$param)
  {
      $this->Response->redirect($this->Service->constructUrl('presupuesto.banco.admin_banco'));
      
  }
}
?>
