<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Inclusión de nuevo cheque de retencion
 *****************************************************  FIN DE INFO
*/
class incluir_cheque_retencion extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {   
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_codigo_temporal->Text = aleatorio_retenciones($this);
              $cod_organizacion = usuario_actual('cod_organizacion');
             
              // para llenar el listado de Beneficiarios
              $this->carga_proveedores();

              // coloca el año presupuestario "Presente"
              $ano_array = ano_presupuesto($cod_organizacion,1,$this);
              $this->lbl_ano->Text = $ano_array[0]['ano'];
              $ano=$ano_array[0]['ano'];

              // coloca la fecha
              $this->lbl_fecha->Text = date("d/m/Y");
              //$this->lbl_total->Text="0.00";

        $sql2 = "select codigo,descripcion from presupuesto.retencion
              where ((cod_organizacion = '$cod_organizacion') and(ano = '$ano'))
              order by codigo";
        $resultado2=cargar_data($sql2,$this);
        $this->drop_retenciones->DataSource=$resultado2;
        $this->drop_retenciones->dataBind();

         //meses
             $arreglo=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
             $this->drop_mes->DataSource=$arreglo;
             $this->drop_mes->dataBind();
             
        //$this->txt_motivo->Enabled="False";
          // coloca la fecha
          $this->lbl_fecha->Text = date("d/m/Y");

          $this->txt_fecha_cheque->Text = date("d/m/Y");

          $sql="select * from presupuesto.bancos where cod_organizacion='$cod_organizacion'";
          $res_banco=cargar_data($sql,$this);
          $this->drop_banco->DataSource=$res_banco;
          $this->drop_banco->dataBind();

              //------firmas--------------//
           $this->txt_preparado->Text="EP";
           $this->txt_revisado->Text="MN";
           $this->txt_aprobado->Text="JFS";
           $this->txt_auxiliar->Text="IR";
           $this->txt_diario->Text="";
            //------firmas--------------//
          }
    }


    public function carga_proveedores()
    {
      // para llenar el listado de Beneficiarios
      $cod_organizacion = usuario_actual('cod_organizacion');
      $ano = ano_presupuesto($cod_organizacion,1,$this);
      $ano = $ano[0]['ano'];
      $sql2 = "select distinct p.cod_proveedor, CONCAT(p.nombre,' / ',p.rif) as nomb from presupuesto.proveedores p
             
                where p.cod_organizacion = '$cod_organizacion'   order by p.nombre";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_proveedor->Datasource = $datos2;
      $this->drop_proveedor->dataBind();
    }

   public function cargar_cuentas()
    {
      $cod_org = usuario_actual('cod_organizacion');
      $cod_banco=$this->drop_banco->SelectedValue;
      $sql2 = "select * from presupuesto.bancos_cuentas where cod_organizacion='$cod_org' and cod_banco='$cod_banco' ";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_cuentas->Datasource = $datos2;
      $this->drop_cuentas->dataBind();
    }


/* Esta función carga las retenciones segun tipo y del mes seleecionado
 * seleccionado del drop respectivo */
    public function carga_retenciones($sender, $param)
    {
       //$this->lbl_total->Text=0;
       $cod_proveedor=$this->drop_proveedor->SelectedValue;
        $cod_organizacion = usuario_actual('cod_organizacion');
       $ano = $this->lbl_ano->Text;
       $mes = $this->drop_mes->SelectedValue;
       $fecha_inicio="$ano/$mes/01";
       $fecha_fin="$ano/$mes/31";
       $codigo_sin_descomponer = $this->drop_retenciones->SelectedValue;
       $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));

     //consulto retenciones realizadas a ordenes de pago segun retencion y mes
       $sql = "select m.fecha_cheque,dp.id,dp.monto,CONCAT(dp.tipo_documento_causado,'-',dp.numero_documento_causado) as orden,CONCAT(p.nombre,' / ',p.rif) as proveedor FROM presupuesto.detalle_pagado as dp
            INNER JOIN presupuesto.maestro_pagado as m ON (m.numero=dp.numero_documento_pagado AND m.ano=dp.ano)
            INNER JOIN presupuesto.proveedores as p ON (p.cod_proveedor = '$cod_proveedor')
            where ((dp.es_retencion='1') and (m.cod_organizacion = '$cod_organizacion') and
                       (dp.ano = '$ano') and
                       (dp.partida='$codigo[partida]' and dp.generica='$codigo[generica]' and especifica='$codigo[especifica]'
            and  dp.subespecifica='$codigo[subespecifica]' and dp.ordinal='$codigo[ordinal]')and (m.estatus_actual='NORMAL')
        and m.fecha_cheque  BETWEEN '$fecha_inicio' AND '$fecha_fin' ) order by m.fecha_cheque ASC";
        $datos = cargar_data($sql,$this);
        $resultado = cargar_data($sql,$this);


       //consulto retenciones realizadas a ordenes de pago segun retencion en el año
       $fecha_inicio="$ano/01/01";
       $fecha_fin="$ano/12/31";
       $sql = "select SUM(dp.monto) as monto FROM presupuesto.detalle_pagado as dp
            INNER JOIN presupuesto.maestro_pagado as m ON (m.numero=dp.numero_documento_pagado AND m.ano=dp.ano)
            where ((dp.es_retencion='1') and (m.cod_organizacion = '$cod_organizacion') and (dp.ano = '$ano') and
                  (dp.partida='$codigo[partida]' and dp.generica='$codigo[generica]' and especifica='$codigo[especifica]'
            and  dp.subespecifica='$codigo[subespecifica]' and dp.ordinal='$codigo[ordinal]')and (m.estatus_actual='NORMAL')
        and m.fecha_cheque  BETWEEN '$fecha_inicio' AND '$fecha_fin' )";
        $datos = cargar_data($sql,$this);
        $resultado_acum_ano = cargar_data($sql,$this);


        //consulto retenciones realizadas a ordenes de pago segun retencion en el año
       $fecha_inicio="$ano/01/01";
       $fecha_fin="$ano/12/31";
       $sql = " SELECT SUM(rp.monto) as monto FROM presupuesto.retencion_pagado as rp
                INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=rp.cod_movimiento AND c.ano=rp.ano AND c.estatus_actual='NORMAL')
                INNER JOIN presupuesto.bancos_cuentas_movimientos as m ON (m.cod_movimiento=rp.cod_movimiento AND m.ano=rp.ano)
                WHERE (rp.codigo_retencion='$codigo_sin_descomponer' AND m.fecha  BETWEEN '$fecha_inicio' AND '$fecha_fin' ) ";
        $datos = cargar_data($sql,$this);
        $resultado_acum_ano_pagado = cargar_data($sql,$this);
       
        //consulto el acumulado asignado a la retencion al abrir presupuesto año actual
        //y que deberia estar sumado las retenciones hasta la fecha del presente año
         $sql_acum = "select asignado
              from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (es_retencion ='1')and(ano = '$ano')
            and((programa='$codigo[programa]' AND subprograma='$codigo[subprograma]' AND proyecto='$codigo[proyecto]' AND actividad='$codigo[actividad]' AND partida='$codigo[partida]' and generica='$codigo[generica]' and especifica='$codigo[especifica]'
            and  subespecifica='$codigo[subespecifica]' and ordinal='$codigo[ordinal]')) ) 
             ";
        $resultado_inicial=cargar_data($sql_acum,$this);
       
        // se suma retenciones realizadas a ordenes de pago segun mes pendiente por cancelar
        if(!empty($resultado)) foreach($resultado as $item)$acumulado_mes+=abs($item['monto']);
        $acumulado_total_ano=abs($resultado2[0]['monto']);
        $pagado_total_ano=$resultado3[0]['pagado'];
        
        //$this->mensaje->setErrorMessage($sender, " Total Retenido Año ".$ano." Bs. ".$acumulado_total_ano." Total Pagado Año ".$ano." Bs. ".$pagado_total_ano." Retenciones del mes Bs.".$acumulado_mes, 'grow');

        // se muestra total retencion del mes
        if(!empty($resultado))$this->lbl_total_mes->Text= $acumulado_mes;
        else $this->lbl_total_mes->Text= "0.00";

        
        if(empty($resultado_inicial)){
            //$this->lbl_total_acumulado_retencion->Text= $resultado_inicial[0][asignado]."+".abs($resultado_acum_ano[0][monto])."-".$resultado_acum_ano_pagado[0][monto];
           $this->lbl_total_acumulado_retencion->Text= $resultado_inicial[0]['asignado']+abs($resultado_acum_ano[0]['monto'])-$resultado_acum_ano_pagado[0]['monto'];
           $this->lbl_total_acumulado_retencion->Text= number_format( $this->lbl_total_acumulado_retencion->Text, 2, '.', '');
           $this->txt_total->Text=$resultado_inicial[0]['asignado']+abs($resultado_acum_ano[0]['monto'])-$resultado_acum_ano_pagado[0]['monto'];
           $this->txt_total->Text= number_format( $this->txt_total->Text, 2, '.', '');
        } else {
            $this->lbl_total_acumulado_retencion->Text= "0.00";
        }//fin si
        
    
        
    }

/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
            $item->monto->Text = "Bs ".number_format(abs($item->monto->Text), 2, ',', '.');
          
        }
    }



/* esa funcion añade el codigo presupuestario en la tabla temporal con el fin
 * de conservarlo ahí hasta que se incluya definitivamente en cheque.
 */
    function anadir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            /* Se incluye  el codigo a la tabla temporal
             * siempre y cuando su valor sea mayor de cero (0).
             */
            $primera=true;
            $monto_total=$this->txt_total->Text;

           
          //  $monto_total = number_format($this->txt_total->Text, 3 ,'.', '');
          //  $monto_total_acumulado=number_format($this->txt_total->Text, 3 ,'.', '');

           
            if(($monto_total =="0.00")||($monto_total =="")||($monto_total < 0)||($this->txt_total->Text > $this->lbl_total_acumulado_retencion->Text))
            {
               $error=true;
               $this->mensaje->setErrorMessage($sender, "Operacion Invalida, Revise Monto a Pagar Bs. ".$this->txt_total->Text, 'grow');
            }else{
                $codigo_sin_descomponer = $this->drop_retenciones->SelectedValue;
                $ano = $this->lbl_ano->Text;
                $cod_organizacion = usuario_actual('cod_organizacion');
                $numero= $this->lbl_codigo_temporal->Text;
                $nombre= $this->drop_retenciones->Text;
                $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));


                $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                                  'numero' => $numero,
                                                  'sector' => $codigo['sector'],
                                                  'programa' => $codigo['programa'],
                                                  'subprograma' => $codigo['subprograma'],
                                                  'proyecto' => $codigo['proyecto'],
                                                  'actividad' => $codigo['actividad'],
                                                  'partida' => $codigo['partida'],
                                                  'generica' => $codigo['generica'],
                                                  'especifica' => $codigo['especifica'],
                                                  'subespecifica' => $codigo['subespecifica'],
                                                  'ordinal' => $codigo['ordinal']);
                    $no_existe = verificar_existencia('presupuesto.temporal_retencion','ano',$ano,$criterios_adicionales,$sender);
                    if ($no_existe == true)
                        {
                            //obtenemos nombre de la retencion
                            $sql = "select descripcion from presupuesto.retencion
                                        where ((cod_organizacion = '$cod_organizacion') and (codigo ='$codigo_sin_descomponer')and(ano = '$ano')
                                         )";
                            $resultado_desc = cargar_data($sql,$this);

                            $sql = "insert into presupuesto.temporal_retencion
                                        (ano, cod_organizacion, numero,nombre, sector, programa, subprograma, proyecto, actividad, partida,
                                         generica, especifica, subespecifica, ordinal, monto)
                                        values ('$ano','$cod_organizacion','$numero','".$resultado_desc[0][descripcion]."',
                                                '$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                                                '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                                                '$codigo[subespecifica]','$codigo[ordinal]','$monto_total')";
                            $resultado=modificar_data($sql,$sender);
                        }//fin si no existe
                        
                         /* if($primera){
                        // si el text_motivo ya contiene
                         if (strlen($this->txt_motivo->Text) > "0") {
                            $this->txt_motivo->Text .= ", ".$compromiso[0]['nombre']." Nº".$compromiso[0]['numero'];
                            $primera=false;
                        }else{
                            $this->txt_motivo->Text="Cancelación: ".$compromiso[0]['nombre']." Nº".$compromiso[0]['numero'];
                            $primera=false;
                        }//fin si
                    }//FIN SI*/
                
            }//fin si
           
            $this->actualiza_listado();
            $this->txt_motivo->Enabled=true;
       }
    }


/* esta funcion elimina un codigo presupuestario del listado */
	public function eliminar($sender,$param)
	{
		$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM presupuesto.temporal_retencion WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();
	}

/* Esta funcion actualiza el listado de los codigos presupuestarios que se
 * muestran en el ActiveDataGrid.
 */
    function actualiza_listado()
    {
   

       // se actualiza el listado
        $cod_organizacion = usuario_actual('cod_organizacion');
        $numero = $this->lbl_codigo_temporal->Text;
        $sql = "select id,nombre, CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,monto
                from presupuesto.temporal_retencion
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))
              order by codigo ";
        $datos = cargar_data($sql,$this);
        $this->DataGrid->Datasource = $datos;
        $this->DataGrid->dataBind();

        // si no hay nada que listar, se deshabilita el boton incluir
        $this->btn_incluir->Enabled = !(empty($datos));

        // se actualiza la sumatoria total
        $sql = "select SUM(monto) as total from presupuesto.temporal_retencion
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))";
        $datos = cargar_data($sql,$this);
        // para evitar que se muestre null cuando viene vacio.
        if (empty($datos[0]['total']) == false)
        { $this->lbl_totalpr->Text=$datos[0]['total'];}
        else
        { $this->lbl_totalpr->Text="0.00";}

    }


    function incluir_click($sender, $param)
    {
          //if (($this->IsValid)&&( $this->btn_incluir->Enabled))
          if ($this->btn_incluir->Enabled)
        {
            $num_cheque=$this->txt_numero_cheque->Text;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $this->lbl_ano->Text);
            $no_existe_movimientos = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$num_cheque,$criterios_adicionales,$sender);
           
            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $this->lbl_ano->Text,'tipo'=>'R');
            $existe_reverso = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$num_cheque,$criterios_adicionales,$sender);
           
            $banco=$this->drop_banco->SelectedValue;
            $cuenta=$this->drop_cuentas->SelectedValue;
            $ano=$this->lbl_ano->Text;
            $numero = $this->lbl_codigo_temporal->Text;

            $error=false;

                  // se suma total retenciones
                $sql = "select SUM(monto) as total from presupuesto.temporal_retencion
                      where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))";
                $datos = cargar_data($sql,$this);
                $monto_total=$datos[0]['total'];

            /*  if ((empty($monto_total) == false)or($this->txt_total->Text > $this->lbl_total_acumulado_retencion->Text))
            {
               $error=true;
               $this->mensaje->setErrorMessage($sender, "Operacion Invalida, Revise Monto! :".$this->txt_total->Text, 'grow');
             }//fin si*/

             
        if($error==false){
                    $saldo_actual=disponibilidad_bancaria($ano,$cod_organizacion,$banco,$cuenta,$sender);
                 if($saldo_actual>$monto_total)//se verifica disponibilidad en cuenta
                 {

                        if ($no_existe_movimientos==true)// sino existe el numero cheque
                         {

                            $this->btn_incluir->Enabled=false;
                            $cod_proveedor = $this->drop_proveedor->SelectedValue;
                            $fecha=cambiaf_a_mysql($this->lbl_fecha->Text);
                            $num_cheque=$this->txt_numero_cheque->Text;
                            $fecha_cheque=cambiaf_a_mysql($this->txt_fecha_cheque->Text);
                            $motivo=$this->txt_motivo->Text;
                            
                            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $ano);
                            $numero_movimiento=proximo_numero("presupuesto.bancos_cuentas_movimientos","cod_movimiento",$criterios_adicionales,$sender);
                            $cod_movimiento=rellena($numero_movimiento,10,"0");

                             /* se incluyen retenciones*/
                        
                            $sql = "select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                                                generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,monto
                                    from presupuesto.temporal_retencion
                                  where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))
                                  order by codigo ";
                            $datos = cargar_data($sql,$this);
                            if (empty($datos[0]['codigo']) == false)
                                        {
                                            // ciclo para modificar los acumulados
                                            foreach ($datos as $undato)
                                            {

                                                // captura de datos desde la temporal_retencion
                                                $codigo_sin_descomponer = $undato['codigo'];    

                                                 // se modifica el acumulado en tabla presupuesto_gastos
                                                 $cod = descomponer_codigo_gasto($codigo_sin_descomponer);
                                                 modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '-',  $undato['monto'], $this);
                                                 modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '+',  $undato['monto'], $this);
                                                //se registra la retencion
                                                registrar_retencion($ano,$cod_movimiento,$codigo_sin_descomponer,$undato['monto'],$sender);

                                            }
                             }//fin si
                               
                                                   
                             /* se incluyen retenciones*/

                                //se registra el movimiento en las cuenta bancaria seleccionada
                                registrar_movimiento($ano,$cod_organizacion,$banco,$cuenta,$monto_total,'haber',$motivo,$num_cheque,$this,'CH',$fecha_cheque,$cod_movimiento);
                                //se registra el cheque
                                registrar_cheque($ano,$cod_movimiento,'RETENCION',$cod_proveedor,$sender);
                                
                                // Se incluye el rastro en el archivo de bitácora
                                $descripcion_log = "Se ha incluido el cheque de retencion:  # ".$num_cheque. " / año: ".$ano.
                                                   " a favor del proveedor Nro: ".$cod_proveedor.
                                                   " por un monto total de Bs. ".$monto_total." Motivo: ".$motivo;
                                inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
                                $this->mensaje->setSuccessMessage($sender, "Cheque Nº $num_cheque guardado exitosamente!!", 'grow');

                                // Se eliminan los registros aleatorios que habia en la tabla temporal.
                                $sql = "delete from presupuesto.temporal_retencion where (numero='$numero')";
                                $resultado=modificar_data($sql,$sender);

                        }else{

                            if($error==false)$this->mensaje->setErrorMessage($sender, "Operacion Invalida, Cheque $num_cheque ya registrado!", 'grow');

                         }// fin si existe
                 }else{
                    $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente en cuenta $cuenta !</br> Saldo actual: Bs.".number_format($saldo_actual, 2, ',', '.'), 'grow');
                    // $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente en cuenta $cuenta $saldo_actual", 'grow');


                }//fin si
            }//fin si error false
        }//fin si 
    }


/* Esta función imprime el detalle sobre el cheque y sobre el comprobante una vez
 * que se haya guardado el cheque.
 */

    public function imprimir_item($sender, $param)
    {


       $cheque=$this->txt_numero_cheque->Text;
       $cod_proveedor=$this->drop_proveedor->SelectedValue;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $ano = $this->lbl_ano->Text;
       $codigo_sin_descomponer = $this->drop_retenciones->SelectedValue;
       $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));
       $cuenta=$this->drop_cuentas->SelectedValue;
         //consulto pagos realizados en el año
       $sql = "SELECT m.*,b.nombre as nombre_banco, p.nombre, p.rif FROM presupuesto.bancos_cuentas_movimientos as m
            INNER JOIN presupuesto.bancos as b ON ( b.cod_banco=m.cod_banco)
             JOIN presupuesto.proveedores as p ON (p.cod_proveedor='$cod_proveedor')
            WHERE (  m.referencia='$cheque'
                    AND m.numero_cuenta='$cuenta' AND m.ano='$ano' AND m.tipo='CH')";
        $resultado = cargar_data($sql,$this);
        
        $motivo = $resultado[0]['descripcion'];
        $monto_total = $resultado[0]['haber'];
        $fecha=cambiaf_a_mysql($resultado[0]['fecha']);
        $fecha_cheque=cambiaf_a_normal($resultado[0]['fecha']);
        $fecha=split('[/.-]', $fecha_cheque);
        $dia_mes = $fecha[0]."-".$fecha[1];//dia mes actual
        $ano_cheque = $fecha[2];//año actual
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];
        $banco=$resultado[0]['nombre_banco'];
        


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
}
?>
