<?php
class agregar_curriculo_anexo extends TPage
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
         $this->t3->text=$this->t3->text."$ruta$sender->FileName"."\n";
         $sender->saveAs($ruta.$sender->FileName);
   }
   function guardar($sender, $param)
   {
      $cedula=$this->Request['cedula'];
      $nombre=$this->t1->text;
      $f_inicio=cambiaf_a_mysql($this->fecha1->text);
      $f_final=cambiaf_a_mysql($this->fecha2->text);
      $hrs=$this->t2->text;
      $tipo=$this->rb1->SelectedValue;
      $ponente_participante=$this->rb2->SelectedValue;
      $imagen=$this->t3->text;
      $sql1="insert into expedientes.curriculo_anexos(nombre, fecha_inicio, fecha_final, horas, tipo, ponente_participante, imagen, cedula) values('$nombre', '$f_inicio', '$f_final', '$hrs', '$tipo', '$ponente_participante', '$imagen', '$cedula')";
      $resultado1=modificar_data($sql1, $sender);
      //$this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
      $cedula=$this->Request['cedula'];
      $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
   }
}
?>
