<?php

class listar_organizaciones extends TPage
{

	public function onLoad($param)
	{
			//Busqueda de Registros
        $sql="select rif, nombre, telefono1, telefono2 from organizacion.organizaciones order by nombre";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}
	public function editItem($sender,$param)
	{
	//	$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
	//	$this->Response->redirect($this->Service->constructUrl('IncluirDecla',array('id'=>$id)));
	}

	public function anularItem($sender,$param)
	{
	/*	$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
		$this->Response->redirect($this->Service->constructUrl('Anular',array('id'=>$id)));*/
	}

	public function changePage($sender,$param)
	{
        $sql="select rif, nombre, telefono1, telefono2 from organizacion.organizaciones order by nombre";
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

}

?>
