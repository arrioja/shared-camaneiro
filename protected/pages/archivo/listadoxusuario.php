<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class listadoxusuario extends TPage
{
    function onload($param)
    {
        $sql="select distinct usuario from archivo.archivo_log order by usuario asc";
        $resultado=cargar_data($sql, $this);
        $this->ddl1->DataSource=$resultado;
		$this->ddl1->dataBind();        
    }
    function listar($sender, $param)
    {
        $usuario=$this->ddl1->Text;
        $sql2="select * from archivo.archivo_log where(usuario='$usuario')";
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->DataSource=$resultado2;
		$this->dg1->dataBind();
    }
}
?>
