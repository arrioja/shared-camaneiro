<?php
/* Creado por:  Pedro E. Arrioja M.  pedroarrioja@gmail.com
 * Descripcion: Este Control muestra un mensaje que puede ser enviado desde el 
 *              servidor y disparado desde el cliente mediante AJAX.
 */
class MensajeDiv extends TTemplateControl
{
	public function redireccion($sender, $param)
    {  
        $redirec = $this->redir->Text;
        if (!empty($redirec))
            { // si hay que redireccionar se lleva a la pagina en cuestión.
                $this->Response->redirect($this->Service->constructUrl($redirec));
            }
        else
            { // si no hay que redireccionar, se limpian los campos por si se
              // necesita de nuevo el control en la misma pagina
                $this->titulo->Text = "Cargando Informaci&oacute;n";
                $this->texto->Text = "";
                $this->imagen->Imageurl = "imagenes/iconos/loadinfo.gif";
            }
        
  	}
}

?>
