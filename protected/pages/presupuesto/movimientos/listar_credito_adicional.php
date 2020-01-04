<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página presenta un listado de los créditos adicionales que
 *              la institución ha recibido en el año seleccionado.
 *****************************************************  FIN DE INFO
*/

class listar_credito_adicional extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $this->drop_ano->dataBind();
          }
    }


/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->monto->Text = "Bs ".number_format($item->monto->Text, 2, ',', '.');
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
        }
    }


    public function nuevo_item2($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->monto2->Text = "Bs ".number_format($item->monto2->Text, 2, ',', '.');
        }
    }

	public function actualiza_listado1()
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
        $sql="select id, num_documento, fecha, motivo, monto_total from presupuesto.maestro_creditos 
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))
              order by fecha";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}

	public function actualiza_listado2($sender,$param)
	{
		//Busqueda de Registros
        $id2=$sender->CommandParameter;

        $sql="select ano, cod_organizacion, numero, num_documento from presupuesto.maestro_creditos
              where (id = '$id2')";
        $resultado=cargar_data($sql,$this);

        // tomo estos datos de la consulta para asegurarme de estar tomando el
        // detalle del credito adicional correcto.
        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $numero = $resultado[0]['numero'];

        $this->DataGrid2->Caption="Detalle del Cr&eacute;dito adicional (".$resultado[0]['num_documento']."):";
        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto_aumento from presupuesto.detalle_creditos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (numero ='$numero'))
              order by codigo";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid2->DataSource=$resultado;
		$this->DataGrid2->dataBind();
	}

    public function imprimir_listado($sender, $param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;

        $sql="select id, num_documento, fecha, motivo, monto_total from presupuesto.maestro_creditos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))
              order by fecha";

        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Créditos Adicionales al Presupuestarios. \n".
                             "Año: ".$ano;
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
            $pdf->SetAutoPageBreak(TRUE, 12);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Créditos Adicionales.');
            $pdf->SetSubject('Listado de Créditos Adicionales de un año.');

            $pdf->AddPage();

            $listado_header = array('Oficio', 'Fecha','Motivo', 'Monto');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Créditos Adicionales del año ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(30, 20, 150, 50);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $total = 0;
            $borde="LR"; // mientras borde tenga este valor, se dibujan las lineas de la izquierda y derecha
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row['acumulado'] == '1')
                {$pdf->SetFont('', 'B');} else {$pdf->SetFont('', '');}
                // si es el último elemento de la lista, se coloca para que se dibujen las lineas
                // inferiores de la tabla
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['num_documento'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, cambiaf_a_normal($row['fecha']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, $row['motivo'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[3], 6, "Bs. ".number_format($row['monto_total'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $total = $total + $row['monto_total'];
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'C', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'C', 0);
            $pdf->Cell($w[2], 6, "TOTAL Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, number_format($total, 2, ',', '.'), 1, 0, 'R', 0);

            $pdf->Output("creditos_adicionales_".$ano.".pdf",'D');
        }
    }


    public function imprimir_item($sender, $param)
    {
        $id2=$sender->CommandParameter;

        $sql="select ano, cod_organizacion, monto_total, fecha, motivo, numero, num_documento from presupuesto.maestro_creditos
              where (id = '$id2')";
        $resultado=cargar_data($sql,$this);


        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $numero = $resultado[0]['numero'];
        $num_documento = $resultado[0]['num_documento'];
        $motivo = $resultado[0]['motivo'];
        $monto_total = $resultado[0]['monto_total'];
        $fecha = cambiaf_a_normal($resultado[0]['fecha']);

        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto_aumento from presupuesto.detalle_creditos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (numero ='$numero'))
              order by codigo";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de Crédito Adicional. \n".
                             "Número: ".$numero.", Año: ".$ano;
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
            $pdf->SetTitle('Detalle de Crédito Adicional.');
            $pdf->SetSubject('Detalle de Crédito Adicional.');

            $pdf->AddPage();

            $listado_header = array('Código Presupuestario', 'Monto');

            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(50, 6, "Número del Documento:", 0, 0, 'L', 1);
            $pdf->Cell(50, 6, $num_documento, 0, 0, 'L', 1);
            $pdf->Cell(15, 6, "Fecha:", 0, 0, 'L', 1);
            $pdf->Cell(20, 6, $fecha, 0, 0, 'L', 1);
            $pdf->Ln();
            $pdf->Cell(30, 6, "Motivo:", 0, 1, 'L', 1);
            $pdf->MultiCell(0, 0, $motivo, 0, 'JL', 0, 0, '', '', true, 0);

            $pdf->Ln();
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);


            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Códigos presupuestarios afectados por el crédito adicional", 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header del listado
            $w = array(130, 50);
            for($i = 0; $i < count($listado_header); $i++)
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
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
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, "Bs. ".number_format($row['monto_aumento'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, number_format($monto_total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("detalle_credito_adicional_".$numero.".pdf",'D');
        }
    }

    public function imprimir_codigos($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;

        $sql="select distinct CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              SUM(monto_aumento) as monto from presupuesto.detalle_creditos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))
              group by codigo";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de códigos presupuestarios con créditos adicionales. \n".
                             "Año: ".$ano;
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
            $pdf->SetTitle('Detalle de Créditos Adicionales.');
            $pdf->SetSubject('Detalle de Créditos Adicionales.');

            $pdf->AddPage();

            $listado_header = array('Código Presupuestario', 'Monto');

            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Códigos presupuestarios con Créditos Adicionales", 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header del listado
            $w = array(130, 50);
            for($i = 0; $i < count($listado_header); $i++)
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $total = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, "Bs. ".number_format($row['monto'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $total = $total + $row['monto']; // para mostrarlo al final del listado
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, number_format($total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("cod_con_creditos_adicionales_".$ano.".pdf",'D');
        }
    }


}

?>