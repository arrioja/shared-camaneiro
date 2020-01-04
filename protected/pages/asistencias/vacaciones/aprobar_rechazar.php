<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Mediante esta página, El usuario puede aprobar o rechazar las
 *              solicitudes de vacaciones que se encuentren pendientes; es
 *              necesario tener el permiso apropiado y el nivel requerido.
 *****************************************************  FIN DE INFO
*/

class aprobar_rechazar extends TPage
{
    var $conteo =1;
    var $cedula_conteo = "";
    var $vacaciones;
    public function onLoad($param)
    {
        parent::onLoad($param);
        if ((!$this->IsPostBack) && (!$this->IsCallBack))
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $cedula = usuario_actual('cedula');
            $resultado = vista_usuario($cedula,$cod_organizacion,'D',$this);

            // Se enlaza el nuevo arreglo con el listado de Direcciones
            $this->drop_direcciones->DataSource=$resultado;
            $this->drop_direcciones->dataBind();
        }
    }

/* Esta función se encarga de mostrar el listado con las solicitudes de vacaciones pendientes para la
 * dirección seleccionada.
 */
    public function consulta_vacaciones($sender, $param)
    {
         $dir = $this->drop_direcciones->SelectedValue;
         if($dir!=""){
       
        $sql ="select p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombres, v.id, v.dias_disfrute, v.dias_feriados, 
               v.dias_restados, v.fecha_desde, v.fecha_hasta, v.periodo
                  from asistencias.vacaciones_disfrute as v, organizacion.personas as p,
                    organizacion.personas_nivel_dir as c, organizacion.direcciones as d
                    where((v.cedula=p.cedula) and
                          (p.cedula=c.cedula) and
                          (d.codigo=c.cod_direccion) and
                          (c.cod_direccion LIKE '$dir%') and
                          (v.estatus = '0'))
                    order by p.nombres, p.apellidos, p.cedula,  v.fecha_desde";
        $this->vacaciones=cargar_data($sql,$sender);

        $this->DataGrid->DataSource=$this->vacaciones;
        $this->DataGrid->dataBind();
        }//fin si
    }


    public function aprobar_rechazar_click($opcion,$sender,$param)
    {
        // se marca el registro como aprobado o rechazado.
        $id=$sender->CommandParameter;
		$sql="UPDATE asistencias.vacaciones_disfrute set estatus='$opcion' where id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->consulta_vacaciones($sender, $param);

        // ahora, dependiendo si se aprueba o se rechaza, se toman acciones.
        if ($opcion == 2)
        { // si la solicitud fue rechazada, se muestra el mensaje correspondiente
        // se incluye el registro en la bitácora de eventos
            $descripcion_log = "Se ha rechazado la solicitud de vacaciones registrada con el id: ".$id;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'D',$descripcion_log,"",$sender);
            $this->LTB->titulo->Text = "Solicitud de Vacación Rechazada";
            $this->LTB->texto->Text = "La Solicitud de vacación ha sido rechazada.";
            $this->LTB->imagen->Imageurl = "imagenes/botones/mal.png";

        }
        else
        { // si la solicitud fue aprobada, se muestra el mensaje correspondiente

            $sql2="Select v.id, v.dias_disfrute, v.cedula, v.fecha_desde, v.fecha_hasta, v.dias_feriados,
                          v.periodo, v.dias_restados, CONCAT(p.nombres,' ',p.apellidos) as nombres
                   from asistencias.vacaciones_disfrute v, organizacion.personas p
                   where (v.id='$id') and (v.cedula=p.cedula)";
            $resultado=cargar_data($sql2,$sender);

            $dias_disfrute = $resultado[0]['dias_disfrute'];
            $dias_feriados = $resultado[0]['dias_feriados'];
            $dias_restados = $resultado[0]['dias_restados'];
            $cedula = $resultado[0]['cedula'];
            $periodo = $resultado[0]['periodo'];
            $desde = $resultado[0]['fecha_desde'];
            $hasta = $resultado[0]['fecha_hasta'];
            $nombres = $resultado[0]['nombres'];
            $id_vacacion = $resultado[0]['id'];


          // Se incluye la observación en el sistema de asistencias.
            $observaciones="El funcionario(a) ".$nombres.", titular de la cédula de identidad ".$cedula.", se encuentra ".
					  "disfrutando ".$dias_disfrute." días de vacaciones correspondientes al período ".$periodo.", a partir del día ".
					  cambiaf_a_normal($desde)." hasta el ".cambiaf_a_normal($hasta);

          // Descuento los dias de vacaciones que me especifican en el periodo de las vacaciones disponibles.
            $sql="UPDATE asistencias.vacaciones set pendientes=pendientes-'$dias_disfrute', disfrutados=disfrutados+'$dias_disfrute'
                  where cedula='$cedula' and periodo='$periodo'";
            $resultado=modificar_data($sql,$this);


          // si es necesario el descuento de días por horario especial, se llama a la función que se
          // encarga de realizarlo
          if ($dias_restados > 0)
            { // si hay que descontra algun dia, se llama a la funcion que lo hace.
              descuento_especial_de_dias($dias_restados,$cedula,$sender);
              $observaciones=$observaciones.". Se descontaron ".$dias_restados." días de sus vacaciones por horario especial.";
            }

          /* Se Inserta las observaciones correspondientes en el sistema de asistencias para
           * que aparezcan en los reportes. */
            $cod_organizacion = usuario_actual('cod_organizacion');
            $horario_vigente = obtener_horario_vigente($cod_organizacion,$desde,$sender);
            $hora_desde = $horario_vigente[0]['hora_entrada'];
            $hora_hasta = $horario_vigente[0]['hora_salida'];

            $codigo_nuevo=proximo_numero("asistencias.justificaciones","codigo",null,$sender);

            $sqli="insert into asistencias.justificaciones (codigo, tipo_id_doc, estatus)
	 			   values ('$codigo_nuevo','VA', '1')";
            $resultadoi=modificar_data($sqli,$sender);

            $sqli="insert into asistencias.justificaciones_personas (cedula, codigo_just)
		           values ('$cedula','$codigo_nuevo')";
            $resultadoi=modificar_data($sqli,$sender);

            // se coloca la relacion de la justificacion con la vacacion
            $sql="UPDATE asistencias.vacaciones_disfrute set referencia='$codigo_nuevo' WHERE id='$id_vacacion'";
            $resultado=modificar_data($sql,$sender);

            $sqli="insert into asistencias.justificaciones_dias (codigo_just, fecha_desde, hora_desde, fecha_hasta,
			              hora_hasta, codigo_tipo_falta, descuenta_ticket,lun,mar,mie,jue,vie,codigo_tipo_justificacion,
						  observaciones,hora_completa)
				   values ('$codigo_nuevo','$desde', '$hora_desde', '$hasta', '$hora_hasta','IN','No','1','1','1','1','1',
						   '1','$observaciones','1')";
            $resultadoi=modificar_data($sqli,$sender);

            // se incluye el registro en la bitácora de eventos
            $descripcion_log = "<strong>Aprobados</strong> ".$dias_disfrute." días de vacaciones de ".$nombres." C.I.: ".$cedula.
                               ", desde ".cambiaf_a_normal($desde)." al ".cambiaf_a_normal($hasta).
                               ", período ".$periodo." (".$dias_feriados." días feriados, ".$dias_restados.
                                      " días descontados). <strong> Cód. Observación: ".$codigo_nuevo."</strong> ";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'A',$descripcion_log,"",$sender);
            $this->LTB->titulo->Text = "Aprobado";
            $this->LTB->texto->Text = $descripcion_log;
            $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
        }

        $params = array('mensaje');
        $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

    }

    public function aprobar_click($sender,$param)
    {
        $this->aprobar_rechazar_click('1',$sender, $param);
    }

    public function rechazar_click($sender,$param)
    {
        $this->aprobar_rechazar_click('2',$sender, $param);
    }

/* Esta función se encarga de contar cuántas cédulas existen en el arreglo que
 * coincidan con la dada, el número de cédulas será usado para ajustar el RowCount
 * del grid de datos.
 */
    public function cuenta_cedulas($datos, $cedula)
    {
        $contador=0;
        foreach ($datos as $undato)
        {
            if ($undato['cedula'] == $cedula)
            {$contador++;}
        }
        return $contador;
    }


/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $color1 = array("#E6ECFF","#BFCFFF");

        $item=$param->Item;

        if ($item->cedula->Text != "Cedula")
        {
            if ($this->cedula_conteo != $item->cedula->Text )
                {
                    $item->cedula->RowSpan = $this->cuenta_cedulas($this->vacaciones,$item->cedula->Text);
                    $item->nombres->RowSpan = $item->cedula->RowSpan;
                    $this->cedula_conteo = $item->cedula->Text;
                    $item->cedula->BackColor = $color1[$this->conteo];
                    $item->nombres->BackColor = $color1[$this->conteo];
                    $this->conteo = !$this->conteo;
                }
            else
                {
                    $item->cedula->Visible = false;
                    $item->nombres->Visible = false;
                }
        }

        if ($item->hasta->Text != "Hasta")
        {
            $item->hasta->Text = cambiaf_a_normal($item->hasta->Text);
        }
        if ($item->desde->Text != "Desde")
        {
            $item->desde->Text = cambiaf_a_normal($item->desde->Text);
        }
    }


}
?>
