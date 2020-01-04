<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald Salazar
 * Descripción: Muestra los datos de personas en el sistema para ser modificados, etc.
 *****************************************************  FIN DE INFO
*/

class modificar_persona extends TPage
{
    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo no exista en el sistema.
     */

    public function validar_cedula($sender, $param)
    {

       // $param->IsValid=(verificar_existencia('organizacion.personas','cedula',$this->txt_cedula->Text,null,$sender));
        $var=false;
        $var=(verificar_existencia('organizacion.personas','cedula',$this->txt_cedula->Text,null,$sender));

        
        if(!$var){
         $this->txt_nombre->ReadOnly = False;
         $this->txt_apellido->ReadOnly =False;
         $sql="SELECT * FROM organizacion.personas WHERE cedula='".$this->txt_cedula->Text."'";
         $resultado=cargar_data($sql,$sender);
         
            $this->txt_nombre->Text=$resultado[0][nombres];
            $this->txt_apellido->Text=$resultado[0][apellidos];
            $this->txt_fecha_nac->Text=cambiaf_a_normal($resultado[0][fecha_nacimiento]);
            $this->txt_fecha_in->Text=cambiaf_a_normal($resultado[0][fecha_ingreso]);
            $this->txt_lugar_nac->Text=$resultado[0][lugar_nacimiento];
            $this->drop_sexo->SelectedValue=$resultado[0][sexo];
            $this->drop_estado_civil->SelectedValue=$resultado[0][edo_civil];
            $this->txt_profesion->Text=$resultado[0][profesion];
            $this->txt_grado_instruccion->Text=$resultado[0][grado_instruccion];
            $this->txt_telefono_hab->Text=$resultado[0][tlf_habitacion];
            $this->txt_telefono_cel->Text=$resultado[0][tlf_celular];
            $this->txt_direccion->Text=$resultado[0][direccion];

        }else{
            $param->IsValid=False;

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
            $nombres = $this->txt_nombre->Text;
            $apellidos = $this->txt_apellido->Text;
            $fecha_nac = cambiaf_a_mysql($this->txt_fecha_nac->Text);
            $fecha_in = cambiaf_a_mysql($this->txt_fecha_in->Text);
            $lugar_nac = $this->txt_lugar_nac->Text;
            $sexo = $this->drop_sexo->Text;
            $estado_civil = $this->drop_estado_civil->Text;
            $profesion = $this->txt_profesion->Text;
            $grado_instruccion = $this->txt_grado_instruccion->Text;
            $telefono_hab = $this->txt_telefono_hab->Text;
            $telefono_cel = $this->txt_telefono_cel->Text;
            $direccion = $this->txt_direccion->Text;

            $sql="UPDATE organizacion.personas SET
                    nombres='$nombres',apellidos='$apellidos',fecha_nacimiento='$fecha_nac', lugar_nacimiento='$lugar_nac',
                    sexo='$sexo',edo_civil='$estado_civil',profesion='$profesion', grado_instruccion='$grado_instruccion',
                    direccion= '$direccion', tlf_habitacion='$telefono_hab',tlf_celular='$telefono_cel',fecha_ingreso='$fecha_in'
                    WHERE cedula='$cedula'";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha Modificado la persona C.I.: ".$cedula." / ".$nombres." ".$apellidos;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->LTB->titulo->Text = "Modificado";
            $this->LTB->texto->Text = $descripcion_log;
            $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
 

        }
	}
   public function btn_limpiar_click($sender, $param)
	{
         $this->Response->redirect($this->Service->constructUrl('organizacion.modificar_persona'));
    }
    
}

?>
