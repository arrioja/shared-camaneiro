<?php
/*
****************************************************  INFO DEL ARCHIVO
  		   Creado por: 	Pedro E. Arrioja M.
  Descripción General:  Funciones relacionadas con el manejo de archivos de todo
 *                      tipo, creación, acceso, adición de información, etc.
****************************************************  FIN DE INFO
 */

function elimina_grafico($arch)
{
    if (file_exists("imagenes/temporales/".$arch))
       { unlink("imagenes/temporales/".$arch);}
}

/* elimina un archivo desde el path que se le diga */
function elimina_archivo($arch)
{
    if (file_exists($arch))
       { unlink($arch);}
}

?>
