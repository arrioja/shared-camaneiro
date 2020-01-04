<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Esta página presenta un listado de los movimientos bancarios
 *              que ha hecho la organización en los diferentes cuentas de bancos en un mes seleccionado.
 *****************************************************  FIN DE INFO
*/

class eliminar_movimiento extends TPage
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
            //$item->codigo->Text ="<img alt='Anular' src='imagenes/iconos/delete.png' style='cursor:pointer' border='0' onclick=\"location.href ='?page=finanzas.movimientos.reverso_movimiento&id=$id'\" />";
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


    public function eliminar($sender,$param)
	{
       $parametros=$sender->CommandParameter;//recibe el id
       $datos=explode(",", $parametros);
       $id=$datos[0];

      //$this->mensaje->setErrorMessage($sender, "Movimiento a Eliminar"+$id, 'grow');

            $sql="SELECT * FROM  presupuesto.bancos_cuentas_movimientos WHERE  id='$id'";
            $resultado_rpt=cargar_data($sql,$this);

            $cod_organizacion = usuario_actual('cod_organizacion');
            $banco=$resultado_rpt[0]['cod_banco'];
            $cuenta=$resultado_rpt[0]['numero_cuenta'];
            $cod_movimiento=$resultado_rpt[0]['cod_movimiento'];
            $referencia=$resultado_rpt[0]['referencia'];
            $tipo=$resultado_rpt[0]['tipo'];
            $nombre_movimiento=$resultado_rpt[0]['nombre_movimiento'];
            $ano = $this->drop_ano->SelectedValue;


            // se verifica si el movimiento fue reversado
            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $ano,'numero_cuenta' => $cuenta,'tipo' => 'R');
            $reverso = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$referencia,$criterios_adicionales,$sender);


            if (($tipo=="ND")||($tipo=="E")||($tipo=="CH")){//+
             $debe_haber='debe';
             $monto_total=$resultado_rpt[0]['haber'];
            }elseif (($tipo=="NC")||($tipo=="I")||($tipo=="D")){//-
             $monto_total=$resultado_rpt[0]['debe'];
             $debe_haber='haber';
            }//fin si



            // si es un cheque
            if ($tipo=="CH"){//+


                 //consulto tipo de cheque
                 $sql = "SELECT c.tipo_pago,c.id FROM presupuesto.bancos_cuentas_movimientos as m
                         INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                         WHERE ( m.id='$id')";
                 $resultado_tipo=cargar_data($sql,$this);
                 $tipo_c = $resultado_tipo[0]['tipo_pago'];
                 $id_cheque = $resultado_tipo[0]['id'];


                        // tipo de cheque
                        switch($tipo_c){
                            case 'CON ORDEN':

                               $sql=" SELECT id as id2,numero
                                    FROM  presupuesto.maestro_pagado
                                    WHERE cod_organizacion='$cod_organizacion' AND  ano='$ano' and numero_cheque='$referencia' and banco='$banco'";
                               $arre_numero= cargar_data($sql,$this);
                               $numero=$arre_numero[0]['numero'];

                               $id2=$arre_numero[0]['id2'];
                                if($reverso){//si no fue anulado
                                      $sql = "select CONCAT(dp.sector,'-',dp.programa,'-',dp.subprograma,'-',dp.proyecto,'-',dp.actividad,'-',dp.partida,'-',dp.
                                      generica,'-',dp.especifica,'-',dp.subespecifica,'-',dp.ordinal) as codigo, dp.monto, dp.tipo_documento_causado, dp.numero_documento_causado,dp.es_retencion,mp.cod_proveedor,mp.monto_total,mp.motivo,mp.numero_cheque,mp.banco,mp.cuenta
                                      from presupuesto.detalle_pagado dp inner join presupuesto.maestro_pagado mp on (mp.numero=dp.numero_documento_pagado AND mp.ano=dp.ano)
                                      where dp.cod_organizacion='$cod_organizacion' and dp.numero_documento_pagado='$numero' AND dp.ano='$ano'
                                      order by codigo";
                                    $datos = cargar_data($sql,$this);
                                    if (empty($datos[0]['codigo']) == false)
                                    {
                                        // ciclo para modificar los acumulados
                                        foreach ($datos as $undato)
                                        {
                                            $codi= solo_numeros($undato['codigo']);
                                            $cod = descomponer_codigo_gasto($codi);

                                                    if ($undato[es_retencion]==1)//si es retencion se pone positiva para sumarla en la tabla presupuesto_gastos y se guarda en retencion
                                                    {
                                                    $undato[monto]=$undato[monto]*-1;
                                                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '-', $undato['monto'], $this);
                                                    //modificar_montos_pendientes_2('CH', $cod, $cod_organizacion, $ano, '', $undato['numero_documento_causado'], $undato['cod_proveedor'], $undato['monto'],'+', $this);
                                                    }
                                                    else
                                                    {
                                                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '-', $undato['monto'], $this);
                                                    modificar_montos_pendientes_2('CH', $cod, $cod_organizacion, $ano, '', $undato['numero_documento_causado'], $undato['cod_proveedor'], $undato['monto'],'+', $this);
                                                    }
                                        }//fin foreach
                                    }//fin si
                                }//fin si no anulado
                                
                                   //delete orden
                                   $sql="DELETE FROM presupuesto.maestro_pagado WHERE id='$id2'";
                                   modificar_data($sql,$sender);
                                   //delete orden
                                   $sql="DELETE FROM presupuesto.detalle_pagado  WHERE cod_organizacion='$cod_organizacion' AND numero_documento_pagado='$numero' AND ano='$ano'";
                                   modificar_data($sql,$sender);
                                   //delete causado_pagado
                                   $sql="DELETE FROM presupuesto.causado_pagado WHERE numero_documento_pagado='$numero' and tipo_documento_pagado='$tipo' and cod_organizacion='$cod_organizacion' and ano='$ano'";
                                   modificar_data($sql,$sender);
                              
                                 break;

                            case 'RETENCION':
                                 // la retencion ya no esta asociada a presupuesto_gastos
                                 /*if($reverso){//si no fue anulado
                                 $sql2 = "SELECT rp.codigo_retencion as codigo,rp.monto FROM presupuesto.bancos_cuentas_movimientos as m
                                        INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                                        INNER JOIN presupuesto.retencion_pagado as rp ON(rp.cod_movimiento=m.cod_movimiento AND rp.ano=m.ano)
                                        WHERE ( m.id='$id') ";
                                 $datos = cargar_data($sql2,$this);
                                 // ciclo para modificar los acumulados
                                        foreach ($datos as $undato)
                                        {
                                            $codi= solo_numeros($undato['codigo']);
                                            $cod = descomponer_codigo_gasto($codi);
                                            modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '+', $undato['monto'], $this);
                                            modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '-', $undato['monto'], $this);
                                        }//fin foreach
                                 }//fin si no anulado*/
                                 
                                   //delete retencion_pagado
                                    $sql="DELETE FROM presupuesto.retencion_pagado
                                          WHERE ano='$ano' AND cod_movimiento='$cod_movimiento'";
                                    modificar_data($sql,$sender);
                                
                                 break;
                        }//fin caso tipo

                   
                    //delete cheque
                    $sql="DELETE FROM presupuesto.cheques WHERE id='$id_cheque'";
                    modificar_data($sql,$sender);

               }//fin si cheque


                     
                   // si el tipo es reverso y es de un cheque se actualiza tablas a NORMAL
                   if($tipo=='R'){
                        // se verifica si el reverso es de un cheque
                    $criterios_adicionaless=array('cod_organizacion' => $cod_organizacion,'ano' => $ano,'numero_cuenta' => $cuenta,'tipo' => 'CH');
                    $es_cheque = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$referencia,$criterios_adicionaless,$sender);


                           if(!$es_cheque){
                                   //consulto tipo de cheque
                                 $sql = "SELECT c.tipo_pago,c.id FROM presupuesto.bancos_cuentas_movimientos as m
                                         INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                                         WHERE (m.ano='$ano' AND cod_organizacion='$cod_organizacion' AND m.referencia='$referencia' AND m.numero_cuenta='$cuenta' AND m.tipo='CH' )";
                                 $resultado_tipo=cargar_data($sql,$this);
                                 $tipo_c = $resultado_tipo[0]['tipo_pago'];
                                 $id_cheque = $resultado_tipo[0]['id'];


                                // se verifica tipo de cheque para sumar al acumulado correspondiente
                                  switch($tipo_c){
                                    case 'CON ORDEN':
                                           
                                            $sql=" SELECT id as id2,numero FROM  presupuesto.maestro_pagado
                                                  WHERE cod_organizacion='$cod_organizacion' AND  ano='$ano' and numero_cheque='$referencia' and banco='$banco'";
                                            $arre_numero= cargar_data($sql,$this);
                                            $numero=$arre_numero[0]['numero'];
                                            $id2=$arre_numero[0]['id2'];

                                           
                                            $sql = "select CONCAT(dp.sector,'-',dp.programa,'-',dp.subprograma,'-',dp.proyecto,'-',dp.actividad,'-',dp.partida,'-',dp.
                                                    generica,'-',dp.especifica,'-',dp.subespecifica,'-',dp.ordinal) as codigo, dp.monto, dp.tipo_documento_causado, dp.numero_documento_causado,dp.es_retencion,mp.cod_proveedor,mp.monto_total,mp.motivo,mp.numero_cheque,mp.banco,mp.cuenta
                                                    from presupuesto.detalle_pagado dp inner join presupuesto.maestro_pagado mp on (mp.numero=dp.numero_documento_pagado AND mp.ano=dp.ano)
                                                    where dp.cod_organizacion='$cod_organizacion' and dp.numero_documento_pagado='$numero' AND dp.ano='$ano'
                                                    order by codigo";
                                                $datos = cargar_data($sql,$this);
                                                if (empty($datos[0]['codigo']) == false)
                                                {
                                                    // ciclo para modificar los acumulados
                                                    foreach ($datos as $undato)
                                                    {
                                                        $codi= solo_numeros($undato['codigo']);
                                                        $cod = descomponer_codigo_gasto($codi);

                                                                if ($undato[es_retencion]==1)//si es retencion se pone positiva para sumarla en la tabla presupuesto_gastos y se guarda en retencion
                                                                {
                                                                $undato[monto]=$undato[monto]*-1;
                                                                //modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '+', $undato['monto'], $this);
                                                                //modificar_montos_pendientes_2('CH', $cod, $cod_organizacion, $ano, '', $undato['numero_documento_causado'], $undato['cod_proveedor'], $undato['monto'],'+', $this);
                                                                }
                                                                else
                                                                {
                                                                modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '+', $undato['monto'], $this);
                                                                modificar_montos_pendientes_2('CH', $cod, $cod_organizacion, $ano, '', $undato['numero_documento_causado'], $undato['cod_proveedor'], $undato['monto'],'-', $this);
                                                                }
                                                    }//fin foreach
                                                }//fin si

                                            //por ultimo poner status anulada la orden
                                            $sql="update presupuesto.maestro_pagado set estatus_actual='NORMAL' where id='$id2'";
                                            modificar_data($sql,$sender);
                                            //por ultimo poner status anulada causado_pagado
                                            $sql="update presupuesto.causado_pagado set estatus='NORMAL' where numero_documento_pagado='$numero' and tipo_documento_pagado='CH' and cod_organizacion='$cod_organizacion' and ano='$ano'";
                                            modificar_data($sql,$sender);
                                          break;
                                      CASE 'RETENCION':
                                          // la retencion ya no esta asociada a presupuesto_gastos
                                        /*$sql2 = "SELECT rp.codigo_retencion as codigo,rp.monto FROM presupuesto.bancos_cuentas_movimientos as m
                                        INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                                        INNER JOIN presupuesto.retencion_pagado as rp ON(rp.cod_movimiento=m.cod_movimiento AND rp.ano=m.ano)
                                        WHERE ( m.id='$id') ";
                                        $datos = cargar_data($sql2,$this);
                                        // ciclo para modificar los acumulados
                                        foreach ($datos as $undato)
                                        {
                                            $codi= solo_numeros($undato['codigo']);
                                            $cod = descomponer_codigo_gasto($codi);
                                            modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '-', $undato['monto'], $this);
                                            modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '+', $undato['monto'], $this);
                                        }//fin foreach*/
                                          break;
                                }//fin caso
                           //poner status anulada cheque
                           $sql="UPDATE presupuesto.cheques SET estatus_actual='NORMAL'
                                 WHERE id='$id_cheque' ";
                           modificar_data($sql,$sender);

                           }//fin si
                   }//fin si reverso



                    //se elimina el movimiento
                    $sql="DELETE FROM presupuesto.bancos_cuentas_movimientos
                          WHERE cod_organizacion='$cod_organizacion' AND ano='$ano' AND numero_cuenta='$cuenta'
                          AND referencia='$referencia' AND tipo='$tipo'";
                    modificar_data($sql,$sender);

                    // se elimina el reverso si posee
                    if(!$reverso AND $tipo!='R')
                    {
                         $sql="DELETE FROM presupuesto.bancos_cuentas_movimientos
                               WHERE cod_organizacion='$cod_organizacion' AND ano='$ano' AND numero_cuenta='$cuenta'
                               AND referencia='$referencia' AND tipo='R'";
                         modificar_data($sql,$sender);
                    }//fin si

                    
             
             $this->actualiza_listado();
             $this->mensaje->setSuccessMessage($sender, "Eliminado Exitosamente!!", 'grow');
                 // Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha Eliminado el movimiento bancario($tipo-$referencia) ";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            
    }

  

}

?>
