<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class entregar_consumible extends TPage
{
    function onload($param)
    {
        //$sql="select descripcion, sum(actual) from bienes.consumibles group by descripcion";
        //$sql="select *, sum(actual) from bienes.consumibles group by idvinculo";
        $sql="select * from bienes.consumibles_corto";
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }
    public function despachar($sender, $param)
    {
        $idvinculo=$sender->CommandParameter[0];
        $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.despachar',array('idvinculo'=>$idvinculo)));
    }
    public function comprobar_link($sender, $param)
	{
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {            
            if($item->total->text == '0' )
            {                
                $item->column_item->link->Visible = "False";
            }
            else
            {
                $item->column_item->link->Visible = "True";
            }
        }
    }
}
?>
