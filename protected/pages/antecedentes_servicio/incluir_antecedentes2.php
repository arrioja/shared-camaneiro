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
    function llenar_datos($sender, $param)
    {
        $ci=$this->ddl1->SelectedValue;
        list($cedula)=split(' ',$ci);        
        $sql="select * from organizacion.personas where (cedula='$cedula')";
        $resultado=cargar_data($sql, $this);        
        /*$this->t1->text=$resultado[0]['nombres'].' '.$resultado[0]['apellidos'];*/
        //fecha de ingreso
        $fi=cambiaf_a_normal($resultado[0]['fecha_ingreso']);
        $this->t2->text=$fi;
        //cargo de ingreso
        $sql1="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_ingreso asc";
        $resultado1=cargar_data($sql1, $this);
        //$this->t3->text=$resultado1[0]['cargo'];
        //$this->t5->text=$resultado1[0]['cargo'];
        //fecha de egreso del funcionario
        $sql2="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_egreso desc";
        $resultado2=cargar_data($sql2, $this);
        //$this->t4->text=cambiaf_a_normal($resultado2[0]['fecha_egreso']);
        //$this->t5->text=$resultado2[0]['cargo'];
        //---
        //$sql3="select monto_incidencia from nomina.nomina_historial where (cedula='$cedula')and(cod_incidencia='1000') order by monto_incidencia asc";
        //$resultado3=cargar_data($sql3, $this);
        //$this->t6->text=$resultado3[0]['monto_incidencia']*2;
        //---
        //$sql4="select monto_incidencia from nomina.nomina_historial where (cedula='$cedula')and(cod_incidencia='0008') order by monto_incidencia asc";
        //$resultado4=cargar_data($sql4, $this);
        //$this->t10->text=$resultado4[0]['monto_incidencia'];
        //---
        //$sql5="select monto_incidencia from nomina.nomina_historial where (cedula='$cedula')and(cod_incidencia='0009') order by monto_incidencia asc";
        //$resultado5=cargar_data($sql5, $this);
        //$this->t12->text=$resultado5[0]['monto_incidencia'];
        //---
        //$sql6="select monto_incidencia from nomina.nomina_historial where (cedula='$cedula')and(cod_incidencia='0001') order by monto_incidencia asc";
        //$resultado6=cargar_data($sql6, $this);
        //$this->t14->text=$resultado6[0]['monto_incidencia'];
        //llena el grid con los cargos anteriores
        //$sql9="select * from antecedentes.cargos_anteriores where(cedula='$cedula') order by fecha_egreso desc";
        //$resultado9=cargar_data($sql9, $this);
        //$this->dg1->DataSource=$resultado9;
        //$this->dg1->dataBind();
    }
    function agregar_cargos($sender, $param)
    {
        $fecha_i=cambiaf_a_mysql($this->dp2->text);        
        $fecha_e=cambiaf_a_mysql($this->dp3->text);
        $cargo=$this->t17->text;
        $ced=$this->ddl1->text;
        $sueldo=$this->t18->text;
        $sql1="insert into antecedentes.cargos_anteriores
            (fecha_ingreso, fecha_egreso, cargo, cedula, sueldo)
            values
            ('$fecha_i', '$fecha_e', '$cargo', '$ced', '$sueldo')";
        $resultado1=modificar_data($sql1, $sender);        
        $sql2="select * from antecedentes.cargos_anteriores where(cedula='$ced') order by fecha_egreso desc";
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->Datasource = $resultado2;
        $this->dg1->dataBind();

        //$this->Response->Redirect( $this->Service->constructUrl('antecedentes_servicio.incluir_antecedentes'));
    }
    function agregar_antecedentes($sender, $param)
    {
        $cedula=$this->ddl1->text;
        $sql="select id from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado=cargar_data($sql, $this);
        $id=$resultado[0]['id'];        
        if($id<1)
        {
            //se agrega un nuevo registro
            $na=$this->t1->text;
            $fi=cambiaf_a_mysql($this->t2->text);
            $cargo_i=$this->t3->text;
            $sb_i=$this->t6->text;
            $comp_i=$this->t8->text;
            $prima_prof_i=$this->t10->text;
            $prima_res_i=$this->t12->text;
            $prima_ant_i=$this->t14->text;
            $total_i=$sb_i+$comp_i+$prima_prof_i+$prima_res_i+$prima_ant_i;
            $fe=$this->t4->text;
            $cargo_e=$this->t5->text;
            $sb_e=$this->t7->text;
            $comp_e=$this->t9->text;
            $prima_prof_e=$this->t11->text;
            $prima_res_e=$this->t13->text;
            $prima_ant_e=$this->t15->text;
            $total_e=$sb_e+$comp_e+$prima_prof_e+$prima_res_e+$prima_ant_e;
            $causa_e=$this->ddl2->SelectedValue;
            if($this->cb1->Checked=="true")
            {
                $djp=$this->cb1->Value;
            }
            else
            {
                $djp="no";
            }
            if($this->cb2->Checked=="true")
            {
                $pago=$this->cb2->Value;;
            }
            else
            {
                $pago="no";
            }
            $anticipos=$this->t16->text;
            $observaciones=$this->t19->text;
            $sql2="insert into antecedentes.antecedentes
                 (cedula, na, fecha_ingreso, cargo_ingreso, sb_ingreso, comp_ingreso, prima_prof_ingreso, prima_res_ingreso, prima_ant_ingreso, total_ingreso,
                 fecha_egreso, cargo_egreso, sb_egreso, comp_egreso, prima_prof_egreso, prima_res_egreso, prima_ant_egreso, total_egreso, causa_egreso,
                 djp, pago, anticipos, observaciones)
                 values
                 ('$cedula', '$na', '$fi', '$cargo_i', '$sb_i', '$comp_i', '$prima_prof_i', '$prima_res_i', '$prima_ant_i', '$total_i',
                 '$fe', '$cargo_e', '$sb_e', '$comp_e', '$prima_prof_e', '$prima_res_e', '$prima_ant_e', '$total_e', '$causa_e', '$djp', '$pago', '$anticipos', '$observaciones')";
                 $resultado2=modificar_data($sql2, $sender);
        }
        if($id>=1)
        {            
            //se modifica el registro existente
            $na=$this->t1->text;
            $fi=cambiaf_a_mysql($this->t2->text);
            $cargo_i=$this->t3->text;
            $sb_i=$this->t6->text;
            $comp_i=$this->t8->text;
            $prima_prof_i=$this->t10->text;
            $prima_res_i=$this->t12->text;
            $prima_ant_i=$this->t14->text;
            $total_i=$sb_i+$comp_i+$prima_prof_i+$prima_res_i+$prima_ant_i;
            $fe=$this->t4->text;
            $cargo_e=$this->t5->text;
            $sb_e=$this->t7->text;
            $comp_e=$this->t9->text;
            $prima_prof_e=$this->t11->text;
            $prima_res_e=$this->t13->text;
            $prima_ant_e=$this->t15->text;
            $total_e=$sb_e+$comp_e+$prima_prof_e+$prima_res_e+$prima_ant_e;
            $causa_e=$this->ddl2->SelectedValue;
            if($this->cb1->Checked=="true")
            {
                $djp=$this->cb1->Value;
            }
            else
            {
                $djp="no";
            }
            if($this->cb2->Checked=="true")
            {
                $pago=$this->cb2->Value;;
            }
            else
            {
                $pago="no";
            }
            $anticipos=$this->t16->text;
            $observaciones=$this->t19->text;
            $sql3="update antecedentes.antecedentes set cedula='$cedula', na='$na', fecha_ingreso='$fi', cargo_ingreso='$cargo_i',
                   sb_ingreso='$sb_i', comp_ingreso='$comp_i', prima_prof_ingreso='$prima_prof_i', prima_res_ingreso='$prima_res_i',
                   prima_ant_ingreso='$prima_ant_i', total_ingreso='$total_i', fecha_egreso='$fe', cargo_egreso='$cargo_e',
                   sb_egreso='$sb_e', comp_egreso='$comp_e', prima_prof_egreso='$prima_prof_e', prima_res_egreso='$prima_res_e',
                   prima_ant_egreso='$prima_ant_e', total_egreso='$total_e', causa_egreso='$causa_e', djp='$djp', pago='$pago',
                   anticipos='$anticipos', observaciones='$observaciones'";
            $resultado3=modificar_data($sql3, $sender);
        }        
    }
}
?>
