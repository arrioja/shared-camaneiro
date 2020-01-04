<?php
class agregar_archivo extends TPage
{
    function onload($param)
    {
        parent::onLoad($sender, $param);
        if(!$this->IsPostBack)
        {
           $codigo=rand(00000000,99999999);           
           $this->t1->text=$codigo;
           mkdir("attach/archivo/".$codigo, 0777);
           chmod("attach/archivo/".$codigo, 0777);
        }
    }

    function cargar($sender, $param)
    {
        //el adjunto sera subido al presionar el boton cargar, el boton incluir solo inserta en la db el resto de los controles
        if($sender->HasFile)       	        
        $carpeta=$this->t1->text;
        $ruta="attach/archivo/".$carpeta."/";//ruta donde se guardan los adjuntos que se han subido        
        $this->t2->text=$this->t2->text."$ruta$sender->FileName"."\n";
        $sender->saveAs($ruta.$sender->FileName);
    }
        
    function agregar($sender, $param)
    {        
        $descripcion=$this->t3->text;
        if($this->r1->checked)
        {
            $ubicacion="archivo cene";
        }
        else
        {
            $ubicacion="archivo externo";
        }
        $codigo=$this->t1->text;        
        $sql="insert into archivo.archivo_desc (descripcion, codigo, ubicacion) values('$descripcion', '$codigo', '$ubicacion')";
        $resultado=modificar_data($sql, $this);                
        // Se incluye el rastro en el archivo de bitácora
        $descripcion_log = "El usuario ".$login." agrego un nuevo documento con el codigo".$codigo."  al archivo muerto ";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        //reenvio a la pagina de entrega de consumibles
        $this->Response->Redirect( $this->Service->constructUrl('archivo.agregar_archivo'));
    }
}
?>
