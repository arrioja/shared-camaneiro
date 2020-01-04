<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class salida_documento extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->t1->text=date("d/m/Y");
        }
    }
    public function guardar($sender, $param)
    {
        $f=$this->t1->text;
        $fecha=cambiaf_a_mysql($f);
        $a_quien=$this->t2->text;
        $quien_recibe=usuario_actual('login');
        $sql1="insert into entrada_salida_documentos.salida_documento (fecha_salida, a_quien, quien_recibe) values('$fecha', '$a_quien', '$quien_recibe')";
        $resultado1=modificar_data($sql1, $sender);
        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "El usuario ".$quien_recibe." ha dado salida a un documento";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        $this->Response->Redirect( $this->Service->constructUrl('entrada_salida_documentos.salida_documento'));
    }
}
?>
