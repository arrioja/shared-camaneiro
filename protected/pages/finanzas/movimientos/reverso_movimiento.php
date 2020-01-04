<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: En esta página se Anulan los movimientos
 *              bancarios(nota credito,nota debito,cheque) y el usuario describe el detalle de la anulacion.
 *****************************************************  FIN DE INFO
*/
class reverso_movimiento extends TPage
{
 public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              //años
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $ano_presupuesto=ano_presupuesto(usuario_actual('cod_organizacion'),1,$this);
              $this->lbl_ano->Text =$ano_presupuesto[0]['ano'] ;
             // $this->drop_ano->dataBind();
              $cod_organizacion = usuario_actual('cod_organizacion');

              $this->txt_fecha->Text = date("d/m/Y");
              if($this->Request['id']!=""){
                  $id=$this->Request['id'];
                  $sql="SELECT * FROM  presupuesto.bancos_cuentas_movimientos WHERE id='$id'";
                  $resultado_rpt=cargar_data($sql,$this);
                  if ($resultado_rpt[0]['tipo']=='R'){
                   $this->btn_incluir->Enabled=false;
                   $this->txt_motivo->Enabled=false;
                   $this->mensaje->setErrorMessage($sender, "¡Operacion Invalida:No se puede Anular!", 'grow');
                  }//fin si
                  $tipo=$resultado_rpt[0]['tipo'];
                  if ($tipo!='R'){

                  $referencia=$resultado_rpt[0]['referencia'];
                  $ano=$resultado_rpt[0]['ano'];
                  $sql="SELECT * FROM  presupuesto.bancos_cuentas_movimientos WHERE ano='$ano' AND referencia='$referencia' AND tipo='R' ";
                  $resultado_rpt=cargar_data($sql,$this);
                 if(count($resultado_rpt)>0){
                   $this->btn_incluir->Enabled=false;
                   $this->txt_motivo->Enabled=false;
                   $this->mensaje->setErrorMessage($sender, "¡Operacion Invalida:Movimiento Previamente Anulado!", 'grow');

                  }//fin si
                }//fin si
              }else{
              $this->btn_incluir->Enabled=false;
                   $this->txt_motivo->Enabled=false;
                   $this->mensaje->setErrorMessage($sender, "¡Operacion Invalida:Movimiento No existe!", 'grow');

             }//fin si
          }//fin si postback

    }

    
public function  incluir_click($sender,$param){

        if (($this->IsValid)&&( $this->btn_incluir->Enabled)&&($this->txt_motivo->Text!=""))
        {
            $id=$this->Request['id'];
            $sql="SELECT * FROM  presupuesto.bancos_cuentas_movimientos WHERE  id='$id'";
            $resultado_rpt=cargar_data($sql,$this);

            $cod_organizacion = usuario_actual('cod_organizacion');
            $banco=$resultado_rpt[0]['cod_banco'];
            $cuenta=$resultado_rpt[0]['numero_cuenta'];
            $cod_movimiento=$resultado_rpt[0]['cod_movimiento'];
            $referencia=$resultado_rpt[0]['referencia'];
            $motivo=$this->txt_motivo->Text;
            $tipo=$resultado_rpt[0]['tipo'];
            $nombre_movimiento=$resultado_rpt[0]['nombre_movimiento'];
            $fecha_arre=split("/",$this->txt_fecha->Text);
            $fecha=$fecha_arre[2]."-".$fecha_arre[1]."-".$fecha_arre[0];
            $ano = $this->lbl_ano->Text;
            if (($tipo=="ND")||($tipo=="E")||($tipo=="CH")){//+
             $debe_haber='debe';
             $monto_total=$resultado_rpt[0]['haber'];
            }elseif (($tipo=="NC")||($tipo=="I")||($tipo=="D")){//-
             $monto_total=$resultado_rpt[0]['debe'];
             $debe_haber='haber';
            }//fin si



            // si es un cheque verificamos el tipo para anular
            if (($tipo=="CH")){//+


                    //consulto tipo de cheque
                    $sql = "SELECT c.tipo_pago FROM presupuesto.bancos_cuentas_movimientos as m
                            INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=m.cod_movimiento AND c.ano=m.ano)
                             WHERE ( m.id='$id')";
                    $resultado_tipo=cargar_data($sql,$this);
                    $tipo = $resultado_tipo[0]['tipo_pago'];

                    // tipo de cheque
                    switch($tipo){
                        case 'SIN ORDEN':
                        //nada solo afecta finanzas
                             break;
                        case 'CON ORDEN':

                           $sql=" SELECT id as id2,numero
                                FROM  presupuesto.maestro_pagado
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
                               //por ultimo poner status anulada la orden
                               $sql="update presupuesto.maestro_pagado set estatus_actual='ANULADA' where id='$id2'";
                               modificar_data($sql,$sender);
                               //por ultimo poner status anulada causado_pagado
                               $sql="update presupuesto.causado_pagado set estatus='ANULADA' where numero_documento_pagado='$numero' and tipo_documento_pagado='CH' and cod_organizacion='$cod_organizacion' and ano='$ano'";
                               modificar_data($sql,$sender);
                             break;
                        case 'RETENCION':
                             // la retencion ya no esta asociada a presupuesto_gastos
                            /* $sql2 = "SELECT rp.codigo_retencion as codigo,rp.monto FROM presupuesto.bancos_cuentas_movimientos as m
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
                                    }//fin foreach*/
                             break;
                    }//fin caso tipo

                //poner status anulada cheque
                   $sql="UPDATE presupuesto.cheques SET estatus_actual='ANULADO'
                        WHERE cod_movimiento='$cod_movimiento' AND ano='$ano'";
                   modificar_data($sql,$sender);
                
               }//fin si cheque

            //se registra el movimiento en las cuenta bancaria seleccionada
            registrar_movimiento($ano,$cod_organizacion,$banco,$cuenta,$monto_total,$debe_haber,$motivo,$referencia,$this,'R',$fecha,"");

             $this->mensaje->setSuccessMessage($sender, "Anulado Exitosamente!!", 'grow');
                 // Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha anulado el movimiento bancario($tipo-$referencia) en bancos_cuentas_movimientos id=".$id;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            $this->btn_incluir->Enabled=false;
         }
       }


    


}
?>