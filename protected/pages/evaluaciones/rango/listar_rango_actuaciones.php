<?php
/*   ****************************************************  INFO DEL ARCHIVO
 * Creado por:  Pedro E. Arrioja M.
 * Descripción: Muestra un listado de los rangos de actuación que se usan en el
 *              sistema.
     ****************************************************  FIN DE INFO
*/
class listar_rango_actuaciones extends TPage
{

	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $this->cargar_listado($this);
            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Consultado listado de Rango de Actuaciones";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'C',$descripcion_log,"",$this);
          }
		
	}

	public function cargar_listado($sender)
	{
        $sql="select * from evaluaciones.rangos_actuacion order by orden, descripcion";
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid->DataSource=$resultado;
        $this->lbl_num_registros->Text = count($resultado);
		$this->DataGrid->dataBind();        
	}

	public function changePage($sender,$param)
	{
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->cargar_listado($sender);
	}

/* esta función le da formato al listado presentado en pantalla*/
	public function formato($sender,$param)
	{
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            if ($item->ItemIndex == "0"){
                $item->ordenar->sube->Visible="false";
            }
            if (($item->ItemIndex+1) == $this->lbl_num_registros->Text) {
                $item->ordenar->baja->Visible="false";
            }
        }
	}

/* esta función implementa el cambio de orden en la lista, llevando un lugar
 * arriba al item que se ha seleccionado */
	public function subir_click($sender,$param)
	{
        $id=$sender->CommandParameter[0];
        $orden=$sender->CommandParameter[1];
        // coloco al que esta por e a mi mismo nivel
        $sql="UPDATE evaluaciones.rangos_actuacion SET orden = '$orden'
              WHERE (orden = '$orden'-1)";
        $resultado=modificar_data($sql,$sender);

        $sql2="UPDATE evaluaciones.rangos_actuacion SET orden = '$orden'-1
              WHERE (id = '$id')";
        $resultado2=modificar_data($sql2,$sender);

        $this->cargar_listado($sender);

        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "Cambiado Orden en el listado de rangos de actuación de evaluaciones";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'M',$descripcion_log,"",$sender);
	}

/* esta función implementa el cambio de orden en la lista, llevando un lugar
 * abajo al item que se ha seleccionado */
	public function bajar_click($sender,$param)
	{
        $id=$sender->CommandParameter[0];
        $orden=$sender->CommandParameter[1];
        // coloco al que esta por encima a mi mismo nivel
        $sql="UPDATE evaluaciones.rangos_actuacion SET orden = '$orden'
              WHERE (orden = '$orden'+1)";
        $resultado=modificar_data($sql,$sender);
        // sube el nivel del actual.
        $sql2="UPDATE evaluaciones.rangos_actuacion SET orden = '$orden'+1
              WHERE (id = '$id')";
        $resultado2=modificar_data($sql2,$sender);

        $this->cargar_listado($sender);

        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "Cambiado Orden en el listado de rangos de actuación de evaluaciones";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'M',$descripcion_log,"",$sender);

	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

}

?>
