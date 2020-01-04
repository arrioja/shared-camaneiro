<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Esta página presenta un listado de los movimientos bancarios
 *              que ha hecho la organización en los diferentes cuentas de bancos en un mes seleccionado.
 *****************************************************  FIN DE INFO
*/

class listar_movimiento extends TPage
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

               //bancos
              $sql="select * from presupuesto.bancos where cod_organizacion='$cod_organizacion'";
              $res_banco=cargar_data($sql,$this);
              $this->drop_banco->DataSource=$res_banco;
              $this->drop_banco->dataBind();

             //meses
             $arreglo=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
             $this->drop_mes->DataSource=$arreglo;
             $this->drop_mes->dataBind();

          }
    }



    /* Funcion que llena el drop con los numeros de cuentas asociadas al drop_banco*/
     public function cargar_cuentas()
    {
      $cod_org = usuario_actual('cod_organizacion');
      $cod_banco=$this->drop_banco->SelectedValue; 
      $sql2 = "select * from presupuesto.bancos_cuentas where cod_organizacion='$cod_org' and cod_banco='$cod_banco' ";
      $datos2 = cargar_data($sql2,$this);
      array_unshift($datos2, "Seleccione");
      $this->drop_cuentas->Datasource = $datos2;
      $this->drop_cuentas->dataBind();
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
            $banco = $this->drop_banco->SelectedValue;
            $cuenta = $this->drop_cuentas->SelectedValue;
            $sql="SELECT bc.id,bc.id as id_mov,bc.cod_movimiento,bc.referencia,bc.fecha,bc.descripcion,bc.debe,bc.haber, bc.debe as monto,tm.nombre as tipo
                FROM  presupuesto.bancos_cuentas_movimientos as bc
            INNER JOIN presupuesto.tipo_movimiento as tm ON(bc.tipo=tm.siglas)
                WHERE  bc.cod_organizacion='$cod_organizacion'AND  bc.cod_banco='$banco' AND  bc.numero_cuenta='$cuenta'
                AND bc.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                ORDER BY bc.fecha,bc.id ASC";
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
            $item->monto->Text = "Bs. ".number_format($item->debe->Text+$item->haber->Text, 2, ',', '.');
            $id=$item->id_mov->Text;
            $item->codigo->Text ="<img alt='Anular' src='imagenes/iconos/delete.png' style='cursor:pointer' border='0' onclick=\"location.href ='?page=finanzas.movimientos.reverso_movimiento&id=$id'\" />";
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
            $fecha_inicio="$ano/$mes/01";
            $fecha_fin="$ano/$mes/31";
            $banco = $this->drop_banco->SelectedValue;
            $nombre_banco = $this->drop_banco->Text;
            $cuenta = $this->drop_cuentas->SelectedValue;
            $sql="SELECT bc.id,bc.cod_movimiento,(
                SELECT (
                SUM( debe - haber ) - bc.haber + bc.debe )
                FROM presupuesto.bancos_cuentas_movimientos
                WHERE  id!=bc.id AND fecha < bc.fecha
				OR (fecha = bc.fecha  AND id < bc.id)
                AND numero_cuenta = bc.numero_cuenta
                AND cod_organizacion=bc.cod_organizacion AND  cod_banco=bc.cod_banco
                ) AS saldo,bc.tipo,bc.referencia,bc.fecha,bc.descripcion,bc.debe,bc.haber,tm.nombre as nombre_movimiento,b.nombre as nombre_banco
                FROM  presupuesto.bancos_cuentas_movimientos as bc INNER JOIN presupuesto.bancos as b on(bc.cod_banco=b.cod_banco)
                INNER JOIN presupuesto.tipo_movimiento as tm ON(bc.tipo=tm.siglas)
                WHERE bc.id='$id2' ";
            $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('../tcpdf/tcpdf.php');

             $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Movimiento bancario Año: $ano Mes: $arreglo_mes[$mes]\n".
                             "Banco: ".$resultado_rpt[0]['nombre_banco']." - Cuenta: $cuenta ";
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
            $pdf->SetTitle('Reporte de Movimiento Bancario');
            $pdf->SetSubject('Reporte de Movimiento Bancario Año:$ano Mes:$arreglo_mes[$mes] ');

            $pdf->AddPage();
           
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Movimiento Bancario de ".$resultado_rpt[0]['nombre_banco']." - Cuenta: $cuenta ", 0, 1, 'C', 1);
            $pdf->Ln('2');
            //$pdf->SetFont('helvetica', 'B', 14);
           // $pdf->Cell(0, 6, "Fecha:", 0,1, 'L', 1);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->Cell(0, 6, "Fecha: ".cambiaf_a_normal($resultado_rpt[0]['fecha']), 0,1, 'L', 1);
            $pdf->Cell(0, 6, "Referencia: ".$resultado_rpt[0]['referencia'],0, 1, 'L', 1);
            $pdf->Cell(0, 6, "Tipo: ".strtoupper($resultado_rpt[0]['nombre_movimiento']), 0, 1, 'L',1);
            $descripcion=myTruncate(($resultado_rpt[0]['descripcion']), 77, ' ', '...');
            $pdf->MultiCell(160, 0, "Detalle: ".ucfirst(strtolower($descripcion)), 0, 'L', 0, 0, '', '', true, 0);
            $pdf->Ln();
            $pdf->Cell(0, 6, "Monto Bs. ".number_format(abs($resultado_rpt[0]['debe']-$resultado_rpt[0]['haber']), 2, ',', '.'), 0, 1, 'L',1);
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
            $banco = $this->drop_banco->SelectedValue;
            $nombre_banco = $this->drop_banco->Text;
            $cuenta = $this->drop_cuentas->SelectedValue;
            $sql="SELECT bc.id,bc.cod_movimiento,bc.tipo,bc.referencia,bc.fecha,bc.descripcion,bc.debe,bc.haber,tm.nombre as nombre_movimiento,b.nombre as nombre_banco,bc.cod_banco,
            (SELECT (
                SUM( debe - haber ) - bc.haber + bc.debe )
                FROM presupuesto.bancos_cuentas_movimientos
                WHERE  id!=bc.id AND (fecha < bc.fecha OR (fecha = bc.fecha  AND id < bc.id))
                AND numero_cuenta = bc.numero_cuenta AND cod_organizacion=bc.cod_organizacion AND  cod_banco=bc.cod_banco
               
                ) AS saldo
                  FROM  presupuesto.bancos_cuentas_movimientos as bc INNER JOIN presupuesto.bancos as b on(bc.cod_banco=b.cod_banco)
                INNER JOIN presupuesto.tipo_movimiento as tm ON(bc.tipo=tm.siglas)
                WHERE  bc.cod_organizacion='$cod_organizacion'AND  bc.cod_banco='$banco' AND  bc.numero_cuenta='$cuenta'
                AND bc.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                 ORDER BY bc.fecha,bc.id ASC";
            $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('../tcpdf/tcpdf.php');
           

            $pdf=new TCPDF('l', 'mm', 'legal', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de Movimientos bancarios Año: $ano Mes: $arreglo_mes[$mes]\n".
                             "Banco: ".$resultado_rpt[0]['nombre_banco']." - Cuenta: $cuenta ";
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
            $pdf->SetTitle('Reporte de Movimientos Bancarios');
            $pdf->SetSubject('Reporte de Movimientos Bancarios Año:$ano Mes:$arreglo_mes[$mes] ');

            $pdf->AddPage();

            $listado_header = array('Fecha','Tipo', 'Referencia', 'Detalle', 'Debe','Haber','Saldo');

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de Movimientos Bancarios de ".$resultado_rpt[0]['nombre_banco']." - Cuenta: $cuenta ", 0, 1, 'C', 1);
            $pdf->Ln(1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, "Año: $ano Mes: $arreglo_mes[$mes] ", 0, 1, '', 1);


            /*saldo incial*//*
            $mes_a=$mes-1;$ano_a=$ano;
            if($mes_a=="0"){$ano_a=$ano-1;$mes_a=12;}
 
            //$fecha_inicio="$ano_a/($mes_a-1)/01";
            $fecha_fin="$ano_a/$mes_a/31";

            $sql="  SELECT SUM( debe - haber )as saldo
                FROM presupuesto.bancos_cuentas_movimientos
                WHERE fecha<='$fecha_fin' AND cod_banco='".$resultado_rpt[0]['cod_banco']."' AND numero_cuenta='$cuenta'
                AND cod_organizacion='$cod_organizacion' ";
            $resultado_rpt_si=cargar_data($sql,$this);
            *//*saldo inicial*/
            $saldo_inicial= saldo_inicial($ano,$mes,$cod_organizacion,$resultado_rpt[0]['cod_banco'],$cuenta,$this);


            $pdf->Cell(0, 6,  "Saldo Inicial del Mes: Bs. ".number_format($saldo_inicial, 2, ',', '.'), 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 10);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(20, 20, 45, 140, 33,33,33);
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
                $pdf->Cell($w[1], 6, strtoupper($row['nombre_movimiento']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, $row[referencia], $borde, 0, 'C', $fill);

                $descripcion=myTruncate($row['descripcion'], 77, ' ', '...');
                $pdf->Cell($w[3], 6, ucfirst(strtolower(substr($row['descripcion'],0,77) )), $borde, 0, 'L', $fill);

                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['debe'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[5], 6, "Bs. ".number_format($row['haber'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[6], 6, "Bs. ".number_format($row['saldo'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $total_debe = $total_debe + ($row['debe']);
                $total_haber = $total_haber + ($row['haber']);
                $total_saldo = $row['saldo'];
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "TOTAL:", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, "Bs. ".number_format($total_debe, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[5], 6, "Bs. ".number_format($total_haber, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[6], 6, "Bs. ".number_format(($total_debe-$total_haber)+$saldo_inicial, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("listado_movimientos_$ano_$mes.pdf",'D');
        }
    }

}

?>
