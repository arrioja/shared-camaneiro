<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página presenta un listado de las órdenes que causan al
 *              presupuesto de la organización en el año seleccionado.
 *****************************************************  FIN DE INFO
*/

class listar_causado extends TPage
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

              // para llenar el listado del Tipo de Documento
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql = "select nombre, siglas from presupuesto.tipo_documento
                    where ((cod_organizacion = '$cod_organizacion') and (operacion = 'CA'))";
              $datos = cargar_data($sql,$this);
              $this->drop_tipo->Datasource = $datos;
              $this->drop_tipo->dataBind();
          }
    }

/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
            $item->monto_total->Text = "Bs ".number_format($item->monto_total->Text, 2, ',', '.');
            $item->monto_pendiente->Text = "Bs ".number_format($item->monto_pendiente->Text, 2, ',', '.');
            $item->monto_reversos->Text = "Bs ".number_format($item->monto_reversos->Text, 2, ',', '.');
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
         if ($this->IsValid)
          {
            //Busqueda de Registros
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano->SelectedValue;
            $tipo = $this->drop_tipo->SelectedValue;
            $sql="select m.id, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos,m.estatus_actual, p.nombre
                  from presupuesto.maestro_causado m, presupuesto.proveedores p
                  where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
                         (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor))
                  order by m.numero desc, m.fecha";
            $resultado=cargar_data($sql,$sender);
            $this->DataGrid->DataSource=$resultado;
            $this->DataGrid->dataBind();
          }
	}

   public function actualiza_listado1()
	{
         $this->actualizar_listado_principal($this);
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


	public function actualiza_listado2($sender,$param)
	{
		//Busqueda de Registros
        $id2=$sender->CommandParameter;

        $sql="select ano, cod_organizacion, tipo_documento, numero from presupuesto.maestro_causado
              where (id = '$id2')";
        $resultado=cargar_data($sql,$this);

        // tomo estos datos de la consulta para asegurarme de estar tomando el
        // detalle del credito adicional correcto.
        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $numero = $resultado[0]['numero'];
        $tipo = $resultado[0]['tipo_documento'];

        $this->DataGrid2->Caption="Detalle del Causado Nro ".$tipo." (".$numero."):";
        $sql="select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto, CONCAT(tipo_documento_compromiso,' (',numero_documento_compromiso,')') as causando from presupuesto.compromiso_causado
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and
                     (numero_documento_causado ='$numero') and (tipo_documento_causado = '$tipo'))";
        $resultado=cargar_data($sql,$this);
		$this->DataGrid2->DataSource=$resultado;
		$this->DataGrid2->dataBind();
	}


public function carga_ordenes_disponibles_pagado($sender,$param)
    {
		
	
	$parametros=$sender->CommandParameter;//recibe un array
	$datos=explode(",", $parametros);
	$id=$datos[0];
        $sql="select m.motivo,m.ano, m.cod_organizacion, m.tipo_documento, m.numero, m.cod_proveedor
              from presupuesto.maestro_causado m
              where (m.id = '$id')";
        $causado=cargar_data($sql,$this);
        $tipo_doc = $causado[0]['tipo_documento'];
        $numero = $causado[0]['numero'];
 		$cod_proveedor = $causado[0]['cod_proveedor'];
		$ano = $this->drop_ano->SelectedValue;
		

             $sql=" SELECT CONCAT(mp.tipo_documento,'-',mp.numero_cheque) as datos FROM presupuesto.causado_pagado cp
                    INNER JOIN presupuesto.maestro_pagado mp on ((cp.numero_documento_pagado=mp.numero) and (cp.tipo_documento_pagado=mp.tipo_documento) and  mp.estatus_actual='NORMAL')
                    INNER JOIN presupuesto.proveedores p on (p.cod_proveedor=mp.cod_proveedor)
                    INNER JOIN presupuesto.tipo_documento td on (td.siglas=cp.tipo_documento_pagado)
                    WHERE (mp.cod_proveedor='$cod_proveedor' and cp.numero_documento_causado='$numero' and cp.tipo_documento_causado='$tipo_doc' and mp.ano='$ano' )
                    group by mp.numero ";
			  
			  /*$sql="select m.id, m.numero, m.fecha, m.monto_total, m.monto_reversos,m.estatus_actual, p.nombre
                  from presupuesto.maestro_pagado m, presupuesto.proveedores p
                  where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and
                         (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor))
                  order by m.numero desc, m.fecha";*/
        $resultado=cargar_data($sql,$this);
	 	
	     
		if (!empty ($resultado)){//si tiene ordenes causadas asociadas
		 // ciclo para modificar los acumulados
            foreach ($resultado as $datos)
            { $mensaje.=" $datos[datos]; ";}// fin
		 $this->mensaje->setErrorMessage($sender, "¡Operacion Invalida!</br>Cheque Asociado: $mensaje", 'grow');
		 
		}else{// sino se anula
		
		 $this->anular_orden($sender, $param);
		   
		}//fin si tiene ordenes causadas asociadas
		
    }
	
	
    public function anular_orden($sender, $param)
    {
   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $id=$datos[0];
   $numero=$datos[1];

//////////ojooooooooooo poner con transacciones
//////////////ojo no elimina los pagados solo actualiza los compromisos

    $cod_organizacion = usuario_actual('cod_organizacion');
    $ano = $this->drop_ano->SelectedValue;
    $tipo = $this->drop_tipo->SelectedValue;

        // modifico los acumulados correspondientes
        $sql = "select CONCAT(cc.sector,'-',cc.programa,'-',cc.subprograma,'-',cc.proyecto,'-',cc.actividad,'-',cc.partida,'-',cc.
                        generica,'-',cc.especifica,'-',cc.subespecifica,'-',cc.ordinal) as codigo, cc.monto, cc.tipo_documento_compromiso, cc.numero_documento_compromiso,mc.cod_proveedor
          from presupuesto.compromiso_causado cc inner join presupuesto.maestro_causado mc on (mc.tipo_documento=cc.tipo_documento_causado and mc.numero=cc.numero_documento_causado AND mc.ano=cc.ano)
          where cc.cod_organizacion='$cod_organizacion' AND cc.ano='$ano' and cc.numero_documento_causado='$numero' and cc.tipo_documento_causado='$tipo'
          order by codigo";
        $datos = cargar_data($sql,$this);
        if (empty($datos[0]['codigo']) == false)
        {
            // ciclo para modificar los acumulados
            foreach ($datos as $undato)
            {
                $codi= solo_numeros($undato['codigo']);
                $cod = descomponer_codigo_gasto($codi);
               
                modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'causado', '-', $undato['monto'], $this);
                
                modificar_montos_pendientes_2('CO', $cod, $cod_organizacion, $ano, $undato['tipo_documento_compromiso'], $undato['numero_documento_compromiso'], $undato['cod_proveedor'], $undato['monto'],'+', $this);
            }
        }
       //por ultimo poner status anulada la orden
       $sql="update presupuesto.maestro_causado set estatus_actual='ANULADA' where id='$id'";
       modificar_data($sql,$sender);
    
       $this->mensaje->setSuccessMessage($sender, "Orden Anulada Exitosamente!!", 'grow');
       
     // Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha anulado la orden en maestro_compromisos id=".$id;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
    $this->actualizar_listado_principal($this);
    }



    public function imprimir_listado($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');

        $tipo = $this->drop_tipo->SelectedValue;
        $ano = $this->drop_ano->Selectedvalue;

        $sql="select m.id,m.estatus_actual, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos, p.nombre
              from presupuesto.maestro_causado m, presupuesto.proveedores p
              where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
                     (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor))
              order by m.numero, m.fecha";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('../tcpdf/tcpdf.php');
            $resultado_drop = obtener_seleccion_drop($this->drop_tipo);
            $tipo_para_encabezado = $resultado_drop[2]; // se extrae el texto seleccionado

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de Causados al Presupuesto de Gastos \n".
                             "Tipo: ".$tipo_para_encabezado." Año: ".$ano.
                             "\nFecha: ".date('d/m/Y');
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
            $pdf->SetTitle('Reporte de Causados al Presupuesto');
            $pdf->SetSubject('Reporte de Órdenes que causan al presupuesto de gastos');

            $pdf->AddPage();

            $listado_header = array('Número', 'Fecha', 'Beneficiario', 'Estado','Total');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de: ".$tipo_para_encabezado.", año: ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 10);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(14, 16, 110, 20,27);
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
            $i=0;$normal=0;$anulada=0;$total=0;$total_normal=0;$total_anulada=0;
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['numero'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, cambiaf_a_normal($row['fecha']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, strtoupper($row['nombre']), $borde, 0, 'L', $fill);
                $pdf->Cell($w[3], 6, $row['estatus_actual'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['monto_total'], 2, ',', '.'), $borde, 0, 'C', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                //acumuladores
                 $total += $row['monto_total'];
                 if($row['estatus_actual']=='NORMAL'){
                    $total_normal +=$row['monto_total'];$normal++;
                 }else{
                    $total_anulada +=$row['monto_total'];$anulada++;
                 }//fin si

                //acumuladores

                $pdf->Ln();
                $fill=!$fill;
                $i++;
                 if($i==37){
                     $pdf->SetFillColor(210,210,210);
                    $pdf->SetTextColor(0);
                    $pdf->SetDrawColor(41, 22, 11);
                    $pdf->SetLineWidth(0.3);
                    $pdf->SetFont('', 'B',10);

                     for($i = 0; $i < count($listado_header); $i++)

                    $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
                    $pdf->Ln();
                     // Color and font restoration
                    $pdf->SetFillColor(224, 235, 255);
                    $pdf->SetTextColor(0);
                    $pdf->SetFont('','',8);
                    $i=-1;
                }//fin si

            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "TOTAL:", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, "Bs. ".number_format($total, 2, ',', '.'), 1, 0, 'R', 0);

            //ultima hoja de reporte
            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 6, "Resumen Total de ".$tipo_para_encabezado." año ".$ano, 0, 1, 'C', 1);
            $pdf->Ln(); $pdf->Ln();

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(100, 6,"Numero Total de Ordenes:", '', 0, 'R', '');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(30, 6,($normal+$anulada), '', 0, 'R', '');
            $pdf->Ln();$pdf->Ln();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(100, 6,"Numero de Ordenes Normales: ", '', 0, 'R', '');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(30, 6,$normal, '', 0, 'R', '');
            $pdf->Ln();$pdf->Ln();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(100, 6,"Numero de Ordenes Anuladas:", '', 0, 'R', '');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(30, 6,$anulada, '', 0, 'R', '');
            $pdf->Ln();$pdf->Ln();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(100, 6,"Monto Total de Ordenes:", '', 0, 'R', '');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(30, 6,number_format($total, 2, ',', '.'), '', 0, 'R', '');
            $pdf->Ln();$pdf->Ln();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(100, 6,"Monto Total de Ordenes Normales:", '', 0, 'R', '');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(30, 6,number_format($total_normal, 2, ',', '.'), '', 0, 'R', '');
            $pdf->Ln();$pdf->Ln();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(100, 6,"Monto Total de Ordenes Anuladas:", '', 0, 'R', '');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(30, 6,number_format($total_anulada, 2, ',', '.'), '', 0, 'R', '');

            //ultima hoja de reporte

            $pdf->Output("listado_ordenes_causantes_".$ano.".pdf",'D');
        }
    }


/* Esta función imprime el detalle de uno de los recortes al cual el usuario haya
 * presionado el botón imprimir.
 */

    public function imprimir_item($sender, $param)
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

}

?>
