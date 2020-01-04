<?php

class listar_banco extends TPage
{

	public function onLoad($param)
	{
			//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.bancos where (cod_organizacion = '$cod_organizacion') order by nombre";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}

	public function changePage($sender,$param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.bancos where (cod_organizacion = '$cod_organizacion') order by nombre";
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
