<?php

class admin_banco extends TPage
{

 public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select * from presupuesto.bancos where cod_organizacion='$cod_org'";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

public function onLoad($param)
	{ 
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
            $item->cod->TextBox->Columns=8;
            $item->nombre->TextBox->Columns=30;
            $item->numero->TextBox->Columns=30;

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
        $sql="DELETE FROM nomina.banco WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->Response->redirect($this->Service->constructUrl('nomina.banco.admin_banco'));

    }

    public function saveItem($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid->DataKeys[$item->ItemIndex];		
        $nombre=$item->nombre->TextBox->Text;
        $info_adicional=$item->info->TextBox->Text;

       
		$sql="UPDATE presupuesto.bancos set nombre='$nombre',info_adicional='$info_adicional' where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
     
    }


    public function changePage($sender,$param)
	{
       /* $sql="select id,codigo_modulo,nombre_largo,archivo_php from intranet.modulos order by codigo_modulo";
			$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $resultado=cargar_data($sql,$this);
            $this->DataGrid->DataSource=$resultado;
            $this->DataGrid->dataBind();*/
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}
}

/*     
        */
?>