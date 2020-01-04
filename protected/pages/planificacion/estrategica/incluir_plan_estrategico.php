<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra la forma para incluir un nuevo Plan Estratégico en la
 *              Organización, en esta oportunidad no se discrimina en cuanto
 *              a Unidades Administrativas, sino que simplemente se incluye el
 *              Plan para toda la organización.
 *****************************************************  FIN DE INFO
*/

class incluir_plan_estrategico extends TPage
{
    public function onLoad($param)
    { 
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $ano_actual = date("Y");
              $ano_final = $ano_actual+20;
              $anos = generar_rango($ano_actual,$ano_final,4);
              $this->drop_ano_inicio->Datasource = $anos;
              $this->drop_ano_inicio->dataBind();
          }
    }


    public function generar_drop_ano_final()
    {
        if ($this->drop_ano_inicio->SelectedValue == "X")
        {
            $vacio = array();
            $this->drop_ano_fin->Datasource = $vacio;
            $this->drop_ano_fin->dataBind();
        }
        else
        {
            $ano_actual = $this->drop_ano_inicio->SelectedValue;
            $ano_final = $ano_actual+20;
            $anos = generar_rango($ano_actual,$ano_final,4);
            $this->drop_ano_fin->Datasource = $anos;
            $this->drop_ano_fin->dataBind();
        }
    }


	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano_inicio = $this->drop_ano_inicio->SelectedValue;
            $ano_fin = $this->drop_ano_fin->SelectedValue;
            $titulo = $this->txt_nombre_completo->Text;
            $descripcion = $this->txt_descripcion->Text;

            $codigo = proximo_numero("planificacion.planes_estrategicos","cod_plan_estrategico",null,$sender);
            $codigo = rellena($codigo,6,"0");

            /* Se Inicia el procedimiento para guardar en la base de datos  */

            $sql="insert into planificacion.planes_estrategicos
                    (cod_organizacion, cod_plan_estrategico, ano_inicio, ano_fin, nombre,
                     descripcion, estatus, porcentaje_completo)
                  value ('$cod_organizacion','$codigo','$ano_inicio','$ano_fin','$titulo',
                         '$descripcion','FUTURO','0')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el Plan Estratégico: ".$titulo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
	}

}

?>
