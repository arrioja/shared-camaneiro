<?php
class nomina_actual extends TPage
{
    function check_codigo($sender,$param)
   {
       $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
     $param->IsValid=verificar_existencia("nomina.nomina_actual","cod",$this->txt_codigo->Text,array('cod_organizacion'=>$cod_org),$sender);
   }

   public function onLoad($param)
    {
    if (!$this->IsPostBack)
        {
        $sql="select periodo from nomina.periodos";
        $datos_periodos=cargar_data($sql,$this);
        $this->drop_periodos->DataSource=$datos_periodos;
        $this->drop_periodos->dataBind();


        $sql="select * from nomina.nomina_actual where cod_organizacion='".usuario_actual('cod_organizacion')."' and status=1";
        $datos_nomina=cargar_data($sql,$this);

        $this->txt_codigo->Text=$datos_nomina[0]['cod'];
        $this->txt_titulo->Text=$datos_nomina[0]['titulo'];
        $this->txt_fecha_i->Text=cambiaf_a_normal($datos_nomina[0]['f_ini']);
        $this->txt_fecha_f->Text=cambiaf_a_normal($datos_nomina[0]['f_fin']);
        $this->drop_periodos->SelectedValue=$datos_nomina[0]['periodo'];
        }
    }

    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.nominas.crear_nomina'));
    }

    public function guardar($serder,$param)
    {
        if ($this->IsValid)
        {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
//adaptar con transacciones **********************************************

        try
        {
        $db = $this->Application->Modules["db2"]->DbConnection;
        $db->Active=true;
        $ejecucion = $db->createCommand('begin')->execute();//inicia transaccion
        $sql="update nomina.nomina_actual set status='0' where cod_organizacion='$cod_org'";
	
        $ejecucion = $db->createCommand($sql)->execute();//poner todas las nominas anteriores inactivas segun la organizacion

		$cod=$this->txt_codigo->Text;
        $titulo=$this->txt_titulo->Text;
        $f_ini=cambiaf_a_mysql($this->txt_fecha_i->Text);
        $f_fin=cambiaf_a_mysql($this->txt_fecha_f->Text);
        $periodo=$this->drop_periodos->SelectedValue;

        $fecha_elab=date("Y-m-d");//fecha de elaboracion
        $sql="insert into nomina.nomina_actual (cod,titulo,f_ini,f_fin, f_elab,periodo,status, cod_organizacion) values('$cod', '$titulo', '$f_ini', '$f_fin', '$fecha_elab', '$periodo', '1','$cod_org')";
		
        $ejecucion = $db->createCommand($sql)->execute();//inserta la nueva nomina
        $ejecucion = $db->createCommand('commit')->execute();
        $db->Active=false;
	    $serder->Response->redirect($serder->Service->constructUrl('nomina.nominas.crear_nomina'));
        }
        catch(Exception $e)
        {
        $mensaje=$e->getMessage();
        $ejecucion = $db->createCommand('rollback')->execute();//devuelve transaccion
        $db->Active=false;
        $this->Response->redirect($this->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
        }
        }

    }

    public function cargar_descripcion($serder,$param)
    {
$periodo=$this->drop_periodos->SelectedValue;
$sql="select * from nomina.periodos where periodo='$periodo'";
        $datos_periodo=cargar_data($sql,$this);


        $this->txt_titulo->Text=$datos_periodo[0]['descripcion'];
        $this->txt_fecha_i->Text=cambiaf_a_normal($datos_periodo[0]['fecha_ini']);
        $this->txt_fecha_f->Text=cambiaf_a_normal($datos_periodo[0]['fecha_fin']);


	   //$serder->Response->redirect($serder->Service->constructUrl('nomina.nominas.crear_nomina'));
    }
}
?>
