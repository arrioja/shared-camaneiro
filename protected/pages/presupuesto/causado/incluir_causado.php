<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Inclusión de nuevo documento de causa de presupuesto, es
 *              requisito que exista un compromiso previamente establecido.
 *****************************************************  FIN DE INFO
*/
class incluir_causado extends TPage
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
                    where ((cod_organizacion = '$cod_organizacion') and (operacion = 'CA'))";
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
              $this->lbl_total->Text="0.00";
             

          }
    }
  public function actualiza_fecha($sender, $param)
    {
    $this->lbl_fecha->Text=$this->txt_fecha->Text;
    }

    public function carga_proveedores_con_compromisos()
    {
      // para llenar el listado de Beneficiarios
      $cod_organizacion = usuario_actual('cod_organizacion');
      $ano = ano_presupuesto($cod_organizacion,1,$this);
      $ano = $ano[0]['ano'];
      $sql2 = "select distinct p.cod_proveedor, CONCAT(p.nombre,' / ',p.rif) as nomb from presupuesto.proveedores p,
                presupuesto.maestro_compromisos c
                where ((p.cod_organizacion = '$cod_organizacion') and (c.monto_pendiente > 0) and
                   (p.cod_proveedor = c.cod_proveedor) and (c.ano = '$ano') and
                   (p.cod_organizacion = c.cod_organizacion)and (c.estatus_actual='NORMAL')) order by p.nombre, c.monto_pendiente";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_proveedor->Datasource = $datos2;
      $this->drop_proveedor->dataBind();
     
    }

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
      
      $sql2 = "select m.id, CONCAT(m.tipo_documento,'-',m.numero,' Tot: ',m.monto_total, ' / Pen: ',m.monto_pendiente) as nomb from
                presupuesto.maestro_compromisos m
                where ((m.monto_pendiente > 0) and (m.cod_organizacion = '$cod_organizacion') and
                       (m.ano = '$ano') and
                       (m.cod_proveedor = '$cod_proveedor') and (m.estatus_actual='NORMAL')) order by nomb";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_compromisos->Datasource = $datos2;
      $this->drop_compromisos->dataBind();

      // para vaciar el listado de codigos presupuestarios
      $vacio=array();
      $this->Repeater->DataSource=$vacio;
      $this->Repeater->dataBind();
      $this->txt_motivo->Text ="";// se vacia el motivo
      $this->txt_motivo->Enabled=false;
      $this->lbl_motivo->Text="";
      $this->actualiza_listado();

    }



/* Esta función carga los códigos presupuestarios asociados al compromiso
 * seleccionado del drop respectivo */
    public function carga_codigos_presupuestarios($sender, $param)
    {
        $id = $this->drop_compromisos->SelectedValue;
        $cod_proveedor = $this->drop_proveedor->SelectedValue;
        $sql="select m.motivo,m.ano, m.cod_organizacion, m.tipo_documento, m.numero
              from presupuesto.maestro_compromisos m
              where (m.id = '$id')";
        $compromiso=cargar_data($sql,$this);
        $cod_organizacion = $compromiso[0]['cod_organizacion'];
        $ano = $compromiso[0]['ano'];
        $tipo_doc = $compromiso[0]['tipo_documento'];
        $numero = $compromiso[0]['numero'];
        
        
        $this->lbl_motivo->Text=$tipo_doc. ' por concepto de: '.$compromiso[0]['motivo'];
        
        $sql = "select distinct(CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal)) as codigo, id as id2, cod_organizacion, tipo_documento, numero, ano, sum(monto_parcial) monto_parcial, sum(monto_pendiente) monto_pendiente
              from presupuesto.detalle_compromisos
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero') and
                     (tipo_documento = '$tipo_doc') and (ano = '$ano') and (cod_proveedor = '$cod_proveedor'))
              group by codigo";
        $resultado=cargar_data($sql,$this);
        $this->Repeater->DataSource=$resultado;
        $this->Repeater->dataBind();
    }

/* Esta funcion actualiza el listado de los codigos presupuestarios que se
 * muestran en el ActiveDataGrid.
 */
    function actualiza_listado()
    {
        // se actualiza el listado
        $cod_organizacion = usuario_actual('cod_organizacion');
        $numero = $this->lbl_codigo_temporal->Text;
        $sql = "select id, CONCAT(tipo_documento_compromiso,'-',numero_documento_compromiso) as compromiso, CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto from presupuesto.temporal_compromiso_causado
              where ((cod_organizacion = '$cod_organizacion') and (numero_documento_causado = '$numero'))
              order by codigo";
        $datos = cargar_data($sql,$this);
        $this->DataGrid->Datasource = $datos;
        $this->DataGrid->dataBind();
        
        // si no hay nada que listar, se deshabilita el boton incluir
        $this->btn_incluir->Enabled = !(empty($datos));

        // se actualiza la sumatoria total
        $sql = "select SUM(monto) as total from presupuesto.temporal_compromiso_causado
              where ((cod_organizacion = '$cod_organizacion') and (numero_documento_causado = '$numero'))";
        $datos = cargar_data($sql,$this);
        // para evitar que se muestre null cuando viene vacio.
        if (empty($datos[0]['total']) == false)
        { $this->lbl_total->Text=$datos[0]['total'];}
        else
        { $this->lbl_total->Text="0.00";}
       
    }

/* esa funcion añade el codigo presupuestario en la tabla temporal con el fin
 * de conservarlo ahí hasta que se incluya definitivamente en la orden.
 */
    function anadir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            /* Se incluye una por una las partidas del repeater a la tabla temporal
             * siempre y cuando su valor sea mayor de cero (0).
             */
            $primera=true;
            foreach($this->Repeater->Items as $item)
            {
                // se comprueba que el monto sea mayor que cero y menor o igual al compromiso.
                $monto_pendiente = $item->txt_monto_pendiente->Text;

                if (($monto_pendiente > 0) && ($monto_pendiente <= $item->lbl_monto->Text))
                {
                    $ano = $item->lbl_ano->Value;
                    $cod_organizacion = $item->lbl_cod_organizacion->Value;
                    $numero_documento_compromiso = $item->lbl_numero->Value;
                    $tipo_documento_compromiso = $item->lbl_tipo_documento->Value;
                    $tipo_documento_causado = $this->drop_tipo->SelectedValue;
                    $numero_documento_causado = $this->lbl_codigo_temporal->Text;
                    // se consulta el motivo
                    $id = $this->drop_compromisos->SelectedValue;
                    $sql="select m.tipo_documento, m.ano,m.numero,m.motivo,td.nombre ,td.siglas
                          from presupuesto.maestro_compromisos m INNER JOIN presupuesto.tipo_documento as td ON(m.tipo_documento=td.siglas)
                          where (m.id = '$id')";
                    $compromiso=cargar_data($sql,$this);

                    if($primera){
                        // si el text_motivo ya contiene
                         if (strlen($this->txt_motivo->Text) > "0") {
                            $this->txt_motivo->Text .= ", ".$compromiso[0]['nombre']." Nº".$compromiso[0]['numero']."-".$compromiso[0]['ano']." (".$compromiso[0]['motivo'].")";
                            $primera=false;
                        }else{
                            $this->txt_motivo->Text="Cancelación: ".$compromiso[0]['nombre']." Nº".$compromiso[0]['numero']."-".$compromiso[0]['ano']." (".$compromiso[0]['motivo'].")";
                            $primera=false;
                        }//fin si
                    }//FIN SI
                    $codigo_sin_descomponer = $item->lbl_codigo->Text;
                    $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));


                    $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                                  'tipo_documento_causado' => $tipo_documento_causado,
                                                  'numero_documento_causado' => $numero_documento_causado,
                                                  'tipo_documento_compromiso' => $tipo_documento_compromiso,
                                                  'numero_documento_compromiso' => $numero_documento_compromiso,
                                                  'sector' => $codigo['sector'],
                                                  'programa' => $codigo['programa'],
                                                  'subprograma' => $codigo['subprograma'],
                                                  'proyecto' => $codigo['proyecto'],
                                                  'actividad' => $codigo['actividad'],
                                                  'partida' => $codigo['partida'],
                                                  'generica' => $codigo['generica'],
                                                  'especifica' => $codigo['especifica'],
                                                  'subespecifica' => $codigo['subespecifica'],
                                                  'ordinal' => $codigo['ordinal'],
                                                  'cod_fuente_financiamiento' => '00');
                    $no_existe = verificar_existencia('presupuesto.temporal_compromiso_causado','ano',$ano,$criterios_adicionales,$sender);
                    if ($no_existe == true)
                        {
                            $sql = "insert into presupuesto.temporal_compromiso_causado
                                        (ano, cod_organizacion, tipo_documento_causado, numero_documento_causado, tipo_documento_compromiso,
                                         numero_documento_compromiso, sector, programa, subprograma, proyecto, actividad, partida,
                                         generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto,monto_pendiente,monto_reversos)
                                        values ('$ano','$cod_organizacion','$tipo_documento_causado','$numero_documento_causado',
                                                '$tipo_documento_compromiso','$numero_documento_compromiso',
                                                '$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                                                '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                                                '$codigo[subespecifica]','$codigo[ordinal]','00','$monto_pendiente','$monto_pendiente','0')";
                            $resultado=modificar_data($sql,$sender);
                        }
                } // del if monto > 0
            } // del for each del repeater
            $this->actualiza_listado();
            $this->txt_motivo->Enabled=true;
       }
    }

/* esta funcion elimina un codigo presupuestario del listado */
	public function eliminar($sender,$param)
	{
		$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM presupuesto.temporal_compromiso_causado WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();
	}


    function incluir_click($sender, $param)
    {
        if (($this->IsValid)&&($this->btn_incluir->Enabled)&&($this->txt_motivo->Text!=""))
        {
            $this->btn_incluir->Enabled=False;//Se deshabilita boton incluir
            $this->btn_anadir->Enabled=False;//Se deshabilita boton añadir
            // captura de datos desde los controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano=$this->lbl_ano->Text;
            $fecha=cambiaf_a_mysql($this->lbl_fecha->Text);
            $aleatorio = $this->lbl_codigo_temporal->Text;
            $tipo_documento = $this->drop_tipo->SelectedValue;
            $motivo=$this->txt_motivo->Text;

            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                         'tipo_documento' => $tipo_documento,
                                         'ano' => $ano);

            $numero=proximo_numero("presupuesto.maestro_causado","numero",$criterios_adicionales,$sender);
            $numero=rellena($numero,6,"0");
            $cod_proveedor = $this->drop_proveedor->SelectedValue;
            $monto_total=$this->lbl_total->Text;
 
            // inserción en la base de datos de la información del archivo maestro.
            $sql = "insert into presupuesto.maestro_causado
                    (cod_organizacion, ano, fecha, tipo_documento, numero, cod_proveedor, monto_total, monto_pendiente, estatus_actual,motivo)
                    values ('$cod_organizacion','$ano','$fecha','$tipo_documento','$numero','$cod_proveedor','$monto_total','$monto_total','NORMAL','$motivo')";
            $resultado=modificar_data($sql,$sender);

            // inclusión en la base de datos del detalle
            $sql = "insert into presupuesto.compromiso_causado
                    (ano, cod_organizacion, tipo_documento_causado, numero_documento_causado, tipo_documento_compromiso,
                     numero_documento_compromiso, sector, programa, subprograma, proyecto, actividad, partida,
                     generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto,monto_pendiente,monto_reversos)
                     select t.ano, t.cod_organizacion, t.tipo_documento_causado, t.numero_documento_causado, t.tipo_documento_compromiso,
                            t.numero_documento_compromiso, t.sector, t.programa, t.subprograma, t.proyecto, t.actividad, t.partida,
                            t.generica, t.especifica, t.subespecifica, t.ordinal, t.cod_fuente_financiamiento, t.monto,t.monto_pendiente,t.monto_reversos from presupuesto.temporal_compromiso_causado as t where (t.numero_documento_causado='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            // antes de eliminar los de la tabla temporal, modifico los acumulados correspondientes
            $sql = "select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo, monto, tipo_documento_compromiso, numero_documento_compromiso
              from presupuesto.temporal_compromiso_causado
              where ((cod_organizacion = '$cod_organizacion') and (numero_documento_causado = '$aleatorio'))
              order by codigo";
            $datos = cargar_data($sql,$this);
            if (empty($datos[0]['codigo']) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= solo_numeros($undato['codigo']);
                    $cod = descomponer_codigo_gasto($codi);
                    //modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'comprometido', '-', $undato['monto'], $this); por orden de yanet no se actualiza el comprometido
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'causado', '+', $undato['monto'], $this);
                    modificar_montos_pendientes('CO', $cod, $cod_organizacion, $ano, $undato['tipo_documento_compromiso'], $undato['numero_documento_compromiso'], $cod_proveedor, $undato['monto'], $this);
                }
            }

            // Se cambia el numero aleatorio por el numero que realmente que debe tener.
            $sql = "update presupuesto.compromiso_causado set numero_documento_causado='$numero' where (numero_documento_causado='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

              // Se eliminan los registros aleatorios que habia en la tabla temporal.
            $sql = "delete from presupuesto.temporal_compromiso_causado where (numero_documento_causado='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            // Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha incluido la orden: ".$tipo_documento." # ".$numero. " / año: ".$ano.
                               " a favor del proveedor Nro: ".$cod_proveedor.
                               " por un monto total de Bs. ".$monto_total." Motivo: ".$motivo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->mensaje->setSuccessMessage($sender, "OP por concepto de: ".$this->txt_motivo->Text.". Guardada exitosamente!!", 'grow');
           
            $this->lbl_numero->Text=$numero;
        }
    }



    /* Esta función imprime el detalle de uno de los recortes al cual el usuario haya
 * presionado el botón imprimir.
 */

    public function imprimir_item($sender, $param)
    {

        if(!$this->lbl_numero->Text==""){
        $numero=$this->lbl_numero->Text;
        $ano=$this->lbl_ano->Text;
        $tipo_documento_causado = $this->drop_tipo->SelectedValue;
        $sql="select mc.*, td.nombre as nombre_documento, p.rif, p.nombre
              from presupuesto.maestro_causado mc, presupuesto.proveedores p,
                   presupuesto.tipo_documento td
              where (p.cod_proveedor = mc.cod_proveedor) and
                    (p.cod_organizacion = mc.cod_organizacion) and
                    (mc.tipo_documento = td.siglas) and
                    (mc.cod_organizacion = td.cod_organizacion) and
                    (mc.numero = '$numero') and (mc.tipo_documento='$tipo_documento_causado') and (ano='$ano')";
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
            $pdf->Cell(101, 6, strtoupper($nom_beneficiario), 1, 0, 'L', 1);
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

}
?>