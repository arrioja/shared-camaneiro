<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class entrada_documento extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->t1->text=date("d/m/Y");
            $this->t5->text=usuario_actual('login');
        }
    }
    public function guardar($sender, $param)
    {
        $f=$this->t1->text;        
        $fecha=cambiaf_a_mysql($f);        
        $numero=$this->t2->text;
        $a_quien=$this->t3->text;
        $quien_envia=$this->t4->text;
        $quien_recibe=$this->t5->text;
        $descripcion=$this->t6->text;
        $sql1="insert into entrada_salida_documentos.entrada_documento 
               (fecha_entrada, numero, a_quien, quien_envia, quien_recibe, descripcion)
               values('$fecha', '$numero', '$a_quien', '$quien_envia', '$quien_recibe', '$descripcion')";
        $resultado1=modificar_data($sql1, $sender);
        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "El usuario ".$this->t5->text." ha dado entrada a un documento";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);        
        $this->Response->Redirect( $this->Service->constructUrl('entrada_salida_documentos.entrada_documento'));
    }
}
?>
