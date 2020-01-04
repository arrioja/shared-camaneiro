<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Esta página presenta un listado de los movimientos de Retencion
 *              que ha hecho la organización en los diferentes retenciones cargadas segun el año seleccionado.
 *****************************************************  FIN DE INFO
*/

class movimientos_retencion extends TPage
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
              //años
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $this->drop_ano->dataBind();
              $cod_organizacion = usuario_actual('cod_organizacion');

             //meses
             $arreglo=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
             $this->drop_mes->DataSource=$arreglo;
             $this->drop_mes->dataBind();

          }
    }



    /* Funcion que llena el drop con los numeros de cuentas asociadas al drop*/
     public function cargar_retenciones()
    {
      $cod_org = usuario_actual('cod_organizacion');
      $ano=$this->drop_ano->SelectedValue;
      $sql2 = "select * from presupuesto.retencion where cod_organizacion='$cod_org' and ano='$ano' ";
      $datos2 = cargar_data($sql2,$this);
     // array_unshift($datos2, "Seleccione");
      $this->drop_retenciones->Datasource = $datos2;
      $this->drop_retenciones->dataBind();
    }


    	public function actualiza_listado()
	{
         if ($this->IsValid)
          {
            //Busqueda de Registros
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano->SelectedValue;
            $mes = $this->drop_mes->SelectedValue;
            $fecha_inicio="$ano/$mes/01";
            $fecha_fin="$ano/$mes/31";
            $codigo_sin_descomponer=$this->drop_retenciones->SelectedValue;
            $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));
           
           
            $sql = "(select dp.id,m.fecha_cheque as fecha,dp.monto,
                    CONCAT(dp.tipo_documento_causado,'-',dp.numero_documento_causado) as documento,
                    CONCAT(p.nombre,' (',p.rif,')') as proveedor
                     FROM presupuesto.detalle_pagado as dp
            INNER JOIN presupuesto.maestro_pagado as m ON (m.numero=dp.numero_documento_pagado AND m.ano=dp.ano)
            INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = m.cod_proveedor)
            where ((dp.es_retencion='1') and (m.cod_organizacion = '$cod_organizacion') and
                       (dp.ano = '$ano') and
                       (dp.partida='$codigo[partida]' and dp.generica='$codigo[generica]' and especifica='$codigo[especifica]'
            and  dp.subespecifica='$codigo[subespecifica]' and dp.ordinal='$codigo[ordinal]')and (m.estatus_actual='NORMAL')
            and m.fecha_cheque  BETWEEN '$fecha_inicio' AND '$fecha_fin' ))

             UNION
               ( SELECT rp.id,m.fecha,rp.monto,
                CONCAT(m.tipo,'-',m.referencia) as documento,
                CONCAT(p.nombre,' (',p.rif,')') as proveedor
                FROM presupuesto.retencion_pagado as rp
                INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=rp.cod_movimiento AND c.ano=rp.ano AND c.estatus_actual='NORMAL')
                INNER JOIN presupuesto.bancos_cuentas_movimientos as m ON (m.cod_movimiento=rp.cod_movimiento AND m.ano=rp.ano)
                INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = c.cod_proveedor)
            WHERE (rp.codigo_retencion='$codigo_sin_descomponer' AND m.fecha  BETWEEN '$fecha_inicio' AND '$fecha_fin' ) 
)
           ORDER BY fecha ASC " ;

             //   order by m.fecha_cheque ASC

            $resultado=cargar_data($sql,$this);
            $this->DataGrid->DataSource=$resultado;
            $this->DataGrid->dataBind();
          }
	}
    /* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
            $item->haber->Text = "Bs. ".number_format('0', 2, ',', '.');
            $item->debe->Text = "Bs. ".number_format('0', 2, ',', '.');

            if($item->monto->Text<0)
             $item->debe->Text = "Bs. ".number_format(abs($item->monto->Text), 2, ',', '.');
             else
             $item->haber->Text = "Bs. ".number_format($item->monto->Text, 2, ',', '.');
   
            $id=$item->id->Text;
           
        }
    }

    public function changePage($sender,$param)
	{
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->actualiza_listado();

	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}



public function imprimir_detalle($sender, $param)
    {
 $arreglo_mes=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');

        $id2=$sender->CommandParameter;
        //Busqueda de Registros
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano->SelectedValue;
            $mes = $this->drop_mes->SelectedValue;
            $retencion=$this->drop_retenciones->SelectedValue;
            $fecha_inicio="$ano/$mes/01";
            $fecha_fin="$ano/$mes/31";

          $sql = "select dp.id,m.fecha_cheque as fecha,dp.monto,
                    CONCAT(dp.tipo_documento_causado,'-',dp.numero_documento_causado) as documento,
                    CONCAT(p.nombre,' (',p.rif,')') as proveedor
                     FROM presupuesto.detalle_pagado as dp
            INNER JOIN presupuesto.maestro_pagado as m ON (m.numero=dp.numero_documento_pagado AND m.ano=dp.ano)
            INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = m.cod_proveedor)
            where (dp.id='$id2') ";
            $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('../tcpdf/tcpdf.php');

             $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Movimiento Retencion Año: $ano Mes: $arreglo_mes[$mes]\n".
                             "Retencion: ".$retencion;
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
            $pdf->SetTitle('Reporte de Movimiento Retencion');
            $pdf->SetSubject('Reporte de Retencion Año:$ano Mes:$arreglo_mes[$mes] ');

            $pdf->AddPage();
           
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Movimiento Retencion de ".$retencion, 0, 1, 'C', 1);
            $pdf->Ln('2');
            //$pdf->SetFont('helvetica', 'B', 14);
           // $pdf->Cell(0, 6, "Fecha:", 0,1, 'L', 1);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->Cell(0, 6, "Fecha: ".cambiaf_a_normal($resultado_rpt[0]['fecha']), 0,1, 'L', 1);
            $pdf->Cell(0, 6, "Documento: ".$resultado_rpt[0]['documento'],0, 1, 'L', 1);
            $pdf->MultiCell(160, 0, "Proveedor: ".$resultado_rpt[0]['proveedor'], 0, 'L', 0, 0, '', '', true, 0);
            $pdf->Ln();
            $pdf->Cell(0, 6, "Monto Bs. ".number_format(abs($resultado_rpt[0]['monto']), 2, ',', '.'), 0, 1, 'L',1);
            $pdf->Cell(0, 6, "Saldo Cuenta: Bs. ".number_format($resultado_rpt[0]['saldo'], 2, ',', '.'), 0, 1, 'L', 1);

            $pdf->Output("movimiento_$ano_$mes_$referencia.pdf",'D');
        }
    }



    //listado de movimientos todos del mes
    public function imprimir_listado($sender, $param)
    {
 $arreglo_mes=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');

        //Busqueda de Registros
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano->SelectedValue;
            $mes = $this->drop_mes->SelectedValue;
            $fecha_inicio="$ano/$mes/01";
            $fecha_fin="$ano/$mes/31";
            $codigo_sin_descomponer=$this->drop_retenciones->SelectedValue;
            $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));
           
           
           //obtenemos nombre de la retencion
                            $sql = "select descripcion from presupuesto.retencion
                                    where ((cod_organizacion = '$cod_organizacion') and (codigo ='$codigo_sin_descomponer')and(ano = '$ano') )";
                            $resultado_desc = cargar_data($sql,$this);
           $retencion=$resultado_desc[0]['descripcion']." (".codigo_resumido($this->drop_retenciones->Text,$sender).")";


                    $sql = "(select dp.id,m.fecha_cheque as fecha,dp.monto,
                    CONCAT(dp.tipo_documento_causado,'-',dp.numero_documento_causado) as documento,
                    CONCAT(p.nombre,' (',p.rif,')') as proveedor
                     FROM presupuesto.detalle_pagado as dp
            INNER JOIN presupuesto.maestro_pagado as m ON (m.numero=dp.numero_documento_pagado AND m.ano=dp.ano)
            INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = m.cod_proveedor)
            where ((dp.es_retencion='1') and (m.cod_organizacion = '$cod_organizacion') and
                       (dp.ano = '$ano') and
                       (dp.partida='$codigo[partida]' and dp.generica='$codigo[generica]' and especifica='$codigo[especifica]'
            and  dp.subespecifica='$codigo[subespecifica]' and dp.ordinal='$codigo[ordinal]')and (m.estatus_actual='NORMAL')
            and m.fecha_cheque  BETWEEN '$fecha_inicio' AND '$fecha_fin' ))

             UNION
               ( SELECT rp.id,m.fecha,rp.monto,
                CONCAT(m.tipo,'-',m.referencia) as documento,
                CONCAT(p.nombre,' (',p.rif,')') as proveedor
                FROM presupuesto.retencion_pagado as rp
                INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=rp.cod_movimiento AND c.ano=rp.ano AND c.estatus_actual='NORMAL')
                INNER JOIN presupuesto.bancos_cuentas_movimientos as m ON (m.cod_movimiento=rp.cod_movimiento AND m.ano=rp.ano)
                INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = c.cod_proveedor)
            WHERE (rp.codigo_retencion='$codigo_sin_descomponer' AND m.fecha  BETWEEN '$fecha_inicio' AND '$fecha_fin' )
)
           ORDER BY fecha ASC " ;
            $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('../tcpdf/tcpdf.php');
           

            $pdf=new TCPDF('l', 'mm', 'legal', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de Movimientos Retencion Año: $ano Mes: $arreglo_mes[$mes]\n".
                             $retencion;
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
            $pdf->SetTitle('Reporte de Movimientos Retencion');
            $pdf->SetSubject('Reporte de Movimientos Retencion Año:$ano Mes:$arreglo_mes[$mes] ');

            $pdf->AddPage();

            $listado_header = array('Fecha','Documento', 'Proveedor', 'Debe','Haber','Saldo');

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de Movimientos de ".$retencion, 0, 1, 'C', 1);
            $pdf->Ln(1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, "Año: $ano Mes: $arreglo_mes[$mes] ", 0, 1, '', 1);



            $saldo_inicial= saldo_mes_retencion($ano,$mes,$cod_organizacion,$codigo_sin_descomponer,$this);



            $pdf->Cell(0, 6,  "Saldo Inicial del Mes: Bs. ".number_format($saldo_inicial, 2, ',', '.'), 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 10);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(20, 30, 180, 33, 33,33);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 5, $listado_header[$i], 1, 0, 'C', 1);
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
                $pdf->Cell($w[0], 6, cambiaf_a_normal($row['fecha']), $borde, 0, 'L', $fill);
                $pdf->Cell($w[1], 6, strtoupper($row['documento']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, $row['proveedor'], $borde, 0, 'L', $fill);
               
                if($row['monto']<0){
                $monto="Bs. ".number_format(abs($row['monto']), 2, ',', '.');
                 $pdf->Cell($w[3], 6,$monto , $borde, 0, 'R', $fill);
                  $total_debe+= abs($row['monto']);
                }else{$pdf->Cell($w[3], 6,"Bs. ".number_format('0', 2, ',', '.') , $borde, 0, 'R', $fill);}

                 if($row['monto']>0){
                 $monto="Bs. ".number_format($row['monto'], 2, ',', '.');
                 $pdf->Cell($w[4], 6, $monto, $borde, 0, 'R', $fill);
                  $total_haber += ($row['monto']);
                 }else{$pdf->Cell($w[4], 6,"Bs. ".number_format('0', 2, ',', '.') , $borde, 0, 'R', $fill);}
               
                $pdf->Cell($w[5], 6, "Bs. ".number_format($row['saldo'], 2, ',', '.'), $borde, 0, 'R', $fill);
               
               
                $total_saldo = $row['saldo'];
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "TOTAL:", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "Bs. ".number_format($total_debe, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[4], 6, "Bs. ".number_format($total_haber, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[5], 6, "Bs. ".number_format(($total_debe-$total_haber)+$saldo_inicial, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("listado_movimientos_retencion_$ano_$mes.pdf",'D');
        }
    }

}

?>
