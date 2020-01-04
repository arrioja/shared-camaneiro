<?php

class listar_modulos extends TPage
{
    public function cargar()
    {
        $sql="select id,codigo_modulo,nombre_largo,archivo_php,nombre_corto from intranet.modulos order by codigo_modulo";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

	public function onLoad($param)
	{
			//Busqueda de Registros
        if (!$this->IsPostBack)
        {
		$this->DataGrid->DataSource=$this->cargar();
		$this->DataGrid->dataBind();
        }
	}
	 public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }

    public function itemCreated($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->cod->TextBox->Columns=10;
            $item->nombre_corto->TextBox->Columns=15;
            $item->nombre->TextBox->Columns=30;
            $item->archivo->TextBox->Columns=35;
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->Borrar->Button->Attributes->onclick='if(!confirm(\'Esta Seguro de Borrar el Registro?\')) return false;';
        }
    }

    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
    }


    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM intranet.modulos WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->Response->redirect($this->Service->constructUrl('intranet.listar_modulos'));


    }

    public function saveItem($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid->DataKeys[$item->ItemIndex];
		$cod=$item->cod->TextBox->Text;
        $nombre=$item->nombre->TextBox->Text;
        $archivo=$item->archivo->TextBox->Text;
        $nombre_corto=$item->nombre_corto->TextBox->Text;

		$sql="UPDATE intranet.modulos set codigo_modulo='$cod', nombre_largo='$nombre', archivo_php='$archivo', nombre_corto='$nombre_corto'  where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

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

?>
