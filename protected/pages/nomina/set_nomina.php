<?php

class set_nomina extends TPage
{

    public function cargar_tipo_nomina()
    {
         $cod_organizacion=usuario_actual('cod_organizacion');
         $sql="select * from nomina.tipo_nomina where cod_organizacion='$cod_organizacion'";
         $resultado=cargar_data($sql,$this);
         return $resultado;
    }





    function set($sender)
    {


    if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
        {
            $sesion=new THttpSession;
            $sesion->open();
            //$resultado_drop = obtener_seleccion_drop($this->drop_tipo_nomina);
            
	    
            $sesion['tipo_nomina']=$this->drop_tipo_nomina->SelectedValue;
            $sesion->close(); 
            if (empty($this->Request['redir']))
                $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
            else
                $this->Response->redirect($this->Service->constructUrl($this->Request['redir']));//si hay redireccionamiento
               //$this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes',array('codigo'=>'00002')));
        }
    }



public function onLoad($param)
	{

       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
        $this->drop_tipo_nomina->DataSource=$this->cargar_tipo_nomina();
        $this->drop_tipo_nomina->dataBind();
        }
        
   }
}

?>
