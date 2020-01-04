<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra un Listado de los proveedores registrados para la
 *              organización a la que pertenece el usuario actual.
 *****************************************************  FIN DE INFO
*/
class listar_proveedor extends TPage
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
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select id, cod_proveedor,rif,direccion, nombre, telefono1, telefono2 from presupuesto.proveedores
              where (cod_organizacion = '$cod_organizacion')  order by nombre";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }



    public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{			//Busqueda de Registros
       
		$this->DataGrid->DataSource=$this->cargar($serder, $param);
		$this->DataGrid->dataBind();
        }
	}
	public function editItem($sender,$param)
	{
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->DataGrid->DataSource=$this->cargar($serder, $param);
        $this->DataGrid->dataBind();
	}

    public function saveItem($sender,$param)
    {
        $item=$param->Item;

        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        $rif=$item->rif->TextBox->Text;
        $nombre=$item->nombre->TextBox->Text;
        $direccion=$item->direccion->TextBox->Text;
        $telefono1=$item->telefono1->TextBox->Text;
        $telefono2=$item->telefono2->TextBox->Text;

		$sql="UPDATE presupuesto.proveedores set rif='$rif', nombre='$nombre',direccion='$direccion', telefono1='$telefono1', telefono2='$telefono2'  where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar($serder, $param);
        $this->DataGrid->dataBind();

    }

	public function anularItem($sender,$param)
	{
	/*	$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
		$this->Response->redirect($this->Service->constructUrl('Anular',array('id'=>$id)));*/
	}


    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->cod->TextBox->ReadOnly="True";
            $item->cod->TextBox->Columns=6;
            $item->rif->TextBox->Columns=8;
            $item->telefono1->TextBox->Columns=9;
            $item->telefono2->TextBox->Columns=9;
        }


    }

    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar($serder, $param);
        $this->DataGrid->dataBind();
    }

	public function changePage($sender,$param)
	{
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->DataGrid->DataSource=$this->cargar($serder, $param);
        $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

public function asignar_ramo($sender,$param)
    {

   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $cod=$datos[0];
   //$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

$this->Response->redirect($this->Service->constructUrl('presupuesto.proveedor.proveedor_ramo',array('cod'=>$cod)));
    }

}

?>
