<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra la pagina para incluir un Plan Operativo vinculado a un
 *              Plan Estratégico.
 *****************************************************  FIN DE INFO
*/

class incluir_poa extends TPage
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

          }
    }


    public function generar_drop_ano()
    {
        $cod_plan = $this->drop_plan->SelectedValue;

        if ($cod_plan == "XXX")
        {
            $vacio = array();
            $this->drop_ano_inicio->Datasource = $vacio;
            $this->drop_ano_inicio->dataBind();
        }
        else
        {
            $sql="Select ano_inicio, ano_fin
                  From planificacion.planes_estrategicos
                  Where (cod_plan_estrategico = '$cod_plan')";
            $resultado=cargar_data($sql,$this);

            $ano_actual = $resultado[0]['ano_inicio'];
            $ano_final =$resultado[0]['ano_fin'];
            $anos = generar_rango($ano_actual,$ano_final,4);
            $this->drop_ano_inicio->Datasource = $anos;
            $this->drop_ano_inicio->dataBind();
        }
    }


	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano = $this->drop_ano_inicio->SelectedValue;
            $cod_plan = $this->drop_plan->SelectedValue;

            $titulo = $this->txt_nombre_completo->Text;
            $descripcion = $this->txt_descripcion->Text;

            $codigo = proximo_numero("planificacion.planes_operativos","cod_plan_operativo",null,$sender);
            $codigo = rellena($codigo,6,"0");

            /* Se Inicia el procedimiento para guardar en la base de datos  */

            $sql="insert into planificacion.planes_operativos
                    (cod_organizacion, cod_plan_estrategico, cod_plan_operativo, ano, nombre,
                     descripcion, porcentaje_completo)
                  value ('$cod_organizacion','$cod_plan','$codigo','$ano','$titulo',
                         '$descripcion','0')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el Plan Operativo: ".$titulo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
	}

}

?>
