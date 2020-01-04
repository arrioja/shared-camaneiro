<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Página para la solicitud de Jsutificacion grupal por parte de l@s funionari@s seleccionados
 *              en la pantalla incluir_justificacion_grupal             
 *****************************************************  FIN DE INFO
*/

class incluir_justificacion_grupal2 extends TPage
{
    
    public function onLoad($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->cargar_tipo_faltas($this,$param);
            $this->cargar_tipo_justificaciones($this,$param);
           
            // busco los nombres de los funcionarios en la tabla temporal cargada en la pantalla anterior
           $sql="select p.cedula, CONCAT('(',p.nombres,' ',p.apellidos,')') as nombre
 						FROM asistencias.justificaciones_personas_temporal as t, organizacion.personas as p
						WHERE ((t.numero= '".$this->Request[numero]."') and (t.cedula=p.cedula) )
						";
            $resultado=cargar_data($sql,$this);

            if (empty($resultado) == false)
            foreach ($resultado as $funcionario)$this->funcionarios->Text.="C.I. $funcionario[cedula] $funcionario[nombre]</br> ";
            else
            $this->Response->Redirect( $this->Service->constructUrl('asistencias.justificaciones.incluir_justificacion_grupal'));

            }

    }

 //refresca la pagina
	public function limpiar($sender,$param)
	{

    $this->Response->Redirect( $this->Service->constructUrl('asistencias.justificaciones.incluir_justificacion_grupal'));//

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

/* Esta funcion se encarga de vaciar los campos del formulario para dejar todo limpio*/
    public function vaciar_campos()
    {
        $this->txt_nombre->Text = "";
        $this->btn_incluir->Enabled=false;
        $this->drop_tipo->SelectedIndex = 0;
        $this->drop_falta->SelectedIndex = 0;
    }


    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo exista en el sistema y se colocan los datos de la persona
     * que resulte seleccionada.
     */
    public function validar_cedula($sender, $param)
    {
        $cedula=$this->txt_cedula->Text;
        
        $cod_organizacion = usuario_actual('cod_organizacion');
        // Para comprobar que existen los datos de la persona en la organización del usuario.
        $sql="select p.nombres, p.apellidos from organizacion.personas p, organizacion.personas_nivel_dir n
              where (p.cedula='$cedula') and (n.cod_organizacion = '$cod_organizacion') and (p.cedula = n.cedula)";
        $datos=cargar_data($sql,$sender);
        if (empty($datos) == true)
        { // si no existe, se vacian los controles para forzar validación y
          // muestro un mensaje de error al usuario para que sepa que la cedula no se encuentra
            $this->LTB->titulo->Text = "Número de Cédula no encontrado";
            $this->LTB->texto->Text = "La cédula que introdujo no se encuentra en nuestros registros, ".
                                      "compruebe que sea correcta e inténtelo de nuevo, si el problema ".
                                      "persiste, comuníquese con la Dirección de Sistemas.";
            $this->LTB->imagen->Imageurl = "imagenes/botones/cedula_no.png";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
            
            $this->vaciar_campos();
            
        }
        else
        { // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_nombre->Text = $datos[0]['nombres']." ".$datos[0]['apellidos'];
            $this->btn_incluir->Enabled=true;              
        }
    }



    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo exista en el sistema (sólo a efectos del $this->isvalid.
     * Pareciera redundar, pero no.
     */
    public function validar_cedula2($sender, $param)
    {
        $cedula=$this->txt_cedula->Text;
        $cod_organizacion = usuario_actual('cod_organizacion');
        // Para comprobar que existen los datos de la persona en la organización del usuario.
        $sql="select p.nombres, p.apellidos from organizacion.personas p, organizacion.personas_nivel_dir n
              where (p.cedula='$cedula') and (n.cod_organizacion = '$cod_organizacion') and (p.cedula = n.cedula)";
        $datos=cargar_data($sql,$sender);
        if (empty($datos) == true)
        { // si no existe, se vacian los controles para forzar validación y
          // muestro un mensaje de error al usuario para que sepa que la cedula no se encuentra
            $this->LTB->titulo->Text = "Número de Cédula no encontrado";
            $this->LTB->texto->Text = "La cédula que introdujo no se encuentra en nuestros registros, ".
                                      "compruebe que sea correcta e inténtelo de nuevo, si el problema ".
                                      "persiste, comuníquese con la Dirección de Sistemas.";
            $this->LTB->imagen->Imageurl = "imagenes/botones/cedula_no.png";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
            $this->vaciar_campos();
            $param->IsValid = false;
        }
        else
        { $param->IsValid = true;}
    }




    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $this->btn_incluir->Enabled=false;

            $codigo_nuevo=proximo_numero("asistencias.justificaciones","codigo",null,$sender);
           // $cedula=$this->txt_cedula->Text;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $fecha_desde = cambiaf_a_mysql($this->txt_fecha_desde->Text);
            $hora_desde= date("G:i",strtotime($this->txt_hora_desde->Text));
            $fecha_hasta = cambiaf_a_mysql($this->txt_fecha_hasta->Text);
            $hora_hasta=date("G:i",strtotime($this->txt_hora_hasta->Text));
            $falta = $this->drop_falta->SelectedValue;
            
            //$tipo = (int)$this->drop_tipo->SelectedValue;
            $tipo_j =$this->drop_tipo->SelectedValue;
            $tipo=rellena($tipo_j,3,"0");

            // se obtiene si la justificacion descuenta ticket
            $sql="SELECT descuenta_ticket FROM asistencias.tipo_justificaciones
                    WHERE (codigo='$tipo') AND (cod_organizacion = '$cod_organizacion') ";
            $datos_t=cargar_data($sql,$sender);
            $descuento = $datos_t[0]['descuenta_ticket'];
            //$descuento = "Si";


            $observaciones = $this->txt_observacion->Text;

            $sql="insert into asistencias.justificaciones (codigo, tipo_id_doc, estatus)
                  values ('$codigo_nuevo','RH', '1')";
            $resultado=modificar_data($sql,$sender);

            // busco las cedulas de los funcionarios en la tabla temporal cargada en la pantalla anterior
            $sql="select t.cedula
 						FROM asistencias.justificaciones_personas_temporal as t, organizacion.personas as p
						WHERE ((t.numero= '".$this->Request[numero]."') and (t.cedula=p.cedula) ) 
						";
            $resultado=cargar_data($sql,$sender);
            
            // inserto todos los funcionarios con ese codigo de justificacion
            foreach ($resultado as $funcionarios){
                $sql="insert into asistencias.justificaciones_personas (cedula, codigo_just)
                      values ('$funcionarios[cedula]','$codigo_nuevo') ";
                $resultado=modificar_data($sql,$sender);
            }

            $sql="DELETE FROM asistencias.justificaciones_personas_temporal
						WHERE numero= '".$this->Request[numero]."' ";
            $resultado=modificar_data($sql,$sender);
            
            $sql="insert into asistencias.justificaciones_dias (codigo_just, fecha_desde, hora_desde, fecha_hasta,
			 	  hora_hasta, codigo_tipo_falta, descuenta_ticket,lun,mar,mie,jue,vie,codigo_tipo_justificacion,observaciones,hora_completa)
				  values ('$codigo_nuevo','$fecha_desde','$hora_desde','$fecha_hasta','$hora_hasta','$falta','$descuento',
				  '".$this->lunes->checked."','".$this->martes->checked."','".$this->miercoles->checked."','".$this->jueves->checked."','".$this->viernes->checked."','$tipo','$observaciones','".$this->hora_completa->checked."')";
            $resultado=modificar_data($sql,$sender);


                    // Se incluye el rastro en el archivo de bitácora
                    $descripcion_log = "Se ha incluido la justificacion Codigo:". $codigo_nuevo.
                                       " del Funcionario Cedula Nro: ".$cedula.
                                       " Motivo: ".$observaciones;
                    inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
                    $this->mensaje3->setSuccessMessage($sender, "Justificacion Codigo Nº $codigo_nuevo guardada exitosamente!!", 'grow');


//            $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.consulta_cedula',array('ced'=>$cedula)));//
// HACER IMPRESIÓN DE LA SOLICITUD DE PERMISO.
        }
 	}


}

?>
