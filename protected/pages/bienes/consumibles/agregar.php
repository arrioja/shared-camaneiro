<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class agregar extends TPage
{
    public function onLoad($param)
	{
         parent::onLoad($sender, $param);
         if(!$this->IsPostBack)
         {
            $this->fecha1->text=date("d/m/Y");
            $cadena=$this->Request['vinculo'];
            $this->oculto->text=$cadena;
            $sql="select * from bienes.consumibles where(idvinculo='$cadena')";
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
            $idvinculo=$this->Request['vinculo'];
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
            $fecha=cambiaf_a_mysql($this->fecha1->text);
            //inserta ubn registro nuevo en consumibles
            $sql1="insert into bienes.consumibles
            (descripcion, marca, unidad, costo, maximo, minimo, actual, alerta, proveedor, fecha, ref_producto, ano, partida, generica, especifica, subespecifica, idvinculo)
            values
            ('$descripcion', '$marca', '$unidad', '$costo', '$maximo', '$minimo', '$actual', '$alerta', '$proveedor', '$fecha', '$ref_producto', '$ano', '$partida', '$generica', '$especifica', '$subespecifica', '$idvinculo')";
            $resultado1=modificar_data($sql1, $sender);
            //recalcula la suma del producto
            $sql2="select sum(actual) from bienes.consumibles where(idvinculo='$idvinculo')";
            $resultado2=cargar_data($sql2, $sender);
            $nuevo_total=$resultado2[0]['sum(actual)'];
            //
            $sql3="update bienes.consumibles_corto set total='$nuevo_total' where(idvinculo='$idvinculo')";
            $resultado3=modificar_data($sql3, $sender);
            //redirige la pagina a agregar_existente
            $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.agregar_existente'));
        }
    }
}
?>
