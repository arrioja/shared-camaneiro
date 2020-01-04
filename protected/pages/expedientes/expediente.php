<?php
class expediente extends TPage
{
 function onload($param)
    {
       parent::onLoad($param);
       if (!$this->IsPostBack)
       {
           $sql="select *,concat(nombres,' ',apellidos,' (',cedula,')') as nombres from organizacion.personas order by nombres asc";
           $resultado=cargar_data($sql, $this);
           $this->ddl1->DataSource=$resultado;
           $this->ddl1->dataBind();
           $datos_seleccionados=$this->ddl1->SelectedValue;
           if($this->Request['cedula']!='')
           {
            $this->ddl1->SelectedValue=$this->Request['cedula'];
            $this->Request['cedula']='';
            $this->buscar($this, $param);
           }
       }
       
    }    
    public function buscar($sender,$param)
    {        
        $cedula=$this->ddl1->SelectedValue;
        $sql="select * from organizacion.personas where (cedula='$cedula') ";
        $resultado=cargar_data($sql, $this);
        $this->t1->text=$resultado[0]['nombres'];
        $this->t2->text=$resultado[0]['apellidos'];
        $this->t3->text=$resultado[0]['cedula'];
        $this->t4->text=$resultado[0]['edo_civil'];
        $f=cambiaf_a_normal($resultado[0]['fecha_nacimiento']);
        $this->t5->text=$f;
        $this->t6->text=$resultado[0]['lugar_nacimiento'];
        $this->t7->text=$resultado[0]['grado_instruccion'];
        $this->t8->text=$resultado[0]['profesion'];
        $this->t9->text=$resultado[0]['direccion'];
        $tlfs=$resultado[0]['tlf_habitacion'].' / '.$resultado[0]['tlf_celular'];
        $this->t10->text=$tlfs;
        $ruta=$this->i1->imageurl="imagenes/fotos/funcionarios/".$resultado[0]['foto'];
        $sql1="SELECT * FROM organizacion.personas_cargos where (cedula='$cedula') ";
        $resultado1=cargar_data($sql1, $this);
        $this->t12->text=$resultado1[0]['denominacion'];
        $this->t13->text=$resultado1[0]['condicion'];
        $sql2="SELECT * FROM nomina.integrantes where (cedula='$cedula') ";
        $resultado2=cargar_data($sql2, $this);
        $this->t18->text=$resultado2[0]['cod'];
        $sql3="SELECT monto_incidencia FROM nomina.nomina where(cedula='$cedula' and cod_incidencia='1000')order by cod desc";
        $resultado3=cargar_data($sql3, $this);
        $this->t19->text=$resultado3[0]['monto_incidencia']*2;
        //llena el tab de curriculo y anexos
        $sql4="SELECT * FROM expedientes.curriculo_anexos where (cedula='$cedula')";
        $resultado4=cargar_data($sql4, $this);
        $this->dg1->DataSource=$resultado4;
        $this->dg1->dataBind();
        //llena el tab de vacaciones
        $sql5="select id, periodo, CONCAT(anos_antiguedad,' + ',anos_antiguedad_otro) as anos, dias, disfrutados, pendientes, restados, disponible_desde, antiguo  from asistencias.vacaciones as v
               where (v.cedula='$cedula') order by v.disponible_desde";
        $resultado5=cargar_data($sql5,$sender);
        $this->dg2->DataSource=$resultado5;
        $this->dg2->dataBind();
        //llena el tab de permisos
        $sql6="SELECT j.codigo as id,j.codigo, jd.fecha_desde AS desde, jd.fecha_hasta AS hasta, jd.hora_desde, jd.hora_hasta, tf.descripcion AS falta, tj.descripcion AS tipo
                FROM asistencias.justificaciones AS j
                INNER JOIN asistencias.justificaciones_personas AS jp ON ( jp.codigo_just = j.codigo )
                INNER JOIN asistencias.justificaciones_dias AS jd ON ( jd.codigo_just = jp.codigo_just )
                INNER JOIN asistencias.tipo_faltas AS tf ON ( tf.codigo = jd.codigo_tipo_falta )
                INNER JOIN asistencias.tipo_justificaciones AS tj ON ( tj.codigo = jd.codigo_tipo_justificacion )
                WHERE (j.estatus = '1' and cedula='$cedula') GROUP BY j.codigo ORDER BY j.codigo DESC";
        $resultado6=cargar_data($sql6,$sender);
        $this->dg3->DataSource=$resultado6;
        $this->dg3->dataBind();
        //
        //llena el tab de amosnestaciones
        $sql7="SELECT * FROM expedientes.amonestaciones where (cedula='$cedula')";
        $resultado7=cargar_data($sql7, $this);
        $this->dg4->DataSource=$resultado7;
        $this->dg4->dataBind();
        //
        //llena el tab de constancias
        $sql8="SELECT * FROM expedientes.constancias_de_trabajo where (cedula='$cedula')";
        $resultado8=cargar_data($sql8, $this);
        $this->dg5->DataSource=$resultado8;
        $this->dg5->dataBind();
        //
        //llena el tab de nombramientos y puntos de cuenta
        $sql9="SELECT * FROM expedientes.nombramientos_ptos_cta where (cedula='$cedula')";
        $resultado9=cargar_data($sql9, $this);
        $this->dg6->DataSource=$resultado9;
        $this->dg6->dataBind();
        //
        //llena el tab de jubilaciones y pensiones
        $sql10="SELECT * FROM expedientes.jubilacion where (cedula='$cedula')";
        $resultado10=cargar_data($sql10, $this);
        $this->dg7->DataSource=$resultado10;
        $this->dg7->dataBind();
        //
        //llena el tab de sso
        $sql11="SELECT * FROM expedientes.sso where (cedula='$cedula')";
        $resultado11=cargar_data($sql11, $this);
        $this->dg8->DataSource=$resultado11;
        $this->dg8->dataBind();
        //
        //llena el tab de varios
        $sql12="SELECT * FROM expedientes.varios where (cedula='$cedula')";
        $resultado12=cargar_data($sql12, $this);
        $this->dg9->DataSource=$resultado12;
        $this->dg9->dataBind();
        //
        //llena el drop 10
        //$cedula=$this->drop_persona->SelectedValue;
        $sql13="SELECT id,denominacion,condicion,decreto_contrato,fecha_ini,fecha_fin,lugar_trabajo
               FROM organizacion.personas_cargos WHERE(cedula='$cedula') order by fecha_ini ASC ";
        $resultado13=cargar_data($sql13, $this);
        $this->dg10->DataSource=$resultado13;
        $this->dg10->dataBind();
    }
    public function nuevo_item0($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->disponible_desde->Text = cambiaf_a_normal($item->disponible_desde->Text);
        }
    }
    public function nuevo_item1($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha_inicio->Text = cambiaf_a_normal($item->fecha_inicio->Text);
            $item->fecha_final->Text = cambiaf_a_normal($item->fecha_final->Text);
        }
    }
    public function nuevo_item2($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->desde->Text = cambiaf_a_normal($item->desde->Text);
            $item->hasta->Text = cambiaf_a_normal($item->hasta->Text);            
        }
    }
    public function nuevo_item4($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);            
        }
    }
    public function nuevo_item5($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha_constancia->Text = cambiaf_a_normal($item->fecha_constancia->Text);
        }
    }
    public function nuevo_item6($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha1->Text = cambiaf_a_normal($item->fecha1->Text);
        }
    }
    public function nuevo_item7($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha2->Text = cambiaf_a_normal($item->fecha2->Text);
        }
    }
    public function nuevo_item8($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha3->Text = cambiaf_a_normal($item->fecha3->Text);
        }
    }
    public function nuevo_item9($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha4->Text = cambiaf_a_normal($item->fecha4->Text);
        }
    }
    public function agregar_curriculo_anexo($sender,$param)
    {       
       $cedula=$this->ddl1->SelectedValue;
       $this->Response->Redirect( $this->Service->constructUrl('expedientes.agregar_curriculo_anexo',array('cedula'=>$cedula)));//
    }
    public function agregar_amonestacion($sender,$param)
    {
       $cedula=$this->ddl1->SelectedValue;
       $this->Response->Redirect( $this->Service->constructUrl('expedientes.agregar_amonestacion',array('cedula'=>$cedula)));//
    }
    public function agregar_nombramiento_pto_cta($sender,$param)
    {
       $cedula=$this->ddl1->SelectedValue;
       $this->Response->Redirect( $this->Service->constructUrl('expedientes.agregar_nombramiento_pto_cta',array('cedula'=>$cedula)));//
    }
    public function agregar_jubilacion_pension($sender,$param)
    {
       $cedula=$this->ddl1->SelectedValue;
       $this->Response->Redirect( $this->Service->constructUrl('expedientes.agregar_jubilacion_pension',array('cedula'=>$cedula)));//
    }
    public function agregar_sso($sender,$param)
    {
       $cedula=$this->ddl1->SelectedValue;
       $this->Response->Redirect( $this->Service->constructUrl('expedientes.agregar_sso',array('cedula'=>$cedula)));//
    }
    public function agregar_varios($sender,$param)
    {
       $cedula=$this->ddl1->SelectedValue;
       $this->Response->Redirect( $this->Service->constructUrl('expedientes.agregar_varios',array('cedula'=>$cedula)));//
    }
    function editar_curriculo_anexo($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $cedula=$sender->CommandParameter[1];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.editar_curriculo_anexo',array('id'=>$id, 'cedula'=>$cedula)));
    }    
    function editar_amonestaciones($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $cedula=$sender->CommandParameter[1];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.editar_amonestaciones',array('id'=>$id, 'cedula'=>$cedula)));
    }
    function editar_nombramientos($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $cedula=$sender->CommandParameter[1];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.editar_nombramientos',array('id'=>$id, 'cedula'=>$cedula)));
    }
    function editar_jubilacion($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $cedula=$sender->CommandParameter[1];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.editar_jubilacion',array('id'=>$id, 'cedula'=>$cedula)));
    }
    function editar_sso($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $cedula=$sender->CommandParameter[1];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.editar_sso',array('id'=>$id, 'cedula'=>$cedula)));
    }
    function editar_varios($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $cedula=$sender->CommandParameter[1];
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.editar_varios',array('id'=>$id, 'cedula'=>$cedula)));
    }
    function detalle_vacacion($sender, $param)
    {
        $id=$sender->CommandParameter[0];
        $cedula=$this->ddl1->SelectedValue;
        $this->Response->Redirect( $this->Service->constructUrl('expedientes.detalle_vacacion',array('id'=>$id, 'cedula'=>$cedula)));
    }    
}
?>
