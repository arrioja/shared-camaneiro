<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página presenta un listado de los compromisos presupuestarios
 *              que ha hecho la organización en el año seleccionado.
 *****************************************************  FIN DE INFO
*/

class listar_compromiso_anulado extends TPage
{

function createMultiple($link, $array) {
            $item = $link->Parent->Data;
            $return = array();
            foreach($array as $key) {
                  $return[] = $item[$key];
             }
             return implode(",", $return);
}

public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $this->drop_ano->dataBind();

              // para llenar el listado del Tipo de Documento
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql = "select nombre, siglas from presupuesto.tipo_documento
                    where ((cod_organizacion = '$cod_organizacion') and (operacion = 'CO'))";
              $datos = cargar_data($sql,$this);
              $this->drop_tipo->Datasource = $datos;
              $this->drop_tipo->dataBind();


          }
    }

/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
            $item->monto_total->Text = "Bs. ".number_format($item->monto_total->Text, 2, ',', '.');
            $item->monto_pendiente->Text = "Bs. ".number_format($item->monto_pendiente->Text, 2, ',', '.');
            $item->monto_reversos->Text = "Bs. ".number_format($item->monto_reversos->Text, 2, ',', '.');
        }
    }


    public function nuevo_item2($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->monto_total2->Text = "Bs. ".number_format($item->monto_total2->Text, 2, ',', '.');
            $item->monto_pendiente2->Text = "Bs. ".number_format($item->monto_pendiente2->Text, 2, ',', '.');
            $item->monto_reversos2->Text = "Bs. ".number_format($item->monto_reversos2->Text, 2, ',', '.');
        }
    }

	public function actualiza_listado1()
	{
         if ($this->IsValid)
          {
            //Busqueda de Registros
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano->SelectedValue;
            $tipo = $this->drop_tipo->SelectedValue;
            $sql="select m.id, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos, p.nombre,m.estatus_actual
                  from presupuesto.maestro_compromisos m, presupuesto.proveedores p
                  where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
                         (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor)) and 						(m.monto_reversos>'0')  order by m.numero desc, m.fecha";
            $resultado=cargar_data($sql,$this);
            $this->DataGrid->DataSource=$resultado;
            $this->DataGrid->dataBind();
          }
	}

    public function changePage($sender,$param)
	{
        /*$cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
        $tipo = $this->drop_tipo->SelectedValue;
        $sql="select m.id, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos, p.nombre
              from presupuesto.maestro_compromisos m, presupuesto.proveedores p
              where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
                     (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor))
              order by m.numero, m.fecha";*/
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->actualiza_listado1();
        /*$resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();*/
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}
	

	public function actualiza_listado2($sender,$param)
	{
		//Busqueda de Registros
        $id2=$sender->CommandParameter;

        $sql="select ano, cod_organizacion, tipo_documento, numero from presupuesto.maestro_compromisos
              where (id = '$id2')";
        $resultado=cargar_data($sql,$this);

        // tomo estos datos de la consulta para asegurarme de estar tomando el
        // detalle del credito adicional correcto.
        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $numero = $resultado[0]['numero'];
        $tipo = $resultado[0]['tipo_documento'];

        $this->DataGrid2->Caption="Detalle del compromiso Nro ".$tipo."(".$numero."):";
        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto_parcial, monto_pendiente, monto_reversos from presupuesto.detalle_compromisos 
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and
                     (numero ='$numero') and (tipo_documento = '$tipo'))";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid2->DataSource=$resultado;
		$this->DataGrid2->dataBind();
	}


    public function imprimir_listado($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');

        $tipo = $this->drop_tipo->SelectedValue;
        $ano = $this->drop_ano->Selectedvalue;

        $sql="select m.id, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos, p.nombre
              from presupuesto.maestro_compromisos m, presupuesto.proveedores p
              where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
                     (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor)) and (m.monto_reversos>'0') 
              order by m.numero, m.fecha";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('../tcpdf/tcpdf.php');
            $resultado_drop = obtener_seleccion_drop($this->drop_tipo);
            $tipo_para_encabezado = $resultado_drop[2]; // se extrae el texto seleccionado

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de Compromisos al Presupuesto de Gastos \n".
                             "Tipo: ".$tipo_para_encabezado." Año: ".$ano.
                             "\nFecha: ".date('d/m/Y');
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
            $pdf->SetTitle('Reporte de Compromisos al Presupuesto');
            $pdf->SetSubject('Reporte de Órdenes que comprometen al presupuesto de gastos');

            $pdf->AddPage();

            $listado_header = array('Número', 'Fecha', 'Beneficiario', 'Total','Total Anulado');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de: ".$tipo_para_encabezado." Anulada Parcialmente, año: ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B',10);
            // Header
            $w = array(18, 22, 95, 25,25);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 8, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['numero'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, cambiaf_a_normal($row['fecha']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, strtoupper($row['nombre']), $borde, 0, 'L', $fill);
                $pdf->Cell($w[3], 6, "Bs. ".number_format($row['monto_total'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['monto_reversos'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $total +=$row['monto_total'];
                $total_reverso +=$row['monto_reversos'];
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "TOTAL:", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "Bs. ".number_format($total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[3], 6, "Bs. ".number_format($total_reverso, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("listado_ordenes_compromisos_".$ano.".pdf",'D');
        }
    }

    public function imprimir_item($sender, $param)
    {
        $id2=$sender->CommandParameter;

        $sql="select mc.*, td.nombre as nombre_documento, p.rif, p.nombre
              from presupuesto.maestro_compromisos mc, presupuesto.proveedores p,
                   presupuesto.tipo_documento td
              where (p.cod_proveedor = mc.cod_proveedor) and
                    (p.cod_organizacion = mc.cod_organizacion) and
                    (mc.tipo_documento = td.siglas) and
                    (mc.cod_organizacion = td.cod_organizacion) and
                    (mc.id = '$id2')";

        $resultado=cargar_data($sql,$this);

        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $numero = $resultado[0]['numero'];
        $tipo = $resultado[0]['tipo_documento'];
        $tipo_nombre = $resultado[0]['nombre_documento'];
        $motivo = $resultado[0]['motivo'];
        $observacion = $resultado[0]['observaciones'];
        $monto_total = $resultado[0]['monto_total'];
        $fecha = cambiaf_a_normal($resultado[0]['fecha']);
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];

        $sql="select CONCAT(ac.partida,'-',ac.generica,'-',ac.especifica,'-',ac.subespecifica,'-',ac.ordinal) as codigo,
              ac.monto_parcial, ac.monto_pendiente, ac.monto_reversos,a.descripcion,ac.cantidad,ac.unidad,ac.precio_unitario from presupuesto.articulos_compromisos ac
              inner join presupuesto.articulos a on (a.cod=ac.articulo)
              where ((ac.cod_organizacion = '$cod_organizacion') and (ac.ano = '$ano') and
                     (ac.numero ='$numero') and (ac.tipo_documento = '$tipo'))";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('../tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de ".$tipo_nombre." Anulada Parcialmente.\n".
                             "Número: ".$numero.", Año: ".$ano;
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);

            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 12);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Detalle de '.$tipo_nombre);
            $pdf->SetSubject('Detalle de '.$tipo_nombre);

            $pdf->AddPage();

            $lineas=1;//contador de lineas para el footer

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(52, 5, "Número del Documento:", 1, 0, 'L', 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(75, 5, $numero."-".$ano, 1, 0, 'L', 1);
            $pdf->Cell(25, 5, "Fecha:", 1, 0, 'L', 1);
            $pdf->Cell(36, 5, $fecha, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(188, 4, 'PROVEEDOR', 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Ln();
            $pdf->SetFont('', 'B');
            $pdf->Cell(25, 6, "C.I/RIF:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',10);
            $pdf->Cell(30, 6, $rif, 1, 0, 'L', 1);
            $pdf->SetFont('', 'B',12);
            $pdf->Cell(30, 6, "Beneficiario:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',10);
            $pdf->Cell(103, 6, $nom_beneficiario, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFont('', 'B');
            $pdf->MultiCell(40, 12, "Motivo:", 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->SetFont('', '',7);
            $pdf->MultiCell(148, 12, $motivo, 1, 'JL', 0, 1, '', '', true, 0);
           $pdf->SetFont('', 'B');
            $pdf->MultiCell(40, 12, "Observacion:", 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->SetFont('', '',7);
            $pdf->MultiCell(148, 12, $observacion, 1, 'JL', 0, 1, '', '', true, 0);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(210,210,210);
            
            $pdf->Cell(188, 6, "Detalle de la ".$tipo_nombre, 1, 1, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->SetFont('helvetica', '', 8);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B',8);
            // Header del listado
            $listado_header = array('Código Presupuestario','Cantidad','Unidad','Denominación','Monto','Monto Total','Monto Anulado');
            $w = array(31,13,18,55,23,24,24);
            for($i = 0; $i < count($listado_header); $i++)
            {
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);

            }
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',8);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
             foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 4, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 4, $row['cantidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 4, $row['unidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 4, $row['descripcion'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[4], 4, "Bs. ".number_format($row['precio_unitario'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[5], 4, "Bs. ".number_format($row['monto_parcial'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[6], 4, "Bs. ".number_format($row['monto_reversos'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $monto_total_reversos+=$row['monto_reversos'];
                $pdf->Ln();
                $fill=!$fill;
                $lineas++;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            for($i=1;$i<22-$lineas;$i++)//PARA AJUSTAR LAS LINEAS DEL FOOTER
                {
                $pdf->Cell($w[0], 4,'',1,0, 'R', 0);
                $pdf->Cell($w[1], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[2], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[3], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[4], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[5], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[6], 4,'',1, 0, 'R', 0);
                $pdf->Ln();
                }
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[5], 6, number_format($monto_total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[6], 6, number_format($monto_total_reversos, 2, ',', '.'), 1, 0, 'R', 0);

            $pdf->Ln();
            $pdf->Ln();
            //firmas
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(188, 3, 'FIRMAS', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('','',8);
            $pdf->MultiCell(62, 3, 'Control Presupuestario', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Administrador', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(64, 3, 'Presidente', 1, 'C', 1, 0, '', '', true);
            $pdf->Ln();
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(64, 15, '', 1, 0, 'C', 1);
            $pdf->Ln();

            //pROVEEDOR
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(188, 3, 'PROVEEDOR', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFont('','',4);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->MultiCell(124,15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 'C', 1, 0, '', '', true);
            //$pdf->Cell(124, 15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 0, 'J', 1);
            $pdf->SetFont('','',8);
            $pdf->Cell(31, 15, 'Firma', 1, 0, 'l', 1);
            $pdf->Cell(33, 15, 'Fecha', 1, 0, 'l', 1);
            $pdf->Ln();
//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false) {
            //RECIBO CONFORME UNIDAD SOLICITANTE
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(188, 3, 'RECIBO CONFORME UNIDAD SOLICITANTE', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(46, 10, 'Nombre y Apellido', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Firma', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Cédula de Identidad', 1, 0, 'L', 1,'',0,true);
            $pdf->Cell(50, 10, 'Fecha', 1, 1, 'L', 1);

            $pdf->Output("detalle_".$tipo_nombre."_".$numero."_".$ano.".pdf",'D');
        }

    }
}

?>
