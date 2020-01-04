<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripcion: Permite al operador incluir o excluir del control de asistencias
 *              a los funcionarios que se le especifique.
 *****************************************************  FIN DE INFO
*/
class listado_controlados extends TPage
{

	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $this->actualiza_listados($this);
          }
    }


    public function actualiza_listados($sender)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select u.id, u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
						ORDER BY p.nombres, p.apellidos";
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid_tiene->DataSource=$resultado;
		$this->DataGrid_tiene->dataBind();

        $sql="select u.id, u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '0') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
						ORDER BY p.nombres, p.apellidos";
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid_no_tiene->DataSource=$resultado;
		$this->DataGrid_no_tiene->dataBind();
    }

	public function agregar($sender,$param)
	{
//        $resultado_drop = obtener_seleccion_drop($this->drop_usuarios);
//        $login = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
   //     $item=$param->Item;
        $id=$this->DataGrid_no_tiene->DataKeys[$param->Item->ItemIndex];

//        $val = $param->Item->Data;

        $sql="update asistencias.personas_status_asistencias set status_asistencia = '1'
              where (id = '$id')";
        $resultado=modificar_data($sql,$this);

        /* Se incluye el rastro en el archivo de bitácora */
//        $descripcion_log = "Se ha incluido a: ".$val." al control de la asistencia";
//        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->actualiza_listados($sender);
	}

	public function eliminar($sender,$param)
	{
//        $resultado_drop = obtener_seleccion_drop($this->drop_usuarios);
//        $login = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $id=$this->DataGrid_tiene->DataKeys[$param->Item->ItemIndex];
        $sql="update asistencias.personas_status_asistencias set status_asistencia = '0'
              where (id = '$id')";
        $resultado=modificar_data($sql,$this);

        /* Se incluye el rastro en el archivo de bitácora */
//        $descripcion_log = "Se hSe ha incluido el usuario ".$login." al grupo ".$codigo;
//        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->actualiza_listados($sender);
	}



}

?>
