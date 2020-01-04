<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra la pagina para la inclusión de objetivos operativos a un
 *              Plan Operativo, vinculado respectivamente a los objetivos estrategicos
 *              de un plan estratégico.
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
              $sql="Select po.cod_plan_operativo, po.nombre
                    From planificacion.planes_operativos po, planificacion.planes_estrategicos pe
                    Where (po.cod_organizacion = '$cod_organizacion') and
                          (po.cod_plan_estrategico = pe.cod_plan_estrategico) and
                          ((pe.estatus = 'ACTIVO') OR (pe.estatus = 'FUTURO'))";
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

	public function actualizar_obj_estrategicos($sender, $param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $cod_plan_operativo = $this->drop_plan->SelectedValue;
        $cod_direccion = $this->drop_direcciones->SelectedValue;
        $sql="Select cod_plan_estrategico
            From planificacion.planes_operativos
            Where (cod_organizacion = '$cod_organizacion') and
                  (cod_plan_operativo = '$cod_plan_operativo')";
        $resultado=cargar_data($sql,$this);
        $cod_plan_estrategico = $resultado[0]['cod_plan_estrategico'];
        $this->lbl_cod_plan_estrategico->Text = $cod_plan_estrategico;



        $sql="Select cod_objetivo_estrategico, nombre
            From planificacion.objetivos_estrategicos
            Where (cod_organizacion = '$cod_organizacion') and
                  (cod_direccion = '$cod_direccion') and
                  (cod_plan_estrategico = '$cod_plan_estrategico')";
        $resultado=cargar_data($sql,$this);


        $this->drop_objetivo->Datasource = $resultado;
        $this->drop_objetivo->dataBind();
    }



	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $cod_direccion = $this->drop_direcciones->SelectedValue;
            $cod_plan_estrategico = $this->lbl_cod_plan_estrategico->Text;
            $cod_plan_operativo = $this->drop_plan->SelectedValue;
            $cod_objetivo_estrategico = $this->drop_objetivo->SelectedValue;

            $titulo = $this->txt_nombre_completo->Text;
            $descripcion = $this->txt_descripcion->Text;

            $codigo = proximo_numero("planificacion.objetivos_operativos","cod_objetivo_operativo",null,$sender);
            $codigo = rellena($codigo,6,"0");

            /* Se Inicia el procedimiento para guardar en la base de datos  */

            $sql="insert into planificacion.objetivos_operativos
                    (cod_organizacion, cod_direccion, cod_plan_estrategico, cod_objetivo_estrategico,
                     cod_plan_operativo, cod_objetivo_operativo, nombre,
                     descripcion, porcentaje_completo)
                  value ('$cod_organizacion','$cod_direccion','$cod_plan_estrategico','$cod_objetivo_estrategico',
                         '$cod_plan_operativo','$codigo','$titulo',
                         '$descripcion','0')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el Objetivo Operativo: ".$titulo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('planificacion.obj_operativos.incluir_objetivo'));
        }
	}

}

?>
