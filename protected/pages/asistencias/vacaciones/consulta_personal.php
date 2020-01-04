<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por:  Ronald .A Salazar C.
 * Descripción: Esta ventana consulta las vacaciones disfrutadas y pendientes del funcionario Conectado.
 *****************************************************  FIN DE INFO
*/
class consulta_personal extends TPage
{

    public function onLoad($param)
    {      
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
       {
           $this->consulta_vacacion($this, $param);
       }
    }

/* Esta función realiza la sonculta de las vacaciones dependiendo de la cédula
 * suministrada.
 */
    public function consulta_vacacion($sender, $param)
    {
        //$cedula = $this->txt_cedula->Text;
        $cedula = usuario_actual('cedula');
        $sql="select id, periodo, CONCAT(anos_antiguedad,' + ',anos_antiguedad_otro) as anos,
                     dias, disfrutados, pendientes, restados, disponible_desde, antiguo  from asistencias.vacaciones as v
                       where (v.cedula='$cedula')
                       order by v.disponible_desde";
        $vacaciones=cargar_data($sql,$sender);

        $sqlp="select nombres, apellidos from organizacion.personas
                       where (cedula='$cedula')";
        $persona=cargar_data($sqlp,$sender);

        // para borrar el segundo grid (es útil por si se ha seleccionado otro funcionario)
        $vacio=array();
        $this->DataGriddetalle->DataSource=$vacio;
        $this->DataGriddetalle->dataBind();

        $this->DataGrid->Caption = "Detalle de vacaciones para: ".$persona[0]['nombres']." ".$persona[0]['apellidos'];
        $this->DataGrid->DataSource=$vacaciones;
        $this->DataGrid->dataBind();
    }

/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $item=$param->Item;
        if ($item->antiguo->Text == "1")
        {
            $item->anos->Text = "---";
        }
        if ($item->disponible_desde->Text != "Disponible desde")
        {
            $item->disponible_desde->Text = cambiaf_a_normal($item->disponible_desde->Text);
        }
       // si el funcionario tiene por lo menos 1 disfrute, puedo mostrar su detalle.
       /* if ($item->detalle->Text != "Detalles")
        {
            if (intval($item->disfrutados->Text) <= 0)
                {$item->detalle->Text = "N/D";}
        }*/
    }


/* Formatea el segundo grid de la pagina (el de el disfrute de las vacaciones*/
    public function formatear2($sender, $param)
    {
        $item=$param->Item;
        if ($item->fecha_desde->Text != "Desde")
        {
            $item->fecha_desde->Text = cambiaf_a_normal($item->fecha_desde->Text);
        }
        if ($item->fecha_hasta->Text != "Hasta")
        {
            $item->fecha_hasta->Text = cambiaf_a_normal($item->fecha_hasta->Text);
        }
        if ($item->estatus->Text != "Estatus")
        {
            switch ($item->estatus->Text)
            {
                case 0: $item->estatus->Text = "PENDIENTE";
                    break;
                case 1: $item->estatus->Text = "APROBADO";
                        $item->estatus->BackColor = "Lime";
                    break;
                case 2: $item->estatus->Text = "RECHAZADO";
                        $item->estatus->BackColor = "Red";
                    break;
                case 3: $item->estatus->Text = "CANCELADO";
                        $item->estatus->BackColor = "orange";
                    break;
            }
        }
    }

/* Esta función muestra un segundo grid con el detalle del disfrute de las vacaciones
 * para el período al cual se le ha hecho click.
 */
    public function mostrar_detalle_disfrute($sender, $param)
    {
        $cedula = usuario_actual('cedula');

        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="select periodo from asistencias.vacaciones WHERE id = '$id'";
        $resultado=cargar_data($sql,$this);
        $periodo = $resultado[0]['periodo'];

        $sql="select * from asistencias.vacaciones_disfrute vd
 							where ((vd.cedula='$cedula') and
								   (vd.periodo='$periodo'))
							order by vd.fecha_desde";
        $disfrutes=cargar_data($sql,$sender);

        $this->DataGriddetalle->Caption = "Detalle de Disfrute de vacaciones del período: ".$periodo;
        $this->DataGriddetalle->DataSource=$disfrutes;
        $this->DataGriddetalle->dataBind();
    }


/* Esta función elimina y/o cancela una solicitud en estado pendiente.
 */
    public function eliminar_click($sender, $param)
    {
        $id2=$sender->CommandParameter;
        //se carga datos de vacacion seleccionada
        $sql="SELECT estatus FROM asistencias.vacaciones_disfrute WHERE id='$id2'";
        $datos_vacacion=cargar_data($sql,$sender);
        $estado=$datos_vacacion[0]['estatus'];

        if($estado=="0"){//pendiente

             // se actualiza las vacaciones para cancelarla
            $sql="UPDATE asistencias.vacaciones_disfrute
                   SET estatus='3',referencia=''
                   WHERE id='$id2'";
            $resultado=modificar_data($sql,$sender);

            // se elimina las vacacion
            $sql="DELETE FROM asistencias.vacaciones_disfrute WHERE id='$id2'";
            //$resultado=modificar_data($sql,$sender);

                $this->mostrar_detalle_disfrute($sender, $param);
                    $this->LTB->titulo->Text = "Cancelar Solicitud de Vacacion";
                    $this->LTB->texto->Text = "Su solicitud ha sido cancelada satisfactoriamente";
                    $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                    //$this->LTB->redir->Text = "asistencias.vacaciones.consulta_cedula";
                    $params = array('mensaje');
                    $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
        }else{
            $this->LTB->titulo->Text = "Eliminar Solicitud de Vacacion";
                    $this->LTB->texto->Text = "No se puede Eliminar esta vacacion, Consulte a la direccion de Recursos Humanos";
                    $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                    //$this->LTB->redir->Text = "asistencias.vacaciones.consulta_cedula";
                    $params = array('mensaje');
                    $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
        }//fin si
    }

/* Esta función imprime la solicitud de vacaciones.
 */
    public function imprimir_item($sender, $param)
    {
         $id2=$sender->CommandParameter;

        //se carga datos de vacacion seleccionada
        $sql="SELECT estatus FROM asistencias.vacaciones_disfrute WHERE id='$id2'";
        $datos_vacacion=cargar_data($sql,$sender);
        $estado=$datos_vacacion[0]['estatus'];

       
               if($estado=="1"){//si esta aprobada
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
                    $f=split(" ",$resultado_rpt[0]['timestamp']);
                    $f2 = split("-",$f[0]);

                    $dias_feriados = dias_feriados($sender);
                    $reincorporacion  = suma_dias_habiles(cambiaf_a_normal($resultado_rpt[0]['fecha_hasta']), 2, $dias_feriados, $sender);

                    for ($num_solicitud = 0 ; $num_solicitud < 2 ; $num_solicitud++) {
                        $pdf->Ln();
                        $pdf->Cell(0, 0, "La Asunción, $f2[2]/$f2[1]/$f2[0]", 0, 0, 'R', 1);
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
                        $observaciones = "Funcionario Solicitante               Aprobado por Director               Procesado RRHH";
                        $pdf->MultiCell(0, 0, $observaciones, 0, 'L', 0, 0, '', '', true, 0);
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
             /*}else{
                    $this->LTB->titulo->Text = "Imprimir Solicitud de Vacacion";
                    $this->LTB->texto->Text = "No se puede Imprimir esta vacacion, su estatus debe ser APROBADO, consulte a la direccion de Recursos Humanos";
                    $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                    //$this->LTB->redir->Text = "asistencias.vacaciones.consulta_cedula";
                    $params = array('mensaje');
                    $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

              */
            }//fin si
    }


}
?>