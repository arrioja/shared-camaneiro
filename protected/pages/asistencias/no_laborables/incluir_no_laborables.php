<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta ventana provee una forma de registrar en el sistema los días
 *              festivos y no laborables a ser tomados en cuenta en los diversos
 *              reportes de asistencia.   Cada día no laborable registrado tiene
 *              prioridad en dichos reportes, lo que significa que si un día es
 *              marcado como no laborable, será marcado en el reporte de esa
 *              manera y no se buscará ningún registro de asistencia para ese
 *              día aunque los hubiese.
 *****************************************************  FIN DE INFO
*/

class incluir_no_laborables extends TPage
{
    var $ano_inicio = 2009;
    var $ano_fin = 2021;
	public function onLoad($param)
	{
        if (!$this->isPostBack)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $meses = array('N/A'=>'Seleccione',
                           '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril',
                           '05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre',
                           '10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');

            $anos['N/A'] = 'Seleccione';
            $anos['XXXX'] = 'TODOS';
            for ($xd = $this->ano_inicio ; $xd <= $this->ano_fin ; $xd++) {
                $anos[$xd] = $xd;
            }

            $dias['N/A'] = 'Seleccione';
            for ($xd = 1 ; $xd <= 31 ; $xd++) {
                $dias[$xd] = $xd;
            }
            $this->drop_dia->DataSource=$dias;
            $this->drop_dia->dataBind();
            $this->drop_mes->DataSource=$meses;
            $this->drop_mes->dataBind();
            $this->drop_ano->DataSource=$anos;
            $this->drop_ano->dataBind();
        }
	}

/* Esta función provee de validación para las fechas que se deseen registrar,
 * se permiten sólo las fechas posibles, por ejemplo: 31 de Febrero no es una fecha
 * posible, no obstante, el 29 de Febrero si lo es (para algunos años).
 */
    public function verifica_fecha($sender,$param)
    {
        if ($this->drop_ano->SelectedValue == "XXXX")
            { // si se selecciona todos los años, se comprueba que la fecha sea válida para TODOS los años.
                $ano = $this->ano_inicio; $valido = true;
                while (($ano < $this->ano_fin) and ($valido == true))
                {
                    $valido = checkdate($this->drop_mes->SelectedValue, $this->drop_dia->SelectedValue, $ano);
                    $ano++;
                }
                $param->IsValid = $valido;
            }
        else // si se selecciona sólo un año, se comprueba sólo esa fecha
            {$param->IsValid = checkdate($this->drop_mes->SelectedValue, $this->drop_dia->SelectedValue, $this->drop_ano->SelectedValue);}
    }

/* Esta función se encarga de verificar que la fecha que se quiera introducir no
 * se encuentre regiatrada en la base de datos.
 */
    public function verifica_existencia($sender,$param)
    {
        $mes = $this->drop_mes->SelectedValue;
        $dia = $this->drop_dia->SelectedValue;
        $ano = $this->drop_ano->SelectedValue;
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="Select id from organizacion.dias_no_laborables
              where (dia = '$dia') and (mes = '$mes') and (ano = '$ano')
              and (cod_organizacion = '$cod_organizacion')";
        $consulta=cargar_data($sql,$sender);
        $param->IsValid = empty($consulta);
    }

    public function incluir_click($sender,$param)
    {
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $mes = $this->drop_mes->SelectedValue;
            $dia = $this->drop_dia->SelectedValue;
            $ano = $this->drop_ano->SelectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $descripcion = $this->txt_descripcion->Text;
            
            // se corre las vacaciones que tengan esa fecha
           // $this->modificar_vacaciones($sender, $param);

            // se corre las justificaciones que tengan esa fecha
           // $this->modificar_justificaciones($sender, $param);
            
            /* Se guarda en la base de datos */
            $sql="insert into organizacion.dias_no_laborables (cod_organizacion, dia, mes, ano, descripcion)
                  values ('$cod_organizacion','$dia','$mes','$ano','$descripcion')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido: ".$descripcion." el día: ".$dia."/".$mes."/".$ano." como no laborable.";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('asistencias.no_laborables.listar_no_laborables'));
        }
    }
     /* En esta funcion, se encarga de modificar las justificaciones que sean en dias habiles
      * y que coincidan con la fecha laborable del año en curso
     * */
	public function modificar_justificaciones_viejoprocedimiento($sender, $param)
	{
            // si selecciono todos los años
            if($this->drop_ano->SelectedValue=="XXXX")// utiliza el año actual
            $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".date("Y");
            else
            $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;

            $fecha_no_laborable_my=cambiaf_a_mysql($fecha_no_laborable);
            $cod_organizacion = usuario_actual('cod_organizacion');
            $dias_feriados = dias_feriados($sender);

            //Obtenemos datos de las justificaciones dentro de la fecha laborable
            $sql="SELECT jd.*,jp.cedula FROM asistencias.justificaciones_dias as jd
                  INNER JOIN asistencias.justificaciones_personas as jp ON (jp.codigo_just=jd.codigo_just)
                  INNER JOIN asistencias.tipo_justificaciones as tj ON(jd.codigo_tipo_justificacion=tj.codigo)
                  WHERE tj.dias_habiles='Si' AND jd.fecha_desde<='$fecha_no_laborable_my' AND jd.fecha_hasta>='$fecha_no_laborable_my' ";
            $justificaciones=cargar_data($sql,$sender);

            //recorre el arreglo para correr las justificaciones que este dentro del dia laborable
            foreach($justificaciones as $datos_justificacion){

            $cedula=$datos_justificacion['cedula'];
            $codigo_just=$datos_justificacion['codigo_just'];
            $justificacion_desde=cambiaf_a_normal($datos_justificacion['fecha_desde']);
            $justificacion_hasta=cambiaf_a_normal($datos_justificacion['fecha_hasta']);
            $hora_desde=$datos_justificacion['hora_desde'];
            $hora_hasta=$datos_justificacion['hora_hasta'];
            $tipo_just=$datos_justificacion['codigo_tipo_justificacion'];
            $tipo_falta=$datos_justificacion['codigo_tipo_falta'];
            $observacion=$datos_justificacion['observaciones'];
            $motivo=" Modificacion de Justificacion por Inclusion de dia no laborable $fecha_no_laborable";
            $ndias_trabajo= 1;

           
             if(($justificacion_desde==$fecha_no_laborable)&&($justificacion_hasta==$fecha_no_laborable)){

                    $fecha1= suma_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender);
                    $fecha2=suma_dias_habiles($justificacion_hasta, $ndias_trabajo+1, $dias_feriados, $sender);
                    $modificaciones.="  Justificacion Desde $fecha1 Hasta $fecha2";
                    $sql="select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
                    $horarios=cargar_data($sql,$sender);
                    $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha1),$dias_disfrute,$horarios,$dias_feriados,$sender);
                    $num_feriados = cuenta_feriados($fecha1, $dias_disfrute, $dias_feriados, $sender);
                    $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha1),$sender);
                    //$hora_desde = $horario_vigente[0]['hora_entrada'];
                    //$hora_hasta = $horario_vigente[0]['hora_salida'];
                    
                    //actualizo la fecha hasta de la justificacion
                    $sql="UPDATE asistencias.justificaciones_dias
                           SET fecha_desde='".cambiaf_a_mysql($fecha1)."',hora_desde='$hora_desde',fecha_hasta='".cambiaf_a_mysql($fecha2)."',hora_hasta='$hora_hasta', observaciones='$motivo.$observacion'
                           WHERE codigo_just='$codigo_just'";
                    $resultado=modificar_data($sql,$sender);

            }else{
                    //numero de dias que trabajo
                    $num_dias2= 1;
                    $igual_hasta="false";

                     //se corre el desde o el hasta, de la justificacion
                    if (($justificacion_desde==$fecha_no_laborable)||($justificacion_hasta==$fecha_no_laborable)){

                                if($justificacion_desde==$fecha_no_laborable){
                                    $fecha1= suma_dias_habiles($justificacion_desde, "2", $dias_feriados, $sender);
                                    $fecha2=$justificacion_hasta;
                                    $modificaciones.="  1) Desde $fecha1 Hasta $fecha2";
                                    $igual_hasta="true";
                                }elseif($justificacion_hasta==$fecha_no_laborable){
                                    $fecha1=$justificacion_desde;
                                    $fecha2= resta_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender);
                                    $modificaciones.="  2) Desde $fecha1 Hasta $fecha2";
                                }//fin si

                                // si se debe correr los dias despues del dia no laborable

                                 $correr= suma_dias_habiles(suma_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                                 $modificaciones.="</br> y Correr dias Desde ".suma_dias_habiles($justificacion_hasta, 2, $dias_feriados, $sender)."  Hasta $correr ";

                                    //si la fecha final es igual a la final de la interrupcion
                                    if($igual_hasta=="true"){
                                        $fecha2=$correr;
                                    }else{// sino se crea otra vacacion
                                     $fecha3=suma_dias_habiles($fecha_no_laborable, 2, $dias_feriados, $sender);
                                     $fecha4=$correr;
                                     //se calcula datos
                                        $sql="select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
                                        $horarios=cargar_data($sql,$sender);
                                        $dias_disfrute2= count(dias_entre_fechas($fecha3,$fecha4, "0", $dias_feriados, $sender));
                                        $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha3),$dias_disfrute2,$horarios,$dias_feriados,$sender);
                                        $num_feriados = cuenta_feriados($fecha3, $dias_disfrute2, $dias_feriados, $sender);

                                        //se inserta los datos de la justificacion
                                        $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha3),$sender);
                                        //$hora_desde = $horario_vigente[0]['hora_entrada'];
                                       // $hora_hasta = $horario_vigente[0]['hora_salida'];


                                        $codigo_nuevo=proximo_numero("asistencias.justificaciones","codigo",null,$sender);

                                        $sqli="insert into asistencias.justificaciones (codigo, tipo_id_doc, estatus)
                                               values ('$codigo_nuevo','RH', '1')";
                                        $resultadoi=modificar_data($sqli,$sender);
                                        $sqli="insert into asistencias.justificaciones_personas (cedula, codigo_just)
                                               values ('$cedula','$codigo_nuevo')";
                                        $resultadoi=modificar_data($sqli,$sender);

                                        $motivo="Justificacion por motivo de inclusion de dia laborable ".$fecha_no_laborable.", corrio Justificacion Codigo #".$codigo_just;

                                        $sqli="insert into asistencias.justificaciones_dias (codigo_just, fecha_desde, hora_desde, fecha_hasta,
                                                      hora_hasta, codigo_tipo_falta, descuenta_ticket,lun,mar,mie,jue,vie,codigo_tipo_justificacion,
                                                      observaciones)
                                               values ('$codigo_nuevo','".cambiaf_a_mysql($fecha3)."', '$hora_desde', '".cambiaf_a_mysql($fecha4)."', '$hora_hasta','$tipo_falta','No','1','1','1','1','1',
                                                       '$tipo_just','$motivo.$observacion')";
                                       $resultadoi=modificar_data($sqli,$sender);

                                    }//fin si


                                //se calcula datos
                                $sql="select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
                                $horarios=cargar_data($sql,$sender);
                                $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha1),$dias_disfrute,$horarios,$dias_feriados,$sender);
                                $num_feriados = cuenta_feriados($fecha1, $dias_disfrute, $dias_feriados, $sender);
                                $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha1),$sender);
                                //$hora_desde = $horario_vigente[0]['hora_entrada'];
                             //   $hora_hasta = $horario_vigente[0]['hora_salida'];
                                $motivo=" Modificacion de Justificacion por Inclusion de dia no laborable $fecha_no_laborable";

                                //actualizo la fecha hasta de la justificacion
                                $sql="UPDATE asistencias.justificaciones_dias
                                       SET fecha_desde='".cambiaf_a_mysql($fecha1)."',hora_desde='$hora_desde',fecha_hasta='".cambiaf_a_mysql($fecha2)."',hora_hasta='$hora_hasta', observaciones='$motivo.$observacion'
                                       WHERE codigo_just='$codigo_just'";
                                $resultado=modificar_data($sql,$sender);


                    }else{
                            $fecha1=$justificacion_desde;
                            $fecha2= resta_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                            $fecha3= suma_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                            $fecha4=$justificacion_hasta;
                            $modificaciones.="  1) Desde $fecha1 Hasta $fecha2</br>
                                           2) Desde $fecha3 Hasta $fecha4</br>";
                            $dias_disfrute= count(dias_entre_fechas($fecha1,$fecha2, "0", $dias_feriados, $sender));

                            // si se debe correr los dias justo despues de la justificacion
                                $correr= suma_dias_habiles(suma_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                                $fecha4=$correr;

                                 //se actualiza primera vacacion
                                $sql="select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
                                $horarios=cargar_data($sql,$sender);
                                $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha1),$dias_disfrute,$horarios,$dias_feriados,$sender);
                                $num_feriados = cuenta_feriados($fecha1, $dias_disfrute, $dias_feriados, $sender);
                                $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha1),$sender);
                                // $hora_desde = $horario_vigente[0]['hora_entrada'];
                                $hora_hasta = $horario_vigente[0]['hora_salida'];

                                $motivo=" Modificacion de Justificacion por Inclusion de dia no laborable $fecha_no_laborable";

                                //actualizo la fecha hasta de la justificacion
                                $sql="UPDATE asistencias.justificaciones_dias
                                       SET fecha_desde='".cambiaf_a_mysql($fecha1)."',hora_desde='$hora_desde',fecha_hasta='".cambiaf_a_mysql($fecha2)."',hora_hasta='$hora_hasta',observaciones='$motivo.$observacion'
                                       WHERE codigo_just='$codigo_just' ";
                                $resultado=modificar_data($sql,$sender);

                               //se actualiza segunda justificacion
                                $sql="select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
                                $horarios=cargar_data($sql,$sender);
                                $dias_disfrute2= count(dias_entre_fechas($fecha3,$fecha4, "0", $dias_feriados, $sender));
                                $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha3),$dias_disfrute2,$horarios,$dias_feriados,$sender);
                                $num_feriados = cuenta_feriados($fecha3, $dias_disfrute2, $dias_feriados, $sender);

                                //se inserta los datos de la justificacion
                                $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha3),$sender);
                                //$hora_desde = $horario_vigente[0]['hora_entrada'];
                                //$hora_hasta = $horario_vigente[0]['hora_salida'];

                                $codigo_nuevo=proximo_numero("asistencias.justificaciones","codigo",null,$sender);

                                $sqli="insert into asistencias.justificaciones (codigo, tipo_id_doc, estatus)
                                       values ('$codigo_nuevo','RH', '1')";
                                $resultadoi=modificar_data($sqli,$sender);
                                $sqli="insert into asistencias.justificaciones_personas (cedula, codigo_just)
                                       values ('$cedula','$codigo_nuevo')";
                                $resultadoi=modificar_data($sqli,$sender);

                                $motivo=" Modificacion de Justificacion por Inclusion de dia no laborable $fecha_no_laborable";

                                $sqli="insert into asistencias.justificaciones_dias (codigo_just, fecha_desde, hora_desde, fecha_hasta,
                                              hora_hasta, codigo_tipo_falta, descuenta_ticket,lun,mar,mie,jue,vie,codigo_tipo_justificacion,
                                              observaciones)
                                       values ('$codigo_nuevo','".cambiaf_a_mysql($fecha3)."', '$hora_desde', '".cambiaf_a_mysql($fecha4)."', '$hora_hasta','$tipo_falta','No','1','1','1','1','1',
                                               '$tipo_just','$motivo.$observacion')";
                                $resultadoi=modificar_data($sqli,$sender);
   
                    }//fin si

         }//fin si


        }// fin recorrer justificaciones

 	}
      /* En esta funcion, se encarga de modificar las justificaciones que sean en dias habiles
      * y que coincidan con la fecha laborable del año en curso
     * */
	public function modificar_justificaciones($sender, $param)
	{
            // si selecciono todos los años
            if($this->drop_ano->SelectedValue=="XXXX")// utiliza el año actual
            $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".date("Y");
            else
            $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;

            $fecha_no_laborable_my=cambiaf_a_mysql($fecha_no_laborable);
            $cod_organizacion = usuario_actual('cod_organizacion');
            $dias_feriados = dias_feriados($sender);

            //Obtenemos datos de las justificaciones dentro de la fecha laborable
            $sql="SELECT jd.*,jp.cedula FROM asistencias.justificaciones_dias as jd
                  INNER JOIN asistencias.justificaciones_personas as jp ON (jp.codigo_just=jd.codigo_just)
                  INNER JOIN asistencias.tipo_justificaciones as tj ON(jd.codigo_tipo_justificacion=tj.codigo)
                  WHERE tj.dias_habiles='Si' AND jd.fecha_desde<='$fecha_no_laborable_my' AND jd.fecha_hasta>='$fecha_no_laborable_my' ";
            $justificaciones=cargar_data($sql,$sender);

            //recorre el arreglo para correr las justificaciones que este dentro del dia laborable
            foreach($justificaciones as $datos_justificacion){

            $cedula=$datos_justificacion['cedula'];
            $codigo_just=$datos_justificacion['codigo_just'];
            $justificacion_desde=cambiaf_a_normal($datos_justificacion['fecha_desde']);
            $justificacion_hasta=cambiaf_a_normal($datos_justificacion['fecha_hasta']);
            $hora_desde=$datos_justificacion['hora_desde'];
            $hora_hasta=$datos_justificacion['hora_hasta'];
            $tipo_just=$datos_justificacion['codigo_tipo_justificacion'];
            $tipo_falta=$datos_justificacion['codigo_tipo_falta'];
            $observacion=$datos_justificacion['observaciones'];
            $motivo=" Modificacion de Justificacion por Inclusion de dia no laborable $fecha_no_laborable";
            $ndias_trabajo= 1;



                         if(($justificacion_desde==$fecha_no_laborable)&&($justificacion_hasta==$fecha_no_laborable)){

                        $fecha1=suma_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender);
                        $fecha2=suma_dias_habiles($justificacion_hasta, $ndias_trabajo+1, $dias_feriados, $sender);
                        $modificaciones.=" Desde $fecha1 Hasta $fecha2";
                        /*se actualiza vacacion*/
                        $this->actualiza($sender,$param,$fecha1,$fecha2,$periodo,$codigo_just,$id_vaca_dis,'justificacion',$cedula);

            }else{
                    //numero de dias que trabajo
                    $num_dias2= count(dias_entre_fechas($fecha_no_laborable,$fecha_no_laborable, "0", $dias_feriados, $sender));
                    $igual_hasta=false;

                     //se corre el desde o el hasta, de la vacacion
                    if (($justificacion_desde==$fecha_no_laborable)||($justificacion_hasta==$$fecha_no_laborable)){

                        if($justificacion_desde==$fecha_no_laborable){
                            $fecha1= suma_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                            $fecha2=$vacacion_hasta;
                            $modificaciones.="  1) Desde $fecha1 Hasta $fecha2";
                            $igual_hasta=true;
                        }elseif($justificacion_hasta==$fecha_no_laborable){
                            $fecha1=$justificacion_desde;
                            $fecha2= resta_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender);
                            $modificaciones.="  2) Desde $fecha1 Hasta $fecha2";
                        }//fin si

                        // si se debe correr los dias justo despues de la vacacion
                         $correr= suma_dias_habiles(suma_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                         $modificaciones.="</br> y Correr dias Desde ".suma_dias_habiles($justificacion_hasta, 2, $dias_feriados, $sender)."  Hasta $correr ";

                            //si la fecha final es igual a la final de la interrupcion
                            if($igual_hasta==true){
                                $fecha2=$correr;
                            }else{// sino se crea otra vacacion
                             $fecha3=suma_dias_habiles($fecha_no_laborable, 2, $dias_feriados, $sender);
                             $fecha4=$correr;
                             /*se crea vacacion*/
                             $this->crear($sender, $param,$fecha3,$fecha4,$periodo,'vacacion',$cedula);
                             $dias_disfrute=$dias_disfrute-$dias_disfrute2;
                            }//fin si
                       /*se actualiza primera vacacion*/
                       $this->actualiza($sender,$param,$fecha1,$fecha2,$periodo,$codigo_just,$id_vaca_dis,'justificacion',$cedula);

                }else{//si es en dias intermedios

                        $fecha1=$justificacion_desde;
                        $fecha2= resta_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                        $fecha3= suma_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                        $fecha4=$vacacion_hasta;
                        $modificaciones.="  1) Desde $fecha1 Hasta $fecha2</br> 2) Desde $fecha3 Hasta $fecha4</br>";

                        // si se debe correr los dias justo despues de la vacacion
                         $fecha4=suma_dias_habiles(suma_dias_habiles($justificacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                         $modificaciones.=" y Correr  Desde ".suma_dias_habiles($justificacion_hasta, 2, $dias_feriados, $sender)."  Hasta $fecha4 ";

                       /*se actualiza primera vacacion*/
                       $this->actualiza_vacacion($sender,$param,$fecha1,$fecha2,$periodo,$codigo_just,$id_vaca_dis,'justificacion',$cedula);
                       /*se crea vacacion*/
                       $this->crear($sender, $param,$fecha3,$fecha4,$periodo,'vacacion',$cedula);
                }//fin si*/
            }

            }// fin recorrer justificaciones

 	}
     /* En esta funcion, se encarga de modificar las vacaciones q coincidan con la fecha laborable del año en curso
     * */
	public function modificar_vacaciones($sender, $param)
	{
            // si selecciono todos los años
            if($this->drop_ano->SelectedValue=="XXXX")// utiliza el año actual
            $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".date("Y");
            else
            $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;

            $fecha_no_laborable_my=cambiaf_a_mysql($fecha_no_laborable);
            $cod_organizacion = usuario_actual('cod_organizacion');
            $dias_feriados = dias_feriados($sender);

            //Obtenemos datos de la vacaciones dentro de la fecha laborable
            $sql="SELECT * FROM asistencias.vacaciones_disfrute 
                WHERE fecha_desde<='$fecha_no_laborable_my' AND fecha_hasta>='$fecha_no_laborable_my'";
            $vacaciones=cargar_data($sql,$sender);

            //recorre el arreglo para correr las vacaciones que este dentro del dia laborable
            foreach($vacaciones as $datos_vacacion){

            $cedula=$datos_vacacion['cedula'];
            $id_vaca_dis=$datos_vacacion['id'];
            $codigo_just=$datos_vacacion['referencia'];
            $periodo=$datos_vacacion['periodo'];
            $dias_disfrute=$datos_vacacion['dias_disfrute'];
            $vacacion_desde=cambiaf_a_normal($datos_vacacion['fecha_desde']);
            $vacacion_hasta=cambiaf_a_normal($datos_vacacion['fecha_hasta']);
            $observacion=" Modificacion por Inclusion de dia no laborable $fecha_no_laborable";
            $ndias_trabajo=1;


             if(($vacacion_desde==$fecha_no_laborable)&&($vacacion_hasta==$fecha_no_laborable)){

                        $fecha1=suma_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender);
                        $fecha2=suma_dias_habiles($vacacion_hasta, $ndias_trabajo+1, $dias_feriados, $sender);
                        $modificaciones.=" Desde $fecha1 Hasta $fecha2";
                        /*se actualiza vacacion*/
                        $this->actualiza($sender, $param,$fecha1,$fecha2,$periodo,$codigo_just,$id_vaca_dis,'vacacion',$cedula);

            }else{
                    //numero de dias que trabajo
                    $num_dias2= count(dias_entre_fechas($fecha_no_laborable,$fecha_no_laborable, "0", $dias_feriados, $sender));
                    $igual_hasta=false;

                     //se corre el desde o el hasta, de la vacacion
                    if (($vacacion_desde==$fecha_no_laborable)||($vacacion_hasta==$$fecha_no_laborable)){

                        if($vacacion_desde==$fecha_no_laborable){
                            $fecha1= suma_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                            $fecha2=$vacacion_hasta;
                            $modificaciones.="  1) Desde $fecha1 Hasta $fecha2";
                            $igual_hasta=true;
                        }elseif($vacacion_hasta==$fecha_no_laborable){
                            $fecha1=$vacacion_desde;
                            $fecha2= resta_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender);
                            $modificaciones.="  2) Desde $fecha1 Hasta $fecha2";
                        }//fin si

                        // si se debe correr los dias justo despues de la vacacion
                         $correr= suma_dias_habiles(suma_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                         $modificaciones.="</br> y Correr dias Desde ".suma_dias_habiles($vacacion_hasta, 2, $dias_feriados, $sender)."  Hasta $correr ";

                            //si la fecha final es igual a la final de la interrupcion
                            if($igual_hasta==true){
                                $fecha2=$correr;
                            }else{// sino se crea otra vacacion
                             $fecha3=suma_dias_habiles($fecha_no_laborable, 2, $dias_feriados, $sender);
                             $fecha4=$correr;
                             /*se crea vacacion*/
                             $this->crear($sender, $param,$fecha3,$fecha4,$periodo,'vacacion',$cedula);
                             $dias_disfrute=$dias_disfrute-$dias_disfrute2;
                            }//fin si
                       /*se actualiza primera vacacion*/
                       $this->actualiza($sender, $param,$fecha1,$fecha2,$periodo,$codigo_just,$id_vaca_dis,'vacacion',$cedula);

                }else{//si es en dias intermedios

                        $fecha1=$vacacion_desde;
                        $fecha2= resta_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                        $fecha3= suma_dias_habiles($fecha_no_laborable, "2", $dias_feriados, $sender);
                        $fecha4=$vacacion_hasta;
                        $modificaciones.="  1) Desde $fecha1 Hasta $fecha2</br> 2) Desde $fecha3 Hasta $fecha4</br>";

                        // si se debe correr los dias justo despues de la vacacion
                         $fecha4=suma_dias_habiles(suma_dias_habiles($vacacion_hasta, "2", $dias_feriados, $sender), $num_dias2, $dias_feriados, $sender);
                         $modificaciones.=" y Correr Vacacion Desde ".suma_dias_habiles($vacacion_hasta, 2, $dias_feriados, $sender)."  Hasta $fecha4 ";

                       /*se actualiza primera vacacion*/
                       $this->actualiza($sender, $param,$fecha1,$fecha2,$periodo,$codigo_just,$id_vaca_dis,'vacacion',$cedula);
                       /*se crea vacacion*/
                       $this->crear($sender, $param,$fecha3,$fecha4,$periodo,'vacacion',$cedula);
                }//fin si*/

         }//fin si modificacion de vacaciones



    }// fin recorrer vacacion
           
 	}

    public function actualiza($sender, $param,$fecha1,$fecha2,$periodo,$codigo_just,$id,$tipo,$cedula){

        $cod_organizacion = usuario_actual('cod_organizacion');
        $dias_feriados = dias_feriados($sender);
        // si selecciono todos los años
        if($this->drop_ano->SelectedValue=="XXXX")// utiliza el año actual
        $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".date("Y");
        else
        $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;

        $fecha_no_laborable_my=cambiaf_a_mysql($fecha_no_laborable);
        $observacion=" Modificacion por Inclusion de dia no laborable $fecha_no_laborable";
        $dias_disfrute= count(dias_entre_fechas($fecha1,$fecha2, "0", $dias_feriados, $sender));

        $sql=" select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
        $horarios=cargar_data($sql,$sender);
        $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha1),$dias_disfrute,$horarios,$dias_feriados,$sender);
        $num_feriados = cuenta_feriados($fecha1, $dias_disfrute, $dias_feriados, $sender);
        $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha1),$sender);
        $hora_desde = $horario_vigente[0]['hora_entrada'];
        $hora_hasta = $horario_vigente[0]['hora_salida'];

        if($tipo=="vacacion"){//si es una vacacion
        // se modifica los datos de la vacacion a disfrutar
          $motivo="Disfrute de ".$dias_disfrute." dias de vacaciones correspondientes al período ".$periodo.", a partir del dia ".$fecha1." hasta el ".$fecha2.".";
        $sql="UPDATE asistencias.vacaciones_disfrute
              SET dias_disfrute='$dias_disfrute', dias_feriados='$num_feriados', dias_restados='$dias_restados',
              fecha_desde='".cambiaf_a_mysql($fecha1)."',fecha_hasta='".cambiaf_a_mysql($fecha2)."', observaciones='$motivo.$observacion'
              WHERE id='$id'";
        $resultado=modificar_data($sql,$sender);

        //actualizo la fecha hasta de la justificacion
        $sql="UPDATE asistencias.justificaciones_dias
               SET fecha_desde='".cambiaf_a_mysql($fecha1)."',hora_desde='$hora_desde',fecha_hasta='".cambiaf_a_mysql($fecha2)."',hora_hasta='$hora_hasta',observaciones='$motivo.$observacion'
               WHERE codigo_just='$codigo_just' ";
        $resultado=modificar_data($sql,$sender);
        }else{// es justificacion
            $sql="UPDATE asistencias.justificaciones_dias
               SET fecha_desde='".cambiaf_a_mysql($fecha1)."',fecha_hasta='".cambiaf_a_mysql($fecha2)."'
               WHERE codigo_just='$codigo_just' ";
        $resultado=modificar_data($sql,$sender);
        }
}

 	public function crear($sender, $param,$fecha_desde,$fecha_hasta,$periodo,$tipo,$cedula){

        $cod_organizacion = usuario_actual('cod_organizacion');
        $dias_feriados = dias_feriados($sender);
        // si selecciono todos los años
        if($this->drop_ano->SelectedValue=="XXXX")// utiliza el año actual
        $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".date("Y");
        else
        $fecha_no_laborable=$this->drop_dia->SelectedValue."/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;

        $fecha_no_laborable_my=cambiaf_a_mysql($fecha_no_laborable);
        $observacion=" Modificacion por Inclusion de dia no laborable $fecha_no_laborable";
        $cod_organizacion = usuario_actual('cod_organizacion');
        $dias_feriados = dias_feriados($sender);


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
        if($tipo=="vacacion"){//si es una vacacion
        $sql="INSERT INTO asistencias.vacaciones_disfrute (cedula,dias_disfrute, dias_feriados, dias_restados,
              fecha_desde,fecha_hasta,periodo,observaciones,referencia,estatus)
              VALUES ('$cedula','$dias_disfrute2','$num_feriados','$dias_restados',
              '".cambiaf_a_mysql($fecha_desde)."','".cambiaf_a_mysql($fecha_hasta)."','$periodo','$motivo.$observacion','$codigo_nuevo','1')";
        $resultado=modificar_data($sql,$sender);
        }
    }

}
?>