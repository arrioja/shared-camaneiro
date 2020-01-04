<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Mediante esta ventana se incluyen los objetivos estratégicos de
 *              un Plan Estratégico y se vinculan de una vez con la Dirección
 *              responsable de hacerlos cumplir.
 *****************************************************  FIN DE INFO
*/

class incluir_objetivo extends TPage
{
    public function onLoad($param)
    { 
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql="Select cod_plan_estrategico, nombre
                    From planificacion.planes_estrategicos
                    Where (cod_organizacion = '$cod_organizacion') and
                          ((estatus = 'ACTIVO') OR (estatus = 'FUTURO'))";
              $resultado=cargar_data($sql,$this);
              $this->drop_plan->Datasource = $resultado;
              $this->drop_plan->dataBind();

              $cedula = usuario_actual('cedula');
              $resultado = vista_usuario($cedula,$cod_organizacion,'D',$this);

              // Se enlaza el nuevo arreglo con el listado de Direcciones
              $this->drop_direcciones->DataSource=$resultado;
              $this->drop_direcciones->dataBind();
          }
    }

	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $cod_direccion = $this->drop_direcciones->SelectedValue;
            $cod_plan_estrategico = $this->drop_plan->SelectedValue;

            $titulo = $this->txt_nombre_completo->Text;
            $descripcion = $this->txt_descripcion->Text;

            $codigo = proximo_numero("planificacion.objetivos_estrategicos","cod_objetivo_estrategico",null,$sender);
            $codigo = rellena($codigo,6,"0");

            /* Se Inicia el procedimiento para guardar en la base de datos  */

            $sql="insert into planificacion.objetivos_estrategicos
                    (cod_organizacion, cod_direccion, cod_plan_estrategico, cod_objetivo_estrategico, nombre,
                     descripcion, porcentaje_completo)
                  value ('$cod_organizacion','$cod_direccion','$cod_plan_estrategico','$codigo','$titulo',
                         '$descripcion','0')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el Objetivo Estratégico: ".$titulo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('planificacion.obj_estrategicos.incluir_objetivo'));
        }
	}

}

?>
