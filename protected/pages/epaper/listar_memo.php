<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Presenta un listado de la memoranda hecha en la dirección
 *              seleccionada; ésto depende del permiso que tenga el usuario
 *              para ver cuál dirección.
 *****************************************************  FIN DE INFO
*/
class listar_memo extends TPage
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
        $sql="select CONCAT(siglas,'-',correlativo,'-',ano) as num_memo,
              fecha, destinatario, asunto, status from organizacion.memoranda
              where ((direccion='".substr($cod_direccion, 1)."') and (ano = '$ano')) order by fecha desc ";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
    }

/* Esta funcion se encarga de colocarme en negrita el registro que cumpla con la
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
    public function ver_detalle($sender,$param)
    {
        $nummemo=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('epaper.detalles_memo',array('nummemo'=>$nummemo)));//
    }
    public function adjuntar($sender,$param)
    {
        $nummemo=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('epaper.adjuntarmemo',array('nummemo'=>$nummemo)));
    }
}

?>
