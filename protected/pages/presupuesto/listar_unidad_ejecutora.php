<?php

class listar_unidad_ejecutora extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
                $this->actualiza_listado();
          }
    }
    
	public function actualiza_listado()
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
       // $ano = $this->drop_ano->Text;
        $sql="select CONCAT(u.sector,'-',u.programa,'-',u.subprograma,'-',u.proyecto,'-',u.actividad) as codigo_presupuestario,
              u.ano, o.codigo, o.nombre from presupuesto.unidad_ejecutora u, organizacion.organizaciones o
              where ((u.cod_organizacion = o.codigo) and (u.cod_organizacion = '$cod_organizacion'))
              order by u.ano, codigo_presupuestario";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}

	public function changePage($sender,$param)
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->Text;
        $sql="select CONCAT(u.sector,'-',u.programa,'-',u.subprograma,'-',u.proyecto,'-',u.actividad) as codigo_presupuestario,
              u.ano, o.codigo, o.nombre from presupuesto.unidad_ejecutora u, organizacion.organizaciones o
              where ((u.cod_organizacion = o.codigo) and (u.cod_organizacion = '$cod_organizacion'))
              order by u.ano, codigo_presupuestario";
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
