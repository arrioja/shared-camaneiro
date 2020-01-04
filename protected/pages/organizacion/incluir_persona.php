<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M. / Carlos A. Ávila P
 * Descripción: Muestra la forma para incluir datos de personas en el sistema,
 *              de manera genérica para que puedan luego ser usados como
 *              usuarios, personal de nomina, etc etc.
 *****************************************************  FIN DE INFO
*/

class incluir_persona extends TPage
{
    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo no exista en el sistema.
     */
var $var_ubicacion;
    public function validar_cedula($sender, $param)
    {
        $param->IsValid=(verificar_existencia('organizacion.personas','cedula',$this->txt_cedula->Text,null,$sender));
        $this->txt_nombre->ReadOnly = !($param->IsValid);
        $this->txt_apellido->ReadOnly = !($param->IsValid);
    }

    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function btn_incluir_click($sender, $param)
	{
       if ($this->IsValid)
        {   $this->btn_incluir->Enabled=false;
            // Se capturan los datos provenientes de los Controles
            $cedula = $this->txt_cedula->Text;
            $nombres = $this->txt_nombre->Text;
            $apellidos = $this->txt_apellido->Text;
            $fecha_nac = cambiaf_a_mysql($this->txt_fecha_nac->Text);
            $lugar_nac = $this->txt_lugar_nac->Text;
            $sexo = $this->drop_sexo->Text;
            $estado_civil = $this->drop_estado_civil->Text;
            $profesion = $this->txt_profesion->Text;
            $grado_instruccion = $this->txt_grado_instruccion->Text;
            $telefono_hab = $this->txt_telefono_hab->Text;
            $telefono_cel = $this->txt_telefono_cel->Text;
            $direccion = $this->txt_direccion->Text;

            $sql="insert into organizacion.personas
                    (cedula,nombres,apellidos,fecha_nacimiento, lugar_nacimiento,
                    sexo,edo_civil,profesion, grado_instruccion,
                    direccion, tlf_habitacion,tlf_celular)
                  values ('$cedula','$nombres','$apellidos','$fecha_nac','$lugar_nac','$sexo',
                          '$estado_civil','$profesion','$grado_instruccion',
                          '$direccion','$telefono_hab','$telefono_cel')";
            $resultado=modificar_data($sql,$sender);
            //se crea carpeta de la persona
            $dir=$_SERVER['DOCUMENT_ROOT']."/cene/expedientes/$cedula";
            mkdir($dir, 0777, true);
            chmod($_SERVER['DOCUMENT_ROOT']."/cene/expedientes/$cedula", 0777);
            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido la persona C.I.: ".$cedula." <p> ".$nombres." ".$apellidos;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            /* Si la inclusión de la persona es exitosa, se mete en el sistema
            * de asistencias para que cuando se desee habilitar se haga.
            * sin problemas.
            */
            $sql="insert into asistencias.personas_status_asistencias (cedula,status_asistencia)
                         values ('$cedula','0')";
            $resultado=modificar_data($sql,$sender);
            /* Adicionalmente, se incluye la cedula dentro de la definicion
            * de niveles y direcciones con el mínimo posible para una
            * persona con cero privilegios
            */
            $sql="insert into organizacion.personas_nivel_dir
                        (cedula,nivel,cod_direccion, cod_organizacion)
                  values ('$cedula','00','XXXXX','XXXXXX')";
            $resultado=modificar_data($sql,$sender);
            /* Si el proximo if se cumple, quiere decir que no hubo errores y  es
            * seguro hacer el commit y ademas redireccionar la pagina al home.
            */
         // se incluye el registro en la bitácora de eventos

            $this->LTB->titulo->Text = "Incluido Exitosamente";
            $this->LTB->texto->Text = $descripcion_log;
            $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
            $this->LTB->redir->Text = "organizacion.incluir_persona";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
 

       }
	}
   public function btn_limpiar_click($sender, $param)
	{
         $this->Response->redirect($this->Service->constructUrl('organizacion.incluir_persona'));
    }

     public function fileUploaded($sender,$param)
    {
        if($sender->HasFile)
        {
            $nombreclean=htmlspecialchars($sender->FileName);
            $hh=date("H")+8;
            $hora = date("d-m-Y $hh:i:s");
            $nuevodirectorio=$_SERVER['DOCUMENT_ROOT']."/cene/imagenes/fotos/$hora.$nombreclean";
            $filename=$sender->FileName;
            $filetemp=$sender->localName;
            $this->var_ubicacion="cene/imagenes/fotos/$hora.$nombreclean";

            if(copy($filetemp, $nuevodirectorio)) $this->Result->Text="Subida con Exito!";
            else $this->Result->Text="Error!";


        }

    }
    
}

?>
