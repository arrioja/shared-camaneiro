<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página se encarga de la inclusión de las instituciones financieras con
 * las cuales la organización mantiene relaciones; es el primer paso ya que si
 * se desea registrar cuentas, es necesario que se registre primero los
 * bancos en donde se encuentran.
 *****************************************************  FIN DE INFO
*/

class incluir_banco extends TPage {
    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo = rellena(proximo_numero('presupuesto.bancos','cod_banco',null,$sender),3,'0');
            $info = $this->txt_info->Text;
            $nombre = $this->txt_nombre->Text;

            /* Se guarda en la base de datos */
            $sql="insert into presupuesto.bancos (cod_organizacion, cod_banco, nombre, info_adicional)
                  values ('$cod_organizacion','$codigo','$nombre','$info')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el Banco: ".$nombre;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }

    }
}
?>