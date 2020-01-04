<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class listadoxfecha extends TPage
{
    function listar($sender, $param)
    {
        $fechaini=cambiaf_a_mysql($this->dp_ini->text);
        $fechafin=cambiaf_a_mysql($this->dp_fin->text);
        $sql="select * from archivo.archivo_log where(fecha2>='$fechaini' and fecha2<='$fechafin')";
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }
}
?>
