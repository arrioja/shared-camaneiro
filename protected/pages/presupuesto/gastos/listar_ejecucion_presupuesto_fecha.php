<?php

class listar_ejecucion_presupuesto_fecha extends TPage
{
var $ver;//variable global en la clase para saver si visualizo la columna 'ver orden'

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          { 
              $this->ver=False;
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),5,$this);
              $this->drop_ano->dataBind();
          }
    }

/* Esta función valida la existencia del código presupuestario en la tabla
 * de presupuestos, de no existir, se muestra un mensaje de error.
 */
    public function validar_existencia($sender, $param)
    {

    }
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            if ($item->acumulado->Text == "1")
            { $item->Font->Bold="true"; }
            $item->monto->Text = "Bs. ".number_format($item->monto->Text, 2, ',', '.');
            $item->aumentos->Text = "Bs. ".number_format($item->aumentos->Text, 2, ',', '.');
            $item->disminuciones->Text = "Bs. ".number_format($item->disminuciones->Text, 2, ',', '.');
            $item->modificado->Text = "Bs. ".number_format($item->modificado->Text, 2, ',', '.');
            $item->comprometido->Text = "Bs. ".number_format($item->comprometido->Text, 2, ',', '.');
            $item->causado->Text = "Bs. ".number_format($item->causado->Text, 2, ',', '.');
            $item->pagado->Text = "Bs. ".number_format($item->pagado->Text, 2, ',', '.');
            $item->disponible->Text = "Bs. ".number_format($item->disponible->Text, 2, ',', '.');
        }
        if ($this->ver)
            $item->ver->Visible="True";
        else
            $item->ver->Visible="False";
    }

	public function actualiza_listado($sender,$param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
        $fecha_cierre = cambiaf_a_mysql($this->txt_fecha_desde->Text);

        if ($this->gasto->Checked)
            {
                $es_retencion = 0;
                $limite = " Limit 2,50000";
            }
        else
            {
                $es_retencion = 1;
                $limite = "";
            }


//******************** SE ELIMINAN TODOS LOS DATOS DE LA TABLA TEMPORAL
        $sql1 = "truncate table presupuesto.presupuesto_gastos_ejecucion_temporal";
        $resultado1=modificar_data($sql1,$this);

//******************** SE COPIAN LOS DATOS QUE SE ENCUENTRAN EN LA EJECUCION ACTUAL A LA TEMPORAL

        $sql01 = "insert into presupuesto.presupuesto_gastos_ejecucion_temporal
                (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                 generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, descripcion,
                 asignado, acumulado, es_retencion)
                    select pg.cod_organizacion, pg.ano, pg.sector, pg.programa, pg.subprograma, pg.proyecto, pg.actividad, pg.partida,
                 pg.generica, pg.especifica, pg.subespecifica, pg.ordinal, pg.cod_fuente_financiamiento, pg.descripcion,
                 pg.asignado, pg.acumulado, pg.es_retencion from presupuesto.presupuesto_gastos as pg";
        $resultado01=modificar_data($sql01,$this);


// *******************  SE ELIMIMINAN LOS VALORES ACUMULADOS

            $sql02 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal pg
                    SET pg.asignado = 0,
                        pg.aumentos = 0,
                        pg.disminuciones = 0,
                        pg.comprometido = 0,
                        pg.causado = 0,
                        pg.pagado = 0,
                        pg.disponible = 0
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion') and
                          (pg.es_retencion = 0) and (acumulado = 1))";
            $resultado02=modificar_data($sql02,$this);

// ***************** SE PROCESAN CREDITOS ADICIONALES APROBADOS
            $sql03 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt, presupuesto.detalle_creditos dc, presupuesto.maestro_creditos mc
                      SET gt.aumentos =
                         (select gt.aumentos + SUM(dc.monto_aumento) from presupuesto.detalle_creditos dc, presupuesto.maestro_creditos mc
                          Where ((dc.cod_organizacion = '$cod_organizacion') and
                                 (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                                 (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                                 (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                                 (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                                 (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                                 (gt.subespecifica = dc.subespecifica) and
                                 (mc.fecha <= '$fecha_cierre')))
                      WHERE ((dc.cod_organizacion = '$cod_organizacion') and
                                 (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                                 (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                                 (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                                 (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                                 (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                                 (gt.subespecifica = dc.subespecifica) and
                                 (mc.fecha <= '$fecha_cierre'))";
            $resultado03=modificar_data($sql03,$this);



// ***************** SE PROCESAN TRANSFERENCIAS APROBADOS

            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt, presupuesto.detalle_traslados dc, presupuesto.maestro_traslados mc
                    SET gt.disminuciones =
                        (select gt.disminuciones + SUM(dc.monto_disminucion) from presupuesto.detalle_traslados dc, presupuesto.maestro_traslados mc
                        Where ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                               (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                               (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                               (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                               (gt.subespecifica = dc.subespecifica) and
                               (mc.fecha <= '$fecha_cierre')))
                    WHERE ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                               (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                               (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                               (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                               (gt.subespecifica = dc.subespecifica) and
                               (mc.fecha <= '$fecha_cierre'))";
            $resultado04=modificar_data($sql04,$this);


            $sql05 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt, presupuesto.detalle_traslados dc, presupuesto.maestro_traslados mc
                    SET gt.aumentos =
                        (select gt.aumentos + SUM(dc.monto_aumento) from presupuesto.detalle_traslados dc, presupuesto.maestro_traslados mc
                        Where ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                               (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                               (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                               (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                               (gt.subespecifica = dc.subespecifica) and
                               (mc.fecha <= '$fecha_cierre')))
                    WHERE ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                               (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                               (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                               (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                               (gt.subespecifica = dc.subespecifica) and
                               (mc.fecha <= '$fecha_cierre'))";
            $resultado05=modificar_data($sql05,$this);



// ***************** SE PROCESAN RECORTES APROBADOS
            $sql06 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt, presupuesto.detalle_recortes dc, presupuesto.maestro_recortes mc
                      SET gt.disminuciones =
                          (select gt.disminuciones + SUM(dc.monto_disminucion) from presupuesto.detalle_recortes dc, presupuesto.maestro_recortes mc
                           Where ((dc.cod_organizacion = '$cod_organizacion') and
                                  (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                                  (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                                  (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                                  (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                                  (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                                  (gt.subespecifica = dc.subespecifica) and
                                  (mc.fecha <= '$fecha_cierre')))
                      Where ((dc.cod_organizacion = '$cod_organizacion') and
                             (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                             (mc.ano = dc.ano) and (mc.numero = dc.numero) and
                             (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                             (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                             (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                             (gt.subespecifica = dc.subespecifica) and
                             (mc.fecha <= '$fecha_cierre'))";
            $resultado06=modificar_data($sql06,$this);




// ***************** SE PROCESAN COMPROMISOS APROBADOS
            $sql07 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt
                    SET gt.comprometido = 
                       (select SUM(dc.monto_parcial) from presupuesto.detalle_compromisos dc, presupuesto.maestro_compromisos mc
                        Where ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.tipo_documento = dc.tipo_documento) and
                               (mc.numero = dc.numero) and (mc.estatus_actual = 'NORMAL') and
                               (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                               (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                               (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                               (gt.subespecifica = dc.subespecifica) and
                               (mc.fecha <= '$fecha_cierre')))";
            $resultado07=modificar_data($sql07,$this);


// ***************** SE PROCESAN CAUSADOS APROBADOS
            $sql08 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt
                    SET gt.causado =
                       (select SUM(dc.monto) from presupuesto.compromiso_causado dc, presupuesto.maestro_causado mc
                        Where ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.tipo_documento = dc.tipo_documento_causado) and
                               (mc.numero = dc.numero_documento_causado) and (mc.estatus_actual = 'NORMAL') and
                               (gt.sector = dc.sector) and (gt.programa = dc.programa) and (gt.proyecto = dc.proyecto) and
                               (gt.actividad = dc.actividad) and (gt.partida = dc.partida) and
                               (gt.generica = dc.generica) and (gt.especifica = dc.especifica) and
                               (gt.subespecifica = dc.subespecifica) and
                               (mc.fecha <= '$fecha_cierre'))),
                        gt.disponible = gt.disponible - gt.comprometido";
            $resultado08=modificar_data($sql08,$this);


/*

// OJO, MUY IMPORTANTE FALTAN HACER LOS PAGOS!!!!!
//calcular pagos a la fecha seleccionada

*/

        

// ***************** SE ACTUALIZAN LOS MONTOS DISPONIBLES.

            $sql02 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal pg
                    SET pg.disponible = pg.asignado + pg.aumentos - pg.disminuciones - pg.comprometido
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion') and
                          (pg.es_retencion = 0))";
            $resultado02=modificar_data($sql02,$this);


// *******************  SE RECALCULAN LOS MONTOS ACUMULADOS
//  A falta de algo mejor, se utilizará una segunta tabla temporal para poder tomar los valores de ahi
// y pasarlos a los acumulados de la tabla que mostrará la ejecución.

//******************** SE ELIMINAN TODOS LOS DATOS DE LA TABLA TEMPORAL 2
        $sql1 = "truncate table presupuesto.presupuesto_gastos_ejecucion_temporal2";
        $resultado1=modificar_data($sql1,$this);

//******************** SE COPIAN LOS DATOS QUE SE ENCUENTRAN EN LA EJECUCION ACTUAL A LA TEMPORAL

        $sql01 = "insert into presupuesto.presupuesto_gastos_ejecucion_temporal2
                      (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                       generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, descripcion,
                       asignado, aumentos, disminuciones, comprometido, causado, pagado, disponible, acumulado, es_retencion)
                     select pg.cod_organizacion, pg.ano, pg.sector, pg.programa, pg.subprograma, pg.proyecto, pg.actividad, pg.partida,
                       pg.generica, pg.especifica, pg.subespecifica, pg.ordinal, pg.cod_fuente_financiamiento, pg.descripcion,
                       pg.asignado, pg.aumentos, pg.disminuciones, pg.comprometido, pg.causado, pg.pagado, pg.disponible, pg.acumulado, pg.es_retencion from presupuesto.presupuesto_gastos_ejecucion_temporal as pg
                     where pg.acumulado=0";
        $resultado01=modificar_data($sql01,$this);

//  SE ACTUALIZAN LOS DATOS ACUMULADOS DE LA TABLA TEMPORAL 1 CON LOS DATOS DE LA TEMPORAL 2


            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.asignado =
                          (select SUM(gt2.asignado) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector))),
                          gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector))),
                          gt1.disminuciones =
                          (select SUM(gt2.disminuciones) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector))),
                          gt1.comprometido =
                          (select SUM(gt2.comprometido) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector))),
                          gt1.causado =
                          (select SUM(gt2.causado) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector))),
                          gt1.pagado =
                          (select SUM(gt2.pagado) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector))),
                          gt1.disponible =
                          (select SUM(gt2.disponible) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector)))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);


            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector) and
                                 (gt2.programa = gt1.programa)))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = gt2.programa) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);



            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector) and
                                 (gt2.programa = gt1.programa) and
                                 (gt2.subprograma = gt1.subprograma) ))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = gt2.programa) and (gt1.subprograma = gt2.subprograma) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);

            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector) and
                                 (gt2.programa = gt1.programa) and
                                 (gt2.subprograma = gt1.subprograma) and
                                 (gt2.proyecto = gt1.proyecto) ))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = gt2.programa) and (gt1.subprograma = gt2.subprograma) and
                               (gt1.proyecto = gt2.proyecto) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);

            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector) and
                                 (gt2.programa = gt1.programa) and
                                 (gt2.subprograma = gt1.subprograma) and
                                 (gt2.proyecto = gt1.proyecto) and
                                 (gt2.actividad = gt1.actividad) ))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = gt2.programa) and (gt1.subprograma = gt2.subprograma) and
                               (gt1.proyecto = gt2.proyecto) and
                               (gt1.actividad = gt2.actividad) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);

            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector) and
                                 (gt2.programa = gt1.programa) and
                                 (gt2.subprograma = gt1.subprograma) and
                                 (gt2.proyecto = gt1.proyecto) and
                                 (gt2.actividad = gt1.actividad) and
                                 (gt2.partida = gt1.partida) ))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = gt2.programa) and (gt1.subprograma = gt2.subprograma) and
                               (gt1.proyecto = gt2.proyecto) and
                               (gt1.actividad = gt2.actividad) and (gt1.partida = gt2.partida) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);

            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector) and
                                 (gt2.programa = gt1.programa) and
                                 (gt2.subprograma = gt1.subprograma) and
                                 (gt2.proyecto = gt1.proyecto) and
                                 (gt2.actividad = gt1.actividad) and
                                 (gt2.partida = gt1.partida) and
                                 (gt2.generica = gt1.generica) ))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = gt2.programa) and (gt1.subprograma = gt2.subprograma) and
                               (gt1.proyecto = gt2.proyecto) and
                               (gt1.actividad = gt2.actividad) and (gt1.partida = gt2.partida) and
                               (gt1.generica = gt2.generica) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);

            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector) and
                                 (gt2.programa = gt1.programa) and
                                 (gt2.subprograma = gt1.subprograma) and
                                 (gt2.proyecto = gt1.proyecto) and
                                 (gt2.actividad = gt1.actividad) and
                                 (gt2.partida = gt1.partida) and
                                 (gt2.generica = gt1.generica) and
                                 (gt2.especifica = gt1.especifica)))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = gt2.programa) and (gt1.subprograma = gt2.subprograma) and
                               (gt1.proyecto = gt2.proyecto) and
                               (gt1.actividad = gt2.actividad) and (gt1.partida = gt2.partida) and
                               (gt1.generica = gt2.generica) and (gt1.especifica = gt2.especifica) and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);




/*
            $sql04 = "UPDATE presupuesto.presupuesto_gastos_ejecucion_temporal gt1, presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                      SET gt1.aumentos =
                          (select SUM(gt2.aumentos) from presupuesto.presupuesto_gastos_ejecucion_temporal2 gt2
                          Where ((gt2.cod_organizacion = '$cod_organizacion') and
                                 (gt2.ano = '$ano') and
                                 (gt2.sector = gt1.sector)))
                      WHERE ((gt1.cod_organizacion = '$cod_organizacion') and
                               (gt1.ano = '$ano') and (gt1.cod_organizacion = gt2.cod_organizacion) and
                               (gt1.ano = gt2.ano) and
                               (gt1.sector = gt2.sector) and
                               (gt1.programa = '00') and (gt1.subprograma = '00') and
                               (gt1.proyecto = '00') and
                               (gt1.actividad = '00') and (gt1.partida = '000') and
                               (gt1.generica = '00') and (gt1.especifica = '00') and
                               (gt1.subespecifica = '00000') and
                               (gt1.acumulado = 1))";
            $resultado04=modificar_data($sql04,$this);
*/


//   SE CONSULTA LA TABLA PARA GENERAR EL REPORTE EN PANTALLA
        $sql10="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado from presupuesto.presupuesto_gastos_ejecucion_temporal
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion') )
              order by codigo ".$limite;

        $resultado10=cargar_data($sql10,$this);
        $this->DataGrid->DataSource=$resultado10;
        $this->DataGrid->dataBind();


	}
    public function ver_ordenes()
	{
     /*   $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;

        if ($this->gasto->Checked)
            {
                $es_retencion = 0;
                $limite = " Limit 2,50000";
            }
        else
            {
                $es_retencion = 1;
                $limite = "";
            }
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion') )
              order by codigo".$limite;
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
        $this->btn_filtrar->Enabled="True";
        $this->btn_filtrar->IsDefaultButton="True";*/
	}


	public function consulta_filtrada($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;
        if ($this->gasto->Checked)
            {
                $es_retencion = 0;
                $limite = " Limit 2,50000";//pa q?
            }
        else
            {
                $es_retencion = 1;
                $limite = "";
            }
        $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') ";

        if ($codigo[generica]!='00')
        {
        $sql=$sql." and (generica = '$codigo[generica]')";
            if ($codigo[especifica]!='00')
            $sql=$sql." and (especifica = '$codigo[especifica]')";

        }
        //$sql=$sql." ) order by codigo".$limite; //pa q?
        $sql=$sql." ) order by codigo";
return $sql;
    }

	public function filtrar($sender, $param)
	{
        $sql=$this->consulta_filtrada($sender, $param);
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
        $this->filtrada->Text="1";
        $this->ver=True;
	}

    public function imprimir_listado($sender, $param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $ano = $this->drop_ano->SelectedValue;

        if ($this->gasto->Checked)
            {
                $es_retencion = 0;
                $limite = " Limit 2,50000";
            }
        else
            {
                $es_retencion = 1;
                $limite = "";
            }
        /*if ($this->filtrada->Text=="1")
        $sql=$this->consulta_filtrada($sender, $param);
        else*/
        $sql="select CONCAT(partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              descripcion, asignado, aumentos, disminuciones, (asignado+aumentos-disminuciones) as modificado,
              comprometido, causado, pagado, disponible, acumulado from presupuesto.presupuesto_gastos
              where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (es_retencion = '$es_retencion'))
              order by codigo".$limite;

        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('l', 'mm', 'legal', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Reporte de Ejecución Presupuestaria. \n".
                             "Reporte del Año: ".$ano.", a la fecha: ".date("d/m/Y");
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
            $pdf->SetAutoPageBreak(TRUE, 12);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Ejecución Presupuestaria.');
            $pdf->SetSubject('Reporte de Ejecución Presupuestaria de la Institución.');

            $pdf->AddPage();

            $listado_header = array('Código', 'Descripción', 'Asignado','Aumentos','Disminuciones','Modificado','Comprometido','Causado','Pagado','Disponible');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Ejecución Presupuestaria del año ".$ano, 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 10);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(28, 75, 28,28,28,28,28,28,28,28);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',8);
            // Data
            $fill = 1;
            $cual_fill_se_usa = 1;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row['acumulado'] == '1')
                {$pdf->SetFont('', 'B');} else {$pdf->SetFont('', '');}
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->SetTextColor(0,0,0); // se coloca en negro para la siguiente columna
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['descripcion'], $borde, 0, 'L', $fill);
                $pdf->SetTextColor(0,130,0);
                $pdf->Cell($w[2], 6, "Bs. ".number_format($row['asignado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[3], 6, "Bs. ".number_format($row['aumentos'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(230,20,20); // se coloca en rojo para la siguiente columna
                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['disminuciones'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0,0,0); // se coloca en negro para la siguiente columna
                $pdf->Cell($w[5], 6, "Bs. ".number_format($row['modificado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(230,20,20); // se coloca en rojo para la siguiente columna
                $pdf->Cell($w[6], 6, "Bs. ".number_format($row['comprometido'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[7], 6, "Bs. ".number_format($row['causado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[8], 6, "Bs. ".number_format($row['pagado'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0,0,0); // se coloca en negro para la siguiente columna
                $pdf->Cell($w[9], 6, "Bs. ".number_format($row['disponible'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $cual_fill_se_usa = !$cual_fill_se_usa;
                ($cual_fill_se_usa == 1)?$pdf->SetFillColor(224, 235, 255):$pdf->SetFillColor(255, 255, 255);
            }

            $pdf->Output("ejecucion_presupuesto_de_gastos_".$ano.".pdf",'D');
        }
    }


}

?>
