<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripcion General:  Vincula a los usuarios registrados con las direcciones
 *                       que tendra privilegios para ver.
 *****************************************************  FIN DE INFO
*/
class asignar_usuarios_vista extends TPage
{

	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $sql="select u.cedula, u.login, u.email, CONCAT(p.nombres,' ',p.apellidos,' - ',u.cedula) as nombre_completo, n.cod_organizacion
                            FROM intranet.usuarios as u, organizacion.personas as p, organizacion.personas_nivel_dir n
                            WHERE ((u.cedula=p.cedula) and (u.cedula = n.cedula))
                            ORDER BY p.nombres, p.apellidos";
            $resultado=cargar_data($sql,$this);
            $this->drop_usuarios->DataSource=$resultado;
            $this->drop_usuarios->dataBind();
          }
	}

/* esta función se encarga de implementar el evento on_intemchange del dropdown
 * de usuarios, en la cual se actualiza el listado de los grupos a los que el
 * usuario seleccionado pertenece y a los que no.
 */
    public function actualiza_listado()
    {

        $cedula=$this->drop_usuarios->SelectedValue;

        $sql="SELECT cod_organizacion from organizacion.personas_nivel_dir WHERE (cedula = '$cedula') ";
        $resultado=cargar_data($sql,$this);
        $cod_organizacion=$resultado[0]['cod_organizacion'];

        //Se actualiza el grid de las vistas a los que pertenece
       
        $sql="SELECT uv.id,uv.cod_direccion as codigo,c.nombre_completo as nombre
                FROM intranet.usuarios_vistas AS uv
                LEFT JOIN organizacion.direcciones AS c ON ( c.codigo = uv.cod_direccion AND c.codigo_organizacion = uv.cod_organizacion )
                WHERE (uv.cedula ='$cedula' AND uv.cod_organizacion ='$cod_organizacion')
                ORDER BY uv.cod_direccion ASC ";

        $resultado=cargar_data($sql,$this);
		$this->DataGrid_tiene->DataSource=$resultado;
		$this->DataGrid_tiene->dataBind();
        // Se actualiza el grid de los grupos a los que no pertenece
        $sql2="SELECT d.nombre_completo AS nombre, d.codigo
                FROM organizacion.direcciones AS d
                WHERE d.codigo_organizacion =  '$cod_organizacion'
                AND d.codigo NOT
                IN (SELECT uv.cod_direccion FROM intranet.usuarios_vistas AS uv WHERE uv.cedula =  '$cedula' AND uv.cod_organizacion =  '$cod_organizacion' )
                ORDER BY d.codigo ASC";
        $resultado2=cargar_data($sql2,$this);
		$this->DataGrid_no_tiene->DataSource=$resultado2;
		$this->DataGrid_no_tiene->dataBind();
    }

	public function agregar($sender,$param)
	{
        $cedula=$this->drop_usuarios->SelectedValue;

        $sql="SELECT cod_organizacion from organizacion.personas_nivel_dir WHERE (cedula = '$cedula') ";
        $resultado=cargar_data($sql,$this);
        $cod_organizacion=$resultado[0]['cod_organizacion'];

        $codigo=$this->DataGrid_no_tiene->DataKeys[$param->Item->ItemIndex];
        $sql="insert into intranet.usuarios_vistas(cedula,cod_organizacion,cod_direccion) values ('$cedula','$cod_organizacion','$codigo')";
        $resultado=modificar_data($sql,$this);

        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "Se ha incluido la Vista del Funcionario ".$cedula." a la Direccion ".$codigo;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->actualiza_listado();
	}

	public function eliminar($sender,$param)
	{
		$id=$this->DataGrid_tiene->DataKeys[$param->Item->ItemIndex];
        $sql="delete from intranet.usuarios_vistas WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();
	}
}

?>
