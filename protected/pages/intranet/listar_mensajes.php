<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra un listado de los mensajes que han sido registrados en
 *              el sistema.
 *****************************************************  FIN DE INFO.
*/
class listar_mensajes extends TPage
{ 

public function onLoad($param)
	{
    //    $cod_organizacion = usuario_actual('cod_organizacion');
    //    $sql="select * from intranet.mensajes where cod_organizacion = '$cod_organizacion'";
        $sql="select * from intranet.mensajes";
        $mensaje=cargar_data($sql,$this);
        $this->Repeater->DataSource=$mensaje;
        $this->Repeater->dataBind();

    }
}
?>
