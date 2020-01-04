<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class pagar extends TPage
{
    function onload($param)
    {
        parent::onLoad($sender, $param);
        if(!$this->IsPostBack)
        {
            $this->t13->text=aleatorio_pagado($this);
            $id=$this->Request['id'];
            $numero=$this->Request['numero'];
            $cod_organizacion = usuario_actual('cod_organizacion');
            $anotemp=ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
            $ano=$anotemp[0]['ano'];
            $tipo="OP";
            //
            $sql1="select mc.*, td.nombre as nombre_documento, p.rif, p.nombre
                   from presupuesto.maestro_causado mc, presupuesto.proveedores p,
                   presupuesto.tipo_documento td
                   where (p.cod_proveedor = mc.cod_proveedor) and (p.cod_organizacion = mc.cod_organizacion) and
                   (mc.tipo_documento = td.siglas) and (mc.cod_organizacion = td.cod_organizacion) and (mc.id = '$id') and (mc.monto_pendiente>0)";//monto pendiente >0 pa saber q no c ha terminado de pagar
            $resultado1=cargar_data($sql1, $this);
            $this->t1->text=$resultado1[0]['numero'];
            $this->t2->text=$resultado1[0]['nombre'];
            $this->dp1->text=cambiaf_a_normal($resultado1[0]['fecha']);
            $this->t4->text=$resultado1[0]['monto_total'];
            $this->t5->text=$resultado1[0]['monto_pendiente'];
            $this->t6->text=$resultado1[0]['motivo'];
            //
            $sql2="select * from presupuesto.bancos";
            $resultado2=cargar_data($sql2, $this);
            $this->ddl1->DataSource=$resultado2;
            $this->ddl1->dataBind();
            //
            $sql3="truncate table presupuesto.temporal_presupuesto_pagado";
            $resultado2=modificar_data($sql3, $this);
            //
            $sql4="insert presupuesto.temporal_presupuesto_pagado (descripcion, codigo, monto)
                   select descripcion,
                   concat(ppg.sector,'-',ppg.programa,'-',ppg.subprograma,'-',ppg.proyecto,'-',ppg.actividad,'-',ppg.partida,'-', ppg.generica,'-',ppg.especifica,'-',ppg.subespecifica,'-',ppg.ordinal) as codigo, monto
                   from presupuesto.compromiso_causado as pcc
                   join presupuesto.presupuesto_gastos as ppg
                   on((ppg.sector=pcc.sector)and(ppg.programa=pcc.programa)and(ppg.subprograma=pcc.subprograma)and(ppg.proyecto=pcc.proyecto)and(ppg.actividad=pcc.actividad)and(ppg.partida=pcc.partida)and(ppg.generica=pcc.generica)and(ppg.especifica=pcc.especifica)and(ppg.subespecifica=pcc.subespecifica))
                   where ((ppg.cod_organizacion = '$cod_organizacion') and (ppg.ano = '$ano') and (numero_documento_causado ='$numero') and (tipo_documento_causado = '$tipo'))";
            $resultado4=modificar_data($sql4, $this);
            //
            $sql5="select * from presupuesto.temporal_presupuesto_pagado";
            $resultado5=cargar_data($sql5, $this);
            $this->dg1->DataSource=$resultado5;
            $this->dg1->dataBind();
            //
            $sql6="select *, concat(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',generica,'-',especifica,'-',subespecifica,'-',ordinal)as codigo
                   from presupuesto.presupuesto_gastos where(es_retencion='1')";
            $resultado6=cargar_data($sql6, $this);
            $this->ddl3->DataSource=$resultado6;
            $this->ddl3->dataBind();
            //
            $aleatorio=$this->t13->text;
            $sql7="update presupuesto.temporal_presupuesto_pagado set aleatorio='$aleatorio' where(aleatorio='0')";
            $resultado7=modificar_data($sql7, $this);
        }
    }
    function cargar_cuentas($sender, $param)
    {
        $cod_banco=$this->ddl1->selectedvalue;
        $sql3="select * from presupuesto.bancos_cuentas where(cod_banco='$cod_banco')";
        $resultado3=cargar_data($sql3, $this);
        $this->ddl2->DataSource=$resultado3;
        $this->ddl2->dataBind();
    }
    function agregar_temporal($sender, $param)
    {
        $aleatorio=$this->t13->text;
        $cheque=$this->t3->text;        
        $banco=$this->ddl1->text;
        $cuenta=$this->ddl2->text;
        $observaciones=$this->t7->text;
        $detalle=$this->t8->text;
        $parciales=$this->t9->text;
        $debito=$this->t10->text;
        $credito=$this->t11->text;
        $monto_total=$this->t4->text;
        $monto_pendiente=$this->t5->text;
        $monto_retencion=$this->t12->text;
        $codigo=$this->ddl3->selectedvalue;        
        $textodrop=obtener_seleccion_drop($this->ddl3);
        $descripcion=$textodrop[2];        
        $sql1="insert into presupuesto.temporal_presupuesto_pagado 
               (aleatorio, descripcion, codigo, monto)
               values
               ('$aleatorio' ,'$descripcion', '$codigo', '$monto_retencion')";
        $resultado1=modificar_data($sql1, $this);  
        //
        $sql2="select * from presupuesto.temporal_presupuesto_pagado";
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->DataSource=$resultado2;
        $this->dg1->dataBind();
    }
    function borrar_item_temporal($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $sql1="delete from presupuesto.temporal_presupuesto_pagado where(id='$id')";
        $resultado1=modificar_data($sql1, $this);
        //
        $sql2="select * from presupuesto.temporal_presupuesto_pagado";
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->DataSource=$resultado2;
        $this->dg1->dataBind();
    }    
}
?>
