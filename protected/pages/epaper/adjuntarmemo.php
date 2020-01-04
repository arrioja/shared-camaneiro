<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class adjuntarmemo extends TPage
{
    function cargar($sender, $param)
    {        
        //el adjunto sera subido al presionar el boton cargar, el boton incluir solo inserta en la db el resto de los controles
        if($sender->HasFile)
       	{
            $cadena=$this->Request['nummemo'];
            list($siglas, $correlativo, $ano)=explode('-', $cadena);
            //$aleatorio = $this->numero->Text;
            $cod_direccion = usuario_actual('cod_direccion');
            $siglas = usuario_actual('siglas_direccion');
            //$ano = $this->drop_ano->Text;
            $ruta="attach/";//ruta donde se guardan los adjuntos que se han subido
            $codigo=rand(0000000,99999999);//genera un aleatorio de 8 digitos que representa el nombre del adjunto        
            while(file_exists($ruta.$codigo))//mientras exista un archivo con el mismo nombre del codigo
            {
                $codigo=rand(00000000,99999999);//genero otro aleatorio
            }
            if(file_exists($ruta.$codigo))
            {
                echo "error";//da un error si todos lo aleatorios ya existen
            }
            else//si no existe el archivo con el aleatorio actualizo los datos en la db y subo el archivo al servidor
            {
                $sql="insert into organizacion.adjuntos (direccion, siglas, correlativo, ano, nom_adjunto,cod_adjunto)
                        values('$cod_direccion', '$siglas', '$correlativo', '$ano', '$sender->FileName','$codigo')";
                //$sql="update organizacion.adjuntos set cod_adjunto='$codigo' and nom_adjunto='$sender->FileName'
                      //where(direccion='$direccion')";
                $resultado=modificar_data($sql, $sender);
                $this->t1->text=$this->t1->text.$codigo."-".$sender->FileName."\n";                
                $sender->saveAs($ruta.$codigo);
            }
       	}
    }
}
?>
