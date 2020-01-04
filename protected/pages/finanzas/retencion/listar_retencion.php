<?php
class listar_retencion extends TPage
{
	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              //$this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $this->drop_ano->dataBind();
          }
	}

    public function nuevo_item($sender,$param)
    {

        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $ano = $this->drop_ano->SelectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $item->saldo->Text = "Bs. ".number_format( saldo_actual_retencion($ano,$cod_organizacion,$item->codigo->Text,$sender), 2, ',', '.');
            $item->codigo->Text = codigo_resumido($item->codigo->Text,$sender);

        }
    }

/* esta función se encarga de implementar el evento on_intemchange del dropdown*/
    public function actualiza_listado($sender,$param)
    {
        $ano = $this->drop_ano->SelectedValue;
        $codigo_organizacion = usuario_actual('cod_organizacion');
        $sql="  SELECT r.codigo,r.ano,r.descripcion,r.saldo_inicial as saldo from presupuesto.retencion as r
                WHERE ((r.ano = '$ano') AND (r.cod_organizacion='$codigo_organizacion')) ORDER BY r.codigo ASC ";

       $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
    }


    public function imprimir_listado($sender, $param)
    {
         $ano = $this->drop_ano->SelectedValue;
        $codigo_organizacion = usuario_actual('cod_organizacion');
        $sql="  SELECT r.codigo,r.ano,r.descripcion,r.saldo_inicial as saldo from presupuesto.retencion as r
                WHERE ((r.ano = '$ano') AND (r.cod_organizacion='$codigo_organizacion')) ORDER BY r.codigo ASC ";
          $resultado_rpt=cargar_data($sql,$this);

      if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
          require('/var/www/tcpdf/tcpdf.php');

               $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de Retenciones \n".
                             "Año: ".$ano.", A la fecha: ".date("d-m-Y");
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
            $pdf->SetTitle('Listado de Retenciones');
            $pdf->SetSubject('Listado de Retenciones');

            $pdf->AddPage();

            $listado_header = array('Nombre', 'Codigo', 'Saldo Actual');

            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de Retenciones Año: ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(110, 30, 45);
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
                $pdf->Cell($w[0], 6, $row['descripcion'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[1], 6,codigo_resumido($row['codigo'],$sender) , $borde, 0, 'C', $fill);
                $saldo = "Bs. ".number_format( saldo_actual_retencion($ano,$codigo_organizacion,$row['codigo'],$sender), 2, ',', '.');
                $pdf->Cell($w[2], 6,$saldo, $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $total = $total + (saldo_actual_retencion($ano,$codigo_organizacion,$row['codigo'],$sender));
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "TOTAL:", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "Bs.".number_format($total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("listado_de_retenciones.pdf","D");
        }
    }



}

?>
