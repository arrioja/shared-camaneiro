<?php
require('/var/www/tcpdf/tcpdf.php');
class listar_cuenta_bancaria extends TPage
{
	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $sql="select * from presupuesto.bancos order by nombre";
            $bancos=cargar_data($sql,$this);
            $this->drop_bancos->DataSource=$bancos;
            $this->drop_bancos->dataBind();
          }
	}

    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->saldo->Text = "Bs. ".number_format($item->saldo->Text, 2, ',', '.');
        }
    }

/* esta función se encarga de implementar el evento on_intemchange del dropdown*/
    public function actualiza_listado($sender,$param)
    {
        $cod_banco = $this->drop_bancos->SelectedValue;
        $codigo_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.bancos_cuentas where ((cod_banco = '$cod_banco') and (cod_organizacion='$codigo_organizacion')) order by tipo_cuenta, numero_cuenta";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();
    }


    public function imprimir_listado($sender, $param)
    {
        $cod_banco = $this->drop_bancos->SelectedValue;
        $codigo_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.bancos_cuentas where ((cod_banco = '$cod_banco') and (cod_organizacion='$codigo_organizacion')) order by tipo_cuenta, numero_cuenta";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            $resultado_drop = obtener_seleccion_drop($this->drop_bancos);
            $nombre_banco = $resultado_drop[2]; // se extrae el texto seleccionado

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de cuentas Bancarias Registradas \n".
                             "Banco: ".$nombre_banco.", fecha: ".date("d-m-Y");
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
            $pdf->SetTitle('Listado de Cuentas Bancarias');
            $pdf->SetSubject('Listado de Cuentas Bancarias');

            $pdf->AddPage();

            $listado_header = array('Número de Cuenta', 'Tipo', 'Saldo Actual');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de Cuentas Bancarias del Banco: ".$nombre_banco, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(60, 25, 70);
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
                $pdf->Cell($w[0], 6, $row['numero_cuenta'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['tipo_cuenta'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, "Bs. ".number_format($row['saldo'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $total = $total + $row['saldo'];
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "TOTAL:", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "Bs.".number_format($total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("listado_de_cuentas_bancarias.pdf","D");
        }
    }



}

?>
