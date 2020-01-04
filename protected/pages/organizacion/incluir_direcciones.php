<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra la forma para incluir datos de organizaciones.
 *****************************************************  FIN DE INFO
*/

class incluir_direcciones extends TPage
{
    public function onLoad($param)
    { // para colocarle el código tentativo de referencia al label al lado del rif.
        $codigo = proximo_numero("organizacion.direcciones","codigo",null,$this);
        $codigo = rellena($codigo,5,"0");
        $this->lbl_codigo->Text="(tentativo):".$codigo;
        /* para llenar el drop down de las organizaciones*/
        $sql="select codigo, nombre from organizacion.organizaciones order by nombre";
        $organizaciones=cargar_data($sql,$this);
        $this->drop_organizaciones->DataSource=$organizaciones;
        $this->drop_organizaciones->dataBind();
    }


	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $resultado_drop = obtener_seleccion_drop($this->drop_organizaciones);
            $codigo_organizacion = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
            // Se capturan los datos provenientes de los Controles
            $codigo = proximo_numero("organizacion.direcciones","codigo",null,$sender);
            $codigo = rellena($codigo,5,"0");
            $nombre_completo = $this->txt_nombre_completo->Text;
            $nombre_abreviado = $this->txt_nombre_abreviado->Text;
            $siglas = $this->txt_siglas->Text;
            $fecha_cre = cambiaf_a_mysql($this->txt_fecha_cre->Text);
            $mision = $this->txt_mision->Text;
            $vision = $this->txt_vision->Text;

            /* Se Inicia el procedimiento para guardar en la base de datos  */

            mysql_query("BEGIN");  //inicio la transaccion
            $sql="insert into organizacion.direcciones
                    (codigo, nombre_completo, nombre_abreviado, siglas, fecha_creacion, mision, vision,
                     codigo_organizacion, status)
                  value ('$codigo', '$nombre_completo', '$nombre_abreviado', '$siglas', '$fecha_cre',
                         '$mision', '$vision','$codigo_organizacion','ACTIVO')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido la Direccion ".$nombre_completo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
	}

}

?>
