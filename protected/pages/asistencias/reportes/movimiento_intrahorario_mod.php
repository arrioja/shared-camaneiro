<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Esta página emite un reporte de las entradas y salidas del personal
 *              en el horario de trabajo, con la utilidad de modificar o eliminar.
 *****************************************************  FIN DE INFO
*/
include("protected/comunes/libchart/classes/libchart.php");
class movimiento_intrahorario_mod extends TPage
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
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select u.id, u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos,' (',p.cedula,')') as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
						ORDER BY p.nombres, p.apellidos";
            $resultado=cargar_data($sql,$this);
            // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
            $todos = array('cedula'=>'0', 'nombre'=>'TODAS LAS PERSONAS');
            array_unshift($resultado, $todos);
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
        $fecha_reporte = cambiaf_a_mysql($this->txt_fecha_desde->Text);
        $cedula = $this->drop_direcciones->SelectedValue;
        $cod_organizacion = usuario_actual('cod_organizacion');
       
        $condicion_adicional = "(p.cedula = '$cedula') and ";
        // si la consulta es global, se elimina la condición de la cédula
        if ($cedula == '0')
            { $condicion_adicional = ""; }

            $sql="SELECT e.id,(p.cedula) as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombres, e.fecha, e.hora
		  		  FROM asistencias.entrada_salida as e, organizacion.personas as p, organizacion.personas_nivel_dir n
				  WHERE ((p.cedula = e.cedula) and (e.fecha = '$fecha_reporte') and
                         $condicion_adicional
                         (n.cod_organizacion = '$cod_organizacion') and (n.cedula = p.cedula))
				  ORDER BY e.hora ASC ";

            $sqlj="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.codigo, jd.fecha_desde, jd.hora_desde,
                  jd.fecha_hasta, jd.hora_hasta, jd.observaciones, jd.lun, jd.mar, jd.mie, jd.jue, jd.vie,
                  tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
                            WHERE ((p.cedula = jp.cedula) and
                                   (p.cedula = n.cedula) and (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                                   $condicion_adicional
                                   (p.fecha_ingreso <= '$fecha_reporte') and
                                   (jd.fecha_desde <= '$fecha_reporte') and
                                   (jd.fecha_hasta >= '$fecha_reporte') and
                                   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
                            ORDER BY p.nombres, p.apellidos, jp.cedula";

            $this->asistentes=cargar_data($sql,$this);

            $arreglo_data = array();
            $i=2;
            
            foreach($this->asistentes as $arreglo){

                if ($i%2!=0)array_push($arreglo_data, array('id'=>$arreglo[id],'tipo'=>"Salida", 'hora'=>$arreglo[hora]));
                else array_push($arreglo_data, array('id'=>$arreglo[id],'tipo'=>"Entrada", 'hora'=>$arreglo[hora]));

                $i++;
            }//fin each

            $this->DataGrid->DataSource=null;
            $this->DataGrid->dataBind();
            // Se enlaza el nuevo arreglo con el listado
             if(!empty($arreglo_data)){
                $this->DataGrid->Caption="Movimientos Intrahorario del ".$this->txt_fecha_desde->Text;
                $this->DataGrid->DataSource=$arreglo_data;
                $this->DataGrid->dataBind();
             }else{
                $this->DataGrid->DataSource=$this->asistentes;
                $this->DataGrid->dataBind();
             }


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
        if(($item->hora->Text != "Hora")&&($item->ItemType!='EditItem'))
        {
          $item->hora->Text = date("h:i:s a",strtotime($item->hora->Text));
        //$item->hora->TextBox->Text=date("h:i:s a",strtotime($item->hora->TextBox->Text));
        }
// $item->hora->TextBox->Text=date("h:i:s a",strtotime($item->hora->TextBox->Text));
        if ($item->tipo->Text == "Entrada")$item->tipo->ForeColor = "Green";
        elseif  ($item->tipo->Text == "Salida") $item->tipo->ForeColor = "Red";
        
    }


 public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->consulta_asistencia($sender, $param);
    }


  public function saveItem($sender,$param)
    {
        $item=$param->Item;

        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        // Se capturan los datos provenientes de los Controles
        $hora = $item->hora->TextBox->Text;
		$sql="UPDATE asistencias.entrada_salida set hora='$hora' WHERE id='$id'";
        $resultado=modificar_data($sql,$sender);

        $this->DataGrid->EditItemIndex=-1;
        $this->consulta_asistencia($sender, $param);
    }

       public function deleteItem($sender,$param)
    {
       $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM asistencias.entrada_salida  WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->consulta_asistencia($sender, $param);
    }

        public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->consulta_asistencia($sender, $param);
    }

     public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

       $item=$param->Item;

         if($item->ItemType==='EditItem')
        {
        
           $item->tipo->TextBox->Enabled="False";
           
        }

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->Borrar->Button->Attributes->onclick='if(!confirm(\'esta Seguro?\')) return false;';
        }

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

        $info_adicional= "Reporte de Movimiento Intrahorario del ".$this->txt_fecha_desde->Text."\nFuncionario(a):".$nombre_fun;
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

        $asistentes_header = array('Nombre y Apellido','Hora','Tipo');
        $cedula = $this->drop_direcciones->SelectedValue;

        $condicion_adicional = "(p.cedula = '$cedula') and ";
        // si la consulta es global, se elimina la condición de la cédula
        if ($cedula == '0')
            { $condicion_adicional = ""; }

        $sql="SELECT (p.cedula) as cedula_integrantes, CONCAT(p.nombres,' ',p.apellidos) as nombres, e.fecha, e.hora
              FROM asistencias.entrada_salida as e, organizacion.personas as p, organizacion.personas_nivel_dir n
              WHERE ((p.cedula = e.cedula) and (e.fecha = '$fecha_reporte') and
                     $condicion_adicional
                     (n.cod_organizacion = '$cod_organizacion') and (n.cedula = p.cedula))
              ORDER BY e.hora";

        $sqlj="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.codigo, jd.fecha_desde, jd.hora_desde,
              jd.fecha_hasta, jd.hora_hasta, jd.observaciones, jd.lun, jd.mar, jd.mie, jd.jue, jd.vie,
              tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
                        FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                             asistencias.justificaciones_personas as jp, organizacion.personas as p, organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
                        WHERE ((p.cedula = jp.cedula) and
                               (p.cedula = n.cedula) and (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                               $condicion_adicional
                               (p.fecha_ingreso <= '$fecha_reporte') and
                               (jd.fecha_desde <= '$fecha_reporte') and
                               (jd.fecha_hasta >= '$fecha_reporte') and
                               (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
                        ORDER BY p.nombres, p.apellidos, jp.cedula";

        $asistentes_rpt=cargar_data($sql,$this);

        $justificaciones_rpt=cargar_data($sqlj,$this);

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
        // Header
        $w = array(136,25,25);
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
        foreach($asistentes_rpt as $row) {
            $pdf->SetTextColor(0);
            $pdf->Cell($w[0], 6, $row['nombres'], 'LR', 0, 'L', $fill);
            $pdf->Cell($w[1], 6, date("h:i:s a",strtotime($row['hora'])), 'LR', 0, 'C', $fill);

            if ($i%2!=0)$pdf->Cell($w[2], 6, "Salida", 'LR', 0, 'L', $fill);
            else $pdf->Cell($w[2], 6, "Entrada", 'LR', 0, 'L', $fill);

            $i++;
            $pdf->Ln();
            $fill=!$fill;
        }

         /* foreach($this->asistentes as $arreglo){

                if ($i%2!=0)array_push($arreglo_data, array('tipo'=>"Salida", 'hora'=>$arreglo[hora]));
                else array_push($arreglo_data, array('tipo'=>"Entrada", 'hora'=>$arreglo[hora]));

                $i++;
            }//fin each*/

        // Se añaden las observaciones a la asistencia
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
            $pdf->MultiCell($w[0], 0, $observacion, 1, 'J',$fill,'1','','',true,0,true);
            $fill=!$fill;
        }

        $pdf->Output("Reporte_Intrahorario_".cambiaf_a_mysql($this->txt_fecha_desde->Text).".pdf",'D');
    }


}

?>