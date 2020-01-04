<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Mediante esta página, El usuario puede aprobar o rechazar las
 *              solicitudes de vacaciones que se encuentren pendientes; es
 *              necesario tener el permiso apropiado y el nivel requerido.
 *****************************************************  FIN DE INFO
*/

class listar_vacacion extends TPage
{
    var $conteo =1;
    var $cedula_conteo = "";
    var $vacaciones;
    public function onLoad($param)
    {
        parent::onLoad($param);
        if ((!$this->IsPostBack) && (!$this->IsCallBack))
        {
             $sql ="select p.cedula, CONCAT(p.nombres,' ',p.apellidos,' (',p.cedula,')') as nombre
               from asistencias.vacaciones_disfrute as v, organizacion.personas as p, asistencias.personas_status_asistencias as u
                    WHERE( v.estatus = '1' AND v.cedula=p.cedula) and (u.status_asistencia = '1') and (u.cedula=p.cedula)
                    GROUP BY v.cedula
                    ORDER BY p.nombres, p.apellidos, p.cedula";
            $resultado=cargar_data($sql,$this);
            $this->drop_cedula->DataSource=$resultado;
            $this->drop_cedula->dataBind();
        }
    }

/* Esta función se encarga de mostrar el listado con las solicitudes de vacaciones pendientes para la
 * dirección seleccionada.
 */
    public function consulta_vacaciones($sender, $param)
    {
        $cedula = $this->drop_cedula->SelectedValue;
        if($cedula!=""){
            $AND=" AND v.cedula ='$cedula' ";

       
        $sql ="select p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombres, v.id, v.dias_disfrute, v.dias_feriados, 
               v.dias_restados, v.fecha_desde, v.fecha_hasta, v.periodo
               from asistencias.vacaciones_disfrute as v, organizacion.personas as p
                    where(v.cedula=p.cedula AND (v.estatus = '1') $AND )
                    order by p.nombres, p.apellidos, p.cedula, v.fecha_desde DESC";
        $this->vacaciones=cargar_data($sql,$sender);

        $this->DataGrid->DataSource=$this->vacaciones;
        $this->DataGrid->dataBind();
        }
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
                    $this->cedula_conteo = $item->cedula->Text;
                    $item->cedula->BackColor = $color1[$this->conteo];
                    $item->nombres->BackColor = $color1[$this->conteo];
                    $this->conteo = !$this->conteo;
                }
            else
                {
                    $item->cedula->Visible = false;
                    $item->nombres->Visible = false;
                }
        }

        if ($item->hasta->Text != "Hasta")
        {
            $item->hasta->Text = cambiaf_a_normal($item->hasta->Text);
        }
        if ($item->desde->Text != "Desde")
        {
            $item->desde->Text = cambiaf_a_normal($item->desde->Text);
        }
    }


    public function changePage($sender,$param)
	{

        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->consulta_vacaciones($sender, $param);

	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

    public function interrumpir_click($sender,$param)
    {
        $id=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.interrumpir_vacacion',array('id'=>$id)));
    }
    public function modificar_click($sender,$param)
    {
        $id=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.modificar_vacacion',array('id'=>$id)));
    }

}
?>
