<?php 
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: Ronald A. Salazar C.
 * Descripción: Esta página presenta un listado de los cheques
 *****************************************************  FIN DE INFO
*/

class listar_cheques extends TPage
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
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $this->drop_ano->dataBind();

              //meses
             $arreglo=array('CON ORDEN'=>'CON ORDEN','SIN ORDEN'=>'SIN ORDEN','RETENCION'=>'RETENCION');
             $this->drop_tipo->DataSource=$arreglo;
             $this->drop_tipo->dataBind();
            //------firmas--------------//
           $this->txt_preparado->Text="EP";
           $this->txt_revisado->Text="MN";
           $this->txt_aprobado->Text="JFS";
           $this->txt_auxiliar->Text="IR";
           $this->txt_diario->Text="";
            //------firmas--------------//
          }
    }

/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha_cheque->Text = cambiaf_a_normal($item->fecha_cheque->Text);
            $item->monto_total->Text = "Bs ".number_format($item->monto_total->Text, 2, ',', '.');
            $ano=$this->drop_ano->Text;
            $n_cheque=$item->num_cheque->Text;
            $sql="SELECT id FROM presupuesto.bancos_cuentas_movimientos WHERE referencia='$n_cheque' and tipo='CH' and ano='$ano' ";
            $resultado=cargar_data($sql,$this);
            $id=$resultado[0]['id'];
            $item->anular->Text ="<img alt='Anular' src='imagenes/iconos/delete.png' style='cursor:pointer' border='0' onclick=\"location.href ='?page=finanzas.movimientos.reverso_movimiento&id=$id'\" />";
           
        }
    }

    public function nuevo_item2($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->monto_total2->Text = "Bs ".number_format($item->monto_total2->Text, 2, ',', '.');
            
        }
    }

   public function actualizar_listado_principal($sender)
	{
      
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano->SelectedValue;
            $tipo=$this->drop_tipo->SelectedValue;
            $proveedor = $this->drop_proveedor->SelectedValue;
            
            //Busqueda de Cheques
           $sql="SELECT m.id,m.id as id2,m.cod_movimiento as numero, m.referencia as numero_cheque , m.fecha as fecha_cheque, m.haber as monto_total,c.estatus_actual, p.nombre
                FROM presupuesto.bancos_cuentas_movimientos as m
                 INNER JOIN presupuesto.cheques as c ON(c.ano=m.ano AND c.cod_movimiento=m.cod_movimiento )
                INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = c.cod_proveedor)
                WHERE m.ano='$ano' AND m.tipo='CH'  ";
       
            // si selecciono un tipo espacifico de cheque
            if($tipo!="")$sql.="AND c.tipo_pago='$tipo'";

            
            // si selecciono un proveedor si existe
             if($proveedor!=""){
                 $sql.=" AND c.cod_proveedor='$proveedor'";
             }else{
                 // para llenar el listado de Beneficiarios
              $sql2 = "select p.cod_proveedor as cod_proveedor, CONCAT(p.nombre,' / ',p.rif) as nomb from presupuesto.proveedores as p
                        INNER JOIN presupuesto.cheques as c ON (c.cod_proveedor=p.cod_proveedor)
                        WHERE (p.cod_organizacion = '$cod_organizacion' AND c.ano='$ano' ) GROUP BY c.cod_proveedor ORDER BY p.nombre ASC";
              $datos = cargar_data($sql2,$sender);
              $this->drop_proveedor->Datasource = $datos;
              $this->drop_proveedor->dataBind();
             }

            $sql.=" ORDER BY m.id DESC";
              
            $resultado=cargar_data($sql,$sender);
            $this->DataGrid->DataSource=$resultado;
            $this->DataGrid->dataBind();


    }

    public function changePage($sender,$param)
	{
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->actualizar_listado_principal($sender);
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

    public function imprimir_listado($sender, $param)
    {

            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano->SelectedValue;
            $tipo=$this->drop_tipo->SelectedValue;
            $proveedor = $this->drop_proveedor->SelectedValue;

            //Busqueda de Cheques
           $sql="SELECT m.id,m.id as id2,m.cod_movimiento as numero, m.referencia as numero_cheque , m.fecha as fecha_cheque, m.haber as monto_total,c.estatus_actual, p.nombre
                FROM presupuesto.bancos_cuentas_movimientos as m
                 INNER JOIN presupuesto.cheques as c ON(c.ano=m.ano AND c.cod_movimiento=m.cod_movimiento )
                INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = c.cod_proveedor)
                WHERE m.ano='$ano'  ";

            // si selecciono un tipo espacifico de cheque
            if($tipo!="")$sql.="AND c.tipo_pago='$tipo'";

            // si selecciono un proveedor si existe
             if($proveedor!="")$sql.=" AND c.cod_proveedor='$proveedor'";

            $sql.=" ORDER BY m.id  DESC";


        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            $tipo=ucwords(strtolower($tipo));
            $info_adicional= "Listado de Cheques $tipo\n".
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
            $pdf->SetTitle('Reporte de Cheques al Presupuesto');
            $pdf->SetSubject('Reporte de Cheques que pagan el presupuesto de gastos');

            $pdf->AddPage();
          
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de: Cheques $tipo, año: ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('helvetica', 'B', 10);
            // Header
            $listado_header = array('Movimiento','Numero','Fecha', 'Beneficiario', 'Total');
            $w = array(20,22,22, 100, 30);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('helvetica','',9);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['numero'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['numero_cheque'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, cambiaf_a_normal($row['fecha_cheque']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 6, $row['nombre'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['monto_total'], 2, ',', '.'), $borde, 0, 'R', $fill);
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
            $pdf->Cell($w[3], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, number_format($total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Output("listado_cheques_".$ano.".pdf",'D');
        }
    }
/*Se verifica el tipo de cheque para llamar a la funcion correspondiente con la consulta sql*/
public function impresion_cheque($sender,$param)
{

       $id=$sender->CommandParameter;//id movimiento
        //consulto pagos realizados en el año
       $sql = "SELECT c.tipo_pago FROM presupuesto.bancos_cuentas_movimientos as m
                INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
            WHERE ( m.id='$id')";
        $resultado=cargar_data($sql,$this);

        $tipo = $resultado[0]['tipo_pago'];
        // se llama al tipo de impresion segun tipo y se construye consulta sql
        switch($tipo){
            case 'SIN ORDEN':
                $sql2 = "SELECT c.cod_proveedor,m.*,b.nombre as nombre_banco, p.nombre, p.rif FROM presupuesto.bancos_cuentas_movimientos as m
                    INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                    INNER JOIN presupuesto.bancos as b ON ( b.cod_banco=m.cod_banco)
                    JOIN presupuesto.proveedores as p ON (p.cod_proveedor=c.cod_proveedor)
                    WHERE ( m.id='$id')";
                $this->imprimir_item_sin_orden($sender,$param,$sql2); break;
            case 'CON ORDEN':
                $sql2 = "SELECT c.cod_proveedor,mp.*,b.nombre as nombre_banco, p.nombre, p.rif,mp.numero as id
                    FROM presupuesto.bancos_cuentas_movimientos as m
                    INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                    INNER JOIN presupuesto.bancos as b ON ( b.cod_banco=m.cod_banco)
                    INNER JOIN presupuesto.maestro_pagado as mp ON(mp.tipo_documento=m.tipo AND mp.numero_cheque=m.referencia AND mp.cuenta=m.numero_cuenta AND mp.ano=m.ano)
                    JOIN presupuesto.proveedores as p ON (p.cod_proveedor=c.cod_proveedor)
                    WHERE ( m.id='$id') ";
                $this->imprimir_item_con_orden($sender,$param,$sql2); break;
            case 'RETENCION':
             $sql2 = "SELECT rp.codigo_retencion,c.cod_proveedor,m.*,b.nombre as nombre_banco, p.nombre, p.rif FROM presupuesto.bancos_cuentas_movimientos as m
                    INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                    INNER JOIN presupuesto.bancos as b ON ( b.cod_banco=m.cod_banco)
                    INNER JOIN presupuesto.retencion_pagado as rp ON(rp.cod_movimiento=m.cod_movimiento AND rp.ano=m.ano)
                    JOIN presupuesto.proveedores as p ON (p.cod_proveedor=c.cod_proveedor)
                    WHERE ( m.id='$id')";
                $this->imprimir_item_retencion($sender,$param,$sql2); break;
        }

}
/* Esta función imprime el detalle sobre el cheque y sobre el comprobante  con orden*/
    public function imprimir_item_con_orden($sender,$param,$sql)
    {
        $resultado=cargar_data($sql,$this);

        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $id2 = $resultado[0]['id'];
        $id2=rellena($id2,6,"0");
        $numero = $resultado[0]['numero'];
        $tipo = $resultado[0]['tipo_documento'];
        $motivo = $resultado[0]['motivo'];
        $monto_total = $resultado[0]['monto_total'];
        $fecha = cambiaf_a_normal($resultado[0]['fecha_cheque']);
        $fecha=split('[/.-]', $fecha);
        $dia_mes = $fecha[0]."-".$fecha[1];//dia mes actual
        $ano_cheque = $fecha[2];//año actual
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];
        $cod_proveedor=$resultado[0]['cod_proveedor'];
        $cheque=$resultado[0]['numero_cheque'];
        $banco=$resultado[0]['nombre_banco'];
        $fecha_cheque=cambiaf_a_normal($resultado[0]['fecha_cheque']);


        $sql="SELECT monto, CONCAT(tipo_documento_causado,' (',numero_documento_causado,')') as pagado, es_retencion from presupuesto.detalle_pagado
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and
                     (numero_documento_pagado ='$numero') and (es_retencion ='0'))";
        $resultado_rpt=cargar_data($sql,$this);

        $sql_pro="select * from presupuesto.proveedores where cod_proveedor='$cod_proveedor'";
        $resultado_pro=cargar_data($sql_pro,$this);


        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('p', 'mm', 'legal', true, 'utf-8', false);
            $info_adicional= "Detalle de Recibo de Pago \n".
                             "Número: ".$numero.", Año: ".$ano;
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //set auto page breaks
            $pdf->SetAutoPageBreak(FALSE, 12);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Imprimir Cheque y Recibo de pago');
            $pdf->SetSubject('Imprimir Cheque y Recibo de pago');

            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $listado_header = array('Código Presupuestario','Descripción', 'Monto');
            $pdf->SetFont('helvetica', '', 12);
           //monto en numero
           $pdf->SetXY(131, 33);
           $pdf->Cell(20, 6, "**".number_format($monto_total, 2, ',', '.')."**", 0, 0, 'L', 1);
           //nombre
           $pdf->SetXY(26, 47);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           //monto en letras
           $pdf->SetXY(28, 53);
           //$pdf->Cell(20, 6, num_a_letras($monto_total+100000), 0, 0, 'L', 1);
          
            $centimos=substr($monto_total, -2); // devuelve "d"
           $pdf->MultiCell(140, 0, strtoupper(num_a_letras($monto_total))." CON $centimos/100", 0, 'JL', 0, 0, '', '', true, 0);

             //lugar emision
           $pdf->SetXY(13, 65);
           $pdf->Cell(11, 6, "La Asunción", 0, 0, 'L', 1);

           //fecha dia-mes
           $pdf->SetXY(38, 65);
           $pdf->Cell(5, 5, $dia_mes, 0, 0, 'L', 1);
           //fecha año
           $pdf->SetXY(68, 65);
           $pdf->Cell(20, 6, $ano_cheque, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 12);
            //numero cheque
           $pdf->SetXY(32, 125);
           $pdf->Cell(20, 6, $cheque, 0, 0, 'L', 1);
           //nombre banco
           $pdf->SetXY(85, 125);
           $pdf->Cell(20, 6, $banco, 0, 0, 'L', 1);
           //fecha dia-mes-año
           $pdf->SetXY(150, 125);
           $pdf->Cell(20, 6, $dia_mes."-".$ano_cheque, 0, 0, 'L', 1);
           //nombre beneficiario
           $pdf->SetXY(32, 131);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 10);
           //concepto
           $pdf->SetXY(43, 138);
           $pdf->MultiCell(132, 16, $motivo.'.', 0, 'JL', 0, 0, '', '', true, 0);

            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(210,210,210);
            // Data
            $fill = 0;

            //CODIGOS AGRUPADOS
             $sql="SELECT *,CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo, SUM(monto) as monto_sumado  FROM  presupuesto.detalle_pagado
                WHERE  ano =  '$ano'
                AND  cod_organizacion =  '$cod_organizacion'
                AND  numero_documento_pagado =  '$numero'
                AND  es_retencion ='0'
                GROUP BY codigo ";
            $resultado_rpt=cargar_data($sql,$this);
            $eje_x=45;
            $eje_y=172;// comienza en la segunda linea
            foreach($resultado_rpt as $row) {

                $codigo = descomponer_codigo_gasto($row['codigo']);
                $sql="select descripcion from presupuesto.presupuesto_gastos where partida='$codigo[partida]' and generica='$codigo[generica]' and especifica='$codigo[especifica]' and subespecifica='$codigo[subespecifica]' and ordinal='$codigo[ordinal]' and ano='$ano'";
                $res=cargar_data($sql,$this);
                 //codigo
                $pdf->SetXY(13, $eje_y);
                $pdf->Cell(20, 6, $codigo[partida].'-'.$codigo[generica].'-'.$codigo[especifica].'-'.$codigo[subespecifica], 0, 0, 'L', $fill);
                //detalle
                $pdf->SetXY(43, $eje_y+1);
                $pdf->SetFont('helvetica','',8);
                //$descripcion="ffffffff ffffffffffff ffffffffffffffffffff fffffffffffffff fffffffff fffffffffffff fffffffff fffffffffffffffff ffffffff fffffffffffff fffffffffff ffffffffff ffffffff fffffff ffffffffff";
                $descripcion=$res[0]["descripcion"];
                $descripcion=myTruncate($descripcion, 160, ' ', '.');
                $pdf->MultiCell(70, 0, ucfirst(strtolower($descripcion)), 0, 'JL', 0, 0, '', '', true, 0);
                 //monto total por partida
                $pdf->SetFont('helvetica','',10);
                $pdf->SetXY(116, $eje_y);
                $pdf->Cell(20, 6,number_format($row['monto_sumado'], 2, ',', '.'), 0, 0, 'R', $fill);

                $eje_y=$eje_y+8;
            }
           //RETENCIONES AGRUPADAS
             $sql="SELECT *,CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo, SUM(monto) as monto_sumado  FROM  presupuesto.detalle_pagado
                WHERE  ano =  '$ano'
                AND  cod_organizacion =  '$cod_organizacion'
                AND  numero_documento_pagado =  '$numero'
                AND  es_retencion ='1'
                GROUP BY codigo";
            $resultado_rpt=cargar_data($sql,$this);
            $eje_x=45;
            // se muestra las retenciones agrupadas por codigo
            foreach($resultado_rpt as $row) {
            $codigo = descomponer_codigo_gasto($row['codigo']);
                $sql="SELECT descripcion FROM presupuesto.retencion WHERE codigo='$row[codigo]' AND ano='$ano'";
                $res=cargar_data($sql,$this);
                 //codigo
                $pdf->SetXY(13, $eje_y);
                $pdf->Cell(20, 6, $codigo[partida].'-'.$codigo[generica].'-'.$codigo[especifica].'-'.$codigo[subespecifica], 0, 0, 'L', $fill);
                //detalle
                $pdf->SetXY(43, $eje_y+1);
                $pdf->SetFont('helvetica','',8);
                $descripcion=$res[0]["descripcion"];
                $descripcion=myTruncate($descripcion, 160, ' ', '.');
                $pdf->MultiCell(70, 0, ucfirst(strtolower($descripcion)), 0, 'JL', 0, 0, '', '', true, 0);
                $pdf->SetFont('helvetica','',10);
                 //monto total por partida
            $pdf->SetXY(157, $eje_y);
            $pdf->Cell(20, 6,number_format($row['monto_sumado'], 2, ',', '.'), 0, 0, 'R', $fill);
            $eje_y=$eje_y+8;
            }

             $sql_pro="select cp.numero_cuenta_banco, CONCAT(cp.tipo_documento_causado,'-',cp.numero_documento_causado,'-',cp.ano) as ndc,ano,tipo_documento_causado,numero_documento_causado from presupuesto.causado_pagado as cp
             WHERE cp.cod_organizacion='$cod_organizacion' and cp.ano='$ano' and cp.numero_documento_pagado='$id2'
            GROUP by cp.numero_documento_causado";
            $resultado_pro=cargar_data($sql_pro,$this);
            $eje_x=45;
            $eje_y=$eje_y;
            //muestra codigos ingresos
                //obtener descripcion y codigo
                $sql="SELECT pi.descripcion, CONCAT(pi.ramo,'-',pi.generica,'-',pi.especifica,'-',pi.subespecifica) as codigo FROM presupuesto.presupuesto_ingresos pi,presupuesto.fuentes_financiamiento_cuentas ffc, presupuesto.fuentes_financiamiento ff WHERE  (ffc.numero_cuenta='".$resultado_pro[0]['numero_cuenta_banco']."')AND(ff.cod_fuente_financiamiento=ffc.cod_fuente_financiamiento) AND (pi.cod_presupuesto_ingreso=ff.cod_presupuesto_ingreso) AND (ff.ano='$ano')";
                $res2=cargar_data($sql,$sender);
                //codigo
                $pdf->SetXY(13, $eje_y);
                $pdf->Cell(20, 6,$res2[0]["codigo"], 0, 0, 'L', 0);
                //detalle
                $pdf->SetXY(43, $eje_y+1);
                $pdf->SetFont('helvetica','',8);
                $descripcion=$res2[0]["descripcion"];
                $descripcion=myTruncate($descripcion, 160, ' ', '.');
                $pdf->MultiCell(70, 0, ucfirst(strtolower($descripcion)), 0, 'JL', 0, 0, '', '', true, 0);
                $pdf->SetFont('helvetica','',10);
                //monto total por partida
                $pdf->SetXY(157, $eje_y);
                $pdf->Cell(20, 6, number_format($monto_total, 2, ',', '.'), 0, 0, 'R', 0);
                //fin muestra codigos ingreso
                $x=1;
               // se concatena las ordenes de pago canceladas
               foreach($resultado_pro as $row) {
                     if ((count($resultado_pro)>1)&&(count($resultado_pro)>$x))// si son varios comp se pone coma ','
                     $numero_doc_cau.=$row['ndc'].": ";
                     else
                     $numero_doc_cau.=$row['ndc'].".";
         
                   // concatenar compromisos
                    $sql="SELECT CONCAT(tipo_documento_compromiso,'-',numero_documento_compromiso,'-',ano) as comp
                                FROM  presupuesto.compromiso_causado
                                WHERE  tipo_documento_causado='$row[tipo_documento_causado]' AND  numero_documento_causado='$row[numero_documento_causado]'
                                AND ano='$row[ano]'
                                GROUP BY  CONCAT(tipo_documento_compromiso,'-',numero_documento_compromiso,'-',ano)";
                   $resultado_com=cargar_data($sql,$this);
              
                   $i=1;
                   foreach($resultado_com as $comp) {// se busca y concatena los compromisos 
                       if ((count($resultado_com)>1)&&(count($resultado_com)>$i))// si son varios comp se pone coma ','
                       $numero_doc_cau.=" $comp[comp],";
                       else
                       $numero_doc_cau.=" $comp[comp]".".";
                   $i++;
                   }
                   $numero_doc_cau.="; ";
                   $x++;
                 }// fin concatenar causados
                 
            //------observaciones ------//
            //nombre
           // $pdf->SetXY(13, 251);
           // $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 0);
            //tipo orden pago - numero
            $pdf->SetXY(13, 256);
            $pdf->MultiCell(75, $eje_y," ".$numero_doc_cau, 0, 'JL', 0, 0, '', '', true, 0);
			//------observaciones ------//
            //------firmas--------------//
            //preparado
            $pdf->SetXY(30, 282);
            $pdf->Cell(20, 6,$this->txt_preparado->Text, 0, 0, 'L', 0);
            //revisado
            $pdf->SetXY(75, 282);
            $pdf->Cell(20, 6, $this->txt_revisado->Text, 0, 0, 'L', 0);
            //aprobado
            $pdf->SetXY(120, 282);
            $pdf->Cell(20, 6, $this->txt_aprobado->Text, 0, 0, 'L', 0);
            //auxiliar
            $pdf->SetXY(142, 285);
            $pdf->Cell(20, 6, $this->txt_auxiliar->Text, 0, 0, 'L', 0);
            //diario
            $pdf->SetXY(164, 285);
            $pdf->Cell(20, 6, $this->txt_diario->Text, 0, 0, 'L', 0);
            //------firmas--------------//
            $pdf->Output("detalle_cheque_".$numero."_".$ano.".pdf",'D');

        }//FIN PARA
    }
/* Esta función imprime el detalle sobre el cheque y sobre el comprobante de retencion*/
    public function imprimir_item_retencion($sender, $param,$sql)
    {

        $resultado = cargar_data($sql,$this);
        
        $codigo_sin_descomponer = $resultado[0]['codigo_retencion'];
        $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));
        $motivo = $resultado[0]['descripcion'];
        $monto_total = $resultado[0]['haber'];
        $fecha=cambiaf_a_normal($resultado[0]['fecha']);
        $fecha=split('[/.-]', $fecha);
        $dia_mes = $fecha[0]."-".$fecha[1];//dia mes actual
        $ano_cheque = $fecha[2];//año actual
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];
        $banco=$resultado[0]['nombre_banco'];
        $cuenta=$resultado[0]['numero_cuenta'];
        $cheque=$resultado[0]['referencia'];
        $ano=$resultado[0]['ano'];
        $cod_proveedor=$resultado[0]['cod_proveedor'];
        $fecha_cheque=cambiaf_a_normal($resultado[0]['fecha']);


        if (!empty ($resultado))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('p', 'mm', 'legal', true, 'utf-8', false);
            $info_adicional= "Detalle de Recibo de Pago \n".
                             "Número: ".$numero.", Año: ".$ano;
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //set auto page breaks
            $pdf->SetAutoPageBreak(FALSE, 12);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Imprimir Cheque y Recibo de pago');
            $pdf->SetSubject('Imprimir Cheque y Recibo de pago');

            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $listado_header = array('Código Presupuestario','Descripción', 'Monto');
            $pdf->SetFont('helvetica', '', 12);
           //monto en numero
           $pdf->SetXY(131, 33);
           $pdf->Cell(20, 6, "**".number_format($monto_total, 2, ',', '.')."**", 0, 0, 'L', 1);
           //nombre
           $pdf->SetXY(26, 47);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           //monto en letras
           $pdf->SetXY(28, 53);
           //$pdf->Cell(20, 6, num_a_letras($monto_total+100000), 0, 0, 'L', 1);

            $centimos=substr($monto_total, -2); // devuelve "d"
           $pdf->MultiCell(140, 0, strtoupper(num_a_letras($monto_total))." CON $centimos/100", 0, 'JL', 0, 0, '', '', true, 0);

             //lugar emision
           $pdf->SetXY(13, 65);
           $pdf->Cell(11, 6, "La Asunción", 0, 0, 'L', 1);

           //fecha dia-mes
           $pdf->SetXY(38, 65);
           $pdf->Cell(20, 6, $dia_mes, 0, 0, 'L', 1);
           //fecha año
           $pdf->SetXY(68, 65);
           $pdf->Cell(20, 6, $ano_cheque, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 12);
            //numero cheque
           $pdf->SetXY(32, 125);
           $pdf->Cell(20, 6, $cheque, 0, 0, 'L', 1);
           //nombre banco
           $pdf->SetXY(85, 125);
           $pdf->Cell(20, 6, $banco, 0, 0, 'L', 1);
           //fecha dia-mes-año
           $pdf->SetXY(150, 125);
           $pdf->Cell(20, 6, $dia_mes."-".$ano_cheque, 0, 0, 'L', 1);
           //nombre beneficiario
           $pdf->SetXY(32, 131);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 10);
           //concepto
           $pdf->SetXY(43, 138);
           $pdf->MultiCell(132, 16, $motivo.'.', 0, 'JL', 0, 0, '', '', true, 0);

            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(210,210,210);
            // Data
            $fill = 0;


            $eje_x=45;
            $eje_y=172;// comienza en la segunda linea1



            // se muestra las retenciones agrupadas por codigo

//consulto pagos realizados en el año
                   $sql2 = "SELECT rp.codigo_retencion as codigo, rp.monto FROM presupuesto.bancos_cuentas_movimientos as m
                    INNER JOIN presupuesto.retencion_pagado as rp ON ( rp.cod_movimiento=m.cod_movimiento AND rp.ano=m.ano)
                    WHERE (  m.referencia='$cheque'
                            AND m.numero_cuenta='$cuenta' AND m.ano='$ano' AND m.tipo='CH')";
                $resultado = cargar_data($sql2,$this);


           // ciclo para modificar los acumulados
                foreach ($resultado as $codigos)
                {
                    $codigo = descomponer_codigo_gasto($codigos[codigo]);
                    $sql="SELECT descripcion FROM presupuesto.retencion WHERE codigo='$codigos[codigo]' AND ano='$ano'";

                    $res=cargar_data($sql,$this);
                     //codigo
                    $pdf->SetXY(13, $eje_y);
                    $pdf->Cell(20, 6, $codigo[partida].'-'.$codigo[generica].'-'.$codigo[especifica].'-'.$codigo[subespecifica], 0, 0, 'L', $fill);
                    //detalle
                    $pdf->SetXY(43, $eje_y+1);
                    $pdf->SetFont('helvetica','',8);
                    $descripcion=$res[0]["descripcion"];
                    $descripcion_retencion=ucfirst(strtolower($descripcion));
                    $descripcion=myTruncate($descripcion, 160, ' ', '.');
                    $pdf->MultiCell(70, 0, ucfirst(strtolower($descripcion)), 0, 'JL', 0, 0, '', '', true, 0);
                    $pdf->SetFont('helvetica','',10);
                     //monto
                    $pdf->SetXY(137, $eje_y);
                    $pdf->Cell(20, 6,number_format($codigos[monto], 2, ',', '.'), 0, 0, 'R', $fill);
                    $eje_y=$eje_y+8;
                }
                



            $eje_x=45;
            $eje_y=$eje_y;
            //muestra codigos ingresos
                //obtener descripcion y codigo
                $sql="SELECT pi.descripcion, CONCAT(pi.ramo,'-',pi.generica,'-',pi.especifica,'-',pi.subespecifica) as codigo FROM presupuesto.presupuesto_ingresos pi,presupuesto.fuentes_financiamiento_cuentas ffc, presupuesto.fuentes_financiamiento ff WHERE  (ffc.numero_cuenta='".$cuenta."')AND(ff.cod_fuente_financiamiento=ffc.cod_fuente_financiamiento) AND (pi.cod_presupuesto_ingreso=ff.cod_presupuesto_ingreso) AND (ff.ano='$ano')";
                $res2=cargar_data($sql,$sender);
                //codigo
                $pdf->SetXY(13, $eje_y);
                $pdf->Cell(20, 6,$res2[0]["codigo"], 0, 0, 'L', 0);
                //detalle
                $pdf->SetXY(43, $eje_y+1);
                $pdf->SetFont('helvetica','',8);
                $descripcion=$res2[0]["descripcion"];
                $descripcion=myTruncate($descripcion, 160, ' ', '.');
                $pdf->MultiCell(70, 0, ucfirst(strtolower($descripcion)), 0, 'JL', 0, 0, '', '', true, 0);
                $pdf->SetFont('helvetica','',10);
                //monto total por partida
                $pdf->SetXY(157, $eje_y);
                $pdf->Cell(20, 6, number_format($monto_total, 2, ',', '.'), 0, 0, 'R', 0);
                //fin muestra codigos ingreso
                $x=1;

            $pdf->SetFont('helvetica','',8);
            //------observaciones ------//
            //nombre
            $pdf->SetXY(13, 251);
            $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 0);
            //tipo orden pago - numero
            $pdf->SetXY(13, 256);
            $pdf->MultiCell(75, 256,"Pago de Retencion", 0, 'JL', 0, 0, '', '', true, 0);
			//------observaciones ------//
            $pdf->SetFont('helvetica','',10);
            //------firmas--------------//
            //preparado
            $pdf->SetXY(30, 282);
            $pdf->Cell(20, 6,strtoupper($this->txt_preparado->Text), 0, 0, 'L', 0);
            //revisado
            $pdf->SetXY(75, 282);
            $pdf->Cell(20, 6, strtoupper($this->txt_revisado->Text), 0, 0, 'L', 0);
            //aprobado
            $pdf->SetXY(120, 282);
            $pdf->Cell(20, 6, strtoupper($this->txt_aprobado->Text), 0, 0, 'L', 0);
            //auxiliar
            $pdf->SetXY(142, 285);
            $pdf->Cell(20, 6, strtoupper($this->txt_auxiliar->Text), 0, 0, 'L', 0);
            //diario
            $pdf->SetXY(164, 285);
            $pdf->Cell(20, 6,strtoupper($this->txt_diario->Text), 0, 0, 'L', 0);
            //------firmas--------------//
            $pdf->Output("detalle_cheque_".$numero."_".$ano.".pdf",'D');

        }//FIN PARA
    }
/* Esta función imprime el detalle sobre el cheque y sobre el comprobante sin orden*/
    public function imprimir_item_sin_orden($sender, $param,$sql)
    {
    
        $resultado = cargar_data($sql,$this);

        $motivo = $resultado[0]['descripcion'];
        $monto_total = $resultado[0]['haber'];
        $fecha=cambiaf_a_normal($resultado[0]['fecha']);
        $fecha=split('[/.-]', $fecha);
        $dia_mes = $fecha[0]."-".$fecha[1];//dia mes actual
        $ano_cheque = $fecha[2];//año actual
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];
        $banco=$resultado[0]['nombre_banco'];
        $cuenta=$resultado[0]['numero_cuenta'];
        $ano=$resultado[0]['ano'];
        $cod_proveedor=$resultado[0]['cod_proveedor'];
        $fecha_cheque=cambiaf_a_normal($resultado[0]['fecha']);


        if (!empty ($resultado))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('p', 'mm', 'legal', true, 'utf-8', false);
            $info_adicional= "Detalle de Recibo de Pago \n".
                             "Número: ".$numero.", Año: ".$ano;
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //set auto page breaks
            $pdf->SetAutoPageBreak(FALSE, 12);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Imprimir Cheque y Recibo de pago');
            $pdf->SetSubject('Imprimir Cheque y Recibo de pago');

            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $listado_header = array('Código Presupuestario','Descripción', 'Monto');
            $pdf->SetFont('helvetica', '', 12);
           //monto en numero
           $pdf->SetXY(131, 33);
           $pdf->Cell(20, 6, "**".number_format($monto_total, 2, ',', '.')."**", 0, 0, 'L', 1);
           //nombre
           $pdf->SetXY(26, 47);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           //monto en letras
           $pdf->SetXY(28, 53);
           //$pdf->Cell(20, 6, num_a_letras($monto_total+100000), 0, 0, 'L', 1);

            $centimos=substr($monto_total, -2); // devuelve "d"
           $pdf->MultiCell(140, 0, strtoupper(num_a_letras($monto_total))." CON $centimos/100", 0, 'JL', 0, 0, '', '', true, 0);

             //lugar emision
           $pdf->SetXY(13, 65);
           $pdf->Cell(11, 6, "La Asunción", 0, 0, 'L', 1);

           //fecha dia-mes
           $pdf->SetXY(38, 65);
           $pdf->Cell(20, 6, $dia_mes, 0, 0, 'L', 1);
           //fecha año
           $pdf->SetXY(68, 65);
           $pdf->Cell(20, 6, $ano_cheque, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 12);
            //numero cheque
           $pdf->SetXY(32, 125);
           $pdf->Cell(20, 6, $cheque, 0, 0, 'L', 1);
           //nombre banco
           $pdf->SetXY(85, 125);
           $pdf->Cell(20, 6, $banco, 0, 0, 'L', 1);
           //fecha dia-mes-año
           $pdf->SetXY(150, 125);
           $pdf->Cell(20, 6, $dia_mes."-".$ano_cheque, 0, 0, 'L', 1);
           //nombre beneficiario
           $pdf->SetXY(32, 131);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 10);
           //concepto
           $pdf->SetXY(43, 138);
           $pdf->MultiCell(132, 16, $motivo.'.', 0, 'JL', 0, 0, '', '', true, 0);

            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(210,210,210);
            // Data
            $fill = 0;


            $eje_x=45;
            $eje_y=172;// comienza en la segunda linea



            // se muestra las retenciones agrupadas por codigo



            $eje_y=$eje_y+8;



            $eje_x=45;
            $eje_y=$eje_y;
            //muestra codigos ingresos
                //obtener descripcion y codigo
                $sql="SELECT pi.descripcion, CONCAT(pi.ramo,'-',pi.generica,'-',pi.especifica,'-',pi.subespecifica) as codigo FROM presupuesto.presupuesto_ingresos pi,presupuesto.fuentes_financiamiento_cuentas ffc, presupuesto.fuentes_financiamiento ff WHERE  (ffc.numero_cuenta='".$cuenta."')AND(ff.cod_fuente_financiamiento=ffc.cod_fuente_financiamiento) AND (pi.cod_presupuesto_ingreso=ff.cod_presupuesto_ingreso) AND (ff.ano='$ano')";
                $res2=cargar_data($sql,$sender);
                //codigo
                $pdf->SetXY(13, $eje_y);
                $pdf->Cell(20, 6,$res2[0]["codigo"], 0, 0, 'L', 0);
                //detalle
                $pdf->SetXY(43, $eje_y+1);
                $pdf->SetFont('helvetica','',8);
                $descripcion=$res2[0]["descripcion"];
                $descripcion=myTruncate($descripcion, 160, ' ', '.');
                $pdf->MultiCell(70, 0, ucfirst(strtolower($descripcion)), 0, 'JL', 0, 0, '', '', true, 0);
                $pdf->SetFont('helvetica','',10);
                //monto total por partida
                $pdf->SetXY(157, $eje_y);
                $pdf->Cell(20, 6, number_format($monto_total, 2, ',', '.'), 0, 0, 'R', 0);
                //fin muestra codigos ingreso
                $x=1;

            $pdf->SetFont('helvetica','',8);
            //------observaciones ------//
            //nombre
            $pdf->SetXY(13, 251);
            $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 0);
            //tipo orden pago - numero
            //$pdf->SetXY(13, 256);
            //$pdf->MultiCell(75, 256,"Pago de Retencion: ".$descripcion_retencion, 0, 'JL', 0, 0, '', '', true, 0);
			//------observaciones ------//
            $pdf->SetFont('helvetica','',10);
            //------firmas--------------//
            //preparado
            $pdf->SetXY(30, 282);
            $pdf->Cell(20, 6,strtoupper($this->txt_preparado->Text), 0, 0, 'L', 0);
            //revisado
            $pdf->SetXY(75, 282);
            $pdf->Cell(20, 6, strtoupper($this->txt_revisado->Text), 0, 0, 'L', 0);
            //aprobado
            $pdf->SetXY(120, 282);
            $pdf->Cell(20, 6, strtoupper($this->txt_aprobado->Text), 0, 0, 'L', 0);
            //auxiliar
            $pdf->SetXY(142, 285);
            $pdf->Cell(20, 6, strtoupper($this->txt_auxiliar->Text), 0, 0, 'L', 0);
            //diario
            $pdf->SetXY(164, 285);
            $pdf->Cell(20, 6,strtoupper($this->txt_diario->Text), 0, 0, 'L', 0);
            //------firmas--------------//
            $pdf->Output("detalle_cheque_".$numero."_".$ano.".pdf",'D');

        }//FIN PARA
    }
    
}

?>
