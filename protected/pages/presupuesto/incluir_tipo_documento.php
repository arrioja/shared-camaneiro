<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: En esta página se pueden crear tipos de documentos que afectan
 *              el presupuesto de la organizacion, tales como ordenes de compra
 *              ordenes de pago, etc etc etc.
 *****************************************************  FIN DE INFO
*/
class incluir_tipo_documento extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
          }
    }

/* Esta función valida la existencia del tipo de documento en la organización
 * seleccionada, se usa un custom validator en el control para
 * responder al usuario si existe o no el mismo.
 */
    public function validar_siglas($sender, $param)
    {
        $codigo_organizacion = usuario_actual('cod_organizacion');
        $siglas = $this->txt_siglas->Text;

        $sql = "select * from presupuesto.tipo_documento
                    where ((cod_organizacion = '$codigo_organizacion') and (siglas = '$siglas'))";

        $existe = cargar_data($sql,$this);
        $param->IsValid = empty($existe);
    }


    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // captura de datos desde los controles
            $codigo_organizacion = usuario_actual('cod_organizacion');
            $siglas = $this->txt_siglas->Text;
            $nombre = $this->txt_nombre->Text;
            $resultado_drop = obtener_seleccion_drop($this->drop_operacion);
            $operacion = $resultado_drop[1];

            // insersión en la base de datos de la información.
            $sql = "insert into  presupuesto.tipo_documento
                    (cod_organizacion, siglas, nombre, operacion)
                    values ('$codigo_organizacion','$siglas','$nombre','$operacion')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluido el tipo de documento ".$siglas."/".$nombre.
                               " en Presupuesto";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
    }
}
?>
