<?php

class listar_estatus_presupuesto extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql="select * from presupuesto.estatus_presupuesto
                           where cod_organizacion = '$cod_organizacion' order by ano";
              $resultado=cargar_data($sql,$this);
              $this->DataGrid->DataSource=$resultado;
              $this->DataGrid->dataBind();
          }
    }

/* Esta funcion se encarga de colocarme en negrita el registro que cumpla con la
 * condicion.
 */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        switch($item->estatus->Text)
        {
            case "PA":
                $item->estatus->Text = "Pasado";
                break;
            case "PR":
                $item->Font->Bold="true";
                $item->estatus->Text = "Presente";
                break;
            case "FU":
                $item->estatus->Text = "Futuro";
                break;
        }
    }

	public function changePage($sender,$param)
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.estatus_presupuesto
                           where cod_organizacion = '$cod_organizacion' order by ano";
		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'P&aacute;gina: ');
	}

}

?>
