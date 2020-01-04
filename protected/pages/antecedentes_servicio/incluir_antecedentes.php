<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class incluir_antecedentes extends TPage
{
    public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            //llena el listbox con las cedulas
            $cedula=$this->ddl1->SelectedValue;
            $sql="select concat(cedula, ' - ',nombres, apellidos) from organizacion.personas order by cedula asc";
            $resultado=cargar_data($sql, $this);
            $this->ddl1->DataSource=$resultado;
            $this->ddl1->dataBind();            
        }
    }
    function agregar_cargos($sender, $param)
    {
        $fecha_i=cambiaf_a_mysql($this->dp2->text);
        $fecha_e=cambiaf_a_mysql($this->dp3->text);
        $cargo=$this->t17->text;
        $ced=$this->ddl1->text;
        list($cedula)=split(' ',$ced);
        $sueldo=$this->t18->text;
        $sql1="insert into antecedentes.cargos_anteriores
            (fecha_ingreso, fecha_egreso, cargo, cedula, sueldo)
            values
            ('$fecha_i', '$fecha_e', '$cargo', '$cedula', '$sueldo')";
        $resultado1=modificar_data($sql1, $sender);
        $sql2="select * from antecedentes.cargos_anteriores where(cedula='$cedula') order by fecha_egreso desc";
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->Datasource = $resultado2;
        $this->dg1->dataBind();

        //$this->Response->Redirect( $this->Service->constructUrl('antecedentes_servicio.incluir_antecedentes'));
    }
    function llenar_grid($sender, $param)
    {
        //llena el grid con los cargos anteriores
        $ced=$this->ddl1->text;
        list($cedula)=split(' ',$ced);
        $sql9="select * from antecedentes.cargos_anteriores where(cedula='$cedula') order by fecha_egreso desc";
        $resultado9=cargar_data($sql9, $this);
        $this->dg1->DataSource=$resultado9;
        $this->dg1->dataBind();
    }
}
?>
