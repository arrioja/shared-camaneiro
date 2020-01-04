<?php

class proveedor_ramo extends TPage
{
var $suma;//variable global para mostrar el total en el footer del grid
var $asignaciones=0;
var $deducciones=0;
    function createMultiple($link, $array) {
                $item = $link->Parent->Data;
                $return = array();
                foreach($array as $key) {
                      $return[] = $item[$key];
                 }
                 return implode(";", $return);
        }

public function cargar_posee($cod_proveedor)
{

 $sql="SELECT CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica) as codigo, id, descripcion from presupuesto.proveedores_ramo where cod_proveedor='$cod_proveedor'";
 return cargar_data($sql,$this);

}

public function cargar_no_posee($cod_proveedor)
{
       
       /*$sql_no="select * from presupuesto.proveedores_ramo where cod not in(select c.cod
        from nomina.conceptos c inner join nomina.integrantes_conceptos ic on ic.cod_concepto=c.cod
        where cedula='$cedula' and ic.tipo_nomina='$tipo_nomina')";*/

$sql_no="SELECT CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica) as codigo, id, descripcion FROM presupuesto.presupuesto_gastos where especifica='00' and subespecifica='00' and ordinal='00000' and descripcion<>'' and id not in(select id from presupuesto.proveedores_ramo where cod_proveedor='$cod_proveedor')";
        return cargar_data($sql_no,$this);
}


public function agregar($sender,$param)//sin revisar
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $cod_proveedor=$this->Request['cod'];//cod proveedor

        $parametros=$sender->CommandParameter;//recibe un array
        $datos=explode(";", $parametros);
        $cod_presupuestario=$datos[0];
        $descripcion=$datos[1];
        $id=$datos[2];//id para filtrar
        $cod_pres=explode("-",$cod_presupuestario);



        $sql="insert into  presupuesto.proveedores_ramo values ('$id','','$cod_org','$cod_proveedor','2010','$cod_pres[0]','$cod_pres[1]','$cod_pres[2]','$cod_pres[3]','$cod_pres[4]','$cod_pres[5]','$cod_pres[6]','$cod_pres[7]','$cod_pres[8]','$cod_pres[9]','$descripcion')";
        $resultado=modificar_data($sql,$this);

        $this->DataGrid_asignados->DataSource=$this->cargar_posee($cod_proveedor);
		$this->DataGrid_asignados->dataBind();

        $this->DataGrid_no_asignados->DataSource=$this->cargar_no_posee($cod_proveedor);
		$this->DataGrid_no_asignados->dataBind();
    }

public function quitar($sender,$param)
    {$cod_proveedor=$this->Request['cod'];//cod proveedor
        $id=$sender->CommandParameter;

        $sql="DELETE FROM presupuesto.proveedores_ramo WHERE id = '$id' and cod_proveedor='$cod_proveedor'";
        $resultado=modificar_data($sql,$this);

        $this->DataGrid_asignados->DataSource=$this->cargar_posee($cod_proveedor);
		$this->DataGrid_asignados->dataBind();

        $this->DataGrid_no_asignados->DataSource=$this->cargar_no_posee($cod_proveedor);
		$this->DataGrid_no_asignados->dataBind();
    }

    public function go($sender,$param)
    {

   $id=$sender->CommandParameter;
   $this->Response->Redirect( $this->Service->constructUrl('nomina.integrantes.integrantes_banco',array('id'=>$id)));//
    }


public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $cod=$this->Request['cod'];

        $sql="select * from presupuesto.proveedores where cod_proveedor ='$cod'";
        $proveedor=cargar_data($sql,$this);


        $this->txt_rif->Text=$proveedor[0][rif];
        $this->txt_cod_proveedor->Text=$proveedor[0][cod_proveedor];
        $this->txt_nombres->Text=$proveedor[0][nombre];


        $this->DataGrid_asignados->DataSource=$this->cargar_posee($cod);
		$this->DataGrid_asignados->dataBind();

        $this->DataGrid_no_asignados->DataSource=$this->cargar_no_posee($cod);
		$this->DataGrid_no_asignados->dataBind();
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
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->cedula->TextBox->ReadOnly="True";
            $item->anos->TextBox->Columns=8;
        }

        if($item->getItemType() === 'Footer')
        {
            $total_asig = new TLabel();
            $total_deduc = new TLabel();

            $total_asig->Text=$this->asignaciones;
            $total_deduc->Text=$this->deducciones;

             $item->Cells[5]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
             $item->Cells[6]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[5]->Controls->insertAt(0,$total_asig);//$item->getControls()->add(0,"hola");
            $item->Cells[5]->HorizontalAlign="Right";
            $item->Cells[5]->ForeColor="Green";

            $item->Cells[6]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[6]->Controls->insertAt(0,$total_deduc);//$item->getControls()->add(0,"hola");
            $item->Cells[6]->HorizontalAlign="Right";
            $item->Cells[6]->ForeColor="Red";
        }


    }

    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->filtrar($serder, $param);
        /*$this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();*/
    }


    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM nomina.integrantes WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));

    }

  


	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'P&aacute;gina: ');
	}
    	public function footer($sender,$param)
	{
		$param->Footer->Controls->insertAt(0,'hola ');
	}
    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('presupuesto.proveedor.listar_proveedor'));
    }
}

/*
        */
?>
