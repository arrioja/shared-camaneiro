<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class modifi_consumible extends TPage
{
    public function onLoad($param)
	{
         parent::onLoad($sender, $param);
         if(!$this->IsPostBack)
         {
            $cadena=$this->Request['id'];
            $this->oculto->text=$cadena;
            $sql="select * from bienes.consumibles where(id='$cadena')";
            $resultado=cargar_data($sql, $this);
            $this->t1->text=$resultado[0]['ano'];
            $this->t2->text=$resultado[0]['partida'];
            $this->t3->text=$resultado[0]['generica'];
            $this->t4->text=$resultado[0]['especifica'];
            $this->t5->text=$resultado[0]['subespecifica'];
            $this->t6->text=$resultado[0]['ref_producto'];
            $this->t7->text=$resultado[0]['descripcion'];
            $this->t8->text=$resultado[0]['marca'];
            $this->t9->text=$resultado[0]['unidad'];
            $this->t10->text=$resultado[0]['costo'];
            $this->t11->text=$resultado[0]['minimo'];
            $this->t12->text=$resultado[0]['maximo'];
            $this->t13->text=$resultado[0]['actual'];
            $this->t14->text=$resultado[0]['alerta'];
            $this->t15->text=$resultado[0]['proveedor'];
        }
    }
    public function guardar($sender, $param)
    {
        if ($this->IsValid)
        {
        $id=$this->oculto->text;//guardo el id del registro en un campo oculto en la forma
        $ano=$this->t1->text;
        $partida=$this->t2->text;
        $generica=$this->t3->text;
        $especifica=$this->t4->text;
        $subespecifica=$this->t5->text;
        $ref_producto=$this->t6->text;
        $descripcion=$this->t7->text;
        $marca=$this->t8->text;
        $unidad=$this->t9->text;
        $costo=$this->t10->text;
        $maximo=$this->t11->text;
        $minimo=$this->t12->text;
        $actual=$this->t13->text;
        $alerta=$this->t14->text;
        $proveedor=$this->t15->text;
        $sql2="update bienes.consumibles set
        ano='$ano', partida='$partida', generica='$generica', especifica='$especifica', subespecifica='$subespecifica',
        ref_producto='$ref_producto', descripcion='$descripcion', marca='$marca', unidad='$unidad', costo='$costo',
        maximo='$maximo', minimo='$minimo', actual='$actual', alerta='$alerta', proveedor='$proveedor'
        where(id='$id')";
        $resultado2=modificar_data($sql2, $sender);
        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "El usuario ".$login." ha modificado el artículo ".$descripcion." en el grupo de consumibles";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        }
    }    
}
?>