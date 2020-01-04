<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: Ronald A. Salazar C.
 * Descripcion: Permite al operador incluir o excluir del control de asistencias
 *              a los funcionarios que se le especifique.
 *****************************************************  FIN DE INFO
*/
class incluir_justificacion_grupal extends TPage
{

	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $this->lbl_codigo_temporal->Text = aleatorio_numero($this,'asistencias.justificaciones_personas_temporal');
            $this->actualiza_listados($this);
          }
    }


    public function actualiza_listados($sender)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select p.cedula as id , u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion')
                        AND NOT EXISTS (SELECT * FROM asistencias.justificaciones_personas_temporal as t WHERE (t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula)))
                        ORDER BY p.nombres, p.apellidos";
       
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid_tiene->DataSource=$resultado;
		$this->DataGrid_tiene->dataBind();

        $sql="select t.id, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.justificaciones_personas_temporal as t, organizacion.personas as p
						WHERE ((t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula) )
						ORDER BY id DESC";
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid_no_tiene->DataSource=$resultado;
		$this->DataGrid_no_tiene->dataBind();
    }
/* agrega a la tabla temporal las cedulas de los sujetos seleccionados para justificacion*/
	public function agregar($sender,$param)
	{
        $id=$this->DataGrid_tiene->DataKeys[$param->Item->ItemIndex];

        $sql="INSERT INTO asistencias.justificaciones_personas_temporal
                (numero,cedula)VALUES ('".$this->lbl_codigo_temporal->Text."','$id')";
        $resultado=modificar_data($sql,$this);
 
        $this->actualiza_listados($sender);
	}

	public function eliminar($sender,$param)
	{
//        $resultado_drop = obtener_seleccion_drop($this->drop_usuarios);
//        $login = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $id=$this->DataGrid_no_tiene->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM asistencias.justificaciones_personas_temporal
              where (id = '$id')";
        $resultado=modificar_data($sql,$this);

        /* Se incluye el rastro en el archivo de bitácora */
//        $descripcion_log = "Se hSe ha incluido el usuario ".$login." al grupo ".$codigo;
//        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->actualiza_listados($sender);
	}

    public function incluir($sender,$param)
    {

         $numero=$this->lbl_codigo_temporal->Text;
						

        $this->Response->Redirect( $this->Service->constructUrl('asistencias.justificaciones.incluir_justificacion_grupal2',array('numero'=>$numero)));//

        /*foreach($this->DataGrid->Columns as $index=>$column)
            $column->Visible=$sender->Items[$index]->Selected;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();*/
    }



}

?>
