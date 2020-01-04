<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra un formulario para la inclusión de los tipos de
 *              justificaciones que usará el sistema de asistencia al momento
 *              de justificar una falta de un empleado.
 *****************************************************  FIN DE INFO
*/

class incluir_tipo_justificaciones extends TPage
{

    public function incluir_click($sender,$param)
    {
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo = rellena(proximo_numero('asistencias.tipo_justificaciones','codigo',null,$sender),3,'0');
            $descripcion = $this->txt_descripcion->Text;
            $ticket = $this->drop_ticket->Text;
            $visible = $this->drop_visible->Text;
            $dias_habiles = $this->drop_habiles->Text;

            /* Se guarda en la base de datos */
            $sql="insert into asistencias.tipo_justificaciones (cod_organizacion,codigo, descripcion, descuenta_ticket, visible,dias_habiles)
                  values ('$cod_organizacion','$codigo','$descripcion','$ticket','$visible','$dias_habiles')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el tipo de Justificacion de asistencia: ".$codigo." / ".$descripcion." en la Org:".$cod_organizacion;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
    }

}

?>
