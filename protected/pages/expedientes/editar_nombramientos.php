<?php
class editar_nombramientos extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            //$this->fecha1->text=date("d/m/Y");
            //
            $id=$this->Request['id'];
            $sql="SELECT * FROM expedientes.nombramientos_ptos_cta where(id='$id')";
            $resultado=cargar_data($sql, $this);
            //$this->oculto2->text=$cadena;

            $this->t1->text=$resultado[0]['de'];
            $this->t2->text=$resultado[0]['para'];
            $this->t3->text=$resultado[0]['asunto'];
            $this->fecha1->text=cambiaf_a_normal($resultado[0]['fecha']);                                    
            $this->oculto2->text=$resultado[0]['documento'];
            $this->fu1->tooltip=$resultado[0]['documento'];
        }
    }
    public function guardar($sender, $param)
    {
        if ($this->IsValid)
        {
        $id=$this->Request['id'];
        $cedula=$this->Request['cedula'];
        $de=$this->t1->text;
        $para=$this->t2->text;
        $asunto=$this->t3->text;
        $fecha=cambiaf_a_mysql($this->fecha1->text);                        
        $documento=$this->oculto2->text;
        $sql2="update expedientes.nombramientos_ptos_cta set de='$de', para='$para', asunto='$asunto', fecha='$fecha', cedula='$cedula', documento='$documento'
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
         $this->oculto2->text='';
         $this->oculto2->text=$this->oculto2->text."$ruta$sender->FileName"."\n";
         $sender->saveAs($ruta.$sender->FileName);
      }
      else
      {
         $ruta="expedientes/".$cedula."/";//ruta donde se guardan los adjuntos que se han subido
      }
    }
    function volver_nombramientos($sender, $param)
    {
        $cedula=$this->Request['cedula'];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
    }
}
?>