<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class modificar_denuncia extends TPage
{
     public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            $sql1="select * from denuncias.denuncia order by numero,fecha DESC";
            $resultado1=cargar_data($sql1, $this);
            $this->dg1->DataSource=$resultado1;
            $this->dg1->dataBind();
        }
    }
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);            
        }
    }
    function editar_seleccion($sender, $param)
    {
        $id=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('denuncias.editar',array('id'=>$id)));
    }
}
?>
