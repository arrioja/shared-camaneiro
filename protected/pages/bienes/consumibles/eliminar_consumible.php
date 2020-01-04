<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class eliminar_consumible extends TPage
{
    function onload($param)
    {
        $sql="select *, concat(ano, partida, generica, especifica, subespecifica) from bienes.consumibles";
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }
    function borrar($sender, $param)
    {
        $id=$sender->CommandParameter;
        $sql1="select *, concat(ano, partida, generica, especifica, subespecifica) from bienes.consumibles where(id='$id')";
        $resultado1=cargar_data($sql1, $this);
        $descripcion=$resultado1[0]['descripcion'];
        $sql2="delete from bienes.consumibles where(id='$id')";
        $resultado2=modificar_data($sql2, $sender);
        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "El usuario ".$login." ha eliminado ".$descripcion. " del grupo de consumibles";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.eliminar_consumible',array('id'=>$id)));
    }
}
?>
