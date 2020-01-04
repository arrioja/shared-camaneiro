<?php
class crear_nomina extends TPage
{
    public function onLoad($param)
    {
        if (!$this->IsPostBack)
        {
            if (!usuario_actual('tipo_nomina')!="")
                $this->Response->redirect($this->Service->constructUrl('nomina.set_nomina',array('redir'=>'nomina.nominas.crear_nomina')));
            else
            {
            $sql="select * from nomina.nomina_actual where cod_organizacion='".usuario_actual('cod_organizacion')."' and status='1'";
            $datos_nomina=cargar_data($sql,$this);

            $this->txt_codigo->Text=$datos_nomina[0]['cod'];
            $this->txt_titulo->Text=$datos_nomina[0]['titulo'];
            $this->txt_fecha_i->Text=cambiaf_a_normal($datos_nomina[0]['f_ini']);
            $this->txt_fecha_f->Text=cambiaf_a_normal($datos_nomina[0]['f_fin']);
            }

        }
    }
    function regresar()
  {
      $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
  }

    public function procesar($serder,$param)
    {
        if ($this->IsValid)
        {
        $tipo_nomina=usuario_actual('tipo_nomina');
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        try
        {
        $db = $this->Application->Modules["db2"]->DbConnection;
        $db->Active=true;
        if (verificar_nomina($db))
        {
        $ejecucion = $db->createCommand('begin')->execute();//inicia transaccion
            if (crear_nomina($tipo_nomina,$cod_org,$db))
                {
                $ejecucion = $db->createCommand('commit')->execute();//ejecuta transaccion
                $this->LTB->titulo->Text = "Crear Nómina";
                $this->LTB->texto->Text = "Se ha Creado exitosamente la Nómina de <strong> $tipo_nomina </strong>";
                $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                $this->LTB->redir->Text = "nomina.nominas.admin_nominas";
                $params = array('mensaje');
                $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
                 
               //         $this->btn_procesar->Text="hola";
                }
            else
                {
                $ejecucion = $db->createCommand('rollback')->execute();//devuelve transaccion
                $this->LTB->titulo->Text = "Crear Nómina";
                $this->LTB->texto->Text = "No se pudo crear la Nómina de <strong> $tipo_nomina </strong> error en inserción de registros a la BD";
                $this->LTB->imagen->Imageurl = "imagenes/botones/mal.png";
                $this->LTB->redir->Text = "nomina.nominas.admin_nominas";
                $params = array('mensaje');
                $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
                }
       //echo 'si';
       //crear_nomina($tipo_nomina,$cod_org,$db);
        }
        else
        {
            $ejecucion = $db->createCommand('rollback')->execute();//devuelve transaccion
            $this->LTB->titulo->Text = "Crear Nómina";
            $this->LTB->texto->Text = "Nomina de <strong> $tipo_nomina </strong> duplicada";
            $this->LTB->imagen->Imageurl = "imagenes/botones/mal.png";
            $this->LTB->redir->Text = "nomina.nominas.crear_nomina";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
        }
        $db->Active=false;
	    //$serder->Response->redirect($serder->Service->constructUrl('nomina.nominas.crear_nomina'));
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
}

?>
