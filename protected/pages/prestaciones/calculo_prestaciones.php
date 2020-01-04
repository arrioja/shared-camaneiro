<?php
class calculo_prestaciones extends TPage
{
    public function onload($param)
    {
        $this->t2->text=date("d/m/Y");        
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {            
            //llena el listbox con las cedulas
            $sql="select distinct i.cedula, p.cedula, p.nombres, p.apellidos
                  from nomina.integrantes i, organizacion.personas p
                  where (i.cedula=p.cedula) order by i.cedula asc";
            $resultado=cargar_data($sql, $this);            
            $this->ddl1->DataSource=$resultado;
            $this->ddl1->dataBind();            
        }
    }
    public function nya($sender, $param)
    {
        $cedula=$this->ddl1->SelectedValue;
        $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula') ";
        $resultado=cargar_data($sql, $this);
        $this->t1->text=$resultado[0]['nombres'].' '.$resultado[0]['apellidos'];
    }    
    function DiasHabiles($fecha_inicial,$fecha_final)
    {
        list($dia,$mes,$year) = explode("-",$fecha_inicial);
        $ini = mktime(0, 0, 0, $mes , $dia, $year);
        list($diaf,$mesf,$yearf) = explode("-",$fecha_final);
        $fin = mktime(0, 0, 0, $mesf , $diaf, $yearf);

        $r = 1;
        while($ini != $fin)
        {
            $ini = mktime(0, 0, 0, $mes , $dia+$r, $year);
            $newArray[] .=$ini;
            $r++;
        }
        return $newArray;        
    }
}
?>
