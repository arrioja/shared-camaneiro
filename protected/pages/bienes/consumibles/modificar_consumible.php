<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class modificar_consumible extends TPage
{
    function onload($param)
    {
        $sql="select *, concat(ano, partida, generica, especifica, subespecifica) from bienes.consumibles";
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }
    function modificar($sender, $param)
    {        
        $id=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.modifi_consumible',array('id'=>$id)));
    }
}
?>
