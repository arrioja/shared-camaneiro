<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class editar extends TPage
{
    public function onLoad($param)
	{
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            //$this->fecha1->text=date("d/m/Y");
            //
            $cadena=$this->Request['id'];
            $this->oculto->text=$cadena;
            $sql="select * from denuncias.denuncia where(id='$cadena')";
            $resultado=cargar_data($sql, $this);
            $this->t1->text=$resultado[0]['numero'];
            $f=$resultado[0]['fecha'];
            echo $f;
            $this->fecha1->text=cambiaf_a_normal($f);
            list($dia, $mes, $ano) = split('/', $this->fecha1->text);
            $this->t2->text=$resultado[0]['denunciantes'];
            $this->t3->text=$resultado[0]['motivo'];
            $this->t4->text=$resultado[0]['ubicacion'];
            $this->t5->text=$resultado[0]['organismos'];
            $this->t6->text=$resultado[0]['documentos_consignados'];
            $this->t7->text=$resultado[0]['limitaciones'];
            $this->t8->text=$resultado[0]['estado'];
            $this->t9->text=$resultado[0]['observacion'];
        }
    }
    public function guardar($sender, $param)
    {
        if ($this->IsValid)
        {
        $id=$this->oculto->text;//guardo el id del registro en un campo oculto en la forma
        $numero=$this->t1->text;
        list($dia, $mes, $ano) = split('/', $this->fecha1->text);
        $fecha=cambiaf_a_mysql($this->fecha1->text);
        $denunciantes=$this->t2->text;
        $motivo=$this->t3->text;
        $ubicacion=$this->t4->text;
        $organismos=$this->t5->text;
        $documentos_consignados=$this->t6->text;
        $limitaciones=$this->t7->text;
        $estado=$this->t8->text;
        $observacion=$this->t9->text;
        $sql2="update denuncias.denuncia set
        numero='$numero', fecha='$fecha', denunciantes='$denunciantes', motivo='$motivo', ubicacion='$ubicacion', organismos='$organismos',
        estado='$estado', observacion='$observacion', ano='$ano', documentos_consignados='$documentos_consignados', limitaciones='$limitaciones'
        where(id='$id')";
        $resultado2=modificar_data($sql2, $sender);
        /* Se incluye el rastro en el archivo de bitácora */
        $login=usuario_actual('login');
        $descripcion_log = "El usuario ".$login." ha modificado una actuacion en el sistema ";
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        //redirige la pagina a agregar_existente
        $this->Response->Redirect( $this->Service->constructUrl('denuncias.modificar_denuncia'));
        }
    }
}
?>
