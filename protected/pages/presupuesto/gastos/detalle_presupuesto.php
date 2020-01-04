<?php

class detalle_presupuesto extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          { $cod_organizacion = usuario_actual('cod_organizacion');
              //$ano=date("Y");

             /* $ano_array = ano_presupuesto($cod_organizacion,1,$this);
              $ano=$ano_array[0]['ano'];*/

              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $codigo = codigo_unidad_ejecutora($this);
              //$this->txt_codigo->Text=$codigo.'-'.$this->Request['id'];
              

            
              $codigo_p = substr($this->Request['id'], 0, 18); // devuelve "d"
              $ano=substr($this->Request['id'], -4); // devuelve "d"
              $this->txt_codigo->Text=$codigo.'-'.$codigo_p;
              
             
              $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));              
              $sql="SELECT dc.id,dc.numero,td.nombre as np,p.nombre,mc.fecha,dc.monto_parcial,mc.monto_total,mc.estatus_actual,mc.id id2 FROM presupuesto.detalle_compromisos dc inner join presupuesto.maestro_compromisos mc on ((dc.numero=mc.numero) and(dc.tipo_documento=mc.tipo_documento) and (mc.ano=dc.ano))
                    INNER JOIN presupuesto.proveedores p on (p.cod_proveedor=dc.cod_proveedor)
                    INNER JOIN presupuesto.tipo_documento td on (td.siglas=dc.tipo_documento)
                   WHERE((dc.cod_organizacion = '$cod_organizacion') and (dc.ano = '$ano') and (dc.sector = '$codigo[sector]') and (dc.programa = '$codigo[programa]') and
                            (dc.subprograma = '$codigo[subprograma]') and (dc.proyecto = '$codigo[proyecto]') and (dc.actividad = '$codigo[actividad]')
                    and (dc.partida = '$codigo[partida]')and (dc.generica = '$codigo[generica]')and (dc.especifica= '$codigo[especifica]')and (dc.subespecifica = '$codigo[subespecifica]')and (dc.ordinal = '$codigo[ordinal]') )
                    ORDER BY mc.fecha asc ";
        $resultado=cargar_data($sql,$this);
        $this->DataGrid_Compromisos->DataSource=$resultado;
        $this->DataGrid_Compromisos->dataBind();

            $sql="SELECT cc.id,cc.numero_documento_causado,td.nombre as np,p.nombre,cc.tipo_documento_causado,mc.cod_proveedor,mc.fecha,cc.monto,mc.monto_total,mc.estatus_actual,mc.id id2 FROM presupuesto.compromiso_causado cc inner join presupuesto.maestro_causado mc on ((cc.numero_documento_causado=mc.numero) and(cc.tipo_documento_causado=mc.tipo_documento)and (mc.ano=cc.ano))
                INNER JOIN presupuesto.proveedores p on (p.cod_proveedor=mc.cod_proveedor)
                INNER JOIN presupuesto.tipo_documento td on (td.siglas=cc.tipo_documento_causado)
                WHERE((cc.cod_organizacion = '$cod_organizacion') and (cc.ano = '$ano') and (cc.sector = '$codigo[sector]') and (cc.programa = '$codigo[programa]') and
                            (cc.subprograma = '$codigo[subprograma]') and (cc.proyecto = '$codigo[proyecto]') and (cc.actividad = '$codigo[actividad]')
                    and (cc.partida = '$codigo[partida]')and (cc.generica = '$codigo[generica]')and (cc.especifica= '$codigo[especifica]')and (cc.subespecifica = '$codigo[subespecifica]')and (cc.ordinal = '$codigo[ordinal]'))
            ORDER BY mc.fecha asc";
        $resultado=cargar_data($sql,$this);
        $this->DataGrid_Causados->DataSource=$resultado;
        $this->DataGrid_Causados->dataBind();


            $sql="SELECT dp.numero_documento_pagado,td.nombre as np,mp.id as id2,mp.monto_total as monto,mp.numero_cheque,mp.estatus_actual FROM presupuesto.detalle_pagado dp inner join presupuesto.maestro_pagado mp on ((dp.numero_documento_pagado=mp.numero)and (mp.ano=dp.ano))
                INNER JOIN presupuesto.proveedores p on (p.cod_proveedor=mp.cod_proveedor)
                INNER JOIN presupuesto.tipo_documento td on (td.siglas=mp.tipo_documento)
                   WHERE((dp.cod_organizacion = '$cod_organizacion') and (dp.ano = '$ano') and (dp.sector = '$codigo[sector]') and (dp.programa = '$codigo[programa]') and
                            (dp.subprograma = '$codigo[subprograma]') and (dp.proyecto = '$codigo[proyecto]') and (dp.actividad = '$codigo[actividad]')
                    and (dp.partida = '$codigo[partida]')and (dp.generica = '$codigo[generica]')and (dp.especifica= '$codigo[especifica]')and (dp.subespecifica = '$codigo[subespecifica]')and (dp.ordinal = '$codigo[ordinal]'))
                group by dp.numero_documento_pagado";
        $resultado=cargar_data($sql,$this);
        $this->DataGrid_Pagados->DataSource=$resultado;
        $this->DataGrid_Pagados->dataBind();

        $sql="SELECT dt.id,dt.numero,dt.monto_disminucion, dt.monto_aumento,mt.num_documento FROM presupuesto.detalle_traslados dt inner join presupuesto.maestro_traslados mt on ((dt.numero=mt.numero)and (mt.ano=dt.ano))
       WHERE((dt.cod_organizacion = '$cod_organizacion') and (dt.ano = '$ano') and (dt.sector = '$codigo[sector]') and (dt.programa = '$codigo[programa]') and
                (dt.subprograma = '$codigo[subprograma]') and (dt.proyecto = '$codigo[proyecto]') and (dt.actividad = '$codigo[actividad]')
        and (dt.partida = '$codigo[partida]')and (dt.generica = '$codigo[generica]')and (dt.especifica= '$codigo[especifica]')and (dt.subespecifica = '$codigo[subespecifica]')and (dt.ordinal = '$codigo[ordinal]'))";
        $resultado=cargar_data($sql,$this);
        $this->DataGrid_Traslados->DataSource=$resultado;
        $this->DataGrid_Traslados->dataBind();


       $sql="SELECT dr.id,dr.numero,dr.monto_disminucion,mr.num_documento FROM presupuesto.detalle_recortes dr inner join presupuesto.maestro_recortes mr on ((dr.numero=mr.numero)and (mr.ano=dr.ano))
       WHERE((dr.cod_organizacion = '$cod_organizacion') and (dr.ano = '$ano') and (dr.sector = '$codigo[sector]') and (dr.programa = '$codigo[programa]') and
                (dr.subprograma = '$codigo[subprograma]') and (dr.proyecto = '$codigo[proyecto]') and (dr.actividad = '$codigo[actividad]')
        and (dr.partida = '$codigo[partida]')and (dr.generica = '$codigo[generica]')and (dr.especifica= '$codigo[especifica]')and (dr.subespecifica = '$codigo[subespecifica]')and (dr.ordinal = '$codigo[ordinal]'))";
        $resultado=cargar_data($sql,$this);
        $this->DataGrid_Recortes->DataSource=$resultado;
        $this->DataGrid_Recortes->dataBind();

          }
    }

    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {

            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);

           // $item->disponible->Text = "Bs. ".number_format($item->disponible->Text, 2, ',', '.');
        }
       /* if ($this->ver)
            $item->ver->Visible="True";
        else
            $item->ver->Visible="False";*/


    }

    public function nuevo_item2($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha2->Text = cambiaf_a_normal($item->fecha2->Text);
        }
    }

	public function actualiza_listado()
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
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion') )
              order by codigo".$limite;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
        $this->btn_filtrar->Enabled="True";
        $this->btn_filtrar->IsDefaultButton="True";
	}
    public function ver_detalle_cheque($sender,$param)
	{

        $cod_organizacion = usuario_actual('cod_organizacion');

        $tipo = 'CH';
        $ano=substr($this->Request['id'], -4); // devuelve "d"
        //Busqueda de Registros
        $id2=$sender->CommandParameter;
        $id2=rellena($id2,6,"0");
        $sql="select m.id, m.numero, m.numero_cheque, m.fecha,m.fecha_cheque, m.monto_total, m.monto_reversos, p.nombre
              from presupuesto.maestro_pagado m, presupuesto.proveedores p
              where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and (m.id='$id2') and
                     (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor))
              order by m.numero, m.fecha ";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            $resultado_drop = obtener_seleccion_drop('CH');
            $tipo_para_encabezado = $resultado_drop[2]; // se extrae el texto seleccionado

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de Cheque\n".
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
            $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Reporte de Cheque al Presupuesto');
            $pdf->SetSubject('Reporte de Cheque que paga el presupuesto de gastos');

            $pdf->AddPage();

            $listado_header = array('Número', 'Realizado','NºCheque','Fecha', 'Beneficiario', 'Total');



            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Detalle de: Cheque, año: ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(18, 22,22,22, 80, 30);
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
                $pdf->Cell($w[2], 6, $row['numero_cheque'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 6, cambiaf_a_normal($row['fecha_cheque']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[4], 6, $row['nombre'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[5], 6, "Bs. ".number_format($row['monto_total'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $total = $total + $row['monto_total'];
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[5], 6, number_format($total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("listado_ordenes_causantes_".$ano.".pdf",'D');
        }
	}

    public function imprimir_compromiso($sender, $param)
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

            $lineas=1;//contador de lineas para el footer

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(50, 5, "Número del Documento:", 1, 0, 'L', 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(75, 5, $numero."-".$ano, 1, 0, 'L', 1);
            $pdf->Cell(25, 5, "Fecha:", 1, 0, 'L', 1);
            $pdf->Cell(36, 5, $fecha, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(186, 4, 'PROVEEDOR', 1, 0, 'C', 1);
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
            $pdf->Cell(101, 6, $nom_beneficiario, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFont('', 'B');
            $pdf->MultiCell(40, 12, "Destino o Motivo:", 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->SetFont('', '',7);
            $pdf->MultiCell(146, 12, $motivo, 1, 'JL', 0, 1, '', '', true, 0);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(186, 2, "", 1, 1, 'C', 1);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(210,210,210);

            $pdf->Cell(186, 6, "Detalle de la ".$tipo_nombre, 1, 1, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->SetFont('helvetica', '', 10);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header del listado
            $listado_header = array('Código Presupuestario','Cantidad','Unidad','Denominación','Monto','Monto Total');
            $w = array(40,16,20,60,25,25);
            for($i = 0; $i < count($listado_header); $i++)
            {
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);

            }
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
                $pdf->Cell($w[1], 6, $row['cantidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, $row['unidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 6, $row['descripcion'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['precio_unitario'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[5], 6, "Bs. ".number_format($row['monto_parcial'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
                $lineas++;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            for($i=1;$i<22-$lineas;$i++)//PARA AJUSTAR LAS LINEAS DEL FOOTER
                {
                $pdf->Cell($w[0], 6,'',1,0, 'R', 0);
                $pdf->Cell($w[1], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[2], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[3], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[4], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[5], 6,'',1, 0, 'R', 0);
                $pdf->Ln();
                }
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[5], 6, number_format($monto_total, 2, ',', '.'), 1, 0, 'R', 0);


            $pdf->Ln();
            $pdf->Ln();
            //firmas
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(186, 3, 'FIRMAS', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('','',8);
            $pdf->MultiCell(62, 3, 'Control Presupuestario', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Administrador', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Presidente', 1, 'C', 1, 0, '', '', true);
            $pdf->Ln();
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Ln();

            //pROVEEDOR
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(186, 3, 'PROVEEDOR', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFont('','',4);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->MultiCell(124,15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 'C', 1, 0, '', '', true);
            //$pdf->Cell(124, 15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 0, 'J', 1);
            $pdf->SetFont('','',8);
            $pdf->Cell(31, 15, 'Firma', 1, 0, 'l', 1);
            $pdf->Cell(31, 15, 'Fecha', 1, 0, 'l', 1);
            $pdf->Ln();
//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false) {
            //RECIBO CONFORME UNIDAD SOLICITANTE
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(186, 3, 'RECIBO CONFORME UNIDAD SOLICITANTE', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(46, 10, 'Nombre y Apellido', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Firma', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Cédula de Identidad', 1, 0, 'L', 1,'',0,true);
            $pdf->Cell(48, 10, 'Fecha', 1, 1, 'L', 1);

            $pdf->Output("detalle_".$tipo_nombre."_".$numero."_".$ano.".pdf",'D');
        }

    }

    public function imprimir_causado($sender, $param)
    {
        $id2=$sender->CommandParameter;

        $sql="select mc.*, td.nombre as nombre_documento, p.rif, p.nombre
              from presupuesto.maestro_causado mc, presupuesto.proveedores p,
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
        $cod_proveedor=$resultado[0]['cod_proveedor'];

        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto, CONCAT(tipo_documento_compromiso,' (',numero_documento_compromiso,')') as causando from presupuesto.compromiso_causado
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and
                     (numero_documento_causado ='$numero') and (tipo_documento_causado = '$tipo'))";
        $resultado_rpt=cargar_data($sql,$this);

        $sql_pro="select * from presupuesto.proveedores where cod_proveedor='$cod_proveedor'";
        $resultado_pro=cargar_data($sql_pro,$this);


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
            $lineas=1;//contador de lineas para el footer

            $listado_header = array('Código Presupuestario','Descripción', 'Monto');
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "ORDEN DE PAGO", 0, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFont('helvetica', '', 12);

            $pdf->Cell(50, 6, "Número del Documento:", 1, 0, 'L', 1);
            $pdf->Cell(75, 6, $numero."-".$ano, 1, 0, 'L', 1);
            $pdf->Cell(25, 6, "Fecha:", 1, 0, 'L', 1);
            $pdf->Cell(36, 6, $fecha, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(186, 4, 'BENEFICIARIO', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(25, 6, "C.I/RIF:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',8);
            $pdf->Cell(30, 6, $rif,1, 0, 'L', 1);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(30, 6, "Con cargo a:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',8);
            $pdf->Cell(101, 6, $nom_beneficiario, 1, 0, 'L', 1);
            $pdf->Ln();

            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(186, 4, 'AUTORIZADO A COBRAR', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(25, 6, "Nombre:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',8);
            $pdf->Cell(60, 6, $resultado_pro[0]['nombre_responsable'],1, 0, 'L', 1);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(35, 6, "Tlf Autorizado:", 1, 0, 'L', 1);
            $pdf->Cell(66, 6, $resultado_pro[0]['tlf_responsable'], 1, 0, 'L', 1);
            $pdf->SetFont('', '',8);
            $pdf->Ln();

            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(186, 4, 'MONTO TOTAL', 1, 1, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(186, 6, num_a_letras($monto_total).' Bolívares Fuertes  ***(BsF. '.$monto_total.')***.', 1, 0, 'C', 1);
            $pdf->Ln();

            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(186, 4, 'FORMA DE PAGO', 1, 1, 'C', 1);
            $pdf->SetFont('helvetica', '', 4);
            $pdf->MultiCell(20, 4, 'Nro de Pagos', 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->MultiCell(20, 4, 'desde', 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->MultiCell(20, 4, 'hasta', 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->MultiCell(45, 4, 'Forma de Pago', 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->MultiCell(45, 4, 'Monto Periodo', 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->MultiCell(36, 4, 'Monto Anual', 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->Ln();

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(25, 16, "Concepto:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',8);
            $pdf->MultiCell(161, 16, $motivo, 1, 'JL', 0, 0, '', '', true, 0);

            $pdf->Ln();
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);


            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(0, 6, "IMPUTACIÓN PRESUPUESTARIA", 1, 1, 'C', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header del listado
            $w = array(50,85, 51);
            for($i = 0; $i < count($listado_header); $i++)
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
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
                   $codigo = descomponer_codigo_gasto($row['codigo']);
                $sql="select descripcion from presupuesto.presupuesto_gastos where sector='$codigo[sector]' and programa='$codigo[programa]' and subprograma='$codigo[subprograma]' and proyecto='$codigo[proyecto]' and actividad='$codigo[actividad]' and partida='$codigo[partida]' and generica='$codigo[generica]' and especifica='$codigo[especifica]' and subespecifica='$codigo[subespecifica]' and ordinal='$codigo[ordinal]'";
                $res=cargar_data($sql,$this);
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $res[0]['descripcion'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, "Bs. ".number_format($row['monto'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
                $lineas++;
            }
            for($i=1;$i<20-$lineas;$i++)//PARA AJUSTAR LAS LINEAS DEL FOOTER
                {
                $pdf->Cell($w[0], 6,'',1,0, 'R', 0);
                $pdf->Cell($w[1], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[2], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[3], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[4], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[5], 6,'',1, 0, 'R', 0);
                $pdf->Ln();
                }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, number_format($monto_total, 2, ',', '.'), 1, 0, 'R', 0);
$pdf->Ln();
$pdf->Ln();$pdf->Ln();
            //firmas
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(186, 3, 'FIRMAS', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('','',8);
            $pdf->MultiCell(62, 3, 'Control Presupuestario', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Administrador', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Presidente', 1, 'C', 1, 0, '', '', true);
            $pdf->Ln();
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->Output("detalle_".$tipo_nombre."_".$numero."_".$ano.".pdf",'D');
        }
    }


public function ver_traslado($sender, $param)
    {
        $id2=$sender->CommandParameter;

        $sql="select ano, cod_organizacion, monto_total, fecha, motivo, numero, num_documento from presupuesto.maestro_traslados
              where (id = '$id2')";
        $resultado=cargar_data($sql,$this);


        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $numero = $resultado[0]['numero'];
        $num_documento = $resultado[0]['num_documento'];
        $motivo = $resultado[0]['motivo'];
        $fecha = cambiaf_a_normal($resultado[0]['fecha']);

        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto_disminucion, monto_aumento from presupuesto.detalle_traslados
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (numero ='$numero'))
              order by codigo";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de Traslado Presupuestario. \n".
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
            $pdf->SetTitle('Detalle de Traslado Presupuestario.');
            $pdf->SetSubject('Detalle de Traslado Presupuestario.');

            $pdf->AddPage();

            $listado_header = array('Código Presupuestario', 'Monto Recibido', 'Monto Cedido');

            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(50, 6, "Número del Documento:", 0, 0, 'L', 1);
            $pdf->Cell(50, 6, $num_documento, 0, 0, 'L', 1);
            $pdf->Cell(30, 6, "Fecha:", 0, 0, 'L', 1);
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
            $pdf->Cell(0, 6, "Códigos Presupuestario afectados por el traspaso", 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header del listado
            $w = array(80, 50, 50);
            for($i = 0; $i < count($listado_header); $i++)
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $borde="LR";$total_cedido=0;$total_recibido=0;
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->SetTextColor(0, 200, 0);
                if ($row['monto_aumento'] == '0.00')
                { $pdf->Cell($w[1], 6, "*****", $borde, 0, 'R', $fill); }
                else
                { $pdf->Cell($w[1], 6, "Bs. ".number_format($row['monto_aumento'], 2, ',', '.'), $borde, 0, 'R', $fill);
                  $total_recibido = $total_recibido+$row['monto_aumento'];
                }
                $pdf->SetTextColor(200, 0, 0);
                if ($row['monto_disminucion'] == '0.00')
                { $pdf->Cell($w[1], 6, "*****", $borde, 0, 'R', $fill); }
                else
                { $pdf->Cell($w[1], 6, "Bs. ".number_format($row['monto_disminucion'], 2, ',', '.'), $borde, 0, 'R', $fill);
                  $total_cedido = $total_cedido+$row['monto_disminucion'];
                }
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "TOTAL:", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "Bs. ".number_format($total_recibido, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[1], 6, "Bs. ".number_format($total_cedido, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("detalle_traslado_presupuestario_".$numero.".pdf",'D');
        }
    }



}

?>
