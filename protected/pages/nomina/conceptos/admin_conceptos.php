<?php

class admin_conceptos extends TPage
{

 public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select * from nomina.conceptos where cod_organizacion='$cod_org'";
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
            $item->descripcion->TextBox->Columns=30;
            $item->formula->TextBox->Columns=30;
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
        $sql="DELETE FROM nomina.conceptos WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->Response->redirect($this->Service->constructUrl('nomina.conceptos.admin_conceptos'));

    }

    public function saveItem($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid->DataKeys[$item->ItemIndex];
		//$cod=$item->cod->TextBox->Text;
        $descripcion=$item->descripcion->TextBox->Text;
        $formula=$item->formula->TextBox->Text;
        $tipo=$item->tipo->DropDownList->Text;
        $tipo_pago=$item->tipo_pago->DropDownList->Text;
        $general=$item->general->DropDownList->Text;
        $frecuencia=$item->frecuencia->DropDownList->Text;
		$sql="UPDATE nomina.conceptos set descripcion='$descripcion', formula='$formula', tipo='$tipo',tipo_pago='$tipo_pago', general='$general',frecuencia='$frecuencia'  where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
     
    }

    public function formatear($sender,$param)
    {
        $item=$param->Item;
        switch($item->general->Text)
        {
            case "0":
                $item->general->Text = "No";
                break;
            case "1":
                //$item->Font->Bold="true";
                $item->general->Text = "S&iacute;";
                break;
        }
    }


    public function changePage($sender,$param)
	{
   			$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid->DataSource=$this->cargar();
            $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Página: ');
	}
}

/*     
        */
?>
