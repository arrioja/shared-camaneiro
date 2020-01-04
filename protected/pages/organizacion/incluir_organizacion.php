<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra la forma para incluir datos de organizaciones.
 *****************************************************  FIN DE INFO
*/

class incluir_organizacion extends TPage
{
    public function onLoad($param)
    { // para colocarle el código tentativo de referencia al label al lado del rif.
       $codigo = proximo_numero("organizacion.organizaciones","codigo",null,$this);
       // la función "rellena" de tantos caracteres a la izquierda como se le diga.
       $codigo = rellena($codigo,6,"0");
       $this->lbl_codigo->Text="C&oacute;digo tentativo:".$codigo;
    }

	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $codigo = proximo_numero("organizacion.organizaciones","codigo",null,$sender);
            $codigo = rellena($codigo,6,"0");
            $rif = $this->txt_rif->Text;
            $nombre = $this->txt_nombre->Text;
            $fecha_cre = cambiaf_a_mysql($this->txt_fecha_cre->Text);
            $telefono1 = $this->txt_telefono1->Text;
            $telefono2 = $this->txt_telefono1->Text;
            $telefono3 = $this->txt_telefono1->Text;
            $telefono4 = $this->txt_telefono1->Text;
            $direccion = $this->txt_direccion->Text;
            $mision = $this->txt_mision->Text;
            $vision = $this->txt_vision->Text;

            /* Se Inicia el procedimiento para guardar en la base de datos
             * Primero los datos en la tabla organizacion.personas, luego en
             * asistencias.personas_status_asistencias y al final en
             * organizacion.personas_nivel_dir
             */

            $sql="insert into organizacion.organizaciones
                    (codigo, rif, nombre, fecha_creacion, telefono1, telefono2,
                     telefono3, telefono4, direccion, mision, vision)
                  value ('$codigo', '$rif', '$nombre', '$fecha_cre', '$telefono1',
                         '$telefono2','$telefono4','$telefono4','$direccion','$mision', '$vision')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido la organizacion: ".$rif." / ".$nombre;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
	}

}

?>
