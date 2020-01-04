<?php
class incluir_niveles extends TPage
{

	public function onLoad($param)
	{
        $sql="select codigo, nombre from organizacion.organizaciones order by nombre";
        $organizaciones=cargar_data($sql,$this);
        $this->drop_organizaciones->DataSource=$organizaciones;
        $this->drop_organizaciones->dataBind();
	}
    
    public function incluir_click($sender,$param)
    {
        if ($this->IsValid)
        {        
            // Se capturan los datos provenientes de los Controles
            $codigo = $this->txt_codigo->Text;
            $nombre = $this->txt_nombre->Text;
            $resultado_drop = obtener_seleccion_drop($this->drop_organizaciones);
            $codigo_organizacion = $resultado_drop[1]; // el segundo valor que corresponde con el codigo
            /* Se guarda en la base de datos */
            $sql="insert into organizacion.nivel (codigo,nombre,codigo_organizacion)
                  values ('$codigo','$nombre','$codigo_organizacion')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido la Direccion ".$nombre_completo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
    }
}
?>