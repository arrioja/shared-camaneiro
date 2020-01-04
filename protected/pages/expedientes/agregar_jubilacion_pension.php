<?php
class agregar_jubilacion_pension extends TPage
{
   public function agregar($sender,$param)
   {
      $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
   }
   function guardar($sender, $param)
   {
      $fecha=cambiaf_a_mysql($this->fecha1->text);
      $cedula=$this->Request['cedula'];
      $descripcion=$this->t1->text;
      $documento=$this->t2->text;
      //$para=$this->t2->text;
      //$asunto=$this->t3->text;            
      $sql1="insert into expedientes.jubilacion(fecha, descripcion, documento, cedula) values('$fecha', '$descripcion', '$documento', '$cedula')";
      $resultado1=modificar_data($sql1, $sender);
      //$this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
      $cedula=$this->Request['cedula'];
      $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
   }
   function cargar($sender, $param)
   {
       if($sender->HasFile)
         $cedula=$this->Request['cedula'];
         $ruta="expedientes/".$cedula."/";//ruta donde se guardan los adjuntos que se han subido
         $this->t2->text=$this->t2->text."$ruta$sender->FileName"."\n";
         $sender->saveAs($ruta.$sender->FileName);
   }
}
?>