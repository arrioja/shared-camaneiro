<?php
class incluir_grupos extends TPage
{

	public function onLoad($param)
	{
			//Busqueda de Registros
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
            $codigo = proximo_numero("intranet.grupos","codigo",null,$this);
            $nombre = $this->txt_nombre->Text;
            $resultado_drop = obtener_seleccion_drop($this->drop_organizaciones);
            $codigo_organizacion = $resultado_drop[1]; // el segundo valor que corresponde con el codigo

            /* Se guarda en la base de datos */
            $sql="insert into intranet.grupos(codigo,cod_organizacion,nombre)
                  values ('$codigo','$codigo_organizacion','$nombre')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Insertado Grupo Cod: ".$codigo." Nombre: ".$nombre;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
    }
}
?>