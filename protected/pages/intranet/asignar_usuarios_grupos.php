<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripcion General:  Vincula a los usuarios registrados con el grupo al cual
 *                       pertenece para tener sus privilegios y accesos.
 *****************************************************  FIN DE INFO
*/
class asignar_usuarios_grupos extends TPage
{

	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $sql="select u.cedula, u.login, u.email, CONCAT(u.cedula,' - ',p.nombres,' ',p.apellidos) as nombre_completo, n.cod_organizacion
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
        // se captura el valor del drop de usuarios
        $resultado_drop = obtener_seleccion_drop($this->drop_usuarios);
        $login = $resultado_drop[1]; // el segundo valor que corresponde con el codigo

        // se localiza la cédula dependiendo del valor del drop seleccionado.
        $sql="SELECT cedula from intranet.usuarios WHERE (login = '$login')";
        $resultado=cargar_data($sql,$this);
        $cedula=$resultado[0]['cedula'];

        /* Con el número de cédula del usuario, se localiza ahora el código de
         * la organización a la que pertenece el usuario para que los grupos
         * disponibles sólo sean los de su organización.
         */
        $sql="SELECT cod_organizacion from organizacion.personas_nivel_dir WHERE (cedula = '$cedula')";
        $resultado=cargar_data($sql,$this);
        $cod_organizacion=$resultado[0]['cod_organizacion'];

        //Se actualiza el grid de los grupos a los que pertenece
        $sql="SELECT g.id, g.codigo, g.nombre, u.login, u.id as id2
			 			  FROM intranet.grupos as g
						  LEFT JOIN intranet.usuarios_grupos as u ON g.codigo = u.codigo
						  WHERE (u.login = '$login') order by g.codigo";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid_tiene->DataSource=$resultado;
		$this->DataGrid_tiene->dataBind();
        // Se actualiza el grid de los grupos a los que no pertenece
        $sql2="SELECT g.id, g.codigo, g.nombre
			 			   FROM intranet.grupos as g
						   WHERE (g.cod_organizacion = '$cod_organizacion') and (g.codigo not in (SELECT g.codigo
			 			  FROM intranet.grupos as g
						  LEFT JOIN intranet.usuarios_grupos as u ON g.codigo = u.codigo
						  WHERE (u.login = '$login'))) order by g.codigo";
        $resultado2=cargar_data($sql2,$this);
		$this->DataGrid_no_tiene->DataSource=$resultado2;
		$this->DataGrid_no_tiene->dataBind();
    }

	public function agregar($sender,$param)
	{
        $resultado_drop = obtener_seleccion_drop($this->drop_usuarios);
        $login = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
        $codigo=$this->DataGrid_no_tiene->DataKeys[$param->Item->ItemIndex];
        $sql="insert into intranet.usuarios_grupos(login,codigo) values ('$login','$codigo')";
        $resultado=modificar_data($sql,$this);

        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "Se ha incluido el usuario ".$login." al grupo ".$codigo;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->actualiza_listado();
	}

	public function eliminar($sender,$param)
	{
		$id=$this->DataGrid_tiene->DataKeys[$param->Item->ItemIndex];
        $sql="delete from intranet.usuarios_grupos WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();
	}
}

?>
