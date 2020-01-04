<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class agregar_existente extends TPage
{
    function onload($param)
    {
        $sql="select * from bienes.consumibles_corto";        
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }
    public function comprobar_link($sender, $param)
	{
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            if($item->total->text == '0' )
            {
                $item->column_item->link->Visible = "true";
            }
            else
            {
                $item->column_item->link->Visible = "True";
            }
        }
    }
    public function redirigir($sender, $param)
    {
        $id=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('bienes.consumibles.agregar',array('vinculo'=>$id)));
    }
}
?>
