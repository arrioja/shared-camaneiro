<?php
class editar_curriculo_anexo extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {            
            //
            $id=$this->Request['id'];
            $sql="SELECT * FROM expedientes.curriculo_anexos where(id='$id')";
            $resultado=cargar_data($sql, $this);
            $this->t1->text=$resultado[0]['nombre'];
            $this->fecha1->text=cambiaf_a_normal($resultado[0]['fecha_inicio']);
            $this->fecha2->text=cambiaf_a_normal($resultado[0]['fecha_final']);
            $this->t2->text=$resultado[0]['horas'];
            $this->t3->text=$resultado[0]['imagen'];
            $this->fu1->tooltip=$resultado[0]['imagen'];
            $this->rb1->text=$resultado[0]['tipo'];
            $this->rb2->text=$resultado[0]['ponente_participante'];            
        }
    }
    public function guardar($sender, $param)
    {
        if ($this->IsValid)
        {        
         $id=$this->Request['id'];
         $nombre=$this->t1->text;
         $fecha_inicio=cambiaf_a_mysql($this->fecha1->text);
         $fecha_final=cambiaf_a_mysql($this->fecha2->text);
         $horas=$this->t2->text;
         $tipo=$this->rb1->SelectedValue;
         $ponente_participante=$this->rb2->SelectedValue;         
         $documento=$this->t3->text;
         $sql2="update expedientes.curriculo_anexos set
         nombre='$nombre', fecha_inicio='$fecha_inicio', fecha_final='$fecha_final', horas='$horas', tipo='$tipo', ponente_participante='$ponente_participante', imagen='$documento'
         where(id='$id')";
         $resultado2=modificar_data($sql2, $sender);
         $cedula=$this->Request['cedula'];
         $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
         //$this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente'));
         /* Se incluye el rastro en el archivo de bitácora */
         //$login=usuario_actual('login');
         //$descripcion_log = "El usuario ".$login." ha modificado una actuacion en el sistema ";
         //inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
         //redirige la pagina a agregar_existente
         //$this->Response->Redirect( $this->Service->constructUrl('denuncias.modificar_denuncia'));
        }
    }
    function cargar($sender, $param)
   {
      $cedula=$this->Request['cedula'];
      $id=$this->Request['id'];
      if($sender->HasFile)
      {
         $ruta="expedientes/".$cedula."/";//ruta donde se guardan los adjuntos que se han subido
         $this->t3->text='';
         $this->t3->text=$this->t3->text."$ruta$sender->FileName"."\n";
         $sender->saveAs($ruta.$sender->FileName);
      }
      else
      {
         //$ruta="expedientes/".$cedula."/";//ruta donde se guardan los adjuntos que se han subido
         //$documento="";
         //$sql3="update expedientes.curriculo_anexos set imagen='$documento' where(id='$id')";
         //$resultado3=modificar_data($sql3, $sender);
      }
   }
   function volver_curriculo_anexo($sender, $param)
    {
        $cedula=$this->Request['cedula'];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
    }
}
?>
