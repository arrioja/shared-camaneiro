<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  En esta página se asigna a una persona registrada a una
 *              Organización, Dirección y Nivel dentro de ella.
     ****************************************************  FIN DE INFO
*/

class asignar_direccion extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            /* para llenar el drop down de las organizaciones*/
            $sql="select codigo, nombre from organizacion.organizaciones order by nombre";
            $organizaciones=cargar_data($sql,$this);
            $this->drop_organizaciones->DataSource=$organizaciones;
            $this->drop_organizaciones->dataBind();
          }
    }
/* Esta función se encarga de implementar el evento on_intemchange del dropdown
 * de organizaciones actualizando el listado de direcciones y de nivel de usuario.
 */
    public function actualiza_drops($sender,$param)
    {
        //Actualización del drop de direcciones
        $codigo_organizacion = $this->drop_organizaciones->SelectedValue;
        $sql="select codigo, nombre_completo from organizacion.direcciones where (codigo_organizacion='$codigo_organizacion' ) order by nombre_completo";
        $resultado=cargar_data($sql,$this);
		$this->drop_direcciones->DataSource=$resultado;
		$this->drop_direcciones->dataBind();

        // Actualización del Drop de Niveles
        $sql="select codigo, nombre from organizacion.nivel where (codigo_organizacion='$codigo_organizacion' ) order by codigo";
        $resultado=cargar_data($sql,$this);
		$this->drop_niveles->DataSource=$resultado;
		$this->drop_niveles->dataBind();

    }

    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo exista en el sistema y se colocan los datos de la persona
     * que resulte seleccionada. se valida además que la persona pertenezca a la
     * misma institución que el usuario actual.
     */
    public function validar_cedula($sender, $param)
    {
        // este condicional es para asegurarme que el procedimiento se dispare por acción del boton de comprobar cedula
        // y no por acción del boton incluir, porque no se coloca el condicional, siempre se ejecuta esta comprobación
        // y por siempre se recarga sus valores originales antes de guardar y como consecuencia, los valores del campo
        // siempre permanecen iguales.  Esto se debe a que en el momento de presionar el boton incluir, el condicional
        // $this->IsValid dispara todos los procedimientos de los CustomValidators y este es uno de ellos.
        if ($sender->ID == 'btn_validar_cedula')
        {
            $cedula=$this->txt_cedula->Text;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select p.nombres, p.apellidos, pnd.cod_organizacion, pnd.cod_direccion, pnd.nivel
                    from organizacion.personas p, organizacion.personas_nivel_dir pnd
                  where ((p.cedula='$cedula') and (pnd.cedula = p.cedula) and
                        ((pnd.cod_organizacion = 'XXXXXX') or (pnd.cod_organizacion = '$cod_organizacion')))";
            $datos_persona=cargar_data($sql,$sender);
            if (empty($datos_persona ))
            { // si no existe, se vacian los controles para forzar validación
                $this->txt_nombre->Text = "";
                $this->txt_apellido->Text = "";
                $param->isValid = false;
            }
            else
            { // si existe, se colocan los datos de la persona en los campos correspondientes
                $this->txt_nombre->Text = $datos_persona[0]['nombres'];
                $this->txt_apellido->Text = $datos_persona[0]['apellidos'];
                $this->drop_organizaciones->SelectedValue = $datos_persona[0]['cod_organizacion'];
                $this->actualiza_drops($sender,$param);
                $this->drop_direcciones->SelectedValue = $datos_persona[0]['cod_direccion'];
                $this->drop_niveles->SelectedValue = $datos_persona[0]['nivel'];

                $this->lbl_cod_direccion->Text = $datos_persona[0]['cod_direccion'];
                $this->lbl_cod_organizacion->Text = $datos_persona[0]['cod_organizacion'];

                $param->isValid = true;
            }
        }
    }

/* Esta función se encarga de validar si la cedula ya se encuentra incluida 
 * en alguna organización diferente a la actual, con esto se evita que el usuario le cambie
 * alguna asignacion a algun usuario de otra institución.
 */
    public function validar_cedula_en_direccion($sender, $param)
    {   
        $cedula=$this->txt_cedula->Text;
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select p.nombres, p.apellidos
                from organizacion.personas p, organizacion.personas_nivel_dir pnd
              where ((p.cedula='$cedula') and (pnd.cedula = p.cedula) and
                    ((pnd.cod_organizacion = 'XXXXXX') or (pnd.cod_organizacion = '$cod_organizacion')))";
        $datos_persona=cargar_data($sql,$sender);
        if (!empty($datos_persona ))
        { // si hay un dato, la persona es nueva o de mi organizacion, asi que si la puedo editar            
            $param->isValid = true;
        }
        else
        { // Si no existe, quiere decir que no se ha registrado o no es de esta organizacion, asi que no se puede editar
            $param->isValid = false;
        }
    }


	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cedula = $this->txt_cedula->Text;
            $codigo_organizacion = $this->drop_organizaciones->SelectedValue;
            $codigo_direccion = $this->drop_direcciones->SelectedValue;
            $codigo_nivel = $this->drop_niveles->SelectedValue;

            $cod_organizacion_actual = usuario_actual('cod_organizacion');
            $sql="select pnd.id, p.nombres, p.apellidos
                    from organizacion.personas p, organizacion.personas_nivel_dir pnd
                  where ((p.cedula='$cedula') and (pnd.cedula = p.cedula) and
                        ((pnd.cod_organizacion = 'XXXXXX') or (pnd.cod_organizacion = '$cod_organizacion_actual')))";
            $datos_persona=cargar_data($sql,$sender);            
            if (!empty($datos_persona ))
            { // si existe lo puedo editar, y como el registro se incluye en la pagina de incluir personas, en esta parte siempre
                // se va a editar, nunca se va a incluir.
                $id = $datos_persona[0]['id']; 
                $sql="update organizacion.personas_nivel_dir set cod_organizacion = '$codigo_organizacion',
                        cod_direccion = '$codigo_direccion', nivel = '$codigo_nivel'
                      where (id = '$id')";
                $resultado=modificar_data($sql,$sender);






                // Se comprueba que la persona tenga un registro en usuarios_vistas, si no lo tiene, se incluye el nuevo registro
                // pero si lo tiene, se modifica el que existe.

                $codigo_direccion_uv = $this->lbl_cod_direccion->Text;
                $codigo_organizacion_uv = $this->lbl_cod_organizacion->Text;
                $sql="select uv.id
                        from intranet.usuarios_vistas uv
                      where ((uv.cedula='$cedula') and (uv.cod_organizacion = '$codigo_organizacion_uv')  and
                             (uv.cod_direccion = '$codigo_direccion_uv'))";
                $datos_uv=cargar_data($sql,$sender);
                if (empty($datos_uv ))
                { // si no hay datos se incluye uno nuevo
                    // Se guardan los datos del nivel de vista del usuario
                    $sql="insert into intranet.usuarios_vistas (cedula, cod_organizacion, cod_direccion)
                          values ('$cedula','$codigo_organizacion','$codigo_direccion')";
                    $resultado=modificar_data($sql,$sender);
                }
                else
                {// si el registro esta, se modifica.
                    $iduv = $datos_uv[0]['id'];
                    $sql="update intranet.usuarios_vistas set cod_organizacion = '$codigo_organizacion', cod_direccion = '$codigo_direccion'
                          where id = '$iduv'";
                    $resultado=modificar_data($sql,$sender);
                }

                $this->lbl_cod_direccion->Text = $codigo_direccion;
                $this->lbl_cod_organizacion->Text = $codigo_organizacion;


                // Se incluye el rastro en el archivo de bitácora
                $descripcion_log = "Vinculada la persona: ".$cedula." Nombre: ".$this->txt_nombre->Text." ".$this->txt_apellido->Text." a la Org: ".$codigo_organizacion." en la Dirección:".$codigo_direccion;
                inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

//                $this->Response->redirect($this->Service->constructUrl('Home'));
            }





/*
            // Se guardan los datos del nivel administrativo del usuario. 
            $sql="insert into organizacion.personas_nivel_dir (cedula, cod_organizacion, cod_direccion, nivel)
                  values ('$cedula','$codigo_organizacion','$codigo_direccion','$codigo_nivel')";
            $resultado=modificar_data($sql,$sender);

            // Se guardan los datos del nivel de vista del usuario 
            $sql="insert into intranet.usuarios_vistas (cedula, cod_organizacion, cod_direccion)
                  values ('$cedula','$codigo_organizacion','$codigo_direccion')";
            $resultado=modificar_data($sql,$sender);

            // Se incluye el rastro en el archivo de bitácora 
            $descripcion_log = "Vinculada la persona: ".$cedula." Nombre: ".$this->txt_nombre->Text." ".$this->txt_apellido->Text." a la Org: ".$codigo_organizacion." en la Dirección:".$codigo_direccion;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));*/
        }
 	}
}

?>
