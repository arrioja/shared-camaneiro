<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Mediante esta página, el usuario puede modificar la vacacion de un funcionario,.
 *              desde su fecha de inicio y los dias de disfrute.
 *****************************************************  FIN DE INFO
*/

class interrumpir_vacacion extends TPage
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
     * la interrupcion, que no sea menor al de la vacacion, feriado ni fin de semana.
     */
    public function validar_fecha_desde($sender, $param)
    {
        $sql="SELECT * FROM asistencias.vacaciones_disfrute WHERE id='".$this->Request[id]."'";
        $datos_vacacion=cargar_data($sql,$sender);
        //$vacacion_desde=cambiaf_a_normal($datos_vacacion[0]['fecha_desde']);
       // $vacacion_hasta=cambiaf_a_normal($datos_vacacion[0]['fecha_hasta']);

        $dias_feriados = dias_feriados($sender);
        $fecha = $this->txt_fecha_desde->Text;

        // si es_feriado = 0 entonces es laborable normal
       if ((cambiaf_a_mysql($fecha) >= $datos_vacacion[0]['fecha_desde'])&&(cambiaf_a_mysql($fecha) <= $datos_vacacion[0]['fecha_hasta']) && (es_feriado($fecha, $dias_feriados, $sender) == 0)){
            $param->IsValid = true;
             //$this->mensaje_v->setErrorMessage($sender, "".cambiaf_a_mysql($fecha)." - ".$datos_vacacion[0]['fecha_desde'], 'grow');
        }else{ 
            $param->IsValid = false;       
       }//fin si
     

    }
    /* Esta función se encarga de realizar validaciones a la fecha de inicio de
     * la interrupcion, que no sea menor al de la vacacion, feriado ni fin de semana.
     */
    public function validar_fecha_hasta($sender, $param)
    {
        $sql="SELECT * FROM asistencias.vacaciones_disfrute WHERE id='".$this->Request[id]."'";
        $datos_vacacion=cargar_data($sql,$sender);
        $dias_feriados = dias_feriados($sender);
        $fecha = $this->txt_fecha_desde->Text;
        $fecha2 = $this->txt_fecha_hasta->Text;

        // si es_feriado = 0 entonces es laborable normal
       if ((cambiaf_a_mysql($fecha2) >= cambiaf_a_mysql($fecha))&&(cambiaf_a_mysql($fecha2) <= $datos_vacacion[0]['fecha_hasta']) && (es_feriado($fecha2, $dias_feriados, $sender) == 0)){
       $param->IsValid = true; }else{$param->IsValid = false;}//fin si


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
            $this->txt_fecha_hasta->Text=cambiaf_a_normal($datos_vacacion[0]['fecha_hasta']);
            
            //$this->mensaje_v->setSuccessMessage($sender, "".$this->txt_fecha_desde->Text, 'grow');

            for ($x = 0 ; $x <= $dias_acumulados ; $x++)
            {
                if ($x < 10)
                  {   $dia_acum ='0'.$x;}
                else
                  {$dia_acum = $x;}
                $dias_acum[$dia_acum] = $dia_acum;
            }
           // $this->lbl_num_dias->Text = $dias_acumulados; // el valor de este label es usado en incluirbtn
           /* $this->num_dias->Datasource = $dias_acum;
            $this->num_dias->dataBind();
            $this->num_dias->SelectedValue=rellena($datos_vacacion[0]['dias_disfrute'],2,"0");*/

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
            $this->btn_incluir->Enabled=false;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $cedula=$this->txt_cedula->Text;
            $dias_feriados = dias_feriados($sender);
          
            //Obtenemos datos de la vacacion
            $sql="SELECT * FROM asistencias.vacaciones_disfrute WHERE id='".$this->Request[id]."'";
            $datos_vacacion=cargar_data($sql,$sender);
            $codigo_just=$datos_vacacion[0]['referencia'];
            $periodo=$datos_vacacion[0]['periodo'];
            $dias_disfrute=$datos_vacacion[0]['dias_disfrute'];
            $vacacion_desde=cambiaf_a_normal($datos_vacacion[0]['fecha_desde']);
            $vacacion_hasta=cambiaf_a_normal($datos_vacacion[0]['fecha_hasta']);
            $observacion=" ".$this->txt_observacion->Text;
            $ndias_trabajo= count(dias_entre_fechas($this->txt_fecha_desde->Text,$this->txt_fecha_hasta->Text, "0", $dias_feriados, $sender));
            
             if(($vacacion_desde==$this->txt_fecha_desde->Text)&&($vacacion_hasta==$this->txt_fecha_hasta->Text)){

                    if($this->correr_dias->checked==false){//se cancela la vacacion y se elimina la justificacion
                         $modificaciones="!Vacacion Cancelada¡";
                         // se modifica los datos de la vacacion a disfrutar
                         $sql="UPDATE asistencias.vacaciones_disfrute
                               SET estatus='3',referencia='',observaciones=observaciones+'$observacion'
                                WHERE id='".$this->Request[id]."'" ;
                         $resultado=modificar_data($sql,$sender);
                         $sql="UPDATE asistencias.vacaciones
                               SET disfrutados=disfrutados-'$ndias_trabajo', pendientes=pendientes+'$ndias_trabajo'
                               WHERE cedula='$cedula' AND periodo='$periodo'";
                         $resultado=modificar_data($sql,$sender);
                         eliminar_justificacion($codigo_just, $sender);
                       
                     }else{//si va a correr la vacacion
                        $fecha1=suma_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender);
                        $fecha2=suma_dias_habiles($vacacion_hasta, $ndias_trabajo+1, $dias_feriados, $sender);
                        $modificaciones.=" Desde $fecha1 Hasta $fecha2";
                        /*se actualiza vacacion*/
                       $this->actualiza_vacacion($sender,$param,$fecha1,$fecha2,$periodo,$codigo_just);
                        
                     }//finsi

            }else{
                    //numero de dias que trabajo
                    $num_dias2= count(dias_entre_fechas($this->txt_fecha_desde->Text,$this->txt_fecha_hasta->Text, "0", $dias_feriados, $sender));
                    $igual_hasta=false;

                     //se corre el desde o el hasta, de la vacacion
                    if (($vacacion_desde==$this->txt_fecha_desde->Text)||($vacacion_hasta==$this->txt_fecha_hasta->Text)){

                        if($vacacion_desde==$this->txt_fecha_desde->Text){
                            $fecha1= suma_dias_habiles($this->txt_fecha_hasta->Text, "2", $dias_feriados, $sender);
                            $fecha2=$vacacion_hasta;
                            $modificaciones.="  1) Desde $fecha1 Hasta $fecha2";
                            $igual_hasta=true;
                        }elseif($vacacion_hasta==$this->txt_fecha_hasta->Text){
                            $fecha1=$vacacion_desde;
                            $fecha2= resta_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender);
                            $modificaciones.="  2) Desde $fecha1 Hasta $fecha2";
                        }//fin si

                        // si se debe correr los dias justo despues de la vacacion
                        if($this->correr_dias->checked==true){
                         $correr= suma_dias_habiles(suma_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                         $modificaciones.="</br> y Correr dias Desde ".suma_dias_habiles($vacacion_hasta, 2, $dias_feriados, $sender)."  Hasta $correr ";

                            //si la fecha final es igual a la final de la interrupcion
                            if($igual_hasta==true){
                                $fecha2=$correr;
                            }else{// sino se crea otra vacacion
                             $fecha3=suma_dias_habiles($this->txt_fecha_hasta->Text, 2, $dias_feriados, $sender);
                             $fecha4=$correr;
                             /*se crea vacacion*/
                             $this->crear_vacacion($sender, $param,$fecha3,$fecha4,$periodo);
                             $dias_disfrute=$dias_disfrute-$dias_disfrute2;
                            }//fin si

                        }else{// se resta de la vacacion
                            $dias_disfrute=$dias_disfrute-$ndias_trabajo;
                           // se actualiza vacaciones
                           $sql="UPDATE asistencias.vacaciones
                                SET disfrutados=disfrutados-'$ndias_trabajo', pendientes=pendientes+'$ndias_trabajo'
                                WHERE cedula='$cedula' AND periodo='$periodo' ";
                          $resultado=modificar_data($sql,$sender);
                        }//fin si

                       /*se actualiza primera vacacion*/
                       $this->actualiza_vacacion($sender,$param,$fecha1,$fecha2,$periodo,$codigo_just);

                }else{//si es en dias intermedios

                        $fecha1=$vacacion_desde;
                        $fecha2= resta_dias_habiles($this->txt_fecha_desde->Text, "2", $dias_feriados, $sender);
                        $fecha3= suma_dias_habiles($this->txt_fecha_hasta->Text, "2", $dias_feriados, $sender);
                        $fecha4=$vacacion_hasta;
                        $modificaciones.="  1) Desde $fecha1 Hasta $fecha2</br> 2) Desde $fecha3 Hasta $fecha4</br>";

                        // si se debe correr los dias justo despues de la vacacion
                        if($this->correr_dias->checked==true){
                         $fecha4=suma_dias_habiles(suma_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                         $modificaciones.=" y Correr Vacacion Desde ".suma_dias_habiles($vacacion_hasta, 2, $dias_feriados, $sender)."  Hasta $fecha4 ";
                        }else{// se resta de la vacacion
                           $sql="UPDATE asistencias.vacaciones
                                SET disfrutados=disfrutados-'$ndias_trabajo', pendientes=pendientes+'$ndias_trabajo'
                                WHERE cedula='$cedula' AND periodo='$periodo'";
                          $resultado=modificar_data($sql,$sender);
                        }//fin si

                       /*se actualiza primera vacacion*/
                       $this->actualiza_vacacion($sender,$param,$fecha1,$fecha2,$periodo,$codigo_just);
                       /*se crea vacacion*/
                       $this->crear_vacacion($sender, $param,$fecha3,$fecha4,$periodo);
                }//fin si*/

         }//fin si modificacion de vacaciones
           

        // Una vez finalizado todo el proceso, se incluye el rastro en el archivo de bitácora
        // y se muestra un mensaje informativo al usuario acerca del resultado de su solicitud.
        $descripcion_log = "Modificada vacacion de: ".$this->txt_nombre->Text." C.I.: ".$cedula.
                               " de Fecha ".$vacacion_desde." hasta ".$vacacion_hasta.", Quedando establecida de la siguiente manera:</br> $modificaciones";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        //$this->mensaje_v->setSuccessMessage($sender, "".$descripcion_log, 'grow');
       // $this->mensaje_v->setSuccessMessage($sender, "$modificaciones", 'grow');
        $this->LTB->titulo->Text = "Modificacion de Vacacion Exitosa";
        $this->LTB->texto->Text = $descripcion_log;
        $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
        $this->LTB->redir->Text = "asistencias.vacaciones.listar_vacacion";
        $params = array('mensaje');
        $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

        }
    }

public function actualiza_vacacion($sender, $param,$fecha1,$fecha2,$periodo,$codigo_just){
    
        $cod_organizacion = usuario_actual('cod_organizacion');
        $dias_feriados = dias_feriados($sender);
        $observacion=" ".$this->txt_observacion->Text;
        $dias_disfrute= count(dias_entre_fechas($fecha1,$fecha2, "0", $dias_feriados, $sender));
    
        $sql=" select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
        $horarios=cargar_data($sql,$sender);
        $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha1),$dias_disfrute,$horarios,$dias_feriados,$sender);
        $num_feriados = cuenta_feriados($fecha1, $dias_disfrute, $dias_feriados, $sender);
        $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha1),$sender);
        $hora_desde = $horario_vigente[0]['hora_entrada'];
        $hora_hasta = $horario_vigente[0]['hora_salida'];

        $motivo="Disfrute de ".$dias_disfrute." dias de vacaciones correspondientes al período ".$periodo.", a partir del dia ".$fecha1." hasta el ".$fecha2.".";

        // se modifica los datos de la vacacion a disfrutar
        $sql="UPDATE asistencias.vacaciones_disfrute
              SET dias_disfrute='$dias_disfrute', dias_feriados='$num_feriados', dias_restados='$dias_restados',
              fecha_desde='".cambiaf_a_mysql($fecha1)."',fecha_hasta='".cambiaf_a_mysql($fecha2)."', observaciones='$motivo.$observacion'
              WHERE id='".$this->Request[id]."'";
       $resultado=modificar_data($sql,$sender);

        //actualizo la fecha hasta de la justificacion
        $sql="UPDATE asistencias.justificaciones_dias
               SET fecha_desde='".cambiaf_a_mysql($fecha1)."',hora_desde='$hora_desde',fecha_hasta='".cambiaf_a_mysql($fecha2)."',hora_hasta='$hora_hasta',observaciones='$motivo.$observacion'
               WHERE codigo_just='$codigo_just' ";
        $resultado=modificar_data($sql,$sender);
}

 	public function crear_vacacion($sender, $param,$fecha_desde,$fecha_hasta,$periodo){

        $cod_organizacion = usuario_actual('cod_organizacion');
        $cedula=$this->txt_cedula->Text;
        $dias_feriados = dias_feriados($sender);
        $observacion=" ".$this->txt_observacion->Text;
      
        $sql="select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
        $horarios=cargar_data($sql,$sender);
        $dias_disfrute2= count(dias_entre_fechas($fecha_desde,$fecha_hasta, "0", $dias_feriados, $sender));
        $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha_desde),$dias_disfrute2,$horarios,$dias_feriados,$sender);
        $num_feriados = cuenta_feriados($fecha_desde, $dias_disfrute2, $dias_feriados, $sender);

        //se inserta los datos de la vacacion
        $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha_desde),$sender);
        $hora_desde = $horario_vigente[0]['hora_entrada'];
        $hora_hasta = $horario_vigente[0]['hora_salida'];

        $codigo_nuevo=proximo_numero("asistencias.justificaciones","codigo",null,$sender);

        $sqli="insert into asistencias.justificaciones (codigo, tipo_id_doc, estatus)
               values ('$codigo_nuevo','VA', '1')";
        $resultadoi=modificar_data($sqli,$sender);
        $sqli="insert into asistencias.justificaciones_personas (cedula, codigo_just)
               values ('$cedula','$codigo_nuevo')";
        $resultadoi=modificar_data($sqli,$sender);

        $motivo="Disfrute de ".$dias_disfrute2." dias de vacaciones correspondientes al período ".$periodo.", a partir del dia ".$fecha_desde." hasta el ".$fecha_hasta.".";

        $sqli="insert into asistencias.justificaciones_dias (codigo_just, fecha_desde, hora_desde, fecha_hasta,
                      hora_hasta, codigo_tipo_falta, descuenta_ticket,lun,mar,mie,jue,vie,codigo_tipo_justificacion,
                      observaciones)
               values ('$codigo_nuevo','".cambiaf_a_mysql($fecha_desde)."', '$hora_desde', '".cambiaf_a_mysql($fecha_hasta)."', '$hora_hasta','IN','No','1','1','1','1','1',
                       '1','$motivo.$observacion')";
        $resultadoi=modificar_data($sqli,$sender);


        $sql="INSERT INTO asistencias.vacaciones_disfrute (cedula,dias_disfrute, dias_feriados, dias_restados,
              fecha_desde,fecha_hasta,periodo,observaciones,referencia,estatus)
              VALUES ('$cedula','$dias_disfrute2','$num_feriados','$dias_restados',
              '".cambiaf_a_mysql($fecha_desde)."','".cambiaf_a_mysql($fecha_hasta)."','$periodo','$motivo.$observacion','$codigo_nuevo','1')";
        $resultado=modificar_data($sql,$sender);
    }


     public function btn_cancelar_click($sender, $param){

        $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.listar_vacacion'));//

    }



}

?>
