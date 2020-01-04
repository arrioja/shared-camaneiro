<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página presenta un listado de los compromisos presupuestarios
 *              que ha hecho la organización en el año seleccionado.
 *****************************************************  FIN DE INFO
*/

class listar_articulos extends TPage
{
     public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql = "select * from presupuesto.articulos where cod_organizacion='$cod_org'";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }
    public function onLoad($param)
    {

        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
 /*             $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $this->drop_ano->dataBind();*/

              // para llenar el listado del Tipo de Documento
              $cod_organizacion = usuario_actual('cod_organizacion');
              /*$sql = "select * from presupuesto.articulos where cod_organizacion='$cod_organizacion'";
              $datos = cargar_data($sql,$this);*/
              $this->DataGrid->Datasource = $this->cargar();
              $this->DataGrid->dataBind();

          }
    }



/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->tipo->TextBox->ReadOnly="True";
            $item->cod->TextBox->ReadOnly="True";

        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
           
            $item->precio->Text = "BsF. ".number_format($item->precio->Text, 2, ',', '.');
            if ($item->tipo->Text=='0' )
                $item->tipo->Text='BIEN';
            else
                $item->tipo->Text='ART&Iacute;CULO';

        }
    }

    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }
    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
    }
    public function saveItem($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid->DataKeys[$item->ItemIndex];

        //$descripcion=$item->descripcion->TextBox->Text;
        $descripcion=$item->descripcion->TextBox->Text;
        $precio=$item->precio->TextBox->Text;

		$sql="UPDATE presupuesto.articulos set descripcion='$descripcion',precio='$precio' where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }


    public function changePage($sender,$param)
	{

        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
              $this->DataGrid->Datasource = $this->cargar();
              $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}




    public function imprimir_listado($sender, $param)
    {

   /*     $cod_organizacion = usuario_actual('cod_organizacion');

        $tipo = $this->drop_tipo->SelectedValue;
        $ano = $this->drop_ano->Selectedvalue;

        $sql="select m.id, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos, p.nombre
              from presupuesto.maestro_compromisos m, presupuesto.proveedores p
              where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
                     (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor))
              order by m.numero, m.fecha";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            $resultado_drop = obtener_seleccion_drop($this->drop_tipo);
            $tipo_para_encabezado = $resultado_drop[2]; // se extrae el texto seleccionado

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de Compromisos al Presupuesto de Gastos \n".
                             "Tipo: ".$tipo_para_encabezado." Año: ".$ano;
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

            $listado_header = array('Número', 'Fecha', 'Beneficiario', 'Total');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de: ".$tipo_para_encabezado.", año: ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(18, 22, 105, 40);
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
                $pdf->Cell($w[0], 6, $row['numero'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, cambiaf_a_normal($row['fecha']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, $row['nombre'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[3], 6, "Bs. ".number_format($row['monto_total'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $total = $total + $row['monto_total'];
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
            $pdf->Output("listado_ordenes_compromisos_".$ano.".pdf",'D');
        }*/
    }

    public function imprimir_item($sender, $param)
    {
  /*      $id2=$sender->CommandParameter;

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
        $monto_total = $resultado[0]['monto_total'];
        $fecha = cambiaf_a_normal($resultado[0]['fecha']);
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];

        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto_parcial, monto_pendiente, monto_reversos from presupuesto.detalle_compromisos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and
                     (numero ='$numero') and (tipo_documento = '$tipo'))";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de ".$tipo_nombre.". \n".
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
            $pdf->SetTitle('Detalle de '.$tipo_nombre);
            $pdf->SetSubject('Detalle de '.$tipo_nombre);

            $pdf->AddPage();

            $listado_header = array('Código Presupuestario', 'Monto');

            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(50, 6, "Número del Documento:", 0, 0, 'L', 1);
            $pdf->Cell(50, 6, $numero."-".$ano, 0, 0, 'L', 1);
            $pdf->Cell(15, 6, "Fecha:", 0, 0, 'L', 1);
            $pdf->Cell(20, 6, $fecha, 0, 0, 'L', 1);
            $pdf->Ln();
            $pdf->Cell(15, 6, "C.I/RIF:", 0, 0, 'L', 1);
            $pdf->Cell(30, 6, $rif, 0, 0, 'L', 1);
            $pdf->Cell(25, 6, "Beneficiario:", 0, 0, 'L', 1);
            $pdf->Cell(150, 6, $nom_beneficiario, 0, 0, 'L', 1);
            $pdf->Ln();
            $pdf->Cell(30, 6, "Motivo:", 0, 1, 'L', 1);
            $pdf->MultiCell(0, 0, $motivo, 0, 'JL', 0, 0, '', '', true, 0);

            $pdf->Ln();
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);


            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Códigos Presupuestarios Comprometidos en la ".$tipo_nombre, 0, 1, '', 1);
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
                $pdf->Cell($w[1], 6, "Bs. ".number_format($row['monto_parcial'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, number_format($monto_total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("detalle_".$tipo_nombre."_".$numero."_".$ano.".pdf",'D');
        }*/
    }


}

?>
