<?php

class listar_direcciones extends TPage
{
	public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
       {
        $sql="select codigo, nombre from organizacion.organizaciones order by nombre";
        $organizaciones=cargar_data($sql,$this);
        $this->drop_organizaciones->DataSource=$organizaciones;
        $this->drop_organizaciones->dataBind();
       }
	}
/* esta función se encarga de implementar el evento on_intemchange del dropdown*/
    public function actualiza_listado($sender,$param)
    {
        //Busqueda de Registros
        $resultado_drop = obtener_seleccion_drop($this->drop_organizaciones);
        $codigo_organizacion = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $sql="select id, siglas, nombre_completo, nombre_abreviado
            from organizacion.direcciones where (codigo_organizacion='$codigo_organizacion' )order by nombre_completo";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
    }
    public function editItem($sender,$param)
	{
		$this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
		$this->actualiza_listado($serder, $param);
	}

	public function anularItem($sender,$param)
	{
	/*	$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
		$this->Response->redirect($this->Service->constructUrl('Anular',array('id'=>$id)));*/
	}

    public function saveItem($sender,$param)
    {
        $item=$param->Item;
        $id=$this->DataGrid->DataKeys[$item->ItemIndex];

        $siglas=$item->siglas->TextBox->Text;
        $n_completo=$item->nombre->TextBox->Text;
        $n_abreviado=$item->abreviado->TextBox->Text;


		$sql="UPDATE organizacion.direcciones set nombre_completo='$n_completo', nombre_abreviado='$n_abreviado', siglas='$siglas' where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->actualiza_listado($serder, $param);
    }

}

?>
