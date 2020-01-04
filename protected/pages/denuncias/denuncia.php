<?php
class denuncia extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            //$this->fecha1->text=date("d/m/Y");
        }
    }    
    public function agregar($sender, $param)
    {
        if ($this->IsValid)
        {
            $fecha=$this->fecha1->text;
            list($dia, $mes, $ano) = split('/', $fecha);            
            $numero=$this->t1->text;            
            $nuevaf=cambiaf_a_mysql($this->fecha1->text);
            $denunciantes=$this->t2->text;
            $motivo=$this->t3->text;
            $ubicacion=$this->t4->text;
            $organismos=$this->t5->text;
            $documentos_consignados=$this->t6->text;
            $limitaciones=$this->t7->text;
            $estado=$this->t8->text;
            $observacion=$this->t9->text;
            $tipo=$this->rb1->SelectedValue;
            $login=usuario_actual('login');
            $sql1="insert into denuncias.denuncia(numero, fecha, denunciantes, motivo, ubicacion, organismos, estado, observacion, tipo, ano, documentos_consignados, limitaciones)
                   values('$numero', '$nuevaf', '$denunciantes', '$motivo', '$ubicacion', '$organismos', '$estado', '$observacion', '$tipo', '$ano', '$documentos_consignados', '$limitaciones')";
            $resultado1=modificar_data($sql1, $sender);
            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "El usuario ".$login." ha incluido una ".$tipo." en el sistema";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            //redirige la pagina a agregar_existente
            $this->Response->Redirect( $this->Service->constructUrl('denuncias.denuncia'));
        }
    }
}
?>
