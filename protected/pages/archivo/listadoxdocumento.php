<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class listadoxdocumento extends TPage
{
    function onload($param)
    {
        $sql="select distinct codigo from archivo.archivo_log order by codigo asc";
        $resultado=cargar_data($sql, $this);
        $this->ddl1->DataSource=$resultado;
		$this->ddl1->dataBind();        
    }
    function listar($sender, $param)
    {
        $cod=$this->ddl1->Text;
        $sql2="select * from archivo.archivo_log where(codigo='$cod')";
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->DataSource=$resultado2;
		$this->dg1->dataBind();
    }
}
?>
