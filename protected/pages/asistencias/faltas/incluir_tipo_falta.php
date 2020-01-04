<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta ventana incluye en la base de datos un nuevo tipo de falta
 *              a la asistencia.
 *****************************************************  FIN DE INFO
*/

class incluir_tipo_falta extends TPage
{

    public function verifica_existencia($sender,$param)
    {
        $codigo = $this->txt_codigo->Text;
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="Select codigo from asistencias.tipo_faltas where (codigo = '$codigo') and (cod_organizacion = '$cod_organizacion')";
        $consulta=cargar_data($sql,$sender);
        $param->IsValid = empty($consulta);
    }

    public function incluir_click($sender,$param)
    {
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo = $this->txt_codigo->Text;
            $descripcion = $this->txt_descripcion->Text;
            $visible = $this->drop_visible->Text;

            /* Se guarda en la base de datos */
            $sql="insert into asistencias.tipo_faltas (cod_organizacion,codigo, descripcion, visible)
                  values ('$cod_organizacion','$codigo','$descripcion','$visible')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el tipo de falta: ".$codigo." / ".$descripcion;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
    }

}

?>