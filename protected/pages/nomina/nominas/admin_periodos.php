<?php

class admin_periodos extends TPage
{
    		function createMultiple($link, $array) {
                        $item = $link->Parent->Data;
                        $return = array();
                        foreach($array as $key) {
                              $return[] = $item[$key];
                         }
                         return implode(",", $return);
                }

    public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select distinct n.cod,n.tipo_nomina, na.titulo from nomina.nomina n inner join nomina.nomina_actual na on n.cod=na.cod where n.cod_organizacion='$cod_org'";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

    public function eliminar($sender,$param)
    {

   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros); 
   $cod=$datos[0];
   $tipo_nomina=$datos[1];
   $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

   $sql="delete from nomina.nomina where cod='$cod' and tipo_nomina='$tipo_nomina' and '$cod_org'";
   $resultado=modificar_data($sql,$this);

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
    }
    public function historial($sender,$param)
    {

   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $cod=$datos[0];
   $tipo_nomina=$datos[1];
   $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        try
        {
        $db = $this->Application->Modules["db2"]->DbConnection;
        $db->Active=true;
        $ejecucion = $db->createCommand('begin')->execute();//inicia transaccion

        $sql="insert into nomina.nomina_historial
        (cod,cedula,cod_incidencia, descripcion,monto_incidencia ,tipo,tipo_nomina, cod_organizacion)
        select n.cod,n.cedula,n.cod_incidencia, n.descripcion,n.monto_incidencia ,n.tipo,tipo_nomina, n.cod_organizacion from nomina.nomina n where cod='$cod' and tipo_nomina='$tipo_nomina' and cod_organizacion='$cod_org'";

        $ejecucion = $db->createCommand($sql)->execute();//inserta en historial
      

        $sql="delete from nomina.nomina where cod='$cod' and tipo_nomina='$tipo_nomina' and cod_organizacion='$cod_org'";
        $ejecucion = $db->createCommand($sql)->execute();//borra de nomina

        $ejecucion = $db->createCommand('commit')->execute();//ejecutar transaccion
        $db->Active=false;
        }
        catch(Exception $e)
        {
        $mensaje=$e->getMessage();
        $ejecucion = $db->createCommand('rollback')->execute();//devuelve transaccion
        $db->Active=false;
        $this->Response->redirect($this->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
        }

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
    }

    public function archivo_banco($sender,$param)
    {

   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $cod=$datos[0];//codigo de nomina
   $tipo_nomina=$datos[1];//tipo de nomina
   $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $this->Response->redirect($this->Service->constructUrl('nomina.nominas.archivo_banco',array('cod_nomina'=>$cod,'tipo_nomina'=>$tipo_nomina)));

    }

    public function reset()
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->txt_cadena->Text="";

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }

    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.nominas.crear_nomina'));
    }

public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{

         /*$columnas = $this->DataGrid->Columns;
         foreach ($columnas as $columna)
             {
                if($columna->ID=="tipo_nomina")
                {

                $sql="select tipo_nomina from nomina.tipo_nomina";
                $resultado=cargar_data($sql,$this);
                $columna->ListDataSource=cargar_tipo_nomina(usuario_actual('cod_organizacion'),$this);

                }
             }*/
        $this->DataGrid->DataSource=$this->cargar();
		$this->DataGrid->dataBind();
        }

   }

    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->filtrar($serder, $param);
       /* $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();*/

    }

    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        //echo $item->ItemType;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
       /*     $item->cedula->TextBox->ReadOnly="True";
            $item->anos->TextBox->Columns=8;*/

        }

    }




    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        echo $id;
        /*$sql="DELETE FROM nomina.integrantes WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
*/
    }



    public function changePage($sender,$param)
	{
       		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid->DataSource=$this->cargar();
            $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}
}

/*
        */
?>
