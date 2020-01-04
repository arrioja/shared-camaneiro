<?php
require('/var/www/odtphp/library/odf.php');
class prueba extends TPage
{
    function onload()
    {
        $odf = new odf("/var/www/cene/plantillas/prueba.odt");
        $odf->setVars('titre', 'PHP');
        $message = "PHP  est un langage de scripts libre ...";
        $odf->setVars('message', $message);
        $odf->exportAsAttachedFile("prueba.odt");
    }
}
?>
