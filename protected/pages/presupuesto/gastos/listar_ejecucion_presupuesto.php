<?php

class listar_ejecucion_presupuesto extends TPage
{
var $ver;//variable global en la clase para saver si visualizo la columna 'ver orden'

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          { 
              $this->ver=False;
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),5,$this);
              $this->drop_ano->dataBind();
              $codigo = codigo_unidad_ejecutora($this);
              if (empty($codigo) == false)
              { $this->txt_codigo->Mask = $codigo."-###-##-##-##-#####"; }
          }
    }

/* Esta función valida la existencia del código presupuestario en la tabla
 * de presupuestos, de no existir, se muestra un mensaje de error.
 */
    public function validar_existencia($sender, $param)
    {
       /*$ano = $this->lbl_ano->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $sql = "select * from presupuesto.presupuesto_gastos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                            (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
                            (ordinal = '$codigo[ordinal]'))";
       $existe = cargar_data($sql,$this);
       //$param->IsValid = !(empty($existe));
       $param->IsValid="True";*/
    }
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            if ($item->acumulado->Text == "1")
            { $item->Font->Bold="true"; }
            $item->monto->Text = "Bs. ".number_format($item->monto->Text, 2, ',', '.');
            $item->aumentos->Text = "Bs. ".number_format($item->aumentos->Text, 2, ',', '.');
            $item->disminuciones->Text = "Bs. ".number_format($item->disminuciones->Text, 2, ',', '.');
            $item->modificado->Text = "Bs. ".number_format($item->modificado->Text, 2, ',', '.');
            $item->comprometido->Text = "Bs. ".number_format($item->comprometido->Text, 2, ',', '.');
            $item->causado->Text = "Bs. ".number_format($item->causado->Text, 2, ',', '.');
            $item->pagado->Text = "Bs. ".number_format($item->pagado->Text, 2, ',', '.');
            $item->disponible->Text = "Bs. ".number_format($item->disponible->Text, 2, ',', '.');
        }


    }

	public function actualiza_listado($sender,$param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
        $es_retencion = 0;
        $limite = " Limit 2,50000";
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal,'-','$ano') as codigo2,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado, id from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion') )
              order by codigo".$limite;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();

        $this->btn_filtrar->Enabled="True";
        $this->btn_filtrar->IsDefaultButton="True";
	}
    public function ver_ordenes()
	{
     /*   $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
       es_retencion = 0;
        $limite = " Limit 2,50000";
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion') )
              order by codigo".$limite;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
        $this->btn_filtrar->Enabled="True";
        $this->btn_filtrar->IsDefaultButton="True";*/
	}


	public function consulta_filtrada($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
        $es_retencion = 0;
       $limite = " Limit 2,50000";
        $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado, id from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') ";

        if ($codigo['generica']!='00')
        {
        $sql=$sql." and (generica = '$codigo[generica]')";
            if ($codigo['especifica']!='00')
            $sql=$sql." and (especifica = '$codigo[especifica]')";

        }
        //$sql=$sql." ) order by codigo".$limite; //pa q?
        $sql=$sql." ) order by codigo";
return $sql;
    }

	public function filtrar($sender, $param)
	{
        $sql=$this->consulta_filtrada($sender, $param);
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
        $this->filtrada->Text="1";
        
	}

    public function imprimir_listado($sender, $param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
        $es_retencion = 0;
        $limite = " Limit 2,50000";
       
        if ($this->filtrada->Text=="1")
        $sql=$this->consulta_filtrada($sender, $param);
        else
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion'))
              order by codigo".$limite;

        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('l', 'mm', 'legal', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Reporte de Ejecución Presupuestaria. \n".
                             "Reporte del Año: ".$ano.", a la fecha: ".date("d/m/Y");
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
            $pdf->SetTitle('Ejecución Presupuestaria.');
            $pdf->SetSubject('Reporte de Ejecución Presupuestaria de la Institución.');

            $pdf->AddPage();

            $listado_header = array('Código', 'Descripción', 'Asignado','Aumentos','Disminuciones','Modificado','Comprometido','Causado','Pagado','Disponible');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Ejecución Presupuestaria del año ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 10);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(28, 75, 28,28,28,28,28,28,28,28);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',8);
            // Data
            $fill = 1;
            $cual_fill_se_usa = 1;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            $i=1;
            foreach($resultado_rpt as $row) {
                if ($row['acumulado'] == '1')
                {$pdf->SetFont('', 'B');} else {$pdf->SetFont('', '');}
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->SetTextColor(0,0,0); // se coloca en negro para la siguiente columna
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, strtoupper($row['descripcion']), $borde, 0, 'L', $fill);
                $pdf->SetTextColor(0,130,0);
                $pdf->Cell($w[2], 6, "Bs. ".number_format($row['asignado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[3], 6, "Bs. ".number_format($row['aumentos'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(230,20,20); // se coloca en rojo para la siguiente columna
                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['disminuciones'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0,0,0); // se coloca en negro para la siguiente columna
                $pdf->Cell($w[5], 6, "Bs. ".number_format($row['modificado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(230,20,20); // se coloca en rojo para la siguiente columna
                $pdf->Cell($w[6], 6, "Bs. ".number_format($row['comprometido'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[7], 6, "Bs. ".number_format($row['causado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[8], 6, "Bs. ".number_format($row['pagado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0,0,0); // se coloca en negro para la siguiente columna
                $pdf->Cell($w[9], 6, "Bs. ".number_format($row['disponible'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $cual_fill_se_usa = !$cual_fill_se_usa;
                ($cual_fill_se_usa == 1)?$pdf->SetFillColor(224, 235, 255):$pdf->SetFillColor(255, 255, 255);

                //se imprime los titulos si es otra hoja($i=28)
                $i++;
                if($i==28){
                    // se realiza el listado de asistentes en el PDF
                    $pdf->SetFillColor(210,210,210);
                    $pdf->SetTextColor(0);
                    $pdf->SetDrawColor(41, 22, 11);
                    $pdf->SetLineWidth(0.3);
                    $pdf->SetFont('', 'B','10');
                    // Header
                    $w = array(28, 75, 28,28,28,28,28,28,28,28);
                    for($i = 0; $i < count($listado_header); $i++)
                    $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
                    $pdf->Ln();
                    // Color and font restoration

                    $pdf->SetFillColor(224, 235, 255);
                    $pdf->SetTextColor(0);
                    $pdf->SetFont('','',8);
                    $i=0;
                }//fin si
                //fin se imprime los titulos si es otra hoja($i=28)
                
            }//fin para

            $pdf->Output("ejecucion_presupuesto_de_gastos_".$ano.".pdf",'D');
        }
    }


}

?>
