<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M. recalcular_presupuesto_gasto
 * Descripción: Este Procedimiento se encarga de recalcular el presupuesto de gastos 
 *****************************************************  FIN DE INFO
*/
class recalcular_presupuesto_gasto extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $cod_organizacion = usuario_actual('cod_organizacion');
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');

              // coloca el año presupuestario actual que el la base de datos es el "Presente"
              $ano = ano_presupuesto($cod_organizacion,1,$this);
              $this->lbl_ano->Text = $ano[0]['ano'];
              
          }
    }

/* Esta función se encarga de generar todos los codigos presupuestarios acumulados
 * del codigo presupuestario pasado como parámetro. Si el codigo no existe, se crea
 * y si existe, se acumulan sus valores.
 */
    public function generar_acumulados_presupuesto_gasto($cod,$monto,$retencion)
    {
       $cod_organizacion = usuario_actual('cod_organizacion');
       $ano = $this->drop_ano->selectedValue;
        for ($xcod = 9 ; $xcod >= 1 ; $xcod--)
        {
            switch($xcod)
            {
                case 1:
                    $cod['programa']="00";
                    break;
                case 2:
                    $cod['subprograma']="00"; 
                    break;
                case 3:
                    $cod['proyecto']="00";
                    break;
                case 4:
                    $cod['actividad']="00";
                    break;
                case 5:
                    $cod['partida']="000";
                    break;
                case 6:
                    $cod['generica']="00";
                    break;
                case 7:
                    $cod['especifica']="00";
                    break;
                case 8:
                    $cod['subespecifica']="00";
                    break;
                case 9:
                    $cod['ordinal']="00000";
                    break;
            }

           $sql = "select * from presupuesto.presupuesto_gastos
                        where ((cod_organizacion = '$cod_organizacion') and
                                (ano = '$ano') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                               (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and 
                               (ordinal = '$cod[ordinal]'))";
           $existe = cargar_data($sql,$this);
           $id = $existe[0]['id'];
     
           if (empty($existe) == true)
           {
               // si el codigo no existe, se incluyen los acumulados de todos los codigos
                $sql2 = "insert into presupuesto.presupuesto_gastos
                        (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                         generica, especifica, subespecifica, ordinal, asignado, disponible, descripcion, acumulado, es_retencion)
                        values ('$cod_organizacion','$ano','$cod[sector]','$cod[programa]','$cod[subprograma]','$cod[proyecto]',
                                '$cod[actividad]','$cod[partida]','$cod[generica]','$cod[especifica]',
                                '$cod[subespecifica]','$cod[ordinal]','$monto','$monto','$descripcion','1','$retencion')";
                $resultado=modificar_data($sql2,$this);
                $sql3 = "select max(id) as id from presupuesto.presupuesto_gastos";
                $result_id = cargar_data($sql3,$this);
                $id_incluido = $result_id[0]['id'];
           }
           else
           {
               if (($existe[0]['acumulado'] == '1') && ($id_acumulado != $id) && ($id_incluido != $id))
               {
                   $id_acumulado = $id;
                   // si el codigo ya existe, se suman los acumulados en dicho código.
                   $sql2 = "update presupuesto.presupuesto_gastos set
                           asignado = asignado+'$monto', disponible = disponible+'$monto' where id = '$id'";
                   $resultado=modificar_data($sql2,$this);
               }
           }
        }
    }




    function generar_acumulados_presupuesto_gasto2($ano,$cod_organizacion,$cod,$monto,$retencion)
    {
     //  $cod_organizacion = usuario_actual('cod_organizacion');
     //  $ano = $this->drop_ano->selectedValue;
     $existe = 'Valor de Inicio';
        for ($xcod = 9 ; $xcod >= 1 ; $xcod--)
        {
            switch($xcod)
            {
                case 1:
                    $cod['programa']="00";
                    break;
                case 2:
                    $cod['subprograma']="00";
                    break;
                case 3:
                    $cod['proyecto']="00";
                    break;
                case 4:
                    $cod['actividad']="00";
                    break;
                case 5:
                    $cod['partida']="000";
                    break;
                case 6:
                    $cod['generica']="00";
                    break;
                case 7:
                    $cod['especifica']="00";
                    break;
                case 8:
                    $cod['subespecifica']="00";
                    break;
                case 9:
                    $cod['ordinal']="00000";
                    break;
            }

           $sql = "select * from presupuesto.presupuesto_gastos
                        where ((cod_organizacion = '$cod_organizacion') and
                                (ano = '$ano') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                               (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                               (ordinal = '$cod[ordinal]'))";
           $existe = cargar_data($sql,$this);
           $id = $existe[0]['id'];

           if (empty($existe) == true)
           {
               // si el codigo no existe, se incluyen los acumulados de todos los codigos
                $sql2 = "insert into presupuesto.presupuesto_gastos
                        (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                         generica, especifica, subespecifica, ordinal, asignado, disponible, descripcion, acumulado, es_retencion)
                        values ('$cod_organizacion','$ano','$cod[sector]','$cod[programa]','$cod[subprograma]','$cod[proyecto]',
                                '$cod[actividad]','$cod[partida]','$cod[generica]','$cod[especifica]',
                                '$cod[subespecifica]','$cod[ordinal]','$monto','$monto','$descripcion','1','$retencion')";
                $resultado=modificar_data($sql2,$this);
                $sql3 = "select max(id) as id from presupuesto.presupuesto_gastos";
                $result_id = cargar_data($sql3,$this);
                $id_incluido = $result_id[0]['id'];
           }
           else
           {
               if (($existe[0]['acumulado'] == '1') && ($id_acumulado != $id) && ($id_incluido != $id))
               {
                   $id_acumulado = $id;
                   // si el codigo ya existe, se suman los acumulados en dicho código.
                   $sql2 = "update presupuesto.presupuesto_gastos set
                           asignado = asignado+'$monto', disponible = disponible+'$monto' where id = '$id'";
                   $resultado=modificar_data($sql2,$this);
               }
           }
        }
    }



    function recalcular_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // se capturan los valores de los controles
            $ano = $this->lbl_ano->Text;
            $cod_organizacion = usuario_actual('cod_organizacion');
//            $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
            $es_retencion = 0;

// *******************  SE ASIGNAN LOS VALORES INICIALES
//            0.- Se limpian todos los valores a cero, primero se eliminan todos los valores de las columnas

            $sql = "UPDATE presupuesto.presupuesto_gastos pg
                    SET pg.aumentos = 0,
                        pg.disminuciones = 0,
                        pg.comprometido = 0,
                        pg.causado = 0,
                        pg.pagado = 0,
                        pg.disponible = pg.asignado
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion') and
                          (pg.es_retencion = 0))";
            $resultado=modificar_data($sql,$sender);

//          y luego se eliminan las asignaciones de los acumulados.

            $sql = "UPDATE presupuesto.presupuesto_gastos pg
                    SET pg.asignado = 0,
                        pg.disponible = 0
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion') and
                          (pg.es_retencion = 0) and (pg.acumulado = 1))";
            $resultado=modificar_data($sql,$sender);




// *******************  SE RECALCULAN LOS MONTOS ACUMULADOS DE LAS ASIGNACIONES INICIALES
// Se genera un listado de los montos no acumulados
           $sql = "select * from presupuesto.presupuesto_gastos pg
                        where ((pg.cod_organizacion = '$cod_organizacion') and
                                (pg.ano = '$ano') and (pg.es_retencion = 0) and (pg.acumulado = 0))";
           $datos = cargar_data($sql,$sender);

// se recorre el listado y se le va asignando los acumulados a cada uno de los códigos.

            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $monto = $undato['asignado'];                 
                    $cod = descomponer_codigo_gasto($codi);
                    $this->generar_acumulados_presupuesto_gasto2($ano,$cod_organizacion,$cod,$monto,$es_retencion);
                }
            }

            $this->lbl_valores_iniciales->Text = "OK";

// *******************  SE REALIZAN LOS MOVIMIENTOS EN LOS CÓDIGOS PRESUPUESTARIOS
//                      CREDITOS ADICIONALES, TRANSFERENCIAS Y REBAJAS
// Se Procesan los créditos adicionales del año
           $sql = "select * from presupuesto.detalle_creditos pg
                        where ((pg.cod_organizacion = '$cod_organizacion') and
                                (pg.ano = '$ano'))";
           $datos = cargar_data($sql,$sender);

            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'aumentos', '+', $undato['monto_aumento'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '+', $undato['monto_aumento'], $this);
                }
            }

// Se Procesan las transferencias del año
           $sql = "select * from presupuesto.detalle_traslados pg
                        where ((pg.cod_organizacion = '$cod_organizacion') and
                                (pg.ano = '$ano'))";
           $datos = cargar_data($sql,$sender);

            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $cod = descomponer_codigo_gasto($codi);

                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'aumentos', '+', $undato['monto_aumento'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disminuciones', '+', $undato['monto_disminucion'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '+', $undato['monto_aumento'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '-', $undato['monto_disminucion'], $this);
                }
            }
            

// Se Procesan los créditos adicionales del año
           $sql = "select * from presupuesto.detalle_recortes pg
                        where ((pg.cod_organizacion = '$cod_organizacion') and
                                (pg.ano = '$ano'))";
           $datos = cargar_data($sql,$sender);

            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disminuciones', '+', $undato['monto_disminucion'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '-', $undato['monto_disminucion'], $this);
                }
            }

            $this->lbl_movimientos->Text = "OK";


// ***************** SE PROCESAN COMPROMISOS APROBADOS

// Se asignan los datos de los compromisos a sus valores iniciales
            $sql = "UPDATE presupuesto.detalle_compromisos pg
                    SET pg.monto_pendiente = pg.monto_parcial-monto_reversos
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion'))";
            $resultado=modificar_data($sql,$sender);

            $sql = "UPDATE presupuesto.maestro_compromisos pg
                    SET pg.monto_pendiente = pg.monto_total-monto_reversos
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion'))";
            $resultado=modificar_data($sql,$sender);
            

           $sql = "select * from presupuesto.detalle_compromisos dc, presupuesto.maestro_compromisos mc
                        where ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.tipo_documento = dc.tipo_documento) and
                               (mc.numero = dc.numero) and (mc.estatus_actual = 'NORMAL')) ";
           $datos = cargar_data($sql,$sender);

            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'comprometido', '+', $undato['monto_parcial'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '-', $undato['monto_parcial'], $this);
                }
            }


            $this->lbl_compromisos->Text = "OK";


// ***************** SE PROCESAN LAS ORDENES QUE CAUSAN AL PRESUPUESTO APROBADAS

// Se asignan los datos de los causados a sus valores iniciales
            $sql = "UPDATE presupuesto.compromiso_causado pg
                    SET pg.monto_pendiente = pg.monto
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion'))";
            $resultado=modificar_data($sql,$sender);

            $sql = "UPDATE presupuesto.maestro_causado pg
                    SET pg.monto_pendiente = pg.monto_total
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion'))";
            $resultado=modificar_data($sql,$sender);

           $sql = "select * from presupuesto.compromiso_causado dc, presupuesto.maestro_causado mc
                        where ((dc.cod_organizacion = '$cod_organizacion') and
                               (dc.ano = '$ano') and (mc.cod_organizacion = dc.cod_organizacion) and
                               (mc.ano = dc.ano) and (mc.tipo_documento = dc.tipo_documento_causado) and
                               (mc.numero = dc.numero_documento_causado) and (mc.estatus_actual = 'NORMAL')) ";
           $datos = cargar_data($sql,$sender);

            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'causado', '+', $undato['monto'], $this);
                    modificar_montos_pendientes('CO', $cod, $cod_organizacion, $ano, $undato['tipo_documento_compromiso'], $undato['numero_documento_compromiso'], $undato['cod_proveedor'], $undato['monto'], $this);
                }
            }



            $this->lbl_causados->Text = "OK";


// ***************** SE PROCESAN LOS CHEQUES EMITIDOS
// Se asignan los datos de los causados a sus valores iniciales
            $sql = "UPDATE presupuesto.compromiso_causado pg
                    SET pg.monto_pendiente = pg.monto
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion'))";
            $resultado=modificar_data($sql,$sender);

            $sql = "UPDATE presupuesto.maestro_causado pg
                    SET pg.monto_pendiente = pg.monto_total
                    WHERE (pg.ano = '$ano' and (pg.cod_organizacion = '$cod_organizacion'))";
            $resultado=modificar_data($sql,$sender);

           $sql = "select * from presupuesto.detalle_pagado dp, presupuesto.maestro_pagado mp
                        where ((dp.cod_organizacion = '$cod_organizacion') and
                               (dp.ano = '$ano') and (mp.cod_organizacion = dp.cod_organizacion) and
                               (mp.ano = dp.ano) and (mp.tipo_documento = 'CH') and
                               (dp.es_retencion = 0) and
                               (mp.numero = dp.numero_documento_pagado) and (mp.estatus_actual = 'NORMAL')) ";
           $datos = cargar_data($sql,$sender);
// OJO Modificación Tipo de Documento Pagado!!
//(mp.ano = dp.ano) and (mp.tipo_documento = dp.tipo_documento_pagado) and
            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'pagado', '+', $undato['monto'], $this);
                    modificar_montos_pendientes('CA', $cod, $cod_organizacion, $ano, $undato['tipo_documento_causado'], $undato['numero_documento_causado'], 0, $undato['monto'], $this);
                }
            }

            $this->lbl_Pagados->Text = "OK";

// Se Procesan las anulaciones de los compromisos
           $sql = "select * from presupuesto.detalle_compromisos pg
                        where ((pg.cod_organizacion = '$cod_organizacion') and
                                (pg.ano = '$ano') and (monto_reversos > 0))";
           $datos = cargar_data($sql,$sender);

            if (empty($datos) == false)
            {
                // ciclo para modificar los acumulados
                foreach ($datos as $undato)
                {
                    $codi= $undato['sector'].$undato['programa'].$undato['subprograma'].
                           $undato['proyecto'].$undato['actividad'].$undato['partida'].
                           $undato['generica'].$undato['especifica'].$undato['subespecifica'].
                           $undato['ordinal'];

                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'comprometido', '-', $undato['monto_reversos'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '+', $undato['monto_reversos'], $this);
                }
            }

            $this->lbl_reversos->Text = "OK";


            // se inserta en la base de datos
/*            $sql = "insert into presupuesto.presupuesto_gastos
                    (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                     generica, especifica, subespecifica, ordinal, asignado, disponible, descripcion, es_retencion)
                    values ('$cod_organizacion','$ano','$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                            '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                            '$codigo[subespecifica]','$codigo[ordinal]','$monto','$monto','$descripcion','$es_retencion')";
            $resultado=modificar_data($sql,$sender);
            $this->generar_acumulados_presupuesto_gasto($codigo,$monto,$es_retencion);
*/
            /* Se incluye el rastro en el archivo de bitácora */
//            $descripcion_log = "Insertado Presupuesto de Gasto. /año:".$ano." /Cod: ".$this->txt_codigo->Text." /Desc: ".$descripcion." /Monto: ".$monto;
//            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            // para asegurarme de autorecargar la pagina hago un llamado a ella misma.
//            $this->Response->redirect($this->Service->constructUrl($this->Page->getPagePath()));
        }

    }
}
?>
