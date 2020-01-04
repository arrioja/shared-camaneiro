<?php
class detalle_vacacion extends TPage
{
    public function onLoad($param)
	   {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            //$this->fecha1->text=date("d/m/Y");
            //
            $id=$this->Request['id'];
            $cedula=$this->Request['cedula'];
            $sql1="select periodo from asistencias.vacaciones WHERE id = '$id'";
            $resultado1=cargar_data($sql1,$this);
            $periodo = $resultado1[0]['periodo'];
            $sql2="select * from asistencias.vacaciones_disfrute vd where ((vd.cedula='$cedula') and (vd.periodo='$periodo')) order by vd.fecha_desde";
            $disfrutes=cargar_data($sql2, $this);
            $this->DataGriddetalle->Caption = "Detalle de Disfrute de vacaciones del período: ".$periodo;
            $this->DataGriddetalle->DataSource=$disfrutes;
            $this->DataGriddetalle->dataBind();
        }
    }
    public function formatear2($sender, $param)
    {
        $item=$param->Item;
        /*if ($item->fecha_desde->Text != "Desde")
        {
            $item->fecha_desde->Text = cambiaf_a_normal($item->fecha_desde->Text);
        }
        if ($item->fecha_hasta->Text != "Hasta")
        {
            $item->fecha_hasta->Text = cambiaf_a_normal($item->fecha_hasta->Text);
        }*/
        if ($item->estatus->Text != "Estatus")
        {
            switch ($item->estatus->Text)
            {
                case 0: $item->estatus->Text = "PENDIENTE";
                    break;
                case 1: $item->estatus->Text = "APROBADO";
                        $item->estatus->BackColor = "Lime";
                    break;
                case 2: $item->estatus->Text = "RECHAZADO";
                        $item->estatus->BackColor = "Red";
                    break;
                case 3: $item->estatus->Text = "CANCELADO";
                        $item->estatus->BackColor = "orange";
                    break;
            }
        }
    }
    public function volver ($sender, $param)
    {
       $cedula=$this->Request['cedula'];
       $this->Response->Redirect( $this->Service->constructUrl('expedientes.expediente',array('cedula'=>$cedula)));
    }
}
?>