<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Mediante esta página, el usuario puede solicitar días de
 *              disfrute de sus vacaciones pendientes.
 *****************************************************  FIN DE INFO
*/

class solicitar_vacacion_personal extends TPage
{
    var $vacio = array(); // para vaciar listados y combos en caso de que la cedula no sea correcta

    public function onLoad($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->txt_cedula->text= usuario_actual('cedula');
            $this->validar_cedula($this, $param);
        }

    }

 //refresca la pagina
	public function limpiar($sender,$param)
	{

    $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.solicitar_vacacion_personal'));//

	}
    /* esta función se encarga de dar formato dd/mm/aaaa a las fechas que se
     * muestren como parte del listado de vacaciones disponibles del funcionario.
     */
    public function formatear_fecha($sender, $param)
    {
/*        $item=$param->Item;
        if ($item->disponible_desde->Text != "")
        {
            $item->disponible_desde->Text = cambiaf_a_normal($item->disponible_desde->Text);
        }
*/
    }

    /* Esta función se encarga de realizar validaciones a la fecha de inicio de
     * las vacaciones, que no sea feriado ni que sea fin de semana.
     */
    public function validar_fecha_inicio($sender, $param)
    {
        $dias_feriados = dias_feriados($sender);
        $fecha = $this->txt_fecha_desde->Text;
        $fecha_actual_my = date('Y-m-d');
        // si es_feriado = 0 entonces es laborable normal
//        if ((cambiaf_a_mysql($fecha) >= $fecha_actual_my) && (es_feriado($fecha, $dias_feriados, $sender) == 0))
//      La condición siguiente obvia el hecho de que se este solicitando una vacación antes de la
//      fecha actual, esto es posible, aunque no recomendable, para que la misma funcione
//      se debe comentar la de arriba y descomentar la de abajo.
        if (es_feriado($fecha, $dias_feriados, $sender) == 0)
        { $param->IsValid = true; }
        else
        { $param->IsValid = false; }
    }

/* Esta funcion se encarga de vaciar los campos del formulario para dejar todo limpio*/
    public function vaciar_campos()
    {
        $this->txt_nombre->Text = "";
        $this->Repeater->DataSource=$this->vacio;
        $this->Repeater->dataBind();
        $this->num_dias->Datasource = $this->vacio;
        $this->num_dias->dataBind();
        $this->btn_incluir->Enabled=false;
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
            $sql="select * from asistencias.vacaciones as v
                  where((v.cedula='$cedula') and (v.pendientes>'0'))
				  order by v.disponible_desde";
            $datos_pendientes=cargar_data($sql,$sender);
            $this->Repeater->DataSource=$datos_pendientes;
            $this->Repeater->dataBind();

            $sql="select sum(v.pendientes) as sumatoria from asistencias.vacaciones as v
                   where((v.cedula='$cedula') and (v.pendientes>'0'))
				   order by v.disponible_desde";
            $datos_sumatoria=cargar_data($sql,$sender);
            $dias_acumulados = $datos_sumatoria[0]['sumatoria'];

            for ($x = 1 ; $x <= $dias_acumulados ; $x++)
            {
                if ($x < 10)
                  {   $dia_acum ='0'.$x;}
                else
                  {$dia_acum = $x;}
                $dias_acum[$dia_acum] = $dia_acum;
            }
            $this->lbl_num_dias->Text = $dias_acumulados; // el valor de este label es usado en incluirbtn
            $this->num_dias->Datasource = $dias_acum;
            $this->num_dias->dataBind();

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
        {   $this->btn_incluir->Enabled=false;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $numd = $this->num_dias->SelectedValue;
            $cedula=$this->txt_cedula->Text;
            $fecha_inicio = $this->txt_fecha_desde->Text;
            $dias_feriados = dias_feriados($sender);
            $fecha_fin  = suma_dias_habiles($fecha_inicio, $numd, $dias_feriados, $sender);
            $num_feriados_total = cuenta_feriados($fecha_inicio, $numd, $dias_feriados, $sender);
            
            $fecha_fin_my = cambiaf_a_mysql($fecha_fin);
            $fecha_inicio_my = cambiaf_a_mysql($fecha_inicio);

            $sql="select * from asistencias.opciones
                  where ((cod_organizacion = '$cod_organizacion')) order by status";
            $horarios=cargar_data($sql,$sender);

            $dias_restados = calcula_dias_restados($fecha_inicio_my,$numd,$horarios,$dias_feriados,$sender);

            /* Se comprueba que la suma de los días que pidió y los días que se
             * le deben restar no sea mayor que los días que tenga pendientes pq
             * si es asi, entonces no tiene dias suficientes para irse de vacaciones
             */
            if (($numd + $dias_restados) > $this->lbl_num_dias->Text)
            { // si no existe, se vacian los controles para forzar validación y
              // muestro un mensaje de error al usuario para que sepa que la cedula no se encuentra
                $this->LTB->titulo->Text = "Días Insifucientes, Necesita: ".($numd + $dias_restados);
                $this->LTB->texto->Text = "Los ".$this->lbl_num_dias->Text." días disponibles de vacaciones ".
                                          "no cubren los ".$numd." días que desea disfrutar ".
                                          " + los ".$dias_restados." días descontados por horario especial. ".
                                          "Seleccione menos días de disfrute.";
                $this->LTB->imagen->Imageurl = "imagenes/botones/prohibido.png";
                $params = array('mensaje');
                $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
            }
            else
            {
               $comprueba_periodos = comprobar_fechas_periodos($fecha_inicio, $numd, $cedula, $dias_feriados, $sender);
               if ($comprueba_periodos == false)
               {
                    $this->LTB->titulo->Text = "Periodos no continuos.";
                    $this->LTB->texto->Text = "Para poder disfrutar del número de días de vacaciones ".
                                              "que desea, debe solicitarlos desde una fecha en la que ".
                                              "se unan varios períodos, compruebe la fecha de vencimiento ".
                                              "de los mismos. ";
                    $this->LTB->imagen->Imageurl = "imagenes/botones/prohibido.png";
                    $params = array('mensaje');
                    $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
               }
               else
               {
                /* si todo esta en orden, se procede a guardar los datos de la solicitud de
                 * vacaciones y se espera por la aprobación del director respectivo.
                 */
                    $sql="select * from asistencias.vacaciones as v
                          where((v.cedula='$cedula') and (v.pendientes > '0'))
                          order by v.disponible_desde";

                    // La siguiente sección de código, incluye la solicitud de vacaciones
                    // separadamente por períodos.
                    $fecha_fin2_my = cambiaf_a_mysql($fecha_fin);
                    $fecha_inicio2_my = cambiaf_a_mysql($fecha_inicio);
                    $fecha_inicio2 = $fecha_inicio;

                    $datos_pendientes=cargar_data($sql,$sender);
                    $cuenta_periodo = count ($datos_pendientes) -1;
                    $xcount=0; $dias = $numd;
                    while (($dias > 0) && ($xcount <= $cuenta_periodo))
                    { // mientras hayas dias solicitados
                        if ($dias > $datos_pendientes[$xcount]['pendientes'])
                        { // si aun no se pagan los dias completos.
                            $dias_disfrute = $datos_pendientes[$xcount]['pendientes'];
                            $restados = 0;
                            $dias = $dias - $datos_pendientes[$xcount]['pendientes'];
                        }
                        else
                        { // si se pagan completos los dias.
                            $dias_disfrute = $dias;
                            $restados = $dias_restados;
                            $dias = 0;
                        }

                        // se incluye en la bd la solicitud de vacaciones del periodo en cuestión.
                        $periodo = $datos_pendientes[$xcount]['periodo'];
                        $num_feriados = cuenta_feriados($fecha_inicio2, $dias_disfrute, $dias_feriados, $sender);
                        $fecha_fin2_my  = cambiaf_a_mysql(suma_dias_habiles($fecha_inicio2, $dias_disfrute, $dias_feriados, $sender));

                        $sql2="insert into asistencias.vacaciones_disfrute (cedula, dias_disfrute, dias_feriados, dias_restados,
                                fecha_desde, fecha_hasta, periodo, estatus)
                              values ('$cedula','$dias_disfrute','$num_feriados','$restados','$fecha_inicio2_my','$fecha_fin2_my','$periodo','0')";
                        $resultado=modificar_data($sql2,$sender);

                        $fecha_inicio2_my=cambiaf_a_mysql(suma_dias_habiles(cambiaf_a_normal($fecha_inicio2_my), $dias_disfrute+1, $feriados, $sender));
                        $fecha_inicio2 = cambiaf_a_normal($fecha_inicio2_my);
                        $xcount++;

                    }

                    /* Una vez finalizado todo el proceso, se incluye el rastro en el archivo de bitácora
                     * y se muestra un mensaje informativo al usuario acerca del resultado de su solicitud.
                     */
                    $descripcion_log = "Incluida solicitud de ".$numd." días de vacaciones de: ".$this->txt_nombre->Text." C.I.: ".$cedula.
                                       ", desde ".$fecha_inicio." al ".cambiaf_a_normal($fecha_fin2_my).", (".$num_feriados_total." días feriados, ".$restados.
                                              " días descontados)";
                    inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

                    //$this->btn_imprimir->Enabled=true;

                    $this->LTB->titulo->Text = "Solicitud de Vacaciones Exitosa";
                    $this->LTB->texto->Text = "Su solicitud de <strong>".$numd." días de vacaciones, desde el ".$fecha_inicio.
                                              " hasta el ".cambiaf_a_normal($fecha_fin2_my).", (".$num_feriados_total." días feriados, ".$restados.
                                              " días descontados), </strong>ha quedado registrada en el sistema, una vez aprobada por ".
                                              "el Director respectivo, podrá disfrutarla.";
                    $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                    $this->LTB->redir->Text = "asistencias.vacaciones.consulta_personal";
                    $params = array('mensaje');
                    $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

                   // $this->Response->Redirect( $this->Service->constructUrl('asistencias.vacaciones.consulta_cedula',array('ced'=>$cedula)));//
               }               

            }

        }
 	}





}

?>
