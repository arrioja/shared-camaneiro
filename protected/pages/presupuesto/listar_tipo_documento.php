<?php

class listar_tipo_documento extends TPage
{

	public function onLoad($param)
	{
			//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.tipo_documento where (cod_organizacion = '$cod_organizacion') order by siglas";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}


    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        switch($item->operacion->Text)
        {
            case "PA":
                $item->operacion->Text = "Pago";
                break;
            case "CO":
                $item->operacion->Text = "Compromiso";
                break;
            case "CA":
                $item->operacion->Text = "Causa";
                break;
        }
    }


	public function changePage($sender,$param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.tipo_documento where (cod_organizacion = '$cod_organizacion') order by siglas";
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
