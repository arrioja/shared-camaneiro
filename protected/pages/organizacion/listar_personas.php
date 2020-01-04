<?php
class listar_personas extends TPage
{
    public function cargar()
    {
        $tipo_nomina = usuario_actual('tipo_nomina');
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select cedula, nombres, apellidos,rif,id from organizacion.personas order by apellidos";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }
    public function onLoad($param)
    {
    parent::onLoad($param);
    if(!$this->IsPostBack)
      {
		$this->DataGrid->DataSource=$this->cargar();
		$this->DataGrid->dataBind();
      }
    }

    public function changePage($sender,$param)
	{
        $sql="select cedula, nombres, apellidos from organizacion.personas order by apellidos";
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}
    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
		$this->DataGrid->DataSource=$this->cargar();
		$this->DataGrid->dataBind();

    }
    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
		$this->DataGrid->DataSource=$this->cargar();
		$this->DataGrid->dataBind();
    }
    public function saveItem($sender,$param)
    {
        $item=$param->Item;
        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        $rif=$item->rif->TextBox->Text;

		$sql="UPDATE organizacion.personas set rif='$rif' where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }
    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->cedula->TextBox->ReadOnly="True";
            $item->nombre->TextBox->ReadOnly="True";
            $item->apellido->TextBox->ReadOnly="True";
        }

    }

}

?>
