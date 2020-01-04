<?php
class agregar_varios extends TPage
{
   public function agregar($sender,$param)
   {
      $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
   }
   function guardar($sender, $param)
   {
      $cedula=$this->Request['cedula'];
      $fecha=cambiaf_a_mysql($this->fecha1->text);
      $descripcion=$this->t1->text;
      $documento=$this->t2->text;      
      //$asunto=$this->t3->text;
      $sql1="insert into expedientes.varios(cedula, fecha, descripcion, documento) values('$cedula', '$fecha', '$descripcion', '$documento')";
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