<?php

class listar_tipo_justificaciones extends TPage
{

	 public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            $this->cargar_data($this, $param);
        }
    }
    public function cargar_data($sender,$param)
    {
      	//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from asistencias.tipo_justificaciones where (cod_organizacion = '$cod_organizacion') order by descripcion";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
    }

	public function changePage($sender,$param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from asistencias.tipo_justificaciones where (cod_organizacion = '$cod_organizacion') order by descripcion";
		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
	}


	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

     public function itemCreated($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
        /*    $item->cod->TextBox->Columns=10;
            $item->nombre_corto->TextBox->Columns=15;
            $item->nombre->TextBox->Columns=30;
            $item->archivo->TextBox->Columns=35;*/
        }

    }
      public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->cargar_data($sql,$this);
        
    }

    public function saveItem($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid->DataKeys[$item->ItemIndex];
		$descripcion=$item->descripcion->TextBox->Text;
        $visible = $item->visible->DropDownList->Text;
        $dias_habiles = $item->habiles->DropDownList->Text;
        $descuenta_ticket = $item->descuenta->DropDownList->Text;
       
		$sql="UPDATE asistencias.tipo_justificaciones set descripcion='$descripcion', visible='$visible', dias_habiles='$dias_habiles', descuenta_ticket='$descuenta_ticket'  WHERE id='$id' ";
        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->cargar_data($sql,$this);
        

    }
    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;

        $this->cargar_data($sql,$this);
      

    }

}

?>
