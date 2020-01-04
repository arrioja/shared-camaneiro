<?php
class evaluacion_continua extends TPage
{
	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            //llena el drop de cedula
            $sql="select cedula from organizacion.personas";
            $cedulas=cargar_data($sql,$this);
            $this->drop_cedula->DataSource=$cedulas;
            $this->drop_cedula->dataBind();
            /*//llena el drop de competencia
            $sql="select * from evaluaciones.competencias";
            $resultado=cargar_data($sql, $this);
            $this->drop_competencia->DataSource=$resultado;
            $this->drop_competencia->dataBind();*/
        }
        
	}
    public function buscar_datos($sender, $param)
    {
        //con la cedula seleccionada llena los campos sombreados de la primera tabla
        $cedula=$this->drop_cedula->Text;
        $sql="select * from organizacion.personas where (cedula='$cedula')";
        $resultado=cargar_data($sql,$sender);
        $this->id_apellidos->Text=$resultado[0]["apellidos"];
        $this->id_nombres->Text=$resultado[0]["nombres"];
        $sql="";
        $sql="select * from organizacion.personas_cargo where(cedula='$cedula')";
        $resultado=cargar_data($sql,$sender);
        $this->id_cargo->Text=$resultado[0]["denominacion"];
        $this->id_tipo->Text=$resultado[0]["condicion"];
        //llena el drop_evaluacion para seleccionar el codigo de la evalaucion que se asociara
        $sql="";
        $sql="select codigo from evaluaciones.datos_evaluados where(cedula='$cedula')";
        $codigos=cargar_data($sql, $this);
        $this->drop_evaluacion->DataSource=$codigos;
        $this->drop_evaluacion->dataBind();
    }
    public function cargar_odi($sender, $param)
    {
        //lee los odi de la tabla detalle_evaluacion asociados al codigo seleccionado y los vacia en el droplist
        $codigo_evaluacion=$this->drop_evaluacion->Text;
        $sql="select * from evaluaciones.detalle_evaluacion where(codigo_evaluacion='$codigo_evaluacion')";
        $resultado=cargar_data($sql, $sender);
        $this->drop_odi->DataSource=$resultado;
        $this->drop_odi->dataBind();
        //limpio la temporal antes de usarla
        $sql="truncate table evaluaciones.codigos_odi";
        $resultado=modificar_data($sql, $sender);
        //guardo el codigo de la evalaucion en una variable
        $cod_odi=$this->drop_evaluacion->Text;
        //lleno la temporal con los odi asociados al codigo correspoendiente
        $sql="insert into evaluaciones.codigos_odi (cod_odi) select cod_odi1 from evaluaciones.detalle_evaluacion where(codigo_evaluacion='$cod_odi')";
        $resultado=modificar_data($sql, $sender);        
        $sql="insert into evaluaciones.codigos_odi (cod_odi) select cod_odi2 from evaluaciones.detalle_evaluacion where(codigo_evaluacion='$cod_odi')";
        $resultado=modificar_data($sql, $sender);
        $sql="insert into evaluaciones.codigos_odi (cod_odi) select cod_odi3 from evaluaciones.detalle_evaluacion where(codigo_evaluacion='$cod_odi')";
        $resultado=modificar_data($sql, $sender);
        $sql="insert into evaluaciones.codigos_odi (cod_odi) select cod_odi4 from evaluaciones.detalle_evaluacion where(codigo_evaluacion='$cod_odi')";
        $resultado=modificar_data($sql, $sender);
        $sql="insert into evaluaciones.codigos_odi (cod_odi) select cod_odi5 from evaluaciones.detalle_evaluacion where(codigo_evaluacion='$cod_odi')";
        $resultado=modificar_data($sql, $sender);
        //leo la temporal para llenar el droplist
        $sql="select * from evaluaciones.codigos_odi";
        $resultado=cargar_data($sql, $sender);
        $this->drop_odi->DataSource=$resultado;
        $this->drop_odi->dataBind();
        //vacio la temporal
        $sql="truncate table evaluaciones.codigos_odi";
        $resultado=modificar_data($sql, $sender);
    }
    public function guardar_datos($sender, $param)
    {
        $nombres=$this->id_nombres->Text;
        $apellidos=$this->id_apellidos->Text;
        $cedula=$this->drop_cedula->Text;
        $numero=$this->id_numero->Text;
        $cargo=$this->id_cargo->Text;
        $tipo=$this->id_tipo->Text;
        $tipoactividad=$this->id_tipo_act->Text;
        $fechaini=$this->id_fecha_ini->Text;
        $horaini=$this->id_hora_ini->Text;
        $fechafinest=$this->id_fecha_est->Text;
        $horafinest=$this->id_hora_est->Text;
        $fechafindef=$this->id_fecha_def->Text;
        $horafindef=$this->id_hora_def->Text;
        $observaciones=$this->id_observaciones->Text;
        $desempeno=$this->id_desempeno->Text;
        $firmaempleado=$this->id_firma_empleado->Text;
        $firmaevaluador=$this->id_firma_evaluador->Text;
        $odivinculado=$this->drop_odi->Text;
        $evaluacion_asociada=$this->drop_evaluacion->Text;        
        $sql="insert into evaluaciones.evaluacioncontinua
              (nombres, apellidos, cedula, numero, cargo, tipocargo, tipoactividad, fechaini, horaini, fechafinest, horafinest, fechafindef, horafindef, observaciones, desempeno, firmaempleado, firmaevaluador, odivinculado, evaluacion_asociada)
              values('".$nombres."', '".$apellidos."','".$cedula."', '".$numero."', '".$cargo."', '".$tipo."', '".$tipoactividad."', '".$fechaini."', '".$horaini."', '".$fechafinest."', '".$horafinest."', '".$fechafindef."', '".$horafindef."', '".$observaciones."', '".$desempeno."', '".$firmaempleado."', '".$firmaevaluador."', '".$odivinculado."', '".$evaluacion_asociada."')";
        $ejecutar=modificar_data($sql, $sender);
    }
}
?>