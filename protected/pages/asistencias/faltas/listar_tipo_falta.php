<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra un listado con aquellas posibles faltas que se puedan
 *              cometer a la Asistencia.
 *****************************************************  FIN DE INFO
*/
class listar_tipo_falta extends TPage
{

	public function onLoad($param)
	{
			//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from asistencias.tipo_faltas where (cod_organizacion = '$cod_organizacion') order by descripcion";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}

	public function changePage($sender,$param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from asistencias.tipo_faltas where (cod_organizacion = '$cod_organizacion') order by descripcion";
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
