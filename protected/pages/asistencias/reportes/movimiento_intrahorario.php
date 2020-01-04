<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página emite un reporte de las entradas y salidas del personal
 *              en el horario de trabajo.
 *****************************************************  FIN DE INFO
*/
include("protected/comunes/libchart/classes/libchart.php");
class movimiento_intrahorario extends TPage
{
    var $justificaciones; // info de las justificaciones 
    var $asistentes;
    var $inasistentes;

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            $this->txt_fecha_desde->Text = date("d/m/Y",strtotime("-1 day"));
            $this->txt_fecha_hasta->Text = date("d/m/Y",strtotime("-1 day"));
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select u.id, u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos,' (',p.cedula,')') as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
						ORDER BY p.nombres, p.apellidos";
            $resultado=cargar_data($sql,$this);
            // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
            //$todos = array('cedula'=>'0', 'nombre'=>'TODAS LAS PERSONAS');
            //array_unshift($resultado, $todos);
            // Se enlaza el nuevo arreglo con el listado de Direcciones
            $this->drop_direcciones->DataSource=$resultado;
            $this->drop_direcciones->dataBind();

            //si tiene parametros
            if($this->Request[ced]!=""&&$this->Request[fec]!=""){
            $this->drop_direcciones->SelectedValue = $this->Request[ced];
            $this->txt_fecha_desde->Text=$this->Request[fec];
            //$this->IsValid=true;
            $this->consulta_asistencia($this,$param);
            }
        }
    }
/* Realiza la consulta y muestra el listado de asistentes e inasistentes*/
    public function consulta_asistencia($sender,$param)
    {

        $desde=cambiaf_a_mysql($this->txt_fecha_desde->Text);
        $hasta=cambiaf_a_mysql($this->txt_fecha_hasta->Text);

        $cedula = $this->drop_direcciones->SelectedValue;
        $cod_organizacion = usuario_actual('cod_organizacion');
       
        $condicion_adicional = "(p.cedula = '$cedula') and ";
        // si la consulta es global, se elimina la condición de la cédula
        if ($cedula == '0')
            { $condicion_adicional = ""; }

            $sql="SELECT (p.cedula) as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombres, e.fecha, e.hora, '' as t_ausente
		  		  FROM asistencias.entrada_salida as e, organizacion.personas as p, organizacion.personas_nivel_dir n
				  WHERE ((p.cedula = e.cedula)  
                        and (e.fecha <= '$hasta') and (e.fecha >= '$desde') and
                         $condicion_adicional
                         (n.cod_organizacion = '$cod_organizacion') and (n.cedula = p.cedula))
				  ORDER BY e.fecha,e.hora ASC ";

            $sqlj="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.codigo, jd.fecha_desde, jd.hora_desde,
                  jd.fecha_hasta, jd.hora_hasta, jd.observaciones, jd.lun, jd.mar, jd.mie, jd.jue, jd.vie,
                  tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
                            WHERE ((p.cedula = jp.cedula) and
                                   (p.cedula = n.cedula) and (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                                   $condicion_adicional
                                   (p.fecha_ingreso <= '$desde') and
                                   (jd.fecha_desde <= '$desde') and
                                   (jd.fecha_hasta >= '$hasta') and
                                   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
                            ORDER BY p.nombres, p.apellidos, jp.cedula";

            $this->asistentes=cargar_data($sql,$this);

             // se obtienen las justificaciones del rango de fechas seleccionado
            $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*, tj.descripcion as descripcion_tipo_justificacion
                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, asistencias.tipo_justificaciones tj
                            WHERE (
                                   (p.cedula = jp.cedula) and
                                   (p.cedula = '$cedula') and
                                   ((
                                     (jd.fecha_desde <= '$desde') and
                                     (jd.fecha_hasta >= '$desde')
                                     ) Or
                                    (
                                     (jd.fecha_desde <= '$hasta') and
                                     (jd.fecha_hasta >= '$hasta')
                                   ) or
                                   (
                                     (jd.fecha_desde >= '$desde') and
                                     (jd.fecha_hasta <= '$hasta')
                                    )) and
                                   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just) and
                                   (jd.codigo_tipo_justificacion = tj.id)
                                  )
                            ORDER BY jd.fecha_desde";
            $this->justificaciones=cargar_data($sql,$this);

        if (!empty($this->asistentes)){

            $arreglo_data = array();
            $i=2;
            $fecha=($this->asistentes[0][fecha]);//fecha inicial
            $tiempo_total="";
            //se rrecorre la horas
            foreach($this->asistentes as $arreglo){

                if ($fecha!=$arreglo[fecha]){//si la fecha es de otro dia se coloca como titulo
                    $fecha=$arreglo[fecha];//fecha
                    //si es 00:00:00 no se toma en cuenta
                    if($tiempo_total!="00:00:00")$tiempo_total ="Total: ".$tiempo_total;
                    else $tiempo_total="";

                    array_push($arreglo_data, array('tipo'=>"", 'hora'=>"",'t_ausente'=>$tiempo_total));
                    array_push($arreglo_data, array('tipo'=>"", 'hora'=>""));
                    array_push($arreglo_data, array('tipo'=>"Intrahorario del ".cambiaf_a_normal($fecha), 'hora'=>''));
                    $tiempo_total="";
                    $tiempo="";
                    $i=2;// se inicializa que tenga el valor de la primera entrada(par)
                }//fin si
                  
                $hora=date("h:i:s a",strtotime($arreglo[hora]));
                $hora_en=date("H:i:s",strtotime($arreglo[hora]));
                if ($i%2!=0){//salida
                    $hora_sa=$hora_en;
                    array_push($arreglo_data, array('tipo'=>"Salida", 'hora'=>$hora));
                }else{//entrada
                    //se obtiene horario vigente
                    $horario_vigente = obtener_horario_vigente($cod_organizacion, $arreglo[fecha], $sender);

                   //si la hora de salida es menor a la hora de entrada vigente
                    if (((strtotime($hora_sa)) < (strtotime($horario_vigente[0]['hora_entrada']." + ".
                    $horario_vigente[0]['holgura_entrada']." minutes")))){
                       //si la hora de entrada es menor a la hora de entrada vigente
                        if (((strtotime($hora_en)) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                        $horario_vigente[0]['holgura_entrada']." minutes")))){
                        $hora_sa=date("H:i:s",(strtotime($horario_vigente[0]['hora_entrada'])));
                        }else{ //si es menor no se cuenta como ausente
                        $hora_en="00:00:00";
                        $hora_sa="00:00:00";
                        }//fin si
                    }

                    //si la hora de entrada es mayor a la hora de salida vigente
                    if (((strtotime($hora_en)) > (strtotime($horario_vigente[0]['hora_salida'])))){
                        //si la hora de entrada es mayor y la de salida es menor
                        if ((((strtotime($hora_en)) > (strtotime($horario_vigente[0]['hora_salida']))))&&(((strtotime($hora_sa)) < (strtotime($horario_vigente[0]['hora_salida']))))){
                         $hora_en=date("H:i:s",(strtotime($horario_vigente[0]['hora_salida'])));
                        }else{//no se cuenta como ausente
                         $hora_en="00:00:00";
                         $hora_sa="00:00:00";
                        }
                    }//fin si
                  
                  $tiempo=date("H:i:s", strtotime("00:00:00") + strtotime($hora_en) - strtotime($hora_sa) );
                  if(($i==2)||($tiempo=="00:00:00"))$tiempo="";//si es la primera entrada no se toma en cuenta o la hora no se tomara en cuenta(00:00:00)

                  //si esta justificado
                  $result  = esta_justificado($this->justificaciones,$cedula,cambiaf_a_normal($arreglo['fecha']),$hora_en,$sender);
                  $result2 = esta_justificado($this->justificaciones,$cedula,cambiaf_a_normal($arreglo['fecha']),$hora_sa,$sender);
                  if (($result != false) && ($result2 != false)){
                    $observacion = $result['descripcion_tipo_justificacion'].", Cód: ".$result['codigo'];
                    array_push($arreglo_data, array('tipo'=>"Entrada", 'hora'=>$hora,'t_ausente'=>$observacion));
                  }else{
                      
                      array_push($arreglo_data, array('tipo'=>"Entrada", 'hora'=>$hora,'t_ausente'=>$tiempo));
                      $tiempo_total= sumahoras($tiempo,$tiempo_total);
                      $observacion="";
                  }// fin si esta justificado


                }//fin si
               
                $i++;
            }//fin each

            if($tiempo_total!="00:00:00")$tiempo_total ="Total: ".$tiempo_total;
            else $tiempo_total="";
            array_push($arreglo_data, array('tipo'=>"", 'hora'=>"",'t_ausente'=>$tiempo_total) );

            // Se enlaza el nuevo arreglo con el listado
             if(!empty($arreglo_data)){
                $this->DataGrid->Caption="Intrahorario del ".$this->txt_fecha_desde->Text;
                $this->DataGrid->DataSource=$arreglo_data;
                $this->DataGrid->dataBind();
             }else{
                $this->DataGrid->DataSource=$this->asistentes;
                $this->DataGrid->dataBind();
             }//fin si
             
       }else{
                $this->DataGrid->Caption="";
                $this->DataGrid->DataSource="";
                $this->DataGrid->dataBind(); 
      }//fin si

            // se obtienen las justificaciones del día seleccionado
            $this->justificaciones=cargar_data($sqlj,$this);
            $this->Repeater->DataSource =  $this->justificaciones;
            $this->Repeater->dataBind();

            // si la consulta de asistentes tiene resultados se habilita la impresion, sino, se deshabilita
            if (!empty($this->asistentes)) {$this->btn_imprimir->Enabled = true;} else {$this->btn_imprimir->Enabled = false;}

    }


/* Formatea el listado para mejor comprension, fechas, colores, etc. */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if ($item->hora->Text != "Tipo")
        {
             $arre=split(' ',$item->tipo->Text);
             if($arre[0]=="Intrahorario"){
                 $item->tipo->BackColor="#EFFFC0";
                 $item->hora->BackColor="#EFFFC0";
                 $item->t_ausente->BackColor="#EFFFC0";
             }//fin si
             if($item->tipo->Text=="Tipo"){
                 $item->tipo->BackColor="#29166F";
                 $item->tipo->ForeColor="white";
                 $item->tipo->BorderColor="white";

                 $item->hora->BackColor="#29166F";
                 $item->hora->ForeColor="white";
                 $item->hora->BorderColor="white";

                 $item->t_ausente->BackColor="#29166F";
                 $item->t_ausente->ForeColor="white";
                 $item->t_ausente->BorderColor="white";
             }//fin si
        }

        if ($item->tipo->Text == "Entrada")$item->tipo->ForeColor = "Green";
        elseif  ($item->tipo->Text == "Salida") $item->tipo->ForeColor = "Red";
        
    }

    public function imprimir_asistencia($sender, $param)
    {
        require('/var/www/tcpdf/tcpdf.php');
        $cod_organizacion = usuario_actual('cod_organizacion');
        $fecha_reporte = cambiaf_a_mysql($this->txt_fecha_desde->Text);

        $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
        $pdf->SetFillColor(205, 205, 205);//color de relleno gris
        $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
        $nombre_fun = $resultado_drop[2]; // se extrae el texto seleccionado

        $info_adicional= "Reporte de Movimiento Intrahorario desde ".$this->txt_fecha_desde->Text." hasta ".$this->txt_fecha_hasta->Text."\nFuncionario(a):".$nombre_fun;
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
        $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->SetAuthor('Proyecto SIMON');
        $pdf->SetTitle('Reporte de Movimiento Intrahorario');
        $pdf->SetSubject($info_adicional);

        $pdf->AddPage();


        /*datos*/
        $desde=cambiaf_a_mysql($this->txt_fecha_desde->Text);
        $hasta=cambiaf_a_mysql($this->txt_fecha_hasta->Text);

        $cedula = $this->drop_direcciones->SelectedValue;
        $cod_organizacion = usuario_actual('cod_organizacion');

        $condicion_adicional = "(p.cedula = '$cedula') and ";
        // si la consulta es global, se elimina la condición de la cédula
        if ($cedula == '0'){ $condicion_adicional = ""; }

            $sql="SELECT (p.cedula) as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombres, e.fecha, e.hora, '' as t_ausente
		  		  FROM asistencias.entrada_salida as e, organizacion.personas as p, organizacion.personas_nivel_dir n
				  WHERE ((p.cedula = e.cedula)
                        and (e.fecha <= '$hasta') and (e.fecha >= '$desde') and
                         $condicion_adicional
                         (n.cod_organizacion = '$cod_organizacion') and (n.cedula = p.cedula))
				  ORDER BY e.fecha,e.hora ASC ";
            $asistentes_rpt=cargar_data($sql,$this);
            $sqlj="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.codigo, jd.fecha_desde, jd.hora_desde,
                  jd.fecha_hasta, jd.hora_hasta, jd.observaciones, jd.lun, jd.mar, jd.mie, jd.jue, jd.vie,
                  tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
                            WHERE ((p.cedula = jp.cedula) and
                                   (p.cedula = n.cedula) and (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                                   $condicion_adicional
                                   (p.fecha_ingreso <= '$desde') and
                                   (jd.fecha_desde <= '$desde') and
                                   (jd.fecha_hasta >= '$hasta') and
                                   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
                            ORDER BY p.nombres, p.apellidos, jp.cedula";
            $justificaciones_rpt=cargar_data($sqlj,$this);

             // se obtienen las justificaciones del rango de fechas seleccionado
            $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*, tj.descripcion as descripcion_tipo_justificacion
                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, asistencias.tipo_justificaciones tj
                            WHERE (
                                   (p.cedula = jp.cedula) and
                                   (p.cedula = '$cedula') and
                                   ((
                                     (jd.fecha_desde <= '$desde') and
                                     (jd.fecha_hasta >= '$desde')
                                     ) Or
                                    (
                                     (jd.fecha_desde <= '$hasta') and
                                     (jd.fecha_hasta >= '$hasta')
                                   ) or
                                   (
                                     (jd.fecha_desde >= '$desde') and
                                     (jd.fecha_hasta <= '$hasta')
                                    )) and
                                   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just) and
                                   (jd.codigo_tipo_justificacion = tj.id)
                                  )
                            ORDER BY jd.fecha_desde";
        /*datos*/

        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(0, 6, "Movimiento de Entrada/Salida", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de asistentes en el PDF
        $pdf->SetFillColor(210,210,210);//$pdf->SetFillColor(0, 0, 130);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        $asistentes_header = array('Tipo','Hora','Ausente(HH:MM:SS)');

         if (!empty($asistentes_rpt)){
            $pdf->SetFont('','',10);
            $arreglo_data = array();
            $i=2;
            $fecha=cambiaf_a_normal($asistentes_rpt[0][fecha]);//fecha inicial
            $pdf->Cell($w[0], 6, "Fecha: $fecha", 0, 0, 'L', $fill);
            $pdf->Ln();
           
             // Header
            $w = array(45,45,95);
            for($i = 0; $i < count($asistentes_header); $i++)
            $pdf->Cell($w[$i], 7, $asistentes_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $i=2;

            $tiempo_total="";
            //se rrecorre la horas
            foreach($asistentes_rpt as $arreglo){

                if ($fecha!=cambiaf_a_normal($arreglo[fecha])){//si la fecha es de otro dia se coloca como titulo
                    $fecha=cambiaf_a_normal($arreglo[fecha]);//fecha
                   
                    //si es 00:00:00 no se toma en cuenta
                    if($tiempo_total!="00:00:00")$tiempo_total ="Total: ".$tiempo_total;
                    else $tiempo_total="";

                    //array_push($arreglo_data, array('tipo'=>"", 'hora'=>"",'t_ausente'=>$tiempo_total));
                       $pdf->Cell($w[0], 6, "", 1, 0, 'L', $fill);
                       $pdf->Cell($w[1], 6, "", 1, 0, 'L', $fill);
                       $pdf->Cell($w[2], 6, $tiempo_total, 1, 0, 'L', $fill);
                       $pdf->Ln();
                    //array_push($arreglo_data, array('tipo'=>"", 'hora'=>""));
                    //array_push($arreglo_data, array('tipo'=>"Intrahorario del ".cambiaf_a_normal($fecha), 'hora'=>''));
                     $pdf->Cell(45, 6, "Fecha: $fecha", 0, 0, 'L', true);
                     $pdf->Ln();

                    $tiempo_total="";
                    $tiempo="";
                    $i=2;// se inicializa que tenga el valor de la primera entrada(par)
                }//fin si

                $hora=date("h:i:s a",strtotime($arreglo[hora]));
                $hora_en=date("H:i:s",strtotime($arreglo[hora]));

                
                if ($i%2!=0){//salida
                    $hora_sa=$hora_en;
                    //array_push($arreglo_data, array('tipo'=>"Salida", 'hora'=>$hora));
                     $pdf->SetTextColor(255,0,0 );
                    $pdf->Cell($w[1], 6, "Salida", 1, 0, 'L', $fill);
                     $pdf->SetTextColor(0);
                    $pdf->Cell($w[0], 6, date("h:i:s a",strtotime($hora)), 1, 0, 'C', $fill);
                }else{//entrada
                   
                    //se obtiene horario vigente
                    $horario_vigente = obtener_horario_vigente($cod_organizacion, $arreglo[fecha], $sender);

                   //si la hora de salida es menor a la hora de entrada vigente
                    if (((strtotime($hora_sa)) < (strtotime($horario_vigente[0]['hora_entrada']." + ".
                    $horario_vigente[0]['holgura_entrada']." minutes")))){
                       //si la hora de entrada es menor a la hora de entrada vigente
                        if (((strtotime($hora_en)) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                        $horario_vigente[0]['holgura_entrada']." minutes")))){
                        $hora_sa=date("H:i:s",(strtotime($horario_vigente[0]['hora_entrada'])));
                        }else{ //si es menor no se cuenta como ausente
                        $hora_en="00:00:00";
                        $hora_sa="00:00:00";
                        }//fin si
                    }

                    //si la hora de entrada es mayor a la hora de salida vigente
                    if (((strtotime($hora_en)) > (strtotime($horario_vigente[0]['hora_salida'])))){
                        //si la hora de entrada es mayor y la de salida es menor
                        if ((((strtotime($hora_en)) > (strtotime($horario_vigente[0]['hora_salida']))))&&(((strtotime($hora_sa)) < (strtotime($horario_vigente[0]['hora_salida']))))){
                         $hora_en=date("H:i:s",(strtotime($horario_vigente[0]['hora_salida'])));
                        }else{//no se cuenta como ausente
                         $hora_en="00:00:00";
                         $hora_sa="00:00:00";
                        }
                    }//fin si

                  $tiempo=date("H:i:s", strtotime("00:00:00") + strtotime($hora_en) - strtotime($hora_sa) );
                  if(($i==2)||($tiempo=="00:00:00"))$tiempo="";//si es la primera entrada no se toma en cuenta o la hora no se tomara en cuenta(00:00:00)




                       // se obtienen las justificaciones del rango de fechas seleccionado
                $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*, tj.descripcion as descripcion_tipo_justificacion
                                FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                     asistencias.justificaciones_personas as jp, organizacion.personas as p, asistencias.tipo_justificaciones tj
                                WHERE (
                                       (p.cedula = jp.cedula) and
                                       (p.cedula = '$cedula') and
                                       ((
                                         (jd.fecha_desde <= '$desde') and
                                         (jd.fecha_hasta >= '$desde')
                                         ) Or
                                        (
                                         (jd.fecha_desde <= '$hasta') and
                                         (jd.fecha_hasta >= '$hasta')
                                       ) or
                                       (
                                         (jd.fecha_desde >= '$desde') and
                                         (jd.fecha_hasta <= '$hasta')
                                        )) and
                                       (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just) and
                                       (jd.codigo_tipo_justificacion = tj.id)
                                      )
                                ORDER BY jd.fecha_desde";
                $this->justificaciones=cargar_data($sql,$this);


                  //si esta justificado
                  $result  = esta_justificado( $this->justificaciones,$cedula,cambiaf_a_normal($arreglo['fecha']),$hora_en,$sender);
                  $result2 = esta_justificado( $this->justificaciones,$cedula,cambiaf_a_normal($arreglo['fecha']),$hora_sa,$sender);
                  if (($result != false) && ($result2 != false)){
                    $observacion = $result['descripcion_tipo_justificacion'].", Cód: ".$result['codigo'];
                    array_push($arreglo_data, array('tipo'=>"Entrada", 'hora'=>$hora,'t_ausente'=>$observacion));
                    $pdf->SetTextColor(3,174,47);
                    $pdf->Cell($w[0], 6, "Entrada", 1, 0, 'L', $fill);
                    $pdf->SetTextColor(0);
                    $pdf->Cell($w[1], 6, $hora, 1, 0, 'C', $fill);
                    $pdf->Cell($w[2], 6, $observacion, 1, 0, 'L', $fill);

                    }else{

                      array_push($arreglo_data, array('tipo'=>"Entrada", 'hora'=>$hora,'t_ausente'=>$tiempo));
                        $pdf->SetTextColor(3,174,47);
                      $pdf->Cell($w[0], 6, "Entrada", 1, 0, 'L', $fill);
                        $pdf->SetTextColor(0);
                       $pdf->Cell($w[1], 6, $hora, 1, 0, 'C', $fill);

                       $pdf->Cell($w[2], 6, $tiempo, 1, 0, 'L', $fill);
                      $tiempo_total= sumahoras($tiempo,$tiempo_total);
                      $observacion="";
                  }// fin si esta justificado

                }//fin si
               
                 $pdf->Ln();
                $i++;
                $fill=false;
                 //$fill=!$fill;
            }//fin each

            if($tiempo_total!="00:00:00")$tiempo_total ="Total: ".$tiempo_total;
            else $tiempo_total="";
            array_push($arreglo_data, array('tipo'=>"", 'hora'=>"",'t_ausente'=>$tiempo_total) );
                       $pdf->Cell($w[0], 6, "", 1, 0, 'L', $fill);
                       $pdf->Cell($w[1], 6, "", 1, 0, 'L', $fill);
                       $pdf->Cell($w[2], 6, $tiempo_total, 1, 0, 'L', $fill);
          $pdf->Ln();

      }//fin si vacio

        $pdf->Output("Reporte_Intrahorario_".cambiaf_a_mysql($this->txt_fecha_desde->Text).".pdf",'D');
    }

}

?>