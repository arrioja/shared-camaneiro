<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class devoluciones extends TPage
{
    function onload($param)
    {        
        //$sql="select ce.*, d.nombre_completo from bienes.consumibles_entregados ce, organizacion.direcciones d where(ce.direccion_entrega= d.codigo) order by f_entrega desc";
        //$sql="select ce.*, d.nombre_completo, co.id as miid, co.costo
              //from bienes.consumibles_entregados ce, organizacion.direcciones d, bienes.consumibles co
              //where(ce.direccion_entrega= d.codigo and ce.id=co.id ) order by f_entrega desc";
        $sql="select ce.*, d.nombre_completo
              from bienes.consumibles_entregados ce, organizacion.direcciones d
              where(ce.direccion_entrega= d.codigo) order by f_entrega desc";
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }

    public function devol($sender, $param)
    {        
        $id=$sender->CommandParameter[0];
        $costo=$sender->CommandParameter[1];
        $regre=$sender->CommandParameter[2];
        $idvinculo=$sender->CommandParameter[3];
        $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.devolver',array('id'=>$id, 'costo'=>$costo, 'regre'=>$regre, 'idvinculo'=>$idvinculo)));
    }
}
?>