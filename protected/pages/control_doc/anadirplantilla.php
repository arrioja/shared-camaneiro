<?php
class anadirplantilla extends TPage
{
    function cargar($sender, $param)
    {
        //el adjunto sera subido al presionar el boton cargar, el boton incluir solo inserta en la db el resto de los controles
        if($sender->HasFile)
       	{
            $ruta="plantillas/";//ruta donde se guardan las plantillas
            if(file_exists($ruta.$sender->FileName))
            {
                echo "ya hay una plantilla con ese nombre";//da un error si todos lo aleatorios ya existen                
            }
            else//si no existe el archivo con el aleatorio inserto los datos en la db y subo el archivo al servidor
            {                
                $sql="insert into plantillas.docu (concepto) values('$sender->FileName')";
                $resultado=modificar_data($sql, $sender);                
                $sender->saveAs($ruta.$sender->FileName);
                $this->tb2->text=$sender->FileName;//almaceno el nombre del archivo en un texto oculto en la pagina
            }
        }
    }    
}
?>
