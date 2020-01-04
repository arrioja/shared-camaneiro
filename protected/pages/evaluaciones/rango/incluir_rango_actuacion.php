<?php
/*   ****************************************************  INFO DEL ARCHIVO
 * Creado por:  Pedro E. Arrioja M.
 * Descripción: Permite incluir rangos de actuaciones que serán utilizados en las
 *              evaluaciones de actividades y de desempeño de funcionarios.
     ****************************************************  FIN DE INFO
*/
class incluir_rango_actuacion extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {

          }
    }

	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $codigo = proximo_numero("evaluaciones.rangos_actuacion","cod_rango_actuacion",null,$sender);
            $codigo = rellena($codigo,6,"0");
            $nombre = $this->txt_nombre->Text;
            $orden = proximo_numero("evaluaciones.rangos_actuacion","orden",null,$sender);

            // se guarda en la base de datos
            $sql="insert into evaluaciones.rangos_actuacion (cod_rango_actuacion, descripcion, orden)
                  value ('$codigo','$nombre','$orden')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha el rango de actuación: ".$codigo." / ".$nombre;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
	}




}
?>