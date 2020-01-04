<?php
class editar_amonestaciones extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {            
            //
            $id=$this->Request['id'];
            $sql="SELECT * FROM expedientes.amonestaciones where(id='$id')";
            $resultado=cargar_data($sql, $this);         
            $this->t1->text=$resultado[0]['de'];
            $this->fecha1->text=cambiaf_a_normal($resultado[0]['fecha']);            
            $this->t2->text=$resultado[0]['asunto'];
            $this->t3->text=$resultado[0]['emisor'];
            $this->t4->text=$resultado[0]['motivo'];
            $this->oculto->text=$resultado[0]['documento'];
            $this->fu1->tooltip=$resultado[0]['documento'];
        }
    }
    public function guardar($sender, $param)
    {
        if ($this->IsValid)
        {        
        $id=$this->Request['id'];
        $de=$this->t1->text;        
        $fecha=cambiaf_a_mysql($this->fecha1->text);        
        $asunto=$this->t2->text;
        $emisor=$this->t3->text;
        $motivo=$this->t4->text;
        $documento=$this->oculto->text;
        $sql2="update expedientes.amonestaciones set asunto='$asunto', de='$de', fecha='$fecha', asunto='$asunto', emisor='$emisor', motivo='$motivo', documento='$documento'
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
         $this->oculto->text='';
         $this->oculto->text=$this->oculto->text."$ruta$sender->FileName"."\n";
         $sender->saveAs($ruta.$sender->FileName);
      }
      else
      {
         $ruta="expedientes/".$cedula."/";//ruta donde se guardan los adjuntos que se han subido
      }
    }
    function volver_amonestaciones($sender, $param)
    {
        $cedula=$this->Request['cedula'];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
    }
}
?>
