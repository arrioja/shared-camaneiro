<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Presenta un listado de la memoranda hecha en la dirección
 *              seleccionada; con la finalidad de que se anule o se active.
 *****************************************************  FIN DE INFO
*/
class anular_memo extends TPage
{
	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              // se llena el listado solo con las organizaciones que el usuario
              // tiene permiso a ver (no sólo a la que pertenece).
              $cedula = usuario_actual('cedula');
              $resultado = vista_usuario($cedula,null,'O',$this);
              $this->drop_organizaciones->DataSource=$resultado;
              $this->drop_organizaciones->dataBind();
              $this->drop_organizaciones->Selectedvalue=usuario_actual('cod_organizacion');
              $this->drop_ano->Selectedvalue=date("Y");
              $sql="SELECT distinct ano FROM organizacion.memoranda WHERE ano!=' ' order by ano desc";
              $resultado=cargar_data($sql,$this);
              //$this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $this->drop_ano->Datasource =$resultado;
              $this->drop_ano->dataBind();
              $this->actualiza_drops($this,$param);
          }
	}
    /* Se encarga de actualizar el listado de las Direcciones a las que tiene
     * permiso el usuario actual para ver, (dependiendo de la organizacion que
     * se haya seleccionado.
     */
    public function actualiza_drops($sender,$param)
    {
        $cedula = usuario_actual('cedula');
        $cod_organizacion = $this->drop_organizaciones->SelectedValue;
        $resultado = vista_usuario($cedula,$cod_organizacion,'D',$sender);
		$this->drop_direcciones->DataSource=$resultado;
		$this->drop_direcciones->dataBind();
    }

    /* Actualiza el listado de la memoranda dependiendo de la dirección seleccionada */
    public function actualiza_listado($sender,$param)
    {
        $cod_direccion = $this->drop_direcciones->SelectedValue;
        $ano = $this->drop_ano->SelectedValue;
        $sql="select id, CONCAT(siglas,'-',correlativo,'-',ano) as num_memo,
              fecha, destinatario, asunto, status from organizacion.memoranda
              where ((direccion='".substr($cod_direccion, 1)."') and (ano = '$ano')) order by fecha desc";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
    }

	public function anular($sender,$param)
	{
		$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="Update organizacion.memoranda set status = '2' WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $descripcion_log = "Anulado el memo registrado bajo el ID ".$id;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'M',$descripcion_log,"",$sender);

		$this->actualiza_listado($sender,$param);

	}


/* Esta funcion se encarga de colocarme en rojo el registro que cumpla con la
 * condicion.
 */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if ($item->fecha->Text != "Fecha")
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
        }
        if ($item->status->Text == "2")
        {
            $item->BackColor = "Red";
        }
    }


}

?>
