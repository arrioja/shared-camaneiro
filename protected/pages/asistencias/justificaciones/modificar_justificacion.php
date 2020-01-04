<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Página para modificar los datos de una justificacion.
 *****************************************************  FIN DE INFO
*/

class modificar_justificacion extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            //$this->cargar_tipo_faltas($this,$param);
           // $this->cargar_tipo_justificaciones($this,$param);
            // busco los nombres de los funcionarios
            $sql ="SELECT p.cedula,CONCAT(p.nombres,' ',p.apellidos) as nombre
                FROM asistencias.justificaciones_personas AS jp
                INNER JOIN organizacion.personas AS p ON (p.cedula=jp.cedula)
                WHERE ( jp.codigo_just ='".$this->Request[numero]."' )
               ";
            $resultado=cargar_data($sql,$this);

             if (empty($resultado) == false){
                foreach ($resultado as $funcionario)$this->funcionarios->Text.="C.I. $funcionario[cedula] $funcionario[nombre]</br> ";
                
                 // busco los nombres de los funcionarios
            $sql ="SELECT jd.fecha_desde AS desde, jd.fecha_hasta AS hasta, jd.hora_desde, jd.hora_hasta, jd.codigo_tipo_falta AS falta, jd.codigo_tipo_justificacion AS tipo, jd.observaciones,jd.lun,jd.mar,jd.mie,jd.jue,jd.vie,jd.hora_completa
                FROM asistencias.justificaciones_personas AS jp
                INNER JOIN asistencias.justificaciones_dias AS jd ON ( jd.codigo_just = jp.codigo_just )
                INNER JOIN asistencias.tipo_faltas AS tf ON ( tf.codigo = jd.codigo_tipo_falta )
                INNER JOIN asistencias.tipo_justificaciones AS tj ON ( tj.codigo = jd.codigo_tipo_justificacion )
                WHERE ( jp.codigo_just ='".$this->Request[numero]."' )
               ";
            $resultado=cargar_data($sql,$this);

            //datos
            $this->txt_fecha_desde->Text=cambiaf_a_normal($resultado[0][desde]);
            $this->txt_fecha_hasta->Text=cambiaf_a_normal($resultado[0][hasta]);
          
            //hora desde
            $this->txt_hora_desde->Text= date_format(date_create($resultado[0][hora_desde]), 'h:i:s');
            $hora=split(':',$resultado[0][hora_desde]);
            if($hora[0]>11)$this->txt_hora_desde->Text.=" PM";
            else $this->txt_hora_desde->Text.=" AM";
            //hora hasta
            $this->txt_hora_hasta->Text= date_format(date_create($resultado[0][hora_hasta]), 'h:i:s');
            $horaf=split(':',$resultado[0][hora_hasta]);
            if($horaf[0]>11)$this->txt_hora_hasta->Text.=" PM";
            else $this->txt_hora_hasta->Text.=" AM";

            //falta
            $this->cargar_tipo_faltas($this,$param);
            $this->drop_falta->Selectedvalue=$resultado[0][falta];
            //justiticacion
            $this->cargar_tipo_justificaciones($this,$param);
            $this->drop_tipo->Selectedvalue=$resultado[0][tipo];
            $this->txt_observacion->Text=$resultado[0][observaciones];
            //dias
            $this->lunes->checked=$resultado[0][lun];
            $this->martes->checked=$resultado[0][mar];
            $this->miercoles->checked=$resultado[0][mie];
            $this->jueves->checked=$resultado[0][jue];
            $this->viernes->checked=$resultado[0][vie];
            $this->hora_completa->checked=$resultado[0][hora_completa];

         }else{
                $this->Response->Redirect($this->Service->constructUrl('asistencias.justificaciones.listar_justificacion'));
         }//fin si


        }

    }
 // se lena el drop de los tipos de faltas para los permisos
	public function cargar_tipo_faltas($sender,$param)
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select tf.codigo, tf.descripcion
            from asistencias.tipo_faltas tf
            where (tf.cod_organizacion = '$cod_organizacion') and
                 (tf.visible = 'Si')
            order by descripcion";
        $resultado=cargar_data($sql,$sender);
		$this->drop_falta->DataSource=$resultado;
		$this->drop_falta->dataBind();
	}

    // Se llena el drop con los tipos de justificaciones para los permisos
	public function cargar_tipo_justificaciones($sender,$param)
	{
		//Busqueda de Registros
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select tj.codigo, tj.descripcion
            from asistencias.tipo_justificaciones tj
            where (tj.cod_organizacion = '$cod_organizacion') and
                 (tj.visible = 'Si')
            order by descripcion";
        $resultado=cargar_data($sql,$sender);
		$this->drop_tipo->DataSource=$resultado;
		$this->drop_tipo->dataBind();
	}
    /* Esta función se encarga de realizar validaciones a la fecha de inicio de
     * las vacaciones, que no sea feriado ni que sea fin de semana.
     */
    public function validar_fecha_inicio($sender, $param)
    {
        $dias_feriados = dias_feriados($sender);
        $fecha = $this->txt_fecha_desde->Text;
//        $fecha_actual_my = date('Y-m-d');
        // si es_feriado = 0 entonces es laborable normal
//        if ((cambiaf_a_mysql($fecha) >= $fecha_actual_my) && (es_feriado($fecha, $dias_feriados, $sender) == 0))
//      La condición siguiente obvia el hecho de que se este solicitando una vacación antes de la
//      fecha actual, esto es posible, aunque no recomendable, para que la misma funcione
//      se debe comentar la de arriba y descomentar la de abajo.
        if (es_feriado($fecha, $dias_feriados, $sender) == 0)
        { $param->IsValid = true; }
        else
        { $param->IsValid = false; }
         $param->IsValid = true;
    }
    /* Esta función se encarga de realizar validaciones a la fecha de inicio de
     * las vacaciones, que no sea feriado ni que sea fin de semana.
     */
  
    public function validar_fecha_hasta($sender, $param)
    {
        $dias_feriados = dias_feriados($sender);
        $fecha = $this->txt_fecha_hasta->Text;
 //       $fecha_actual_my = date('Y-m-d');
        // si es_feriado = 0 entonces es laborable normal
//        if ((cambiaf_a_mysql($fecha) >= $fecha_actual_my) && (es_feriado($fecha, $dias_feriados, $sender) == 0))
//      La condición siguiente obvia el hecho de que se este solicitando una vacación antes de la
//      fecha actual, esto es posible, aunque no recomendable, para que la misma funcione
//      se debe comentar la de arriba y descomentar la de abajo.
        if (es_feriado($fecha, $dias_feriados, $sender) == 0)
        { $param->IsValid = true; }
        else
        { $param->IsValid = false; }
         $param->IsValid = true;
    }

// checked los demas checkbox de los dias
public function checkboxClicked ($sender,$param){

    if($this->todos->checked){//si presiono todos
        $this->todos->checked="true";
        $this->lunes->checked="true";
        $this->martes->checked="true";
        $this->miercoles->checked="true";
        $this->jueves->checked="true";
        $this->viernes->checked="true";
    }else{
        $this->todos->checked="false";
        $this->lunes->checked="false";
        $this->martes->checked="false";
        $this->miercoles->checked="false";
        $this->jueves->checked="false";
        $this->viernes->checked="false";
    }//fin si


}//fin funcion


    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            //$this->btn_incluir->Enabled=false;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $fecha_desde = cambiaf_a_mysql($this->txt_fecha_desde->Text);
            $hora_desde= date("G:i",strtotime($this->txt_hora_desde->Text));
            $fecha_hasta = cambiaf_a_mysql($this->txt_fecha_hasta->Text);
            $hora_hasta=date("H:i",strtotime($this->txt_hora_hasta->Text));
            $falta = $this->drop_falta->SelectedValue;
            $tipo=rellena($this->drop_tipo->SelectedValue,3,"0");
            $motivo=$this->txt_observacion->Text;
            // se obtiene si la justificacion descuenta ticket
            $sql="SELECT descuenta_ticket FROM asistencias.tipo_justificaciones
                    WHERE (codigo='$tipo') AND (cod_organizacion = '$cod_organizacion')";
            $datos_t=cargar_data($sql,$sender);
            $descuento = $datos_t[0]['descuenta_ticket'];
            
            $sql="UPDATE asistencias.justificaciones_dias SET descuenta_ticket='$descuento',codigo_tipo_falta='$falta',codigo_tipo_justificacion='$tipo',fecha_desde='$fecha_desde',hora_desde='$hora_desde',fecha_hasta='$fecha_hasta',hora_hasta='$hora_hasta',observaciones='$motivo',
                   lun='".$this->lunes->checked."',mar='".$this->martes->checked."',mie='".$this->miercoles->checked."',jue='".$this->jueves->checked."',vie='".$this->viernes->checked."', hora_completa='".$this->hora_completa->checked."' WHERE codigo_just='".$this->Request[numero]."'";
            $resultado=modificar_data($sql,$sender);

            //Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha Modificado permiso : #".$this->Request[numero];
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->Redirect( $this->Service->constructUrl('asistencias.justificaciones.listar_justificacion'));//

        }
 	}

    public function btn_cancelar_click($sender, $param){

        $this->Response->Redirect( $this->Service->constructUrl('asistencias.justificaciones.listar_justificacion'));//

    }


}

?>
