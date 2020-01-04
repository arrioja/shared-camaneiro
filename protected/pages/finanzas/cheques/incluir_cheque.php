<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Inclusión de nuevo documento de pagado de presupuesto, es
 *              requisito que exista un causado previamente establecido.
 *****************************************************  FIN DE INFO
*/
class incluir_cheque extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {   
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_codigo_temporal->Text = aleatorio_pagado($this);
              $cod_organizacion = usuario_actual('cod_organizacion');
             
              // para llenar el listado de Beneficiarios
              $this->carga_proveedores_con_causados();

              // coloca el año presupuestario "Presente"
              $ano_array = ano_presupuesto($cod_organizacion,1,$this);
              $this->lbl_ano->Text = $ano_array[0]['ano'];
              $ano=$ano_array[0]['ano'];

              // coloca la fecha
              $this->lbl_fecha->Text = date("d/m/Y");
              $this->lbl_total->Text="0.00";

        $sql2 = "SELECT codigo as codigo,descripcion
              FROM presupuesto.retencion
              where ( cod_organizacion = '$cod_organizacion' AND ano = '$ano' )
              order by codigo ASC";
        $resultado2=cargar_data($sql2,$this);
        $this->drop_retenciones->DataSource=$resultado2;
        $this->drop_retenciones->dataBind();
        $this->txt_motivo->Enabled="False";
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


    public function carga_proveedores_con_causados()
    {
      // para llenar el listado de Beneficiarios
      $cod_organizacion = usuario_actual('cod_organizacion');
      $ano = ano_presupuesto($cod_organizacion,1,$this);
      $ano = $ano[0]['ano'];
      $sql2 = "select distinct p.cod_proveedor, CONCAT(p.nombre,' / ',p.rif) as nomb from presupuesto.proveedores p,
                presupuesto.maestro_causado c
                where ((p.cod_organizacion = '$cod_organizacion') and (c.monto_pendiente > 0) and
                   (p.cod_proveedor = c.cod_proveedor) and (c.ano = '$ano') and
                   (p.cod_organizacion = c.cod_organizacion)and (c.estatus_actual='NORMAL')) order by p.nombre, c.monto_pendiente";
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

    public function carga_ordenes_disponibles()
    {
      /* Si se selecciona un proveedor diferente al que estaba seleccionado, se
       * eliminan las selecciones previas (tanto de ordenes como de codigos).*/
      $aleatorio = $this->lbl_codigo_temporal->Text;
      $sql = "delete from presupuesto.temporal_causado_pagado where (numero_documento_pagado='$aleatorio')";
      $resultado=modificar_data($sql,$this);

      $cod_organizacion = usuario_actual('cod_organizacion');
      $ano = $this->lbl_ano->Text;
      $cod_proveedor =  $this->drop_proveedor->SelectedValue;
      
      $sql2 = "select m.id, CONCAT(m.tipo_documento,'-',m.numero,' Tot: ',m.monto_total, ' / Pen: ',m.monto_pendiente) as nomb from
                presupuesto.maestro_causado m
                where ((m.monto_pendiente > 0) and (m.cod_organizacion = '$cod_organizacion') and
                       (m.ano = '$ano') and
                       (m.cod_proveedor = '$cod_proveedor')and (m.estatus_actual='NORMAL')) order by nomb";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_causados->Datasource = $datos2;
      $this->drop_causados->dataBind();

      // para vaciar el listado de codigos presupuestarios
      $vacio=array();
      $this->Repeater->DataSource=$vacio;
      $this->Repeater->dataBind();
      $this->txt_motivo->Enabled=false;
      $this->txt_motivo->Text="";
      $this->actualiza_listado();
    }



/* Esta función carga los códigos presupuestarios asociados al causado
 * seleccionado del drop respectivo */
    public function carga_codigos_presupuestarios($sender, $param)
    {
        $id = $this->drop_causados->SelectedValue;
        $cod_proveedor = $this->drop_proveedor->SelectedValue;
        $sql="select m.motivo,m.ano, m.cod_organizacion, m.tipo_documento, m.numero
              from presupuesto.maestro_causado m
              where (m.id = '$id')";
        $causado=cargar_data($sql,$this);
        $cod_organizacion = $causado[0]['cod_organizacion'];
        $ano = $causado[0]['ano'];
        $tipo_doc = $causado[0]['tipo_documento'];
        $numero = $causado[0]['numero'];
        //$this->txt_motivo->Text=$causado[0]['motivo'];
        //$this->txt_motivo->Enabled="True";
        
        $sql = "select distinct(CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal)) as codigo, id as id2, cod_organizacion, tipo_documento_causado, numero_documento_causado, ano, sum(monto) monto, sum(monto_pendiente) monto_pendiente
              from presupuesto.compromiso_causado
              where ((cod_organizacion = '$cod_organizacion') and (numero_documento_causado = '$numero') and
                     (tipo_documento_causado = '$tipo_doc') and (ano = '$ano') )
              group by codigo";
        $resultado=cargar_data($sql,$this);
        $this->Repeater->DataSource=$resultado;
        $this->Repeater->dataBind();
        $this->btn_anadir->Enabled="True";
        $this->btn_anadir_retencion->Enabled="True";
    }

/* Esta funcion actualiza el listado de los codigos presupuestarios que se
 * muestran en el ActiveDataGrid.
 */
    function actualiza_listado()
    {
        // se actualiza el listado
        $cod_organizacion = usuario_actual('cod_organizacion');
        $numero = $this->lbl_codigo_temporal->Text;
        $sql = "select id, CONCAT(tipo_documento_causado,'-',numero_documento_causado) as causado,tipo_documento_causado, numero_documento_causado,CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto from presupuesto.temporal_causado_pagado
              where ((cod_organizacion = '$cod_organizacion') and (numero_documento_pagado = '$numero'))
              order by codigo";
        $datos = cargar_data($sql,$this);
        $this->DataGrid->Datasource = $datos;
        $this->DataGrid->dataBind();
        
        // si no hay nada que listar, se deshabilita el boton incluir
		
        $this->btn_incluir->Enabled = !(empty($datos));

        // se actualiza la sumatoria total
        $sql = "select SUM(monto) as total from presupuesto.temporal_causado_pagado
              where ((cod_organizacion = '$cod_organizacion') and (numero_documento_pagado = '$numero'))";
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
                $monto_a_pagar = $item->txt_monto_pendiente->Text;
				$monto_pendiente=$item->lbl_monto->Text;
                if (( $monto_pendiente > 0)&&($monto_a_pagar > 0)&& ($monto_a_pagar <= $monto_pendiente))
                {$monto = $item->txt_monto_pendiente->Text;
                    $ano = $item->lbl_ano->Value;
                    $cod_organizacion = $item->lbl_cod_organizacion->Value;
                    $numero_documento_causado = $item->lbl_numero->Value;
                    $tipo_documento_causado = $item->lbl_tipo_documento_causado->Value;
                    $numero_documento_pagado = $this->lbl_codigo_temporal->Text;

                    $codigo_sin_descomponer = $item->lbl_codigo->Text;
                    $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));

                    $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                                  'numero_documento_pagado' => $numero_documento_pagado,
                                                  'tipo_documento_causado' => $tipo_documento_causado,
                                                  'numero_documento_causado' => $numero_documento_causado,
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
                    $no_existe = verificar_existencia('presupuesto.temporal_causado_pagado','ano',$ano,$criterios_adicionales,$sender);
                    if ($no_existe == true)
                        {
                            $sql = "insert into presupuesto.temporal_causado_pagado
                                        (ano, cod_organizacion, numero_documento_pagado, tipo_documento_causado,
                                         numero_documento_causado, sector, programa, subprograma, proyecto, actividad, partida,
                                         generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto,es_retencion)
                                        values ('$ano','$cod_organizacion','$numero_documento_pagado',
                                                '$tipo_documento_causado','$numero_documento_causado',
                                                '$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                                                '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                                                '$codigo[subespecifica]','$codigo[ordinal]','00','$monto',0)";
                            $resultado=modificar_data($sql,$sender);

                            $numero_documento_causado = $item->lbl_numero->Value;
                            $tipo_documento_causado = $item->lbl_tipo_documento_causado->Value;

                            $sql="select td.nombre from presupuesto.tipo_documento as td WHERE (td.siglas='$tipo_documento_causado') ";
                            $nombre_tipo=cargar_data($sql,$this);
                            $sql="select motivo from presupuesto.maestro_causado WHERE (ano='$ano' AND cod_organizacion='$cod_organizacion' AND
                                numero='$numero_documento_causado' AND tipo_documento='$tipo_documento_causado') ";
                            $motivo_orden=cargar_data($sql,$this);
                            if($primera){
                                // si el text_motivo ya contiene
                                 if (strlen($this->txt_motivo->Text) > "0") {
                                    $this->txt_motivo->Text .= ", Nº".$numero_documento_causado."-$ano (".$motivo_orden[0][motivo].")";
                                    $primera=false;
                                }else{
                                    $this->txt_motivo->Text="Cancelación ".$nombre_tipo[0]['nombre'].": Nº".$numero_documento_causado."-$ano (".$motivo_orden[0][motivo].")";
                                    $primera=false;
                                }//fin si
                            }//FIN SI
                            $this->txt_motivo->Enabled=true;
                        }//fin no_existe
            
				}else{
					if( $monto_a_pagar != 0) 
					 $this->mensaje->setErrorMessage($sender, "Operacion Invalida, Monto a pagar es mayor que Pendiente </br> Codigo: ".$item->lbl_codigo->Text."  !", 'grow');
					// break;
				} // del que comprueba que el monto sea mayor que cero y menor o igual al compromiso.
			} // del for each del repeater
            $this->actualiza_listado();
       }
    }
/* esa funcion añade el codigo presupuestario en la tabla temporal con el fin
 * de conservarlo ahí hasta que se incluya definitivamente en la orden.
 */
    function anadir_retencion_click($sender, $param)
    {
		
		 
        if ($this->IsValid)
        {
            /* Se incluye  a la tabla temporal
             * siempre y cuando su valor sea mayor de cero (0).
             */
            $monto = $this->txt_monto_retencion->Text;
            if ($monto> 0)
                {
                    $monto=-1*$monto;
                    $ano = $this->lbl_ano->Text;
                    $cod_organizacion = usuario_actual('cod_organizacion');
                   /* $numero_documento_causado = '0';
                    $tipo_documento_causado = '0';*/
					foreach($this->Repeater->Items as $item){  
					$tipo_documento_causado = $item->lbl_tipo_documento_causado->Value;
					$numero_documento_causado = $item->lbl_numero->Value;}
		            $numero_documento_pagado = $this->lbl_codigo_temporal->Text;
                    $codigo_sin_descomponer = $this->drop_retenciones->SelectedValue;
                    $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));


                    $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                                  'numero_documento_pagado' => $numero_documento_pagado,
                                                  'tipo_documento_causado' => $tipo_documento_causado,
                                                  'numero_documento_causado' => $numero_documento_causado,
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
                    $no_existe = verificar_existencia('presupuesto.temporal_causado_pagado','ano',$ano,$criterios_adicionales,$sender);
                    if ($no_existe == true)
                        {
                            $sql = "insert into presupuesto.temporal_causado_pagado
                                        (ano, cod_organizacion, numero_documento_pagado, tipo_documento_causado,
                                         numero_documento_causado, sector, programa, subprograma, proyecto, actividad, partida,
                                         generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto,es_retencion)
                                        values ('$ano','$cod_organizacion','$numero_documento_pagado',
                                                '$tipo_documento_causado','$numero_documento_causado',
                                                '$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                                                '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                                                '$codigo[subespecifica]','$codigo[ordinal]','00','$monto',1)";
                            $resultado=modificar_data($sql,$sender);
                        }
                    else
            $this->mensaje->setErrorMessage($sender, "Operacion Invalida, Retención repetida!", 'grow');

            $this->actualiza_listado();
            }
            else
            $this->mensaje->setErrorMessage($sender, "El monto de la retención debe ser mayor a 0", 'grow');
        }
		
		
    }

/* esta funcion elimina un codigo presupuestario del listado */
	public function eliminar($sender,$param)
	{
		$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM presupuesto.temporal_causado_pagado WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();
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
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano=$this->lbl_ano->Text;
            $monto_total=$this->lbl_total->Text;
            $error=false;
            
           $saldo_actual=disponibilidad_bancaria($ano,$cod_organizacion,$banco,$cuenta,$sender);
        
         if($saldo_actual>$monto_total)//se verifica disponibilidad en cuenta
         {
                
       
                if ($no_existe_movimientos==true)// sino existe el numero cheque
                 {

                    $this->btn_incluir->Enabled=false;
                    // captura de datos desde los controles

                    $fecha=cambiaf_a_mysql($this->lbl_fecha->Text);
                    $aleatorio = $this->lbl_codigo_temporal->Text;
                    //$tipo_documento = $this->drop_tipo->SelectedValue;
                    $motivo=$this->txt_motivo->Text;
                    $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,                                         
                                                 'ano' => $ano);

                    $numero=proximo_numero("presupuesto.maestro_pagado","numero",$criterios_adicionales,$sender);
                    $numero=rellena($numero,6,"0");
                    $cod_proveedor = $this->drop_proveedor->SelectedValue;


                    $num_cheque=$this->txt_numero_cheque->Text;
                    $fecha_cheque=cambiaf_a_mysql($this->txt_fecha_cheque->Text);


                    //se actualiza codigo de financiamiento
                    $sql = "update presupuesto.temporal_causado_pagado set cod_fuente_financiamiento=(SELECT cod_fuente_financiamiento  FROM presupuesto.fuentes_financiamiento_cuentas WHERE numero_cuenta='$cuenta'and ano='$ano') where numero_documento_pagado='$aleatorio'";
                    $resultado=modificar_data($sql,$sender);

                    // inserción en la base de datos de la información del archivo maestro.
                    $sql = "insert into presupuesto.maestro_pagado
                            (cod_organizacion, ano, fecha, tipo_documento, numero, cod_proveedor, monto_total, estatus_actual,motivo,numero_cheque,fecha_cheque,banco,cuenta)
                            values ('$cod_organizacion','$ano','$fecha','CH','$numero','$cod_proveedor','$monto_total','NORMAL','$motivo','$num_cheque','$fecha_cheque','$banco','$cuenta')";
                    $resultado=modificar_data($sql,$sender);

                    // inclusión en la base de datos del detalle
                    $sql = "insert into presupuesto.detalle_pagado
                            (ano, cod_organizacion,numero_documento_pagado, tipo_documento_causado ,numero_documento_causado, sector, programa, subprograma, proyecto, actividad, partida,
                             generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto, es_retencion)
                             select t.ano, t.cod_organizacion, t.numero_documento_pagado, t.tipo_documento_causado, t.numero_documento_causado,t.sector, t.programa, t.subprograma, t.proyecto, t.actividad, t.partida,
                                    t.generica, t.especifica, t.subespecifica, t.ordinal, t.cod_fuente_financiamiento, t.monto,t.es_retencion from presupuesto.temporal_causado_pagado as t where (t.numero_documento_pagado='$aleatorio')";
                    $resultado=modificar_data($sql,$sender);

                    // incluir en causado_pagado
                    $sql="select t.ano, t.cod_organizacion, t.numero_documento_pagado, t.tipo_documento_causado, t.numero_documento_causado,t.sector, t.programa, t.subprograma, t.proyecto, t.actividad, t.partida,
                                    t.generica, t.especifica, t.subespecifica, t.ordinal, t.cod_fuente_financiamiento, t.monto,t.es_retencion from presupuesto.temporal_causado_pagado as t where (t.numero_documento_pagado='$aleatorio')";
                            $resultado=cargar_data($sql,$sender);
                    if (!empty($resultado))
                    {
                        // ciclo para insertar
                        foreach ($resultado as $datos)
                        {
                             // inserción en la base de datos de la información del cheque asociado a la orden que paga
                            $sql = "insert into presupuesto.causado_pagado
                                    (ano, cod_banco, cod_organizacion, tipo_documento_causado, numero_documento_causado,
                                     tipo_documento_pagado, numero_documento_pagado, numero_cuenta_banco,
                                     cod_proveedor, fecha_documento_pagado,monto, estatus)
                                    values ('$datos[ano]','$banco','$datos[cod_organizacion]','$datos[tipo_documento_causado]','$datos[numero_documento_causado]',
                                            'CH','$datos[numero_documento_pagado]','$cuenta','$cod_proveedor',
                                            '$fecha','$datos[monto]','NORMAL')";
                            $consulta=modificar_data($sql,$sender);

                        }
                    }

                    // antes de eliminar los de la tabla temporal, modifico los acumulados correspondientes
                    $sql = "select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                                    generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo, monto, tipo_documento_causado, numero_documento_causado,es_retencion
                      from presupuesto.temporal_causado_pagado
                      where ((cod_organizacion = '$cod_organizacion') and (numero_documento_pagado = '$aleatorio'))
                      order by codigo";
                    $datos = cargar_data($sql,$this);
                    if (empty($datos[0]['codigo']) == false)
                    {
                        // ciclo para modificar los acumulados
                        foreach ($datos as $undato)
                        {
                            $codi= solo_numeros($undato['codigo']);
                            $cod = descomponer_codigo_gasto($codi);

                               /* if ($undato[es_retencion]==1)//si es retencion se pone positiva para sumarla en la tabla presupuesto_gastos y se guarda en retencion
                                {
                                $undato[monto]=$undato[monto]*-1;
                                modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '+', $undato['monto'], $this);
                                //modificar_montos_pendientes('CA', $cod, $cod_organizacion, $ano, $undato['tipo_documento_causado'], $undato['numero_documento_causado'], $cod_proveedor, $undato['monto'], $this);
                                }
                                else
                                {
                                modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '+', $undato['monto'], $this);
                                modificar_montos_pendientes('CA', $cod, $cod_organizacion, $ano, $undato['tipo_documento_causado'], $undato['numero_documento_causado'], $cod_proveedor, $undato['monto'], $this);
                                }*/

                                if ($undato[es_retencion]!=1)//si no es retencion se suma en la tabla presupuesto_gastos
                                {
                                modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '+', $undato['monto'], $this);
                                modificar_montos_pendientes('CA', $cod, $cod_organizacion, $ano, $undato['tipo_documento_causado'], $undato['numero_documento_causado'], $cod_proveedor, $undato['monto'], $this);
                                }


                        }
                    } 

                    // Se cambia el numero aleatorio por el numero que realmente que debe tener.
                    $sql = "update presupuesto.maestro_pagado set numero='$numero' where (numero='$aleatorio')";
                    $resultado=modificar_data($sql,$sender);

                    $sql = "update presupuesto.detalle_pagado set numero_documento_pagado='$numero' where (numero_documento_pagado='$aleatorio')";
                    $resultado=modificar_data($sql,$sender);

                     $sql = "update presupuesto.causado_pagado set numero_documento_pagado='$numero' where (numero_documento_pagado='$aleatorio')";
                    $resultado=modificar_data($sql,$sender);

                      // Se eliminan los registros aleatorios que habia en la tabla temporal.
                    $sql = "delete from presupuesto.temporal_causado_pagado where (numero_documento_pagado='$aleatorio')";
                    $resultado=modificar_data($sql,$sender);

                    // se registra el movimientos y cheques
                    $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                                         'ano' => $ano);
                    $numero_movimiento=proximo_numero("presupuesto.bancos_cuentas_movimientos","cod_movimiento",$criterios_adicionales,$sender);
                    $cod_movimiento=rellena($numero_movimiento,10,"0");

                    //se registra el movimiento en las cuenta bancaria seleccionada
                    registrar_movimiento($ano,$cod_organizacion,$banco,$cuenta,$monto_total,'haber',$motivo,$num_cheque,$this,'CH',$fecha_cheque,$cod_movimiento);
                     //se registra el cheque
                    registrar_cheque($ano,$cod_movimiento,'CON ORDEN',$cod_proveedor,$sender);

                    

                    // Se incluye el rastro en el archivo de bitácora
                    $descripcion_log = "Se ha incluido el cheque: ".$tipo_documento." # ".$numero. " / año: ".$ano.
                                       " a favor del proveedor Nro: ".$cod_proveedor.
                                       " por un monto total de Bs. ".$monto_total." Motivo: ".$motivo;
                    inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
                    $this->mensaje->setSuccessMessage($sender, "Cheque Nº $num_cheque guardado exitosamente!!", 'grow');
                    $this->lbl_numero->text=$numero;
                }else{

                    $this->mensaje->setErrorMessage($sender, "Operacion Invalida, Cheque $num_cheque ya registrado!", 'grow');

                 }// fin si existe
         }else{
            $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente en cuenta $cuenta !</br> Saldo actual: Bs.".number_format($saldo_actual, 2, ',', '.'), 'grow');
            // $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente en cuenta $cuenta $saldo_actual", 'grow');

        
        }//fin si
        
        }//fin si 
    }


/* Esta función imprime el detalle sobre el cheque y sobre el comprobante una vez
 * que se haya guardado el cheque.
 */

    public function imprimir_item($sender, $param)
    {
        $id2=$this->lbl_numero->text;//numero documento pagado
        $ano=$this->lbl_ano->Text;

        $sql="select mp.*, p.rif, p.nombre,b.nombre as nombre_banco
              from presupuesto.maestro_pagado mp, presupuesto.proveedores p, presupuesto.bancos b
                  where (p.cod_proveedor = mp.cod_proveedor) and
                    (p.cod_organizacion = mp.cod_organizacion) and (mp.numero = '$id2') and (mp.ano='$ano') and  (b.cod_banco=mp.banco) ";
        $resultado=cargar_data($sql,$sender);

        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
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

            //CODIGOS AGRUPADOS
             $sql="SELECT *,CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo, SUM(monto) as monto_sumado  FROM  presupuesto.detalle_pagado
                WHERE  ano =  '$ano'
                AND  cod_organizacion =  '$cod_organizacion'
                AND  numero_documento_pagado =  '$id2'
                AND  es_retencion ='0'
                GROUP BY codigo";
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
                AND  numero_documento_pagado =  '$id2'
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