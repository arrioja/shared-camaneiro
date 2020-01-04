<?php

class listar_niveles extends TPage
{

	public function onLoad($param)
	{
        $sql="select codigo, nombre from organizacion.organizaciones order by nombre";
        $organizaciones=cargar_data($sql,$this);
        $this->drop_organizaciones->DataSource=$organizaciones;
        $this->drop_organizaciones->dataBind();
	}
    
/* esta función se encarga de implementar el evento on_intemchange del dropdown*/
    public function actualiza_listado($sender,$param)
    {
        //Busqueda de Registros
        $resultado_drop = obtener_seleccion_drop($this->drop_organizaciones);
        $codigo_organizacion = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $sql="select codigo, nombre from organizacion.nivel where (codigo_organizacion='$codigo_organizacion' ) order by codigo desc";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
    }

	public function changePage($sender,$param)
	{
        $sql="select codigo, nombre from organizacion.nivel where (codigo_organizacion='$codigo_organizacion' ) order by codigo desc";
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
