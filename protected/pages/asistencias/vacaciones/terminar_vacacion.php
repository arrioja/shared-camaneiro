<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Mediante esta página, el usuario puede modificar la vacacion de un funcionario,.
 *              desde su fecha de inicio y los dias de disfrute.
 *****************************************************  FIN DE INFO
*/

class terminar_vacacion extends TPage
{
    var $vacio = array(); // para vaciar listados y combos en caso de que la cedula no sea correcta
    
    public function onLoad($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->validar_cedula($this,$param);
        }

    }

    /* esta función se encarga de dar formato dd/mm/aaaa a las fechas que se
     * muestren como parte del listado de vacaciones disponibles del funcionario.
     */
    public function formatear_fecha($sender, $param)
    {
       $item=$param->Item;
        if ($item->fecha_desde->Text != "")
        {
            $item->fecha_desde->Text = cambiaf_a_normal($item->fecha_desde->Text);
        }

    }

    /* Esta función se encarga de realizar validaciones a la fecha de inicio de
     * las vacaciones, que no sea feriado ni que sea fin de semana.
     */
    public function validar_fecha_inicio($sender, $param)
    {
        $dias_feriados = dias_feriados($sender);
        $fecha = $this->txt_fecha_desde->Text;
        //$arre_fecha=split("/",$this->txt_fecha_desde->Text);
        //$fecha="01/01/".$arre_fecha[2];
        $fecha_actual_my = date('Y-m-d');
        // si es_feriado = 0 entonces es laborable normal
//        if ((cambiaf_a_mysql($fecha) >= $fecha_actual_my) && (es_feriado($fecha, $dias_feriados, $sender) == 0))
//      La condición siguiente obvia el hecho de que se este solicitando una vacación antes de la
//      fecha actual, esto es posible, aunque no recomendable, para que la misma funcione
//      se debe comentar la de arriba y descomentar la de abajo.
        if (es_feriado($fecha, $dias_feriados, $sender) == 0)
        { $param->IsValid = true; }
        else
        { $param->IsValid = false; }
    }

    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo exista en el sistema y se colocan los datos de la persona
     * que resulte seleccionada.
     */
    public function validar_cedula($sender, $param)
    {

        //se carga datos de vacacion seleccionada
        $sql="SELECT * FROM asistencias.vacaciones_disfrute WHERE id='".$this->Request[id]."'";
        $datos_vacacion=cargar_data($sql,$sender);
        $cedula=$datos_vacacion[0]['cedula'];
        $this->txt_cedula->Text=$cedula;
        $this->txt_observacion->Text=$datos_vacacion[0]['observaciones'];
        $this->periodo->Text=$datos_vacacion[0]['periodo'];

        $cod_organizacion = usuario_actual('cod_organizacion');
        // Para comprobar que existen los datos de la persona en la organización del usuario.
        $sql="select p.nombres, p.apellidos from organizacion.personas p, organizacion.personas_nivel_dir n
              where (p.cedula='$cedula') and (n.cod_organizacion = '$cod_organizacion') and (p.cedula = n.cedula)";
        $datos=cargar_data($sql,$sender);
        if (empty($datos) == true)
        { // si no existe, se vacian los controles para forzar validación y
          // muestro un mensaje de error al usuario para que sepa que la cedula no se encuentra
            $this->LTB->titulo->Text = "Número de Cédula no encontrado";
            $this->LTB->texto->Text = "La cédula que introdujo no se encuentra en nuestros registros, ".
                                      "compruebe que sea correcta e inténtelo de nuevo, si el problema ".
                                      "persiste, comuníquese con la Dirección de Sistemas.";
            $this->LTB->imagen->Imageurl = "imagenes/botones/cedula_no.png";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);    
            
        }else{ // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_nombre->Text = $datos[0]['nombres']." ".$datos[0]['apellidos'];
            //se muestra datos en repeater
            $this->Repeater->DataSource=$datos_vacacion;
            $this->Repeater->dataBind();
          

            $sql="select sum(v.pendientes) as sumatoria from asistencias.vacaciones as v
                   where((v.cedula='$cedula') and (v.pendientes>'0'))
				   order by v.disponible_desde";
            $datos_sumatoria=cargar_data($sql,$sender);
            // se suma los dias de la vacacion a modificar
            //$dias_acumulados = $datos_sumatoria[0]['sumatoria']+$datos_vacacion[0]['dias_disfrute'];
            // se suma solo los dias a modificar
            $dias_acumulados = $datos_vacacion[0]['dias_disfrute'];
            $this->txt_fecha_desde->Text=cambiaf_a_normal($datos_vacacion[0]['fecha_desde']);
            
            for ($x = 0 ; $x <= $dias_acumulados ; $x++)
            {
                if ($x < 10)
                  {   $dia_acum ='0'.$x;}
                else
                  {$dia_acum = $x;}
                $dias_acum[$dia_acum] = $dia_acum;
            }
            $this->lbl_num_dias->Text = $dias_acumulados; // el valor de este label es usado en incluirbtn
            $this->num_dias->Datasource = $dias_acum;
            $this->num_dias->dataBind();
            $this->num_dias->SelectedValue=rellena($datos_vacacion[0]['dias_disfrute'],2,"0");

            $this->btn_incluir->Enabled=true;
           

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
            $cod_organizacion = usuario_actual('cod_organizacion');
            $cedula=$this->txt_cedula->Text;

            $dias_a_sumar=$this->lbl_num_dias->Text-$this->num_dias->SelectedValue;
            
            //Obtenemos el codigo de la justificacion relacionada a la vacacion
            $sql="SELECT referencia,fecha_hasta FROM asistencias.vacaciones_disfrute
                    WHERE id='".$this->Request[id]."'";
            $datos_vacacion=cargar_data($sql,$sender);
            $codigo_just=$datos_vacacion[0]['referencia'];
            $fecha_final_a=cambiaf_a_normal($datos_vacacion[0]['fecha_hasta']);
            $fecha_inicio = $this->txt_fecha_desde->Text;
            
            // si es cero los dias a disfrutar se elimina la justificacion
           
            if($this->num_dias->SelectedValue==0){// si es cero los dias a disfrutar se elimina la justificacion

            // se actualiza las vacaciones para cancelarla
            $sql="UPDATE asistencias.vacaciones_disfrute
                   SET estatus='3',referencia=''
                   WHERE id='".$this->Request[id]."'";
            $resultado=modificar_data($sql,$sender);

            //se elimina la justificacion
            $sql="DELETE FROM asistencias.justificaciones WHERE codigo='$codigo_just'";
            $resultado=modificar_data($sql,$sender);
            $sql="DELETE FROM asistencias.justificaciones_dias WHERE codigo_just='$codigo_just'";
            $resultado=modificar_data($sql,$sender);
            $sql="DELETE FROM asistencias.justificaciones_personas WHERE codigo_just='$codigo_just'";
            $resultado=modificar_data($sql,$sender);

            $dias_disfrute = $this->lbl_num_dias->Text;
            $dias_feriados = dias_feriados($sender);
            $fecha_fin  = suma_dias_habiles($fecha_inicio, $dias_disfrute, $dias_feriados, $sender);
            $fecha_final_a=$fecha_fin;
            $fecha_fin_my="00/00/0000";
           }else{//si el numero de dias es menor al total a disfrutar
                // se actualiza el numero de dias a disfrutar en vacaciones y justificacion
                
            // se modifica los datos de la vacacion del periodo
             $sql2="UPDATE asistencias.vacaciones
                   SET disfrutados=disfrutados-$dias_a_sumar, pendientes=pendientes+$dias_a_sumar
                   WHERE cedula='$cedula' AND periodo='".$this->periodo->Text."'";
            $resultado=modificar_data($sql2,$sender);

            $dias_disfrute = $this->num_dias->SelectedValue;
           
            $dias_feriados = dias_feriados($sender);
            $fecha_fin  = suma_dias_habiles($fecha_inicio, $dias_disfrute, $dias_feriados, $sender);
            $num_feriados_total = cuenta_feriados($fecha_inicio, $dias_disfrute, $dias_feriados, $sender);
            $observacion=$this->txt_observacion->Text;

            // se pasan al formato mysql las fechas
            $fecha_fin_my = cambiaf_a_mysql($fecha_fin);
            $fecha_inicio_my = cambiaf_a_mysql($fecha_inicio);

            //se obtiene datos del horario
            $sql="select * from asistencias.opciones
                  where ((cod_organizacion = '$cod_organizacion')) order by status";
            $horarios=cargar_data($sql,$sender);

            $dias_restados = calcula_dias_restados($fecha_inicio_my,$dias_disfrute,$horarios,$dias_feriados,$sender);
            $num_feriados = cuenta_feriados($fecha_inicio, $dias_disfrute, $dias_feriados, $sender);
            $fecha_fin2_my  = cambiaf_a_mysql(suma_dias_habiles($fecha_inicio, $dias_disfrute, $dias_feriados, $sender));

            // se modifica los datos de la vacacion a disfrutar
            $sql2="UPDATE asistencias.vacaciones_disfrute
                  SET dias_disfrute='$dias_disfrute', dias_feriados='$num_feriados', dias_restados='$dias_restados',
                  fecha_hasta='$fecha_fin2_my', observaciones='$observacion'
                  WHERE id='".$this->Request[id]."'";
           $resultado=modificar_data($sql2,$sender);

            //se obtiene el horario vigente
            $horario_vigente = obtener_horario_vigente($cod_organizacion,$fecha_inicio_my,$sender);
            $hora_hasta = $horario_vigente[0]['hora_salida'];
            $hasta=$fecha_fin2_my;

            //actualizo la fecha hasta de la justificacion
            $sql="UPDATE asistencias.justificaciones_dias
                   SET fecha_hasta='$hasta',hora_hasta='$hora_hasta'
                   WHERE codigo_just='$codigo_just'";
            $resultado=modificar_data($sql,$sender);

           }//fin si


           // Una vez finalizado todo el proceso, se incluye el rastro en el archivo de bitácora
          // y se muestra un mensaje informativo al usuario acerca del resultado de su solicitud.
           $descripcion_log = "Modificada vacacion de: ".$this->txt_nombre->Text." C.I.: ".$cedula.
                               " de Fecha ".$fecha_inicio." hasta ".$fecha_final_a." por Fecha ".$fecha_inicio." hasta ".cambiaf_a_normal($fecha_fin_my);
           inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

          // $this->mensaje_v->setSuccessMessage($sender, "".$descripcion_log, 'grow');

                   
                    $this->LTB->titulo->Text = "Solicitud de Vacaciones Exitosa";
                    $this->LTB->texto->Text = $descripcion_log;
                    $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                    $this->LTB->redir->Text = "asistencias.vacaciones.listar_vacacion";
                    $params = array('mensaje');
                    $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

                    //$this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.consulta_cedula',array('ced'=>$cedula)));
         

        }
      
 	}


     public function btn_cancelar_click($sender, $param){

        $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.listar_vacacion'));//

    }



}

?>
