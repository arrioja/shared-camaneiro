<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class incluir_consumible extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->fecha1->text=date("d/m/Y");
        }
    }    
    public function agregar($sender, $param)
    {
        if ($this->IsValid)
        {
            $ano=$this->t1->text;            
            $partida=$this->t2->text;
            $generica=$this->t3->text;
            $especifica=$this->t4->text;
            $subespecifica=$this->t5->text;
            $referencia=$this->t6->text;
            $descripcion=$this->t7->text;
            $marca=$this->t8->text;
            $unidad=$this->t9->text;
            $costo=$this->t10->text;
            $maximo=$this->t12->text;
            $actual=$this->t16->text;
            $alerta=$this->t13->text;
            $proveedor=$this->t14->text;            
            $fecha_incluir=cambiaf_a_mysql($this->fecha1->text);            
            $minimo=$this->t11->text;
            //esta consulta verifica la existencia del producto en la db
            $sql="select * from bienes.consumibles where(descripcion='$descripcion' and marca='$marca' and unidad='$unidad' and proveedor='$proveedor' and ref_producto='$referencia' and ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and subespecifica='$subespecifica')";
            $resultado=cargar_data($sql, $sender);
            $cod=$resultado[0]['id'];
            $idvinculo8=$resultado[0]['idvinculo'];            
            if($cod=='')//si no existe el producto inserto un registro vacio en t.maestro luego inserto en la t.detalles y luego actualizo t.maestro con t.detalles
            {
                $sql2='insert into bienes.consumibles_corto() values()';
                $resultado2=modificar_data($sql2, $sender);
                $sql3="select id from bienes.consumibles_corto order by id desc limit 1";
                $resultado3=cargar_data($sql3, $sender);
                $idvinculo=$resultado3[0]['id'];
                $sql4="insert into bienes.consumibles (descripcion, marca, unidad, costo, maximo ,minimo, actual, alerta, proveedor, fecha, ref_producto, ano, partida, generica, especifica, subespecifica, idvinculo )
                values('$descripcion', '$marca', '$unidad', '$costo', '$maximo', '$minimo', '$actual', '$alerta', '$proveedor', '$fecha_incluir', '$referencia', '$ano', '$partida', '$generica', '$especifica', '$subespecifica', '$idvinculo')";
                $resultado4=modificar_data($sql4, $sender);
                $sql5="select *, sum(actual) from bienes.consumibles where(descripcion='$descripcion' and marca='$marca' and unidad='$unidad' and proveedor='$proveedor' and ref_producto='$referencia' and ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and subespecifica='$subespecifica')";
                $resultado5=cargar_data($sql5, $sender);
                $subtotal5=$resultado5[0]['sum(actual)'];
                $idvinculo5=$resultado5[0]['idvinculo'];
                $descripcion5=$resultado5[0]['descripcion'];
                $sql6="update bienes .consumibles_corto set total='$subtotal5', descripcion='$descripcion5', idvinculo='$idvinculo5' where(id='$idvinculo5')";
                $resultado6=modificar_data($sql6, $sender);
            }
            if($cod<>'')// si existe el producto solo inserto en t.detalles y actualizo el registro correspondiente en t.maestro
            {
                $sql7="insert into bienes.consumibles (ref_producto, descripcion, marca, unidad, costo, maximo ,minimo, actual, alerta, proveedor, fecha, ano, partida, generica, especifica, subespecifica, idvinculo )
                values('$referencia', '$descripcion', '$marca', '$unidad', '$costo', '$maximo', '$minimo', '$actual', '$alerta', '$proveedor', '$fecha_incluir', '$ano', '$partida', '$generica', '$especifica', '$subespecifica', '$idvinculo')";
                $resultado7=modificar_data($sql7, $sender);
                $sql8="update bienes.consumibles set idvinculo='$idvinculo8' where(idvinculo='')";
                $resultado8=modificar_data($sql8,$sender);
                $sql9="select sum(actual) from bienes.consumibles where(descripcion='$descripcion' and marca='$marca' and unidad='$unidad' and proveedor='$proveedor' and ref_producto='$referencia' and ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and subespecifica='$subespecifica')";
                $resultado9=cargar_data($sql9, $sender);
                $subtotal9=$resultado9[0]['sum(actual)'];
                $sql10="update bienes.consumibles_corto set total='$subtotal9' where(idvinculo='$idvinculo8')";
                $resultado10=modificar_data($sql10, $sender);                
            }
            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "El usuario ".$login." ha incluido ".$actual." ".$descripcion." en el grupo de consumibles";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.incluir_consumible'));
        }
    }    
}
?>
