<?php
class cuentas extends TPage
{

    public function cargar($sender,$param)
    {
        $cod_org=usuario_actual('cod_organizacion');
        $id_banco=$this->drop_bancos->SelectedValue;

        $sql="select * from presupuesto.bancos_cuentas
        where cod_organizacion='$cod_org' and id_banco='$id_banco'";
        $resultado=cargar_data($sql,$this);
        $this->suma=count($resultado);
        return $resultado;
    }


public function onLoad($param)
	{

       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
            $cod_org=usuario_actual('cod_organizacion');
            $sql="select * from presupuesto.bancos where cod_organizacion='$cod_org'";
                $this->drop_bancos->DataSource=cargar_data($sql,$this);
                $this->drop_bancos->dataBind();
         }

   }


    public function carga_cuentas_bancarias($sender,$param)
    {
      // para llenar el listado de Beneficiarios
      $cod_organizacion = usuario_actual('cod_organizacion');
      $id_banco=$this->drop_bancos->SelectedValue;
      $sql = "select  * from presupuesto.bancos_cuentas
                where (cod_organizacion = '$cod_organizacion') and (id_banco = '$id_banco')";
      $datos = cargar_data($sql,$this);
      $this->DataGrid->Datasource = $datos;
      $this->DataGrid->dataBind();
    }


     /** CHECK si existe el código **/
 function check_codigo($sender,$param)
   {
     $param->IsValid=verificar_existencia_doble("nomina.banco","numero",$this->txt_numero->Text,"cod_organizacion",usuario_actual('cod_organizacion'),$sender);
   }

 function guardar_cuenta($sender, $param)
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
