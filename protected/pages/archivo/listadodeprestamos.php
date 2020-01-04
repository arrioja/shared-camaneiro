<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class listadodeprestamos extends TPage
{
    function onload($param)
    {
        $sql="select * from archivo.archivo_log2";
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }
}
?>
