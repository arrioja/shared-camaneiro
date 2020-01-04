<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class presupuesto_pagado extends TPage
{
    function onload($param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        //
        $anotemp=ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
        $ano=$anotemp[0]['ano'];
        //
        $tipo="OP";
        //
        $sql1="select m.id, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos, p.nombre
               from presupuesto.maestro_causado m, presupuesto.proveedores p
               where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
               (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor)) order by m.numero, m.fecha";
        $resultado1=cargar_data($sql1, $this);
        $this->dg1->DataSource=$resultado1;
        $this->dg1->dataBind();
    }
    function pagar_item($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $numero=$sender->CommandParameter[1];
        echo $id;
        $this->Response->Redirect( $this->Service->constructUrl('presupuesto.gastos.pagar',array('id'=>$id, 'numero'=>$numero)));
    }    
    /*function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $cod_organizacion = usuario_actual('cod_organizacion');              
              //
              $anotemp=ano_presupuesto(usuario_actual('cod_organizacion'),4,$this);
              $ano=$anotemp[0]['ano'];              
              //
              $tipo="OP";              
              //
              $sql1="select m.id, m.numero, m.fecha, m.monto_total, m.monto_pendiente, m.monto_reversos, p.nombre
                     from presupuesto.maestro_causado m, presupuesto.proveedores p
                     where ((m.cod_organizacion = '$cod_organizacion') and (m.ano = '$ano') and (m.tipo_documento = '$tipo') and
                     (p.cod_organizacion = m.cod_organizacion) and (p.cod_proveedor = m.cod_proveedor)) order by m.numero, m.fecha";
              $resultado1=cargar_data($sql1, $this);
              $this->dg1->DataSource=$resultado1;
              $this->dg1->dataBind();
              //
              /*$sql2="select * from presupuesto.bancos";
              $resultado2=cargar_data($sql2, $this);
              $this->ddl2->DataSource=$resultado2;
              $this->ddl2->dataBind();                                                       
          }          
    }*/    
    /*function cargar_cuentas($sender, $param)
    {
        $cod_banco=$this->ddl2->selectedvalue;
        $sql3="select * from presupuesto.bancos_cuentas where(cod_banco='$cod_banco')";
        $resultado3=cargar_data($sql3, $this);
        $this->ddl3->DataSource=$resultado3;
        $this->ddl3->dataBind();
    }*/
    /*function buscar_datos($sender, $param)
    {        
        $numero=$this->ddl1->selectedvalue;        
        $sql1="select mc.*, td.nombre as nombre_documento, p.rif, p.nombre
        from presupuesto.maestro_causado mc, presupuesto.proveedores p, presupuesto.tipo_documento td
        where (p.cod_proveedor = mc.cod_proveedor) and (p.cod_organizacion = mc.cod_organizacion) and
        (mc.tipo_documento = td.siglas) and (mc.cod_organizacion = td.cod_organizacion) and (mc.id = '$numero')";
        $resultado1=cargar_data($sql1, $this);
        $this->dp1->text=cambiaf_a_normal($resultado1[0]['fecha']);        
        $this->t2->text=$resultado1[0]['monto_total'];
        $this->t3->text=$resultado1[0]['monto_pendiente'];
        $this->t4->text=$resultado1[0]['motivo'];
        //print_r($resultado1);
    }*/
    /*function agregar_temporal($sender, $param)
    {
        $cheque=$this->t1->text;
        $banco=$this->ddl2->text;
        $cuenta=$this->ddl3->text;
        $detalle=$this->t6->text;
        $parcial=$this->t7->text;
        $debito=$this->t8->text;
        $credito=$this->t9->text;
        $partida=$this->t10->text;
        $subpartida=$this->t11->text;
        $sql="insert into presupuesto.temporal_presupuesto_pagado
        (banco, cuenta, cheque, detalle, parciales, debito, credito, partida, subpartida)
        values('$banco', '$cuenta', '$cheque', '$detalle', '$parcial', '$debito', '$credito', '$partida', '$subpartida')";
        $resultado=modificar_data($sql, $this);
        //
        //$sql2="select * from presupuesto.temporal_presupuesto_pagado";
        //$resultado2=cargar_data($sql2, $this);
        //$this->dg1->DataSource=$resultado2;
        //$this->dg1->dataBind();
    }*/
    /*function borrar_item_temporal($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        //echo $id;
        $sql1="delete from presupuesto.temporal_presupuesto_pagado where(id='$id')";
        $resultado1=modificar_data($sql1, $this);
        //
        //$sql2="select * from presupuesto.temporal_presupuesto_pagado";
        //$resultado2=cargar_data($sql2, $this);
        //$this->dg1->DataSource=$resultado2;
        //$this->dg1->dataBind();
    }*/    
}
?>
