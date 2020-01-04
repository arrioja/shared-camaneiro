<?php

class listar_presupuesto_gastos extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
//              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),5,$this);
//              $this->drop_ano->dataBind();

          }
    }

    public function nuevo_item($sender,$param)
    {

        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->monto->Text = "Bs. ".number_format($item->monto->Text, 2, ',', '.');
            if ($item->acumulado->Text == "1")
            {
                $item->Font->Bold="true";
            }
        }
    }



	public function actualiza_listado()
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;

        if ($this->gasto->Checked)
            {
                $es_retencion = 0;
                $limite = " Limit 2,50000";
            }
        else
            {
                $es_retencion = 1;
                $limite = "";
            }

        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion'))
              order by codigo".$limite;
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}

    public function imprimir_listado($sender, $param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;

        if ($this->gasto->Checked)
            {
                $es_retencion = 0;
                $limite = " Limit 2,50000";
            }
        else
            {
                $es_retencion = 1;
                $limite = "";
            }

        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion'))
              order by codigo".$limite;

        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Presupuesto de Gastos. \n".
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
            $pdf->SetTitle('Presupuesto de Gastos.');
            $pdf->SetSubject('Listado del Presupuesto de Gastos Institucional.');

            $pdf->AddPage();

            $listado_header = array('Código Presupuestario', 'Descripción', 'Monto Asignado');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Presupuesto de Gastos del año ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(60, 150, 40);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 1;
            $cual_fill_se_usa = 1;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row['acumulado'] == '1')
                {$pdf->SetFont('', 'B');} else {$pdf->SetFont('', '');}
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['descripcion'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[2], 6, "Bs. ".number_format($row['asignado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $cual_fill_se_usa = !$cual_fill_se_usa;
                ($cual_fill_se_usa == 1)?$pdf->SetFillColor(224, 235, 255):$pdf->SetFillColor(255, 255, 255);

            }

            $pdf->Output("presupuesto_de_gastos_".$ano.".pdf",'D');
        }
    }

}

?>
