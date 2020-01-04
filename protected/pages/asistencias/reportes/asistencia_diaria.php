<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página emite un reporte de la asistencia del personal de la
 *              institución a una fecha proporcionada.
 *****************************************************  FIN DE INFO
*/
include("protected/comunes/libchart/classes/libchart.php");
class asistencia_diaria extends TPage
{
    var $horario_vigente;
    var $justificaciones; // info de las justificaciones 
    var $asistentes;
    var $inasistentes;
    // Variables para los gráficos
    var $ind_asistentes=0;
    var $ind_asistentes_no_retrasados=0;
    var $ind_asistentes_tarde_no_just=0;
    var $ind_asistentes_tarde_si_just=0;
    var $ind_inasistentes_no_just=0;
    var $ind_inasistentes_si_just=0;


    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            $this->txt_fecha_desde->Text = date("d/m/Y",strtotime("-1 day"));
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select codigo, nombre_completo as nombre from organizacion.direcciones
                  where (codigo_organizacion='$cod_organizacion' ) order by nombre_completo";
            $resultado=cargar_data($sql,$this);
            // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
            $todos = array('codigo'=>'0', 'nombre'=>'TODAS LAS DIRECCIONES');
            array_unshift($resultado, $todos);
            // Se enlaza el nuevo arreglo con el listado de Direcciones
            $this->drop_direcciones->DataSource=$resultado;
            $this->drop_direcciones->dataBind();
        }
    }
/* Realiza la consulta y muestra el listado de asistentes e inasistentes*/
    public function consulta_asistencia()
    {
        $fecha_reporte = cambiaf_a_mysql($this->txt_fecha_desde->Text);
        $dir = $this->drop_direcciones->SelectedValue;
        $cod_organizacion = usuario_actual('cod_organizacion');

        // se obtienen las justificaciones del día seleccionado
        $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.codigo, jd.fecha_desde, jd.hora_desde,
              jd.fecha_hasta, jd.hora_hasta, jd.observaciones, jd.lun, jd.mar, jd.mie, jd.jue, jd.vie,
              tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
					    FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
						     asistencias.justificaciones_personas as jp, organizacion.personas as p, organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
						WHERE ((p.cedula = jp.cedula) and
						       (p.cedula = n.cedula) and (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
							   (n.cod_direccion LIKE '$dir%') and
						       (p.fecha_ingreso <= '$fecha_reporte') and
						       (jd.fecha_desde <= '$fecha_reporte') and
							   (jd.fecha_hasta >= '$fecha_reporte') and
							   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
						ORDER BY p.nombres, p.apellidos, jp.cedula ";
        $this->justificaciones=cargar_data($sql,$this);        

        // se obtiene el horario vigente para la fecha seleccionada
        $this->horario_vigente = obtener_horario_vigente($cod_organizacion,$fecha_reporte,$this);
        
        // se realizan las consultas para mostrar los listados
        // Se consultan los asistentes
        $sql="SELECT (p.cedula) as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombre,
                           e.cedula, e.fecha, MIN(e.hora) as entrada, MAX(e.hora) as salida
                    FROM asistencias.entrada_salida as e, organizacion.personas as p, organizacion.personas_nivel_dir as n
                    WHERE ((p.cedula = e.cedula) and
                           (p.cedula = n.cedula) and
                           (n.cod_direccion LIKE '$dir%') and
                           (p.fecha_ingreso <= '$fecha_reporte') and
                           (e.fecha <= '$fecha_reporte') and
                           (e.fecha >= '$fecha_reporte'))
                    GROUP BY e.cedula
                    ORDER BY entrada, p.nombres, p.apellidos ";
        $this->asistentes=cargar_data($sql,$this);
        $this->ind_asistentes = count($this->asistentes);

        // se consultan los inasistentes
        $sql2="SELECT p.cedula as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombre
									  FROM organizacion.personas as p, asistencias.personas_status_asistencias as s,
									       organizacion.personas_nivel_dir as n
									  WHERE ((s.status_asistencia = '1') and
									  		 (s.cedula = p.cedula) and
											 (p.cedula = n.cedula) and
							                 (n.cod_direccion LIKE '$dir%') and
									         (p.fecha_ingreso <= '$fecha_reporte') and
									         (p.cedula not in
									           (SELECT e.cedula
										        FROM asistencias.entrada_salida as e, organizacion.personas_nivel_dir as n
										   	    WHERE ((e.fecha = '$fecha_reporte') and
												      (p.cedula = n.cedula) and
							                          (n.cod_direccion LIKE '$dir%'))
											    GROUP BY e.cedula)))
									  ORDER BY p.nombres, p.apellidos";
        $this->inasistentes=cargar_data($sql2,$this);


        // Se consultan los asistentes para comparar inconsistencia de horas en el marcado
        // Si le falta hora
        $inconsistentes = array();
        foreach($this->asistentes as $arreglo){
           
            $sql2="SELECT COUNT(*) as n_horas FROM asistencias.entrada_salida as e
			  WHERE e.fecha = '$fecha_reporte' AND e.cedula = '$arreglo[cedula_integrantes]' ";
            $resultado2=cargar_data($sql2,$this);
            if(!empty($resultado2)){
                if ($resultado2[0][n_horas]%2!=0) {//impar
                    array_unshift($inconsistentes, array('cedula'=>$arreglo[cedula_integrantes], 'nombre'=>$arreglo[nombre],'salida'=>$arreglo[salida]));
                }//fin si
            }//fin si
        }//fin each



        // Se enlaza el nuevo arreglo con el listado de Direcciones
        //$this->DataGrid_fj->Caption="Reporte de Asistencias del ".$this->txt_fecha_desde->Text;
         if(!empty($inconsistentes)){
         $this->DataGrid_fh->DataSource=$inconsistentes;
         $this->DataGrid_fh->dataBind();
         }

        // Se enlaza el nuevo arreglo con el listado de Direcciones
        $this->DataGrid->Caption="Reporte de Asistencias del ".$this->txt_fecha_desde->Text;
        $this->DataGrid->DataSource=$this->asistentes;
        $this->DataGrid->dataBind();


        $this->DataGrid_ina->Caption="Inasistentes el d&iacute;a ".$this->txt_fecha_desde->Text;
        $this->DataGrid_ina->DataSource=$this->inasistentes;
        $this->DataGrid_ina->dataBind();

        /* Por un error que no supe identificar, el cual suma un numero adicional a la variable
         * de inasistentes no justificados, he tenido que sacarla del procedimiento donde normalmente
         * se contaba y tuve que realizarla por resta en esta sección.
         */
        $this->ind_inasistentes_no_just = count($this->inasistentes) - $this->ind_inasistentes_si_just;

        $this->Repeater->DataSource =  $this->justificaciones;
        $this->Repeater->dataBind();

        $xale=rand(100,99999);
        // Se realiza la construcción del gráfico para indicadores

        $chart = new PieChart();
        $dataSet = new XYDataSet();
        if ($this->ind_asistentes>=1) {$dataSet->addPoint(new Point("Funcionarios Asistentes: (".$this->ind_asistentes.")", $this->ind_asistentes));};
        if ($this->ind_inasistentes_no_just>=1) {$dataSet->addPoint(new Point("Inasistentes NO JUSTIFICADOS: (".$this->ind_inasistentes_no_just.")", $this->ind_inasistentes_no_just));};
        if ($this->ind_inasistentes_si_just>=1) {$dataSet->addPoint(new Point("Inasistentes JUSTIFICADOS: (".$this->ind_inasistentes_si_just.")", $this->ind_inasistentes_si_just));};
        $chart->setDataSet($dataSet);
        $chart->setTitle("Porcentajes de Asistencias / Inasistencias del: ".$this->txt_fecha_desde->Text);
        elimina_grafico($xale."_01.png");
        $chart->render("imagenes/temporales/".$xale."_01.png");
        $this->grafico1->ImageUrl = "imagenes/temporales/".$xale."_01.png";


        $chart2 = new PieChart();
        $dataSet2 = new XYDataSet();
        $this->ind_asistentes_no_retrasados=$this->ind_asistentes-$this->ind_asistentes_tarde_no_just-$this->ind_asistentes_tarde_si_just;
        if ($this->ind_asistentes_no_retrasados>=1) {$dataSet2->addPoint(new Point("Puntuales: (".$this->ind_asistentes_no_retrasados.")", $this->ind_asistentes_no_retrasados));};
        if ($this->ind_asistentes_tarde_no_just>=1) {$dataSet2->addPoint(new Point("Impuntuales NO JUSTIFICADOS: (".$this->ind_asistentes_tarde_no_just.")", $this->ind_asistentes_tarde_no_just));};
        if ($this->ind_asistentes_tarde_si_just>=1) {$dataSet2->addPoint(new Point("Impuntuales JUSTIFICADOS: (".$this->ind_asistentes_tarde_si_just.")", $this->ind_asistentes_tarde_si_just));};
        $chart2->setDataSet($dataSet2);
        $chart2->setTitle("Porcentajes de Retrasos del: ".$this->txt_fecha_desde->Text);
        elimina_grafico($xale."_02.png");
        $chart2->render("imagenes/temporales/".$xale."_02.png");
        $this->grafico2->ImageUrl = "imagenes/temporales/".$xale."_02.png";

        // si la consulta de asistentes tiene resultados se habilita la impresion, sino, se deshabilita
        if (!empty($this->asistentes)) {$this->btn_imprimir->Enabled = true;} else {$this->btn_imprimir->Enabled = false;}

    }

    public function intra_click($sender,$param)
    {
        $ced=$sender->CommandParameter;
        $fec = $this->txt_fecha_desde->Text;
        $this->Response->Redirect( $this->Service->constructUrl('asistencias.reportes.movimiento_intrahorario',array('ced'=>$ced,'fec'=>$fec)));
    }
    
/* Formatea el listado para mejor comprension, fechas, colores, etc. */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if ($item->entrada->Text != "Entrada")
        {
            if (strtotime($item->entrada->Text)>= (strtotime($this->horario_vigente[0]['hora_entrada']." + ".
			                                       $this->horario_vigente[0]['holgura_entrada']." minutes")))
               {
                   if (esta_justificado( $this->justificaciones,$item->cedula->Text,$this->txt_fecha_desde->Text,$item->entrada->Text,$sender) != false)
                     {$item->entrada->ForeColor = "Green";$this->ind_asistentes_tarde_si_just++;}
                   else
                     {$item->entrada->ForeColor = "Red";$this->ind_asistentes_tarde_no_just++;}
                   $item->entrada->Font->Bold = "true";
               }
             $item->entrada->Text = date("h:i:s a",strtotime($item->entrada->Text));
        }
        if ($item->salida->Text != "Salida")
        {
            if (strtotime($item->salida->Text) < strtotime($this->horario_vigente[0]['hora_salida']))
            {
               if (esta_justificado($this->justificaciones,$item->cedula->Text,$this->txt_fecha_desde->Text,$item->salida->Text,$sender) != false)
                   {$item->salida->ForeColor = "Green";}
               else
                   {$item->salida->ForeColor = "Red";}
               $item->salida->Font->Bold = "true";
            }
            $item->salida->Text = date("h:i:s a",strtotime($item->salida->Text));
            if ($item->entrada->Text == $item->salida->Text)
            {
                 $item->salida->Text = "";
            }

        }
    }

/* Formatea el listado para mejor comprension, fechas, colores, etc. */
    public function nuevo_itemfh($sender,$param)
    {
        $item=$param->Item;

        if ($item->salida3->Text != "Ultima Entrada Registrada")
        {

            $item->salida3->Text = date("h:i:s a",strtotime($item->salida3->Text));
            if ($item->entrada3->Text == $item->salida3->Text)
            {
                 $item->salida3->Text = "";
            }

        }
    }
/* Formatea el listado para mejor comprension, fechas, colores, etc. */
    public function nuevo_item2($sender,$param)
    {
        $item=$param->Item;
        if ($item->observacion->Text != "Observacion")
        {
            $result = esta_justificado($this->justificaciones,$item->cedula2->Text,$this->txt_fecha_desde->Text,$this->horario_vigente[0]['hora_entrada'],$sender);
            $result2 = esta_justificado($this->justificaciones,$item->cedula2->Text,$this->txt_fecha_desde->Text,$this->horario_vigente[0]['hora_salida'],$sender);
           if (($result != false) && ($result2 != false))
             {
                 $item->observacion->ForeColor = "Green";
                 $item->observacion->Text = $result['descripcion_tipo_justificacion'].", C&oacute;d: ".$result['codigo'];
                 $this->ind_inasistentes_si_just++;
                 
             }
           else
             {
                 $item->observacion->ForeColor = "Red";
                 $item->observacion->Text = "I N A S I S T E N T E";
             }
           $item->observacion->Font->Bold = "true";
        }
    }

    public function imprimir_asistencia($sender, $param)
    {
        require('/var/www/tcpdf/tcpdf.php');
        $cod_organizacion = usuario_actual('cod_organizacion');
        $fecha_reporte = cambiaf_a_mysql($this->txt_fecha_desde->Text);
        $dir = $this->drop_direcciones->SelectedValue;

        $horario_vigente_rpt = obtener_horario_vigente($cod_organizacion,$fecha_reporte,$this);
        $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
        $pdf->SetFillColor(205, 205, 205);//color de relleno gris

        $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
        $nombre_dir = $resultado_drop[2]; // se extrae el texto seleccionado


        $info_adicional= "Reporte de Asistencia del ".$this->txt_fecha_desde->Text."\nDirección:".$nombre_dir;
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
        $pdf->SetTitle('Reporte de Asistencia Diaria');
        $pdf->SetSubject('Reporte de Asistencia Diaria de Funcionarios y Funcionarias');

        $pdf->AddPage();

        $asistentes_header = array('Cedula', 'Nombre', 'Entrada', 'Salida');
        $inasistentes_header = array('Cedula', 'Nombre', 'Observación');
        $justificaciones_header = array('Justificaciones a la asistencia');

        // se consultan los asistentes
        $sql="SELECT (p.cedula) as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombre,
                           e.cedula, e.fecha, MIN(e.hora) as entrada, MAX(e.hora) as salida
                    FROM asistencias.entrada_salida as e, organizacion.personas as p, organizacion.personas_nivel_dir as n
                    WHERE ((p.cedula = e.cedula) and
                           (p.cedula = n.cedula) and
                           (n.cod_direccion LIKE '$dir%') and
                           (p.fecha_ingreso <= '$fecha_reporte') and
                           (e.fecha <= '$fecha_reporte') and
                           (e.fecha >= '$fecha_reporte'))
                    GROUP BY e.cedula
                    ORDER BY entrada, p.nombres, p.apellidos";
        $asistentes_rpt=cargar_data($sql,$this);

        // se consultan los inasistentes
        $sql2="SELECT p.cedula as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombre
                                      FROM organizacion.personas as p, asistencias.personas_status_asistencias as s,
                                           organizacion.personas_nivel_dir as n
                                      WHERE ((s.status_asistencia = '1') and
                                             (s.cedula = p.cedula) and
                                             (p.cedula = n.cedula) and
                                             (n.cod_direccion LIKE '$dir%') and
                                             (p.fecha_ingreso <= '$fecha_reporte') and
                                             (p.cedula not in
                                               (SELECT e.cedula
                                                FROM asistencias.entrada_salida as e, organizacion.personas_nivel_dir as n
                                                WHERE ((e.fecha = '$fecha_reporte') and
                                                      (p.cedula = n.cedula) and
                                                      (n.cod_direccion LIKE '$dir%'))
                                                GROUP BY e.cedula)))
                                      ORDER BY p.nombres, p.apellidos";
        $inasistentes_rpt=cargar_data($sql2,$this);

        // se obtienen las justificaciones del día seleccionado
        $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.codigo, jd.fecha_desde, jd.hora_desde,
              jd.fecha_hasta, jd.hora_hasta, jd.observaciones, jd.lun, jd.mar, jd.mie, jd.jue, jd.vie,
              tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
                        FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                             asistencias.justificaciones_personas as jp, organizacion.personas as p, organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
                        WHERE ((p.cedula = jp.cedula) and
                               (p.cedula = n.cedula) and (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                               (n.cod_direccion LIKE '$dir%') and
                               (p.fecha_ingreso <= '$fecha_reporte') and
                               (jd.fecha_desde <= '$fecha_reporte') and
                               (jd.fecha_hasta >= '$fecha_reporte') and
                               (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
                        ORDER BY p.nombres, p.apellidos, jp.cedula";
        $justificaciones_rpt=cargar_data($sql,$this);

        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(0, 6, "Listado de Asistentes", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de asistentes en el PDF
        $pdf->SetFillColor(210,210,210);//$pdf->SetFillColor(0, 0, 130);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        // Header
        $w = array(22, 105, 30, 29);
        for($i = 0; $i < count($asistentes_header); $i++)
        $pdf->Cell($w[$i], 7, $asistentes_header[$i], 1, 0, 'C', 1);
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $fill = 0;
        foreach($asistentes_rpt as $row) {
            $pdf->Cell($w[0], 6, $row['cedula'], 'LR', 0, 'C', $fill);
            $pdf->Cell($w[1], 6, $row['nombre'], 'LR', 0, 'L', $fill);
            $pdf->SetTextColor(0); // iniciamos con el color negro
            // Para dibujar las columnas de las horas se comprueba si llegaron tarde o no
            if (strtotime($row['entrada'])>= (strtotime($horario_vigente_rpt[0]['hora_entrada']." + ".
                                                        $horario_vigente_rpt[0]['holgura_entrada']." minutes")))
               {// si llegó tarde, ahora se comprueba la justificacion
                   if (esta_justificado($justificaciones_rpt,$row['cedula'],$this->txt_fecha_desde->Text,$row['entrada'],$sender) != false)
                     { $pdf->SetTextColor(0,130,0);} // si esta justificado se coloca en verde
                   else
                     { $pdf->SetTextColor(200,50,50); }// si no esta justificado, se coloca en rojo
               }
             $pdf->Cell($w[2], 6, date("h:i:s a",strtotime($row['entrada'])), 'LR', 0, 'C', $fill);
             $pdf->SetTextColor(0);


            if (strtotime($row['salida'])< strtotime($horario_vigente_rpt[0]['hora_salida']))
            { // si salió temprano, se comprueban las justificaciones
               if (esta_justificado($justificaciones_rpt,$row['cedula'],$this->txt_fecha_desde->Text,$row['salida'],$sender) != false)
                 { $pdf->SetTextColor(0,130,0); } // si esta justificada la salida temprana se coloca en verde
               else
                 {$pdf->SetTextColor(200,50,50); }// si no esta justificada, se coloca en rojo
            }
            $pdf->Cell($w[3], 6, date("h:i:s a",strtotime($row['salida'])), 'LR', 0, 'C', $fill);
            $pdf->SetTextColor(0);

            $pdf->Ln();
            $fill=!$fill;
        }
        // Separación
        if ($dir == "0") // si son todas las Direcciones, se separan por pagina, si no simplemente se continua en la proxima linea
        {$pdf->AddPage();} else {$pdf->Ln();}
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(255, 200, 200);
        $pdf->Cell(0, 0, "Listado de Inasistentes", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de inasistentes en el PDF
        $pdf->SetFillColor(210,210,210);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        // Header
        $w = array(22, 100, 65);
        for($i = 0; $i < count($inasistentes_header); $i++)
        $pdf->Cell($w[$i], 7, $inasistentes_header[$i], 1, 0, 'C', 1);
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $fill = 0;
        foreach($inasistentes_rpt as $row) {
            $pdf->Cell($w[0], 6, $row['cedula_integrantes'], 'LR', 0, 'C', $fill);
            $pdf->Cell($w[1], 6, $row['nombre'], 'LR', 0, 'L', $fill);

            $result = esta_justificado($justificaciones_rpt,$row['cedula_integrantes'],$this->txt_fecha_desde->Text,$horario_vigente_rpt[0]['hora_entrada'],$sender);
            $result2 = esta_justificado($justificaciones_rpt,$row['cedula_integrantes'],$this->txt_fecha_desde->Text,$horario_vigente_rpt[0]['hora_salida'],$sender);
           if (($result != false) && ($result2 != false))
             {
                 $pdf->SetTextColor(0,130,0);
                 $pdf->Cell($w[2], 6, $result['descripcion_tipo_justificacion'].", Cód: ".$result['codigo'], 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);
             }
           else
             {
                 $pdf->SetTextColor(200,50,50);
                 $pdf->Cell($w[2], 6, "I N A S I S T E N T E", 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);
             }
            $pdf->Ln();
            $fill=!$fill;
        }

        // Se añaden las observaciones a la asistencia
        // Separación
        if ($dir == "0") // si son todas las Direcciones, se separan por pagina, si no simplemente se continua en la proxima linea
        {$pdf->AddPage();} else {$pdf->Ln();}
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(0, 0, "Observaciones a la Asistencia", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de inasistentes en el PDF
        $pdf->SetFillColor(210,210,210);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        // Header
        $w = array(186);
        for($i = 0; $i < count($justificaciones_header); $i++)
        $pdf->Cell($w[$i], 7, $justificaciones_header[$i], 1, 0, 'C', 1);
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $fill = 0;
        foreach($justificaciones_rpt as $row) {
            $observacion = "C&oacute;digo: ".$row['codigo'].", Desde el: ".cambiaf_a_normal($row['fecha_desde']).", Hasta el: ".cambiaf_a_normal($row['fecha_hasta']).
                ", de: ".date("h:i:s a",strtotime($row['hora_desde']))." a: ".date("h:i:s a",strtotime($row['hora_hasta'])).", Tipo: ".$row['descripcion_tipo_justificacion'].
                ", Falta: ".$row['descripcion_falta'].", Motivo: ".$row['observaciones'];
            $pdf->MultiCell($w[0], 0, $observacion, 1, 'J',$fill,'1','','',true,0,false);
            $fill=!$fill;
        }

        // Separación, se añaden los gráficos
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(0, 0, "Indicadores", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        if($this->grafico1->ImageUrl!=""){
        $pdf->Cell($pdf->Image("/var/www/cmm/".$this->grafico1->ImageUrl,'','',"170","70",'','','',false));$pdf->Ln(80);
        }//fin si
       if($this->grafico2->ImageUrl!=""){
        $pdf->Cell($pdf->Image("/var/www/cmm/".$this->grafico2->ImageUrl,'','',"170","70",'','','',false));$pdf->Ln(80);
        }//fin si
        $pdf->Ln();

        $pdf->Output("asistencia_diaria_".cambiaf_a_mysql($this->txt_fecha_desde->Text).".pdf",'D');
    }

}

?>
