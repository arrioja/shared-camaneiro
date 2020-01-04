<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo implementa la inclusión de usuarios en el sistema;
 *              es requisito que se encuentren inscritos como personas, y que
 *              además se encuentren asignados a una Organización, Dirección
 *              u un Nivel.
     ****************************************************  FIN DE INFO
*/

class incluir_usuarios extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
    }

    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo exista en el sistema y se colocan los datos de la persona
     * que resulte seleccionada.
     */
    public function validar_cedula($sender, $param)
    {
        $param->isValid = true;
        $cedula=$this->txt_cedula->Text;
        // Para comprobar que existen los datos de la persona
        $sql="select nombres, apellidos from organizacion.personas where cedula='$cedula'";
        $datos=cargar_data($sql,$sender);
        if ($datos == '')
        { // si no existe, se vacian los controles para forzar validación
            $this->txt_nombre->Text = "";
            $this->txt_apellido->Text = "";
            $param->isValid = false;
        }
        else
        { // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_nombre->Text = $datos[0]['nombres'];
            $this->txt_apellido->Text = $datos[0]['apellidos'];
        }

        // para comprobar que la persona ya tenga asociada una Organizacion
        $sql="select o.nombre from organizacion.organizaciones o, organizacion.personas_nivel_dir n
              where (n.cedula ='$cedula') and (n.cod_organizacion = o.codigo)";
        $datos=cargar_data($sql,$sender);
        if ($datos == '')
        { // si no existe, se vacian los controles para forzar validación
            $this->txt_organizacion->Text = "";
            $param->isValid = false;
        }
        else
        { // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_organizacion->Text = $datos[0]['nombre'];
        }

        // para comprobar que la persona ya tenga asociada una Dirección
        $sql="select d.nombre_completo from organizacion.direcciones d, organizacion.personas_nivel_dir n
              where (n.cedula ='$cedula') and (n.cod_direccion = d.codigo) and
              (n.cod_organizacion = d.codigo_organizacion)";
        $datos=cargar_data($sql,$sender);
        if ($datos == '')
        { // si no existe, se vacian los controles para forzar validación
            $this->txt_direccion->Text = "";
            $param->isValid = false;
        }
        else
        { // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_direccion->Text = $datos[0]['nombre_completo'];
        }

        // para comprobar que la persona ya tenga asociada un Nivel
        $sql="select ni.nombre from organizacion.nivel ni, organizacion.personas_nivel_dir n
              where (n.cedula ='$cedula') and (ni.codigo_organizacion = n.cod_organizacion)
              and (ni.codigo = n.nivel)";
        $datos=cargar_data($sql,$sender);
        if ($datos == '')
        { // si no existe, se vacian los controles para forzar validación
            $this->txt_nivel->Text = "";
            $param->isValid = false;
        }
        else
        { // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_nivel->Text = $datos[0]['nombre'];
        }
    }

    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cedula = $this->txt_cedula->Text;
            $login = $this->txt_login->Text;
            $clave=substr(MD5($this->txt_clave->Text), 0, 200);
            $email = $this->txt_email->Text;

            /* Se guardan los datos del usuario. */
            $sql="insert into intranet.usuarios (cedula,login, clave, email)
                  values ('$cedula','$login','$clave','$email')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluido el usuario C.I.: ".$cedula." Nombre: ".$this->txt_nombre->Text." ".$this->txt_apellido->Text." Login: ".$login;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
 	}
}

?>
