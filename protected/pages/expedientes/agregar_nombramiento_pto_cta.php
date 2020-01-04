<?php
class agregar_nombramiento_pto_cta extends TPage
{
   public function agregar($sender,$param)
   {
      $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
   }
   function cargar($sender, $param)
   {
       if($sender->HasFile)
         $cedula=$this->Request['cedula'];
         $ruta="expedientes/".$cedula."/";//ruta donde se guardan los adjuntos que se han subido
         $this->t4->text=$this->t4->text."$ruta$sender->FileName"."\n";
         $sender->saveAs($ruta.$sender->FileName);
   }
   function guardar($sender, $param)
   {      
      $de=$this->t1->text;
      $para=$this->t2->text;
      $asunto=$this->t3->text;
      $fecha=cambiaf_a_mysql($this->fecha1->text);
      $cedula=$this->Request['cedula'];
      $documento=$this->t4->text;
      $sql1="insert into expedientes.nombramientos_ptos_cta(de, para, asunto, fecha, cedula, documento) values('$de', '$para', '$asunto', '$fecha', '$cedula', '$documento')";
      $resultado1=modificar_data($sql1, $sender);
      //$this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
      $cedula=$this->Request['cedula'];
      $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
   }
}
?>