<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class prestamodearchivo extends TPage
{
    function onload($param)
    {
        $sql="select codigo from archivo.archivo_desc";
        $resultado=cargar_data($sql, $this);
        $this->ddl1->DataSource=$resultado;
		$this->ddl1->dataBind();
        //
        $sql2="select concat(nombres,' ',apellidos) as nombrecompleto from organizacion.personas";
        $resultado2=cargar_data($sql2, $this);
        $this->ddl2->DataSource=$resultado2;
		$this->ddl2->dataBind();
    }
    function prestar($sender, $param)
    {
        $codigo=$this->ddl1->text;
        $nombrecompleto=$this->ddl2->text;
        $fecha=cambiaf_a_mysql(date("d/m/Y"));
        $sql="insert into archivo.archivo_log2 (codigo, nombrecompleto, fecha) values('$codigo', '$nombrecompleto', '$fecha')";
        $resultado=modificar_data($sql, $this);
    }
}
?>
