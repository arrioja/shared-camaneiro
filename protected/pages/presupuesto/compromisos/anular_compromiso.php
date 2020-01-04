<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Inclusión de nuevo documento de causa de presupuesto, es
 *              requisito que exista un compromiso previamente establecido.
 *****************************************************  FIN DE INFO
*/
class anular_compromiso extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_codigo_temporal->Text = aleatorio_causados($this);
              $cod_organizacion = usuario_actual('cod_organizacion');
              // para llenar el listado del Tipo de Documento
              $sql = "select nombre, siglas from presupuesto.tipo_documento
                    where ((cod_organizacion = '$cod_organizacion') and (operacion = 'CO'))";
              $datos = cargar_data($sql,$this);
              $this->drop_tipo->Datasource = $datos;
              $this->drop_tipo->dataBind();

              // para llenar el listado de Beneficiarios
              $this->carga_proveedores_con_compromisos();

              // coloca el año presupuestario "Presente"
              $ano = ano_presupuesto($cod_organizacion,1,$this);
              $this->lbl_ano->Text = $ano[0]['ano'];

              // coloca la fecha
             $this->lbl_fecha->Text = date("d/m/Y");
             // $this->lbl_total->Text="0.00";
            

          }
    }

/* Esta función carga las ordenes de compromiso segun proveedror 
 * seleccionado del drop respectivo */
    public function carga_proveedores_con_compromisos()
    {
      // para llenar el listado de Beneficiarios
      $cod_organizacion = usuario_actual('cod_organizacion');
      $ano = ano_presupuesto($cod_organizacion,1,$this);
      $ano = $ano[0]['ano'];
      $tipo= $this->drop_tipo->SelectedValue;
      $sql2 = "select distinct p.cod_proveedor, CONCAT(p.nombre,' / ',p.rif) as nomb from presupuesto.proveedores p,
                presupuesto.maestro_compromisos c
                where ((p.cod_organizacion = '$cod_organizacion') and (c.monto_pendiente > 0) and
                   (p.cod_proveedor = c.cod_proveedor) and (c.ano = '$ano') and
                   (p.cod_organizacion = c.cod_organizacion)and (c.estatus_actual='NORMAL')) and c.tipo_documento='$tipo' order by p.nombre, c.monto_pendiente";
	 $datos2 = cargar_data($sql2,$this);

      $this->drop_proveedor->Datasource = $datos2;
      $this->drop_proveedor->dataBind();
       
    }

/* Esta función carga las ordenes de compromiso asociados al proveedor
 * seleccionado del drop respectivo */
    public function carga_ordenes_disponibles()
    {
      /* Si se selecciona un proveedor diferente al que estaba seleccionado, se
       * eliminan las selecciones previas (tanto de ordenes como de codigos).*/
      $aleatorio = $this->lbl_codigo_temporal->Text;
      $sql = "delete from presupuesto.temporal_compromiso_causado where (numero_documento_causado='$aleatorio')";
      $resultado=modificar_data($sql,$this);

      // para llenar el listado de Beneficiarios
      $cod_organizacion = usuario_actual('cod_organizacion');
      $ano = $this->lbl_ano->Text;
      $cod_proveedor =  $this->drop_proveedor->SelectedValue;
	  $tipo= $this->drop_tipo->SelectedValue;

      $sql2 = "select m.id, CONCAT(m.tipo_documento,'-',m.numero,' Tot: ',m.monto_total, ' / Pen: ',m.monto_pendiente) as nomb from
                presupuesto.maestro_compromisos m
                where ( (m.cod_organizacion = '$cod_organizacion') and
                       (m.ano = '$ano') and (m.tipo_documento='$tipo')and ( m.monto_pendiente>'0')and
                       (m.cod_proveedor = '$cod_proveedor') and (m.estatus_actual='NORMAL')) order by nomb";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_compromisos->Datasource = $datos2;
      $this->drop_compromisos->dataBind();

      // para vaciar el listado de codigos presupuestarios
      $vacio=array();
      $this->Repeater->DataSource=$vacio;
      $this->Repeater->dataBind();

      //$this->actualiza_listado();
    }

/* Esta función carga las ordenes causadas asociados al compromiso
 * seleccionado del drop respectivo */
 public function carga_ordenes_disponibles_causado()
    {
      $id = $this->drop_compromisos->SelectedValue;
        $cod_proveedor = $this->drop_proveedor->SelectedValue;
        $sql="select m.motivo,m.ano, m.cod_organizacion, m.tipo_documento, m.numero
              from presupuesto.maestro_compromisos m
              where (m.id = '$id')";
        $compromiso=cargar_data($sql,$this);
        $tipo_doc = $compromiso[0]['tipo_documento'];
        $numero = $compromiso[0]['numero'];
 		$ano = $this->lbl_ano->Text;
		$cod_proveedor =  $this->drop_proveedor->SelectedValue;

              $sql="SELECT cc.id,cc.numero_documento_causado,td.nombre as np,p.nombre,cc.tipo_documento_causado,mc.cod_proveedor,mc.fecha,cc.numero_documento_compromiso,sum(cc.monto) as monto ,sum(mc.monto_total) monto_total,mc.estatus_actual,mc.id id2 FROM presupuesto.compromiso_causado cc inner join presupuesto.maestro_causado mc on ((cc.numero_documento_causado=mc.numero) and(cc.tipo_documento_causado=mc.tipo_documento))
                INNER JOIN presupuesto.proveedores p on (p.cod_proveedor=mc.cod_proveedor)
                INNER JOIN presupuesto.tipo_documento td on (td.siglas=cc.tipo_documento_causado)
 WHERE (mc.cod_proveedor='$cod_proveedor' and cc.numero_documento_compromiso='$numero' and cc.tipo_documento_compromiso='$tipo_doc' and mc.ano='$ano')
              group by mc.numero";
        $resultado=cargar_data($sql,$this);
        $this->DataGrid_Causados->DataSource=$resultado;
        $this->DataGrid_Causados->dataBind();
		
    }

/* Esta función carga los códigos presupuestarios asociados al compromiso
 * seleccionado del drop respectivo */
    public function carga_codigos_presupuestarios($sender, $param)
    {
        $id = $this->drop_compromisos->SelectedValue;
        $cod_proveedor = $this->drop_proveedor->SelectedValue;
        $sql="select m.motivo,m.ano, m.cod_organizacion, m.tipo_documento, m.numero,m.monto_total as monto_comprometido
              from presupuesto.maestro_compromisos m
              where (m.id = '$id') ";
        $compromiso=cargar_data($sql,$this);
        $cod_organizacion = $compromiso[0]['cod_organizacion'];
        $ano = $compromiso[0]['ano'];
        $tipo_doc = $compromiso[0]['tipo_documento'];
        $numero = $compromiso[0]['numero'];
        $monto_comprometido=$compromiso[0]['monto_comprometido'];

        $this->lbl_motivo->Text=$tipo_doc. ' por concepto de: '.$compromiso[0]['motivo'];

        $sql = "select distinct(CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal)) as codigo, id as id2, cod_organizacion, tipo_documento, numero, ano, sum(monto_parcial) monto_parcial, sum(monto_pendiente) monto_pendiente,(SUM(monto_parcial)-SUM(monto_pendiente)) as monto_causado
              from presupuesto.detalle_compromisos
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero') and
                     (tipo_documento = '$tipo_doc') and (ano = '$ano') and (cod_proveedor = '$cod_proveedor'))
              group by codigo";
        $resultado=cargar_data($sql,$this);
        $this->Repeater->DataSource=$resultado;
        $this->Repeater->dataBind();
 // ciclo para obtener monto a reversar(monto no causado)
            foreach ($resultado as $datos)
            {
                $codi= solo_numeros($datos['codigo']);
                $codigo = descomponer_codigo_gasto($codi);
                // obtengo  causado por codigo
                $sql =" select  SUM(cc.monto) as monto_causado from
                           presupuesto.compromiso_causado as cc inner join presupuesto.maestro_causado as mc on(mc.numero=cc.numero_documento_causado and mc.tipo_documento=cc.tipo_documento_causado and mc.estatus_actual='NORMAL' AND mc.ano = cc.ano)
                        WHERE   cc.sector='$codigo[sector]' and cc.programa='$codigo[programa]' and cc.subprograma='$codigo[subprograma]' and cc.proyecto='$codigo[proyecto]' and cc.actividad='$codigo[actividad]' and cc.partida='$codigo[partida]' and cc.generica='$codigo[generica]' and cc.especifica='$codigo[especifica]' and cc.subespecifica='$codigo[subespecifica]' and cc.ordinal='$codigo[ordinal]' and cc.cod_organizacion='$cod_organizacion' and cc.numero_documento_compromiso='$numero' and cc.tipo_documento_compromiso='$tipo_doc' and cc.ano='$ano' ";
                $causado = cargar_data($sql,$this);
                $monto_causado+=($causado[0]['monto_causado']);
             // obtengo reverso por codigo
             $sql = "select  sum(monto_reversos) as monto_reverso
			  from presupuesto.detalle_compromisos
			  WHERE   sector='$codigo[sector]' and programa='$codigo[programa]' and subprograma='$codigo[subprograma]' and proyecto='$codigo[proyecto]' and actividad='$codigo[actividad]' and partida='$codigo[partida]' and generica='$codigo[generica]' and especifica='$codigo[especifica]' and subespecifica='$codigo[subespecifica]' and ordinal='$codigo[ordinal]' and cod_organizacion='$cod_organizacion' and numero='$numero' and tipo_documento='$tipo_doc' and ano='$ano'";
        	$reverso = cargar_data($sql,$this);
             $monto_reversos+=($reverso[0]['monto_reverso']);
            }



		if(($monto_comprometido  - $monto_causado)>0)
		 $this->btn_anular_orden->Enabled=True;
		else
		 $this->btn_anular_orden->Enabled=False;
         
    }
/* Esta función anula parcialmente una orden de compromiso
 * seleccionado del drop respectivo */
 public function anular_orden($sender, $param)
    {
      if (($this->IsValid)&&( $this->btn_anular_orden->Enabled)){

         
        $id = $this->drop_compromisos->SelectedValue;
        $cod_proveedor = $this->drop_proveedor->SelectedValue;
        $sql="select m.motivo,m.ano, m.cod_organizacion, m.tipo_documento, m.numero,m.monto_total as monto_comprometido
              from presupuesto.maestro_compromisos m
              where (m.id = '$id')";
        $compromiso=cargar_data($sql,$this);
        $cod_organizacion = $compromiso[0]['cod_organizacion'];
        $ano = $compromiso[0]['ano'];
        $tipo = $compromiso[0]['tipo_documento'];
        $numero = $compromiso[0]['numero'];
        $motivo= $this->txt_motivo->Text;
        $error=false;

        if ($tipo=="PC"){
//-------------------------Anular parcialmente un compromiso-----------------------------------------------------

// se consulta los codigos imputados en el compromiso
            $sql =  " select CONCAT(dc.sector,'-',dc.programa,'-',dc.subprograma,'-',dc.proyecto,'-',dc.actividad,'-',dc.partida,'-',dc.
                        generica,'-',dc.especifica,'-',dc.subespecifica,'-',dc.ordinal) as codigo, dc.monto_parcial as monto_comprometido, 
dc.numero,dc.cod_proveedor
          from presupuesto.detalle_compromisos dc inner join presupuesto.maestro_compromisos mc on 
(mc.tipo_documento=dc.tipo_documento and mc.numero=dc.numero and mc.ano=dc.ano)
          where dc.cod_organizacion='$cod_organizacion' and dc.numero='$numero' and dc.tipo_documento='$tipo' and dc.ano='$ano'
          order by codigo
		  ";
    
        $datos = cargar_data($sql,$this);
        if (empty($datos[0]['codigo']) == false)
        {

         // ciclo para modificar los acumulados
            foreach ($datos as $undato)
            {
				$codi= solo_numeros($undato['codigo']);
                $codigo = descomponer_codigo_gasto($codi);
				// se obtiene el monto total causado segun codigo comprometido
				/*$sql = "select Sum(monto) as monto_causado
                  from presupuesto.compromiso_causado
                  WHERE   sector='$codigo[sector]' and programa='$codigo[programa]' and subprograma='$codigo[subprograma]' and proyecto='$codigo[proyecto]' and actividad='$codigo[actividad]' and partida='$codigo[partida]' and generica='$codigo[generica]' and especifica='$codigo[especifica]' and subespecifica='$codigo[subespecifica]' and ordinal='$codigo[ordinal]' and cod_organizacion='$cod_organizacion' and numero_documento_compromiso='$numero' and tipo_documento_compromiso='$tipo' and ano='$ano'";
                $causado = cargar_data($sql,$this);
               */



                foreach($this->Repeater->Items as $item)
            {
                if ($undato['codigo']==$item->lbl_codigo_imputado->Value)
                {
                   
                    if(($item->txt_monto_anular->Text) <= ($item->lbl_monto->Text) )
                    $monto_a_anular = $item->txt_monto_anular->Text;
                    else $error=true;
                }
            }




            if(!$error){
                  
                // se resta el compromiso - monto sin causar
                modificar_acumulados_presupuesto_gasto($codigo, $cod_organizacion, $ano, 'comprometido', '-', $monto_a_anular, $this);
                // se devuelve disponible monto sin causar
                modificar_acumulados_presupuesto_gasto($codigo, $cod_organizacion, $ano, 'disponible', '+',$monto_a_anular, $this);
                // se asigna el monto reversado al campo reverso detalle_compromisos
                $sql="update presupuesto.detalle_compromisos set monto_pendiente=monto_pendiente-'".$monto_a_anular."',monto_reversos='".$monto_a_anular."'
                     WHERE sector='$codigo[sector]' and programa='$codigo[programa]' and subprograma='$codigo[subprograma]' and proyecto='$codigo[proyecto]' and actividad='$codigo[actividad]' and partida='$codigo[partida]' and generica='$codigo[generica]' and especifica='$codigo[especifica]' and subespecifica='$codigo[subespecifica]' and ordinal='$codigo[ordinal]' and cod_organizacion='$cod_organizacion' and cod_organizacion='$cod_organizacion' and numero='$numero' and tipo_documento='$tipo' and ano='$ano'";
               modificar_data($sql,$sender);
               // se actualiza articulos_compromisos
                $sql="update presupuesto.articulos_compromisos set monto_pendiente=monto_pendiente-'".$monto_a_anular."',monto_reversos='".$monto_a_anular."'
                     WHERE sector='$codigo[sector]' and programa='$codigo[programa]' and subprograma='$codigo[subprograma]' and proyecto='$codigo[proyecto]' and actividad='$codigo[actividad]' and partida='$codigo[partida]' and generica='$codigo[generica]' and especifica='$codigo[especifica]' and subespecifica='$codigo[subespecifica]' and ordinal='$codigo[ordinal]' and cod_organizacion='$cod_organizacion' and cod_organizacion='$cod_organizacion' and numero='$numero' and tipo_documento='$tipo' and ano='$ano'";
               modificar_data($sql,$sender);
               
             $monto_comprometido+=$undato['monto_comprometido'];
             $monto_a_anular_total+=$monto_a_anular;
            }//fin si validacion

            }

        }
        if(!$error){
            $this->btn_anular_orden->Enabled=False;
               //por ultimo se asigna el monto reversado al campo reverso
                $sql="update presupuesto.maestro_compromisos set monto_reversos='".$monto_a_anular_total."' ,monto_pendiente=monto_pendiente-'".$monto_a_anular_total."',observaciones='Parcialmente Anulada por concepto: $motivo' where id='$id' ";
			    modificar_data($sql,$sender);
       $this->lbl_numero->Text=$id;
      
      $this->mensaje->setSuccessMessage($sender, "Orden Anulada Parcialmente!!", 'grow');
     // Se incluye el rastro en el archivo de bitácora
       $descripcion_log = "Se ha anulado parcialmente la orden de Compromiso $tipo-$numero Monto Bs.$monto_a_anular_total ";
       inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
      }else{
       $this->mensaje->setErrorMessage($sender, "¡Monto de Anulacion Debe ser menor al Pendiente!", 'grow');

        }//fin si
     }

     }else{
            $this->mensaje->setErrorMessage($sender, "Anulacion Parcial, solo aplica a Orden de Compromiso(PC)", 'grow');

 }//fin si tipo
//-------------------------Anular parcialmente un compromiso-----------------------------------------------------

    }


     public function imprimir_item($sender, $param)
    {
        if(!$this->lbl_numero->Text==""){
        $id=$this->lbl_numero->Text;
        $sql="select mc.*, td.nombre as nombre_documento, p.rif, p.nombre
              from presupuesto.maestro_compromisos mc, presupuesto.proveedores p,
                   presupuesto.tipo_documento td
              where (p.cod_proveedor = mc.cod_proveedor) and
                    (p.cod_organizacion = mc.cod_organizacion) and
                    (mc.tipo_documento = td.siglas) and
                    (mc.cod_organizacion = td.cod_organizacion) and
                    (mc.id = '$id')";

        $resultado=cargar_data($sql,$this);

        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $numero = $resultado[0]['numero'];
        $tipo = $resultado[0]['tipo_documento'];
        $tipo_nombre = $resultado[0]['nombre_documento'];
        $motivo = $resultado[0]['motivo'];
        $observacion = $resultado[0]['observaciones'];
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
            require('../tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de ".$tipo_nombre." Anulada Parcialmente.\n".
                             "Número: ".$numero.", Año: ".$ano;
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);

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
            $pdf->Cell(52, 5, "Número del Documento:", 1, 0, 'L', 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(75, 5, $numero."-".$ano, 1, 0, 'L', 1);
            $pdf->Cell(25, 5, "Fecha:", 1, 0, 'L', 1);
            $pdf->Cell(36, 5, $fecha, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(188, 4, 'PROVEEDOR', 1, 0, 'C', 1);
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
            $pdf->Cell(103, 6, $nom_beneficiario, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFont('', 'B');
            $pdf->MultiCell(40, 12, "Motivo:", 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->SetFont('', '',7);
            $pdf->MultiCell(148, 12, $motivo, 1, 'JL', 0, 1, '', '', true, 0);
            $pdf->SetFont('', 'B');
            $pdf->MultiCell(40, 12, "Observacion:", 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->SetFont('', '',7);
            $pdf->MultiCell(148, 12, $observacion, 1, 'JL', 0, 1, '', '', true, 0);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(210,210,210);

            $pdf->Cell(188, 6, "Detalle de la ".$tipo_nombre, 1, 1, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->SetFont('helvetica', '', 8);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B',8);
            // Header del listado
            $listado_header = array('Código Presupuestario','Cantidad','Unidad','Denominación','Monto','Monto Total','Monto Anulado');
            $w = array(31,13,18,55,23,24,24);
            for($i = 0; $i < count($listado_header); $i++)
            {
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);

            }
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
                $pdf->Cell($w[0], 4, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 4, $row['cantidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 4, $row['unidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 4, $row['descripcion'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[4], 4, "Bs. ".number_format($row['precio_unitario'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[5], 4, "Bs. ".number_format($row['monto_parcial'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[6], 4, "Bs. ".number_format($row['monto_reversos'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $monto_total_reversos+=$row['monto_reversos'];
                $pdf->Ln();
                $fill=!$fill;
                $lineas++;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            for($i=1;$i<22-$lineas;$i++)//PARA AJUSTAR LAS LINEAS DEL FOOTER
                {
                $pdf->Cell($w[0], 4,'',1,0, 'R', 0);
                $pdf->Cell($w[1], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[2], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[3], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[4], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[5], 4,'',1, 0, 'R', 0);
                $pdf->Cell($w[6], 4,'',1, 0, 'R', 0);
                $pdf->Ln();
                }
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[5], 6, number_format($monto_total, 2, ',', '.'), 1, 0, 'R', 0);
            $pdf->Cell($w[6], 6, number_format($monto_total_reversos, 2, ',', '.'), 1, 0, 'R', 0);

            $pdf->Ln();
            $pdf->Ln();
            //firmas
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(188, 3, 'FIRMAS', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('','',8);
            $pdf->MultiCell(62, 3, 'Control Presupuestario', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Administrador', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(64, 3, 'Presidente', 1, 'C', 1, 0, '', '', true);
            $pdf->Ln();
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(64, 15, '', 1, 0, 'C', 1);
            $pdf->Ln();

            //pROVEEDOR
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(188, 3, 'PROVEEDOR', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFont('','',4);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->MultiCell(124,15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 'C', 1, 0, '', '', true);
            //$pdf->Cell(124, 15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 0, 'J', 1);
            $pdf->SetFont('','',8);
            $pdf->Cell(31, 15, 'Firma', 1, 0, 'l', 1);
            $pdf->Cell(33, 15, 'Fecha', 1, 0, 'l', 1);
            $pdf->Ln();
//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false) {
            //RECIBO CONFORME UNIDAD SOLICITANTE
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(188, 3, 'RECIBO CONFORME UNIDAD SOLICITANTE', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(46, 10, 'Nombre y Apellido', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Firma', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Cédula de Identidad', 1, 0, 'L', 1,'',0,true);
            $pdf->Cell(50, 10, 'Fecha', 1, 1, 'L', 1);

            $pdf->Output("detalle_".$tipo_nombre."_".$numero."_".$ano.".pdf",'D');
        }
      }
    }
    
}
?>