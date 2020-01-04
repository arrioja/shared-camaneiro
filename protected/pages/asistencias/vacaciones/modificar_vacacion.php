<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Mediante esta página, se puede modificar los días de
 *              disfrute de la vacacion seleecionada y desde cuando los va a disfrutar.
 *****************************************************  FIN DE INFO
*/

class modificar_vacacion extends TPage
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
/*        $item=$param->Item;
        if ($item->disponible_desde->Text != "")
        {
            $item->disponible_desde->Text = cambiaf_a_normal($item->disponible_desde->Text);
        }
*/
    }

    /* Esta función se encarga de realizar validaciones a la fecha de inicio de
     * las vacaciones, que no sea feriado ni que sea fin de semana.
     */
    public function validar_fecha_inicio($sender, $param)
    {
        $dias_feriados = dias_feriados($sender);
        $fecha = $this->txt_fecha_desde->Text;
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

/* Esta funcion se encarga de vaciar los campos del formulario para dejar todo limpio*/
    public function vaciar_campos()
    {
        $this->txt_nombre->Text = "";
        $this->Repeater->DataSource=$this->vacio;
        $this->Repeater->dataBind();
        $this->num_dias->Datasource = $this->vacio;
        $this->num_dias->dataBind();
        $this->btn_incluir->Enabled=false;
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
        $periodo=$datos_vacacion[0]['periodo'];
        $this->txt_cedula->Text=$cedula;


        //se carga datos de vacacion seleccionada
        $sql="SELECT jd.observaciones FROM asistencias.vacaciones_disfrute as vd
                INNER JOIN asistencias.justificaciones_dias as jd ON(jd.codigo_just=vd.referencia) WHERE vd.id='".$this->Request[id]."'";
        $datos_ob=cargar_data($sql,$sender);

        $this->txt_observacion->Text=$datos_ob[0]['observaciones'];
        

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
                   where((v.cedula='$cedula') and (periodo='$periodo')and (v.pendientes>'0'))
				   order by v.disponible_desde ";
            $datos_sumatoria=cargar_data($sql,$sender);
            // se suma los dias de la vacacion a modificar
            $dias_acumulados = $datos_sumatoria[0]['sumatoria']+$datos_vacacion[0]['dias_disfrute'];
            // se suma solo los dias a modificar
           
            $this->txt_fecha_desde->Text=cambiaf_a_normal($datos_vacacion[0]['fecha_desde']);

            //$this->mensaje_v->setSuccessMessage($sender, "".$this->txt_fecha_desde->Text, 'grow');

            for ($x = 1 ; $x <= $dias_acumulados ; $x++)
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
            $this->btn_incluir->Enabled=false;
            //se carga datos de vacacion seleccionada
            $sql="SELECT * FROM asistencias.vacaciones_disfrute WHERE id='".$this->Request[id]."'";
            $datos_vacacion=cargar_data($sql,$sender);
            $cedula=$datos_vacacion[0]['cedula'];
            $periodo=$datos_vacacion[0]['periodo'];
            $codigo_just=$datos_vacacion[0]['referencia'];
            $dias_vacacion_a=$datos_vacacion[0]['dias_disfrute'];

            $dias_disfrute = $this->num_dias->SelectedValue;
            $sql="SELECT v.pendientes,v.disfrutados FROM asistencias.vacaciones as v
                   WHERE (v.cedula='$cedula') AND (periodo='$periodo')";
            $datos_periodo=cargar_data($sql,$sender);
            // se suma los dias de la vacacion a modificar
            $dias_pendientes = $datos_periodo[0]['pendientes']+$dias_vacacion_a-$dias_disfrute;
            $dias_disfrutados=$datos_periodo[0]['disfrutados']-$dias_vacacion_a+$dias_disfrute;
            
            $dias_feriados = dias_feriados($sender);
            $cod_organizacion = usuario_actual('cod_organizacion');
            
            $fecha1= $this->txt_fecha_desde->Text;
            $fecha2=suma_dias_habiles($fecha1, $dias_disfrute, $dias_feriados, $sender);

            $sql="select * from asistencias.opciones where ((cod_organizacion = '$cod_organizacion')) order by status";
            $horarios=cargar_data($sql,$sender);
            $dias_restados = calcula_dias_restados(cambiaf_a_mysql($fecha1),$dias_disfrute,$horarios,$dias_feriados,$sender);
            $num_feriados = cuenta_feriados($fecha1, $dias_disfrute, $dias_feriados, $sender);
            $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($fecha1),$sender);
            $hora_desde = $horario_vigente[0]['hora_entrada'];
            $hora_hasta = $horario_vigente[0]['hora_salida'];
            $motivo=$this->txt_observacion->Text;

            /*observacion*/
             $sql2="Select CONCAT(p.nombres,' ',p.apellidos) as nombres
                   from asistencias.vacaciones_disfrute v, organizacion.personas p
                   where (v.id='".$this->Request[id]."') and (v.cedula=p.cedula)";
            $resultado_ob=cargar_data($sql2,$sender);
            $nombres = $resultado_ob[0]['nombres'];
            $observaciones="El funcionario(a) ".$nombres.", titular de la cédula de identidad ".$cedula.", se encuentra ".
					  "disfrutando ".$dias_disfrute." días de vacaciones correspondientes al período ".$periodo.", a partir del día ".
					  cambiaf_a_normal($fecha1)." hasta el ".cambiaf_a_normal($fecha2).". ".$motivo;
             /*observacion*/
            
            //se actualiza datos
            $sql="UPDATE asistencias.vacaciones
                   SET disfrutados='$dias_disfrutados', pendientes='$dias_pendientes'
                   WHERE cedula='$cedula' AND periodo='$periodo'";
            $resultado=modificar_data($sql,$sender);

            // se modifica los datos de la vacacion a disfrutar
            $sql="UPDATE asistencias.vacaciones_disfrute
                  SET dias_disfrute='$dias_disfrute', dias_feriados='$num_feriados', dias_restados='$dias_restados',
                  fecha_desde='".cambiaf_a_mysql($fecha1)."',fecha_hasta='".cambiaf_a_mysql($fecha2)."'
                  WHERE id='".$this->Request[id]."'";
           $resultado=modificar_data($sql,$sender);

            //actualizo la fecha hasta de la justificacion
            $sql="UPDATE asistencias.justificaciones_dias
                   SET fecha_desde='".cambiaf_a_mysql($fecha1)."',hora_desde='$hora_desde',fecha_hasta='".cambiaf_a_mysql($fecha2)."',hora_hasta='$hora_hasta', observaciones='$observaciones'
                   WHERE codigo_just='$codigo_just'";
            $resultado=modificar_data($sql,$sender);

                    /* Una vez finalizado todo el proceso, se incluye el rastro en el archivo de bitácora
                     * y se muestra un mensaje informativo al usuario acerca del resultado de su solicitud.
                     */
                    $descripcion_log = "Incluida solicitud de ".$dias_disfrute." días de vacaciones de: ".$this->txt_nombre->Text." C.I.: ".$cedula.
                                       ", desde ".$fecha1." al ".$fecha2.", (".$num_feriados." días feriados, ".$dias_restados.
                                              " días descontados)";
                    inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

                   // $this->btn_imprimir->Enabled=true;

                   $this->LTB->titulo->Text = "Solicitud de Vacaciones Exitosa";
                   $this->LTB->texto->Text = "Actualizada Solicitud, establecida de la siguiente manera: <strong>".$dias_disfrute." días de vacaciones, desde el ".$fecha1.
                                             " hasta el ".$fecha2.", (".$num_feriados." días feriados, ".$dias_restados.
                                             " días descontados), </strong>";
                   $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                   $this->LTB->redir->Text = "asistencias.vacaciones.consulta_cedula";
                   $params = array('mensaje');
                   $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

                   //$this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.consulta_cedula',array('ced'=>$cedula)));//
                            

            

        }
 	}

    /* Esta función imprime la solicitud de vacaciones.
 */
    public function imprimir_item($sender, $param)
    {   
        $id2=$this->Request[id];

        $sql="select p.nombres, p.apellidos, p.cedula, pc.denominacion as cargo, d.nombre_completo,
                     vd.dias_disfrute, vd.periodo, vd.fecha_desde, vd.fecha_hasta, vd.timestamp
                from asistencias.vacaciones_disfrute vd, organizacion.personas p,
                     organizacion.personas_cargos pc, organizacion.direcciones d,
                     organizacion.personas_nivel_dir pnd
                where (p.cedula = vd.cedula) and (p.cedula = pc.cedula) and
                      (p.cedula = pnd.cedula) and (d.codigo = pnd.cod_direccion) and
                      (vd.id = '$id2')";
        $resultado_rpt=cargar_data($sql,$sender);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "SOLICITUD DE VACACIONES.";
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 12);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Solicitud de Vacaciones.');
            $pdf->SetSubject('Solicitud de Vacaciones.');

            $pdf->AddPage();

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $fecha = date("d/m/Y",$resultado_rpt[0]['timestamp']);

            $dias_feriados = dias_feriados($sender);
            $reincorporacion  = suma_dias_habiles(cambiaf_a_normal($resultado_rpt[0]['fecha_hasta']), 2, $dias_feriados, $sender);

            for ($num_solicitud = 0 ; $num_solicitud < 2 ; $num_solicitud++) {
                $pdf->Ln();
                $pdf->Cell(0, 0, "La Asunción, ".$fecha, 0, 0, 'R', 1);
                $pdf->Ln();

                $sql_cargos="SELECT fecha_ini,fecha_fin,lugar_trabajo,denominacion FROM organizacion.personas_cargos WHERE cedula=".$resultado_rpt[0]['cedula']." ORDER BY id DESC LIMIT 1";
                $resultado_cargos=cargar_data($sql_cargos,$sender);

                $motivo = "El suscrito ".$resultado_rpt[0]['nombres']." ".$resultado_rpt[0]['apellidos'].
                          ", titular de la Cédula de Identidad Nº".$resultado_rpt[0]['cedula'].", quien desempeña ".
                          "el cargo de ".$resultado_cargos[0]['denominacion'].", adscrito a la Dirección de ".$resultado_rpt[0]['nombre_completo'].
                          ", de esta Institucion, ".
                          "solicita ".$resultado_rpt[0]['dias_disfrute']." días de Vacaciones ".
                          "correspondientes al Período ".$resultado_rpt[0]['periodo'].
                          ", desde el ".cambiaf_a_normal($resultado_rpt[0]['fecha_desde'])." ".
                          "hasta el ".cambiaf_a_normal($resultado_rpt[0]['fecha_hasta'])." debiendo reincorporarse ".
                          "el ".$reincorporacion.".";
                $pdf->MultiCell(0, 0, $motivo, 0, 'JL', 0, 0, '', '', true, 0);

                $pdf->Ln();

                $observaciones = "OBSERVACIONES: __________________________________________".
                          "___________________________________________________________".
                          "______________________________________________________".
                          "_______________________________________________________".
                          "______________________________________________________";
                $pdf->MultiCell(0, 0, $observaciones, 0, 'JL', 0, 0, '', '', true, 0);

                $pdf->Ln();
                $pdf->MultiCell(0, 0, " ", 0, 'JL', 0, 0, '', '', true, 0);
                $pdf->Ln();
                $pdf->MultiCell(0, 0, " ", 0, 'JL', 0, 0, '', '', true, 0);
                $pdf->Ln();
                $observaciones = "_________________                  _________________             _________________";
                $pdf->MultiCell(0, 0, $observaciones, 0, 'C', 0, 0, '', '', true, 0);
                $pdf->Ln();
                $observaciones = "Firma Funcionario                           Firma Director                            Firma RRHH";
                $pdf->MultiCell(0, 0, $observaciones, 0, 'C', 0, 0, '', '', true, 0);
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 9);
                $pdf->MultiCell(0, 0, "Impreso el: ".date("d/m/Y h:i:s a")." - Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), 0, 'C', 0, 0, '', '', true, 0);
                $pdf->Ln();
                If ($num_solicitud == 0)
                {
                    $pdf->SetFont('helvetica', '', 14);
                    $pdf->MultiCell(0, 0, ".   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .", 0, 'JL', 0, 0, '', '', true, 0);
                    $pdf->Ln();
                }
            }
            $pdf->Output("solicitud_vacacion_".$resultado_rpt[0]['cedula'].".pdf",'D');
        }
    }


}

?>
