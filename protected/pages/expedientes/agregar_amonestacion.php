<?php
class agregar_amonestacion extends TPage
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
         $this->t5->text=$this->t5->text."$ruta$sender->FileName"."\n";
         $sender->saveAs($ruta.$sender->FileName);
   }
   function guardar($sender, $param)
   {
      $cedula=$this->Request['cedula'];
      $de=$this->t1->text;
      $fecha=cambiaf_a_mysql($this->fecha1->text);      
      $asunto=$this->t2->text;
      $emisor=$this->t3->text;
      $motivo=$this->t4->text;
      $documento=$this->t5->text;
      $sql1="insert into expedientes.amonestaciones(cedula, asunto, de, fecha, emisor, motivo, documento) values('$cedula', '$asunto', '$de', '$fecha', '$emisor', '$motivo', '$documento')";
      $resultado1=modificar_data($sql1, $sender);
      //$this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
      $cedula=$this->Request['cedula'];
      $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
   }
}
?>