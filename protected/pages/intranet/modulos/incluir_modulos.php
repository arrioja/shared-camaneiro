<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class incluir_modulos extends TPage
{
    public function incluir_click($sender,$param)
    {
        if ($this->IsValid)
        {
        // Se capturan los datos provenientes de los Controles
        $codigo = $this->txt_codigo->Text;
        $nombre_corto = $this->txt_nombre_corto->Text;
        $nombre_largo = $this->txt_nombre_largo->Text;
        $imagen_grande = $this->txt_imagen_off->Text;
        $imagen_pequena = $this->txt_imagen_on->Text;
        $archivo_php = $this->txt_archivo_php->Text;

        /* Se Inicia el procedimiento para guardar en la base de datos
         */
		$sql="insert into intranet.modulos(codigo_modulo,nombre_corto,nombre_largo,imagen_g,imagen_p, archivo_php)
              values ('$codigo','$nombre_corto','$nombre_largo','$imagen_grande','$imagen_pequena','$archivo_php')";
        $resultado=modificar_data($sql,$sender);

        /* Se incluye el rastro en el archivo de bitácora */
        $descripcion_log = "Insertado M&oacute;dulo Cod:".$codigo." Nom:".$nombre_largo." Arch:".$archivo_php;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        $this->Response->redirect($this->Service->constructUrl('Home'));
        }        
    }
}
?>
