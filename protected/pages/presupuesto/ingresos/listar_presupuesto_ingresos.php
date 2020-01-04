<?php

class listar_presupuesto_ingresos extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),5,$this);
              $this->drop_ano->dataBind();
          }

    }

    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->monto->Text = "Bs. ".number_format($item->monto->Text, 2, ',', '.');
        }
    }
    
	public function actualiza_listado($sender)
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->selectedValue;
        $sql="select CONCAT(ramo,'-',generica,'-',especifica,'-',subespecifica) as codigo, 
              cod_presupuesto_ingreso, descripcion, monto from presupuesto.presupuesto_ingresos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))
              order by ramo, generica, especifica, subespecifica";
        $resultado=cargar_data($sql,$sender);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
	}

	public function changePage($sender,$param)
	{
		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->actualiza_listado($sender);

	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

    public function imprimir_listado($sender, $param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;

        $sql="select CONCAT(ramo,'-',generica,'-',especifica,'-',subespecifica) as codigo,
              cod_presupuesto_ingreso, descripcion, monto from presupuesto.presupuesto_ingresos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))
              order by ramo, generica, especifica, subespecifica";

        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Presupuesto de Ingresos. \n".
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
            $pdf->SetTitle('Presupuesto de Ingresos.');
            $pdf->SetSubject('Listado de Presupuesto de Ingresos.');

            $pdf->AddPage();

            $listado_header = array('Cód. Presup.', 'Descripción', 'Monto');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Presupuesto de Ingresos del año ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(30, 160, 50);
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
                if ($row['acumulado'] == '1')
                {$pdf->SetFont('', 'B');} else {$pdf->SetFont('', '');}
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['descripcion'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[2], 6, "Bs. ".number_format($row['monto'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $total = $total + $row['monto'];
                $pdf->Ln();
                $fill=!$fill;
            }

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'C', 0);
            $pdf->Cell($w[1], 6, "TOTAL Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, number_format($total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("presupuesto_de_Ingresos_".$ano.".pdf",'D');
        }
    }

}

?>
