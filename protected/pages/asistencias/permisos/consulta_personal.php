<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A . Salazar C.
 * Descripción: Mediante esta página, El usuario puede consultar los permisos
 *              realizados por el y ver el estado en que se encuentren
 *****************************************************  FIN DE INFO
*/

class consulta_personal extends TPage
{
    var $conteo =1;
    var $cedula_conteo = "";
    var $permisos;
    var $cedula;
    
    public function onLoad($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            
            if($this->Request['ced']!="")$this->cedula=$this->Request['ced'];
            else $this->cedula=usuario_actual('cedula');
           
            $resultado = vista_usuario($cedula,$cod_organizacion,'D',$this);

            $this->cedula_usuario->Text=$this->cedula;
            
            $this->consulta_permisos($this, $param);
        }
    }

/* Esta función se encarga de mostrar el listado con las solicitudes de vacaciones pendientes para la
 * dirección seleccionada.
 */
    public function consulta_permisos($sender, $param)

    {

        
        $sql ="SELECT  j.codigo,j.estatus as estado, j.id,jd.fecha_desde as desde,jd.fecha_hasta as hasta ,jd.hora_desde,jd.hora_hasta,tf.descripcion as falta,tj.descripcion as tipo,p.cedula,CONCAT(p.nombres,' ',p.apellidos) as nombres
        FROM asistencias.justificaciones_personas AS jp
        INNER JOIN asistencias.justificaciones_dias AS jd ON ( jd.codigo_just = jp.codigo_just )
        INNER JOIN asistencias.justificaciones AS j ON ( j.codigo = jp.codigo_just )
        INNER JOIN asistencias.tipo_faltas AS tf ON ( tf.codigo = jd.codigo_tipo_falta )
        INNER JOIN organizacion.personas AS p ON (p.cedula=jp.cedula)
        INNER JOIN asistencias.tipo_justificaciones AS tj ON ( tj.codigo = jd.codigo_tipo_justificacion )
        INNER JOIN organizacion.personas_nivel_dir AS pnd ON ( pnd.cedula=jp.cedula )
        WHERE  jp.cedula='$this->cedula'
        ORDER BY j.id DESC";
        
       $this->permisos=cargar_data($sql,$sender);
        $this->DataGrid->DataSource=$this->permisos;
        $this->DataGrid->dataBind();
        

    }




/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $color1 = array("#E6ECFF","#BFCFFF");

        $item=$param->Item;

        if ($item->estado->Text != "Estado")
        {
            
            if($item->estado->Text=="0"){$item->estado->Text="Pendiente"; $item->estado->ForeColor = "red";}
            else if($item->estado->Text=="1") {$item->estado->Text="Aprobado";$item->estado->ForeColor = "green";}
            else{ $item->estado->Text="Negado";$item->estado->ForeColor = "black";}
        }

        if ($item->hasta->Text != "Hasta")
        {

            /*$hora = date_create($item->hora_hasta->Text);
            $item->hora_hasta->Text= date_format($hora, 'h:i:s');
            $item->hasta->Text = cambiaf_a_normal($item->hasta->Text)." ".$item->hora_hasta->Text;*/

           //hora hasta
            $horaf=split(':',$item->hora_hasta->Text);
            if($horaf[0]>11)$hora_hasta=($horaf[0]-12).":$horaf[1]:$horaf[2] PM";
            else $hora_hasta=$item->hora_hasta->Text." AM";

            $item->hasta->Text = cambiaf_a_normal($item->hasta->Text)." ".$hora_hasta;

        }
        if ($item->desde->Text != "Desde")
        {
            $item->desde->Text = cambiaf_a_normal($item->desde->Text)." ".$item->hora_desde->Text;
        }
    }



/* Esta función imprime la solicitud de permiso en estado aprobado.
 */
    public function imprimir_item($sender, $param)
    {
         $id2=$sender->CommandParameter;

        //se carga datos de vacacion seleccionada
        $sql="SELECT estatus FROM asistencias.justificaciones WHERE id='$id2'";
        $datos_vacacion=cargar_data($sql,$sender);
        $estado=$datos_vacacion[0]['estatus'];


               if($estado=="1"){//si esta aprobada
               /* $sql="select p.nombres, p.apellidos, p.cedula, pc.denominacion as cargo, d.nombre_completo,
                             vd.dias_disfrute, vd.periodo, vd.fecha_desde, vd.fecha_hasta, vd.timestamp
                        from asistencias.vacaciones_disfrute vd, organizacion.personas p,
                             organizacion.personas_cargos pc, organizacion.direcciones d,
                             organizacion.personas_nivel_dir pnd
                        where (p.cedula = vd.cedula) and (p.cedula = pc.cedula) and
                              (p.cedula = pnd.cedula) and (d.codigo = pnd.cod_direccion) and
                              (vd.id = '$id2')";*/

                $sql ="SELECT j.timestamp, j.id,jd.fecha_desde,jd.fecha_hasta,jd.observaciones,
                        jd.hora_desde,jd.hora_hasta,tf.descripcion as falta,tj.descripcion as tipo,
                        p.nombres,p.apellidos,p.cedula,pc.denominacion as cargo,d.nombre_completo
                FROM asistencias.justificaciones_personas AS jp
                INNER JOIN asistencias.justificaciones_dias AS jd ON ( jd.codigo_just = jp.codigo_just )
                INNER JOIN asistencias.justificaciones AS j ON ( j.codigo = jp.codigo_just )
                INNER JOIN asistencias.tipo_faltas AS tf ON ( tf.codigo = jd.codigo_tipo_falta )
                INNER JOIN organizacion.personas AS p ON (p.cedula=jp.cedula)
                INNER JOIN asistencias.tipo_justificaciones AS tj ON ( tj.codigo = jd.codigo_tipo_justificacion )
                INNER JOIN organizacion.personas_nivel_dir AS pnd ON ( pnd.cedula=jp.cedula )
                INNER JOIN organizacion.personas_cargos AS pc ON ( p.cedula = pc.cedula )
                INNER JOIN organizacion.direcciones AS d ON ( d.codigo = pnd.cod_direccion)
                WHERE  j.id='$id2' AND jp.cedula='".$this->cedula_usuario->Text."'";
                    
            
                $resultado_rpt=cargar_data($sql,$sender);

                if (!empty ($resultado_rpt))
                { // si la consulta trae resultados, entonces si se imprime
                    require('/var/www/tcpdf/tcpdf.php');

                    $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
                    $pdf->SetFillColor(205, 205, 205);//color de relleno gris

                    $info_adicional= "SOLICITUD DE PERMISO.";
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
                    $pdf->SetTitle('Solicitud de PERMISO.');
                    $pdf->SetSubject('Solicitud de PERMISO.');

                    $pdf->AddPage();

                    $pdf->SetFont('helvetica', '', 12);
                    $pdf->SetFillColor(255, 255, 255);
                    $f=split(" ",$resultado_rpt[0]['timestamp']);
                    $f2 = split("-",$f[0]);

                    $dias_feriados = dias_feriados($sender);

                    for ($num_solicitud = 0 ; $num_solicitud < 2 ; $num_solicitud++) {
                        $pdf->Ln();
                        $pdf->Cell(0, 0, "La Asunción, $f2[2]/$f2[1]/$f2[0]", 0, 0, 'R', 1);
                        $pdf->Ln();

                        //$sql_cargos="SELECT fecha_ini,fecha_fin,lugar_trabajo,denominacion FROM organizacion.personas_cargos WHERE cedula=".$resultado_rpt[0]['cedula']." ORDER BY id,fecha_ini DESC";
                        //$resultado_cargos=cargar_data($sql_cargos,$sender);

                        $sql_cargos="SELECT fecha_ini,fecha_fin,lugar_trabajo,denominacion FROM organizacion.personas_cargos WHERE cedula=".$resultado_rpt[0]['cedula']." ORDER BY id DESC LIMIT 1";
                        $resultado_cargos=cargar_data($sql_cargos,$sender);
                        /*//recorremos los cargos para ubicar el que le corresponde segun fecha inicio permiso
                        foreach($resultado_cargos as $datos_cargo){
                            if($datos_cargo['fecha_fin']!="0000-00-00"){
                                //si la fecha de inicio del permiso es menor a la final del cargo
                                // y sea fecha inicio del permiso menor a la inicial del cargo
                                if($resultado_rpt[0]['fecha_desde']<= $datos_cargo['fecha_fin']&&$resultado_rpt[0]['fecha_desde']>= $datos_cargo['fecha_ini'])
                                {
                                    $cargo=$datos_cargo['denominacion'];
                                    $direccion=$datos_cargo['lugar_trabajo'];
                                    break;//se sale
                                }
                            }else{
                                //si la fecha inicial del permiso es mayor a la inicial del cargo
                                if($resultado_rpt[0]['fecha_desde']>= $datos_cargo['fecha_ini'])
                                {
                                    $cargo=$datos_cargo['denominacion'];
                                    $direccion=$datos_cargo['lugar_trabajo'];
                                    break;//se sale
                                }//fin si
                            }//fin si
                        }//fin foreach
*/


                        $motivo = "El suscrito ".$resultado_rpt[0]['nombres']." ".$resultado_rpt[0]['apellidos'].
                                  ", titular de la Cédula de Identidad Nº".$resultado_rpt[0]['cedula'].", quien desempeña ".
                                  "el cargo de ".$resultado_cargos[0]['denominacion'].", adscrito a la Dirección de ".$resultado_rpt[0]['nombre_completo'].
                                  ", de esta institucion, ".
                                  "solicita permiso de ".$resultado_rpt[0]['falta']." por ".$resultado_rpt[0]['tipo'].
                                  ", desde ".$resultado_rpt[0]['hora_desde']." del ".cambiaf_a_normal($resultado_rpt[0]['fecha_desde']).
                                  " hasta las ".$resultado_rpt[0]['hora_hasta']." del ".cambiaf_a_normal($resultado_rpt[0]['fecha_hasta']);
                        $pdf->Ln();
                        $pdf->MultiCell(0, 0, $motivo ,0, 'J', 0, 0, '', '', true, 0);
                        $pdf->Ln();
                        $pdf->MultiCell(0, 0, "MOTIVO: ".utf8_encode($resultado_rpt[0]['observaciones']), 0, 'J', 0, 0, '', '', true, 0);
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
                        $pdf->MultiCell(0, 0, $observaciones, 0, 'C', 0, 0, '', '', true, 0);
                        $pdf->Ln();
                        $pdf->SetFont('helvetica', '', 9);
                        $pdf->MultiCell(0, 0, "Impreso el: ".date("d/m/Y h:i:s a")." - Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), 0, 'C', 0, 0, '', '', true, 0);
                        $pdf->Ln();
                        If ($num_solicitud == 0)
                        {
                            $pdf->SetFont('helvetica', '', 12);
                            $pdf->MultiCell(0, 0, ".   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .   .", 0, 'JL', 0, 0, '', '', true, 0);
                            $pdf->Ln();
                        }
                    }
                    $pdf->Output("permiso_".$resultado_rpt[0]['cedula'].".pdf",'D');
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
