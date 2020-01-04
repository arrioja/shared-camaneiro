<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra la forma para incluir proveedores que serán las empresas
 * y/o personas con los cuales la institución se mantien relaciones comerciales.
 *****************************************************  FIN DE INFO
*/

class incluir_proveedor extends TPage
{
    public function onLoad($param)
    { // para colocarle el código tentativo de referencia al label al lado del rif.
        $cod_organizacion = usuario_actual('cod_organizacion');
       $criterios_adicionales = array (
                                "cod_organizacion" => $cod_organizacion
                                );
       $codigo = proximo_numero("presupuesto.proveedores","cod_proveedor",$criterios_adicionales,$this);
       // la función "rellena" de tantos caracteres a la izquierda como se le diga.
       $codigo = rellena($codigo,6,"0");
       $this->lbl_codigo->Text="C&oacute;digo tentativo:";
       $this->txt_codigo->Text=$codigo;

             /* $cod = codigo_unidad_ejecutora($this);
              if (empty($cod) == false)
              { $this->txt_codigo->Mask = $cod."-###-##-##-##-#####"; }*/
    }

	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $criterios_adicionales = array (
                                    "cod_organizacion" => $cod_organizacion
                                    );
            $codigo = proximo_numero("presupuesto.proveedores","cod_proveedor",$criterios_adicionales,$this);
            $codigo = rellena($codigo,6,"0");
            $rif = $this->txt_rif->Text;
            $nombre = $this->txt_nombre->Text;
            $telefono1 = $this->txt_telefono1->Text;
            $telefono2 = $this->txt_telefono1->Text;
            $direccion = $this->txt_direccion->Text;

            $sql="insert into presupuesto.proveedores
                    (cod_organizacion, cod_proveedor, rif, nombre, telefono1, telefono2, direccion)
                  value ('$cod_organizacion','$codigo', '$rif', '$nombre', '$telefono1', '$telefono2', '$direccion')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el proveedor: ".$rif." / ".$nombre;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            $this->mensaje->setSuccessMessage($sender,"Proveedor ".$this->txt_nombre->Text." guardado exitosamente!!", 'grow');
            //$this->Response->redirect($this->Service->constructUrl('Home'));
        }
	}
}
?>