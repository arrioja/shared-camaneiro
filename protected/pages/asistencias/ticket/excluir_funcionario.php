<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: Ronald A. Salazar C.
 * Descripcion: Permite al operador incluir o excluir del control de asistencias para el descuento de ticket
 *              a los funcionarios que se le especifique.
 *****************************************************  FIN DE INFO
*/
class excluir_funcionario extends TPage
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
        $sql="Select p.cedula as id , u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 			  FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
			  WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion')
              AND NOT EXISTS (SELECT * FROM asistencias.excluidos as t WHERE t.cedula=p.cedula))
              ORDER BY p.nombres, p.apellidos";
       
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid_tiene->DataSource=$resultado;
		$this->DataGrid_tiene->dataBind();

        $sql="select e.id, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.excluidos as e
                            INNER JOIN organizacion.personas as p ON (e.cedula=p.cedula)
						ORDER BY e.id DESC ";
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid_no_tiene->DataSource=$resultado;
		$this->DataGrid_no_tiene->dataBind();
    }
/* agrega a la tabla de exluidos el funcionario seleccionado*/
	public function agregar($sender,$param)
	{
        $id=$this->DataGrid_tiene->DataKeys[$param->Item->ItemIndex];

        $sql="INSERT INTO asistencias.excluidos
                (cedula)VALUES ('$id')";
        $resultado=modificar_data($sql,$this);

        /*Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "Se ha excluido el usuario de CI:".$id." al grupo sin control de horario, tomado para el calculo de ticket".$codigo;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->actualiza_listados($sender);
	}

/* elimina de la tabla de excluidos el funcionario seleccionado*/

	public function eliminar($sender,$param)
	{

        $id=$this->DataGrid_no_tiene->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM asistencias.excluidos
              where (id = '$id')";
        $resultado=modificar_data($sql,$this);

        /*Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "Se ha Eliminado el usuario de CI:".$id." de la Exclusion del grupo sin control de horario, tomado para el calculo de ticket".$codigo;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);


        $this->actualiza_listados($sender);
	}

  
}

?>
