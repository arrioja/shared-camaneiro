<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página muestra un listado de las vacaciones pendientes de
 *              los funcionarios y funcionarias de la Dirección seleccionada.
 *****************************************************  FIN DE INFO
*/
class consulta_direccion extends TPage
{
    var $conteo =1;
    var $cedula_conteo = "";
    var $vacaciones;
    public function onLoad($param)
    {
        parent::onLoad($param);
        if ((!$this->IsPostBack) && (!$this->IsCallBack))
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $cedula = usuario_actual('cedula');
            $resultado = vista_usuario($cedula,$cod_organizacion,'D',$this);
            ///si tiene asignado todas las direcciones
            $sql="SELECT  cod_direccion as codigo FROM intranet.usuarios_vistas
                  WHERE ((cedula = '$cedula') AND (cod_organizacion = '$cod_organizacion') AND (cod_direccion='0') ) ";
            $dir_todas=cargar_data($sql,$this);
                if ($dir_todas[0]['codigo']=="0"){
                // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
                $todos = array('codigo'=>'0', 'nombre'=>'TODAS LAS DIRECCIONES');
                array_unshift($resultado, $todos);
                }//fin si
         
            // Se enlaza el nuevo arreglo con el listado de Direcciones
            $this->drop_direcciones->DataSource=$resultado;
            $this->drop_direcciones->dataBind();
        }
    }

/* Esta función se encarga de mostrar el listado con las vacaciones pendientes para la
 * dirección seleccionada.
 */
    public function consulta_vacaciones($sender, $param)
    {
        $dir = $this->drop_direcciones->SelectedValue;
        $sql ="select p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombres, CONCAT(v.anos_antiguedad,' + ',v.anos_antiguedad_otro) as anos, 
                      v.dias, v.disfrutados, v.pendientes, v.periodo, v.disponible_desde, v.antiguo
                      from asistencias.vacaciones as v, organizacion.personas as p, asistencias.personas_status_asistencias as u,
						organizacion.personas_nivel_dir as c, organizacion.direcciones as d
						where((v.cedula=p.cedula) and
						      (p.cedula=c.cedula) and
							  (d.codigo=c.cod_direccion) and
							  (c.cod_direccion LIKE '$dir%') and
                              (u.status_asistencia = '1') and (u.cedula=p.cedula) and
							  (v.pendientes>'0'))
						order by p.nombres, p.apellidos, p.cedula,  v.disponible_desde";
        $this->vacaciones=cargar_data($sql,$sender);

        $this->DataGrid->DataSource=$this->vacaciones;
        $this->DataGrid->dataBind();
    }



    public function ver_detalle($sender,$param)
    {
        $cedula=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.consulta_cedula',array('ced'=>$cedula)));//
    }

/* Esta función se encarga de contar cuántas cédulas existen en el arreglo que
 * coincidan con la dada, el número de cédulas será usado para ajustar el RowCount
 * del grid de datos.
 */
    public function cuenta_cedulas($datos, $cedula)
    {
        $contador=0;
        foreach ($datos as $undato)
        {
            if ($undato['cedula'] == $cedula)
            {$contador++;}
        }
        return $contador;
    }


/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $color1 = array("#E6ECFF","#BFCFFF");

        $item=$param->Item;

        if ($item->cedula->Text != "Cedula")
        {
            if ($this->cedula_conteo != $item->cedula->Text )
                {
                    $item->cedula->RowSpan = $this->cuenta_cedulas($this->vacaciones,$item->cedula->Text);
                    $item->nombres->RowSpan = $item->cedula->RowSpan;
                    $item->detalle->RowSpan = $item->cedula->RowSpan;
                    $this->cedula_conteo = $item->cedula->Text;
                    $item->cedula->BackColor = $color1[$this->conteo];
                    $item->nombres->BackColor = $color1[$this->conteo];
                    $this->conteo = !$this->conteo;
                }
            else
                {
                    $item->cedula->Visible = false;
                    $item->nombres->Visible = false;
                    $item->detalle->Visible = false;
                }
        }

        if ($item->antiguo->Text == "1")
        {
            $item->anos->Text = "---";
        }
        if ($item->disponible_desde->Text != "Disponible desde")
        {
            $item->disponible_desde->Text = cambiaf_a_normal($item->disponible_desde->Text);
        }
    }


}

?>
