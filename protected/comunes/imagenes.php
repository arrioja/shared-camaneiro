<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Este archivo contiene funciones para el tratamiento de imágenes
 *              en el sistema.
 *****************************************************  FIN DE INFO
*/

function resize_imagen ($sourcefile, $dest_x, $dest_y, $targetfile, $jpegqual)
{
    /* Get the dimensions of the source picture */
    $picsize=getimagesize("$sourcefile");
    $source_x = $picsize[0];
    $source_y  = $picsize[1];

    // para evitar que las imagenes se vean forzadas, se conserva su aspecto
    if ($source_x > $source_y)
        { $pocent = ($source_x)/$source_y;
          $dest_x = intval($dest_x*$pocent);}
    else
        { $pocent = ($source_y)/$source_x;
          $dest_y = intval($dest_y*$pocent); }

    $source_id = imageCreateFromJPEG("$sourcefile");

    $target_id=imagecreatetruecolor($dest_x, $dest_y);

    $target_pic=imagecopyresampled($target_id,$source_id,
                                 0,0,0,0,
                                  $dest_x,$dest_y,
                                  $source_x,$source_y);

    imagejpeg ($target_id,"$targetfile",$jpegqual);
    return true;
} 

?>
