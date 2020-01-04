<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class listado extends TPage
{
    function onload($param)
    {
        $sql="select distinct ubicacion from archivo.archivo_desc";
        $resultado=cargar_data($sql, $this);
        $this->ddl1->DataSource=$resultado;
		$this->ddl1->dataBind();        
    }
    function listar($sender, $param)
    {
        
    }
}
?>
