<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Este es un reporte de asistencias e inasistencias semanales, por dirección
 *              la diferencia con el reporte por fechas es que aqui se presentan
 *              de manera tabular las entradas y salidas de todos los empleados
 *              de la dirección seleccionada para la semana escogida.
 *****************************************************  FIN DE INFO
*/
include("protected/comunes/libchart/classes/libchart.php");
class asistencia_semanal extends TPage
{
    var $justificaciones; // info de las justificaciones
    var $msg_no_laborable = "NO LABORABLE"; // para modificar desde un solo sitio
    var $msg_inasistente = "INASISTENTE"; // para modificar desde un solo sitio
    var $ancho_encabezado = "165px";

        public function onLoad($param)
        {
            parent::onLoad($param);
            if ((!$this->IsPostBack) && (!$this->IsCallBack))
            {
                $this->txt_fecha_desde->Text = date("d/m/Y",strtotime("-1 day"));
                $cod_organizacion = usuario_actual('cod_organizacion');
                $sql="select codigo, nombre_completo as nombre from organizacion.direcciones
                      where (codigo_organizacion='$cod_organizacion' ) order by nombre_completo";
                $resultado=cargar_data($sql,$this);
                // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
                $todos = array('codigo'=>'0', 'nombre'=>'TODAS LAS DIRECCIONES');
                array_unshift($resultado, $todos);
                // Se enlaza el nuevo arreglo con el listado de Direcciones
                $this->drop_direcciones->DataSource=$resultado;
                $this->drop_direcciones->dataBind();
            }
        }


public function es_lunes($sender,$param)
{
    $param->IsValid = es_dia($this->txt_fecha_desde->Text,1,0,0,0,0);
}

/* Esta función Realiza la consulta y muestra el listado de asistentes e
 * inasistentes con los correspondientes gráficos y observaciones*/
    public function consulta_asistencia($sender, $param)
    {
        if ($this->IsValid)
        {
            $desde=cambiaf_a_mysql($this->txt_fecha_desde->Text);
            $hasta=cambiaf_a_mysql(suma_dias($this->txt_fecha_desde->Text, 4));
            $dir = $this->drop_direcciones->SelectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');

            $sql1="SELECT (p.cedula) AS cedula_integrantes, p.nombres, p.apellidos, e.fecha,
								  MIN(e.hora) AS entrada, MAX(e.hora) AS salida
						   FROM organizacion.personas AS p
						   LEFT JOIN (SELECT *
									  FROM asistencias.entrada_salida AS j
									  WHERE ((j.fecha >= '$desde') AND (j.fecha <= '$hasta'))) AS e ON p.cedula = e.cedula
						   WHERE ((p.cedula IN ( SELECT s.cedula
												 FROM asistencias.personas_status_asistencias s, organizacion.personas_nivel_dir as n
												 WHERE (s.status_asistencia =1) and (p.cedula = n.cedula) and (n.cod_direccion LIKE '$dir%') ))
								 AND (p.fecha_ingreso <= '$desde'))
						   GROUP BY p.cedula, e.fecha
						   ORDER BY p.nombres, p.apellidos, e.fecha ";
            $asistencia=cargar_data($sql1,$sender);

            $sql="Select * from organizacion.dias_no_laborables";
            $no_laborables=cargar_data($sql,$sender);

            $sql3="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*,
                   tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
							FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
								 asistencias.justificaciones_personas as jp, organizacion.personas as p,
                                 organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
							WHERE ((p.cedula = jp.cedula) and
								   ((
									 (jd.fecha_desde <= '$desde') and
									 (jd.fecha_hasta >= '$desde')
									 ) Or
									(
									 (jd.fecha_desde <= '$hasta') and
									 (jd.fecha_hasta >= '$hasta')
								   ) or
								   (
									 (jd.fecha_desde >= '$desde') and
									 (jd.fecha_hasta <= '$hasta')
									)) and
								   (j.estatus='1') and (n.cod_direccion LIKE '$dir%') and 
                                   (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                                   (p.cedula = n.cedula) and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
							ORDER BY jd.fecha_desde";
            $this->justificaciones=cargar_data($sql3,$sender);

            /*Con las siguientes líneas, intento conseguir esta estructura en el arreglo:
             * cedula/nombre/elun/slun/emar/smar/emie/smie/ejue/sjue/evie/svie
             * siendo entrada y salida por cada dia.
             */

            $datos=array();
            foreach ($asistencia as $undato)
               {
                  $indice = $undato['cedula_integrantes'];
                  $datos[$indice]['cedula']=$undato['cedula_integrantes'];
                  $datos[$indice]['nombres']=$undato['nombres']." ".$undato['apellidos'];
                  $datos[$indice]['fecha']=$undato['fecha'];

                  list($anio, $mes, $dia) = split("-", $undato['fecha']);
                  $anio=intval($anio); $mes=intval($mes); $dia=intval($dia);
                  $dia_semana = date("D", mktime(0, 0, 0, $mes, $dia, $anio));
                  switch ($dia_semana)
                  {
                      case "Mon":
                        $datos[$indice]['elun']=$undato['entrada'];
                        $datos[$indice]['slun']=$undato['salida'];
                        break;
                      case "Tue":
                        $datos[$indice]['emar']=$undato['entrada'];
                        $datos[$indice]['smar']=$undato['salida'];
                        break;
                      case "Wed":
                        $datos[$indice]['emie']=$undato['entrada'];
                        $datos[$indice]['smie']=$undato['salida'];
                        break;
                      case "Thu":
                        $datos[$indice]['ejue']=$undato['entrada'];
                        $datos[$indice]['sjue']=$undato['salida'];
                        break;
                      case "Fri":
                        $datos[$indice]['evie']=$undato['entrada'];
                        $datos[$indice]['svie']=$undato['salida'];
                        break;
                  }
               }

            $this->DataGrid->DataSource=$datos;
            $this->DataGrid->dataBind();

            $this->Repeater->DataSource =  $this->justificaciones;
            $this->Repeater->dataBind();
        }
    }


/* Formatea el listado para mejor comprension, fechas, colores, etc. */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        $cod_organizacion = usuario_actual('cod_organizacion');

        /* ********************************************************************  LUNES*/
        $estafecha = cambiaf_a_mysql($this->txt_fecha_desde->Text);
        $horario_vigente = obtener_horario_vigente($cod_organizacion,$estafecha,$sender);
        if (($item->elun->Text == "") and ($item->slun->Text == ""))
        {
            $item->elun->ColumnSpan = 2;
            $item->slun->Visible = False;
            $item->elun->Font->Bold = "true";
            $feriado = es_feriado(cambiaf_a_normal($estafecha),$no_laborables,$sender);
            if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
            {
                $item->elun->ForeColor = "Green";
                $item->elun->Text = $this->msg_no_laborable;
            }
            else
            {
                $obser1 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_entrada'],$sender);
                $obser2 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_salida'],$sender);
                if (($obser1 == false) && ($obser2 == false))
                {
                    $item->elun->ForeColor = "Red";
                    $item->elun->Text = $this->msg_inasistente;
                }
                else
                {
                    $item->elun->Text = $obser1['descripcion_tipo_justificacion'];
                    $item->elun->ForeColor = "Green";
                }
            }
        }
        else
        {
            if ($item->elun->Text == "elun")
            {
                $item->elun->Text = "Lun ".cambiaf_a_normal($estafecha);
                $item->elun->ColumnSpan = 2;
                $item->elun->Width=$this->ancho_encabezado;
                $item->slun->Visible = False;
            }

            if (($item->elun->Text != "Lun ".cambiaf_a_normal($estafecha)) and (($item->elun->Text != "")))
            {
                if (strtotime($item->elun->Text) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                                                       $horario_vigente[0]['holgura_entrada']." minutes")))
                   {
                       if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->elun->Text,$sender) != false)
                         {
                             $item->elun->ForeColor = "Green";
                         }
                       else
                         {
                             $item->elun->ForeColor = "Red";
                         }
                       $item->elun->Font->Bold = "true";
                  }
                 $item->elun->Text = date("h:i:s a",strtotime($item->elun->Text));
            }

            if (($item->slun->Text != "slun") and ($item->slun->Text != ""))
            {
                if (strtotime($item->slun->Text) < strtotime($horario_vigente[0]['hora_salida']))
                {
                   if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->slun->Text,$sender) != false)
                       {
                           $item->slun->ForeColor = "Green";
                       }
                   else
                       {
                           $item->slun->ForeColor = "Red";
                       }
                   $item->slun->Font->Bold = "true";
                }
                $item->slun->Text = date("h:i:s a",strtotime($item->slun->Text));
                if ($item->elun->Text == $item->slun->Text)
                {
                     $item->slun->Text = "";
                }
            }
        }


        /* ********************************************************************  MARTES*/
        $estafecha = cambiaf_a_mysql(suma_dias($this->txt_fecha_desde->Text, 1));
        $horario_vigente = obtener_horario_vigente($cod_organizacion,$estafecha,$sender);
        if (($item->emar->Text == "") and ($item->smar->Text == ""))
        {
            $item->emar->ColumnSpan = 2;
            $item->smar->Visible = False;
            $item->emar->Font->Bold = "true";
            $feriado = es_feriado(cambiaf_a_normal($estafecha),$no_laborables,$sender);
            if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
            {
                $item->emar->ForeColor = "Green";
                $item->emar->Text = $this->msg_no_laborable;
            }
            else
            {
                $obser1 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_entrada'],$sender);
                $obser2 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_salida'],$sender);
                if (($obser1 == false) && ($obser2 == false))
                {
                    $item->emar->ForeColor = "Red";
                    $item->emar->Text = $this->msg_inasistente;
                }
                else
                {
                    $item->emar->Text = $obser1['descripcion_tipo_justificacion'];
                    $item->emar->ForeColor = "Green";
                }
            }
        }
        else
        {
            if ($item->emar->Text == "emar")
            {
                $item->emar->Text = "Mar ".cambiaf_a_normal($estafecha);
                $item->emar->ColumnSpan = 2;
                $item->emar->Width=$this->ancho_encabezado;
                $item->smar->Visible = False;
            }

            if (($item->emar->Text != "Mar ".cambiaf_a_normal($estafecha)) and (($item->emar->Text != "")))
            {
                if (strtotime($item->emar->Text) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                                                       $horario_vigente[0]['holgura_entrada']." minutes")))
                   {
                       if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->emar->Text,$sender) != false)
                         {
                             $item->emar->ForeColor = "Green";
                         }
                       else
                         {
                             $item->emar->ForeColor = "Red";
                         }
                       $item->emar->Font->Bold = "true";
                  }
                 $item->emar->Text = date("h:i:s a",strtotime($item->emar->Text));
            }

            if (($item->smar->Text != "smar") and ($item->smar->Text != ""))
            {
                if (strtotime($item->smar->Text) < strtotime($horario_vigente[0]['hora_salida']))
                {
                   if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->smar->Text,$sender) != false)
                       {
                           $item->smar->ForeColor = "Green";
                       }
                   else
                       {
                           $item->smar->ForeColor = "Red";
                       }
                   $item->smar->Font->Bold = "true";
                }
                $item->smar->Text = date("h:i:s a",strtotime($item->smar->Text));
                if ($item->emar->Text == $item->smar->Text)
                {
                     $item->smar->Text = "";
                }
            }
        }


        /* ********************************************************************  MIERCOLES*/
        $estafecha = cambiaf_a_mysql(suma_dias($this->txt_fecha_desde->Text, 2));
        $horario_vigente = obtener_horario_vigente($cod_organizacion,$estafecha,$sender);
        if (($item->emie->Text == "") and ($item->smie->Text == ""))
        {
            $item->emie->ColumnSpan = 2;
            $item->smie->Visible = False;
            $item->emie->Font->Bold = "true";
            $feriado = es_feriado(cambiaf_a_normal($estafecha),$no_laborables,$sender);
            if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
            {
                $item->emie->ForeColor = "Green";
                $item->emie->Text = $this->msg_no_laborable;
            }
            else
            {
                $obser1 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_entrada'],$sender);
                $obser2 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_salida'],$sender);
                if (($obser1 == false) && ($obser2 == false))
                {
                    $item->emie->ForeColor = "Red";
                    $item->emie->Text = $this->msg_inasistente;
                }
                else
                {
                    $item->emie->Text = $obser1['descripcion_tipo_justificacion'];
                    $item->emie->ForeColor = "Green";
                }
            }
        }
        else
        {       
            if ($item->emie->Text == "emie")
            {
                $item->emie->Text = "Mie ".cambiaf_a_normal($estafecha);
                $item->emie->ColumnSpan = 2;
                $item->emie->Width=$this->ancho_encabezado;
                $item->smie->Visible = False;
            }

            if (($item->emie->Text != "Mie ".cambiaf_a_normal($estafecha)) and (($item->emie->Text != "")))
            {
                if (strtotime($item->emie->Text) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                                                       $horario_vigente[0]['holgura_entrada']." minutes")))
                   {
                       if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->emie->Text,$sender) != false)
                         {
                             $item->emie->ForeColor = "Green";
                         }
                       else
                         {
                             $item->emie->ForeColor = "Red";
                         }
                       $item->emie->Font->Bold = "true";
                  }
                 $item->emie->Text = date("h:i:s a",strtotime($item->emie->Text));
            }

            if (($item->smie->Text != "smie") and ($item->smie->Text != ""))
            {
                if (strtotime($item->smie->Text) < strtotime($horario_vigente[0]['hora_salida']))
                {
                   if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->smie->Text,$sender) != false)
                       {
                           $item->smie->ForeColor = "Green";
                       }
                   else
                       {
                           $item->smie->ForeColor = "Red";
                       }
                   $item->smie->Font->Bold = "true";
                }
                $item->smie->Text = date("h:i:s a",strtotime($item->smie->Text));
                if ($item->emie->Text == $item->smie->Text)
                {
                     $item->smie->Text = "";
                }
            }
        }



        /* ********************************************************************  JUEVES*/
        $estafecha = cambiaf_a_mysql(suma_dias($this->txt_fecha_desde->Text, 3));
        $horario_vigente = obtener_horario_vigente($cod_organizacion,$estafecha,$sender);
        if (($item->ejue->Text == "") and ($item->sjue->Text == ""))
        {
            $item->ejue->ColumnSpan = 2;
            $item->sjue->Visible = False;
            $item->ejue->Font->Bold = "true";
            $feriado = es_feriado(cambiaf_a_normal($estafecha),$no_laborables,$sender);
            if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
            {
                $item->ejue->ForeColor = "Green";
                $item->ejue->Text = $this->msg_no_laborable;
            }
            else
            {
                $obser1 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_entrada'],$sender);
                $obser2 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_salida'],$sender);
                if (($obser1 == false) && ($obser2 == false))
                {
                    $item->ejue->ForeColor = "Red";
                    $item->ejue->Text = $this->msg_inasistente;
                }
                else
                {
                    $item->ejue->Text = $obser1['descripcion_tipo_justificacion'];
                    $item->ejue->ForeColor = "Green";
                }
            }
        }
        else
        {
            if ($item->ejue->Text == "ejue")
            {
                $item->ejue->Text = "Jue ".cambiaf_a_normal($estafecha);
                $item->ejue->ColumnSpan = 2;
                $item->ejue->Width=$this->ancho_encabezado;
                $item->sjue->Visible = False;
            }

            if (($item->ejue->Text != "Jue ".cambiaf_a_normal($estafecha)) and (($item->ejue->Text != "")))
            {
                if (strtotime($item->ejue->Text) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                                                       $horario_vigente[0]['holgura_entrada']." minutes")))
                   {
                       if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->ejue->Text,$sender) != false)
                         {
                             $item->ejue->ForeColor = "Green";
                         }
                       else
                         {
                             $item->ejue->ForeColor = "Red";
                         }
                       $item->ejue->Font->Bold = "true";
                  }
                 $item->ejue->Text = date("h:i:s a",strtotime($item->ejue->Text));
            }

            if (($item->sjue->Text != "sjue") and ($item->sjue->Text != ""))
            {
                if (strtotime($item->sjue->Text) < strtotime($horario_vigente[0]['hora_salida']))
                {
                   if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->sjue->Text,$sender) != false)
                       {
                           $item->sjue->ForeColor = "Green";
                       }
                   else
                       {
                           $item->sjue->ForeColor = "Red";
                       }
                   $item->sjue->Font->Bold = "true";
                }
                $item->sjue->Text = date("h:i:s a",strtotime($item->sjue->Text));
                if ($item->ejue->Text == $item->sjue->Text)
                {
                     $item->sjue->Text = "";
                }
            }
        }



        /* ********************************************************************  VIERNES*/
        $estafecha = cambiaf_a_mysql(suma_dias($this->txt_fecha_desde->Text, 4));
        $horario_vigente = obtener_horario_vigente($cod_organizacion,$estafecha,$sender);
        if (($item->evie->Text == "") and ($item->svie->Text == ""))
        {
            $item->evie->ColumnSpan = 2;
            $item->svie->Visible = False;
            $item->evie->Font->Bold = "true";
            $feriado = es_feriado(cambiaf_a_normal($estafecha),$no_laborables,$sender);
            if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
            {
                $item->evie->ForeColor = "Green";
                $item->evie->Text = $this->msg_no_laborable;
            }
            else
            {
                $obser1 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_entrada'],$sender);
                $obser2 = esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$horario_vigente[0]['hora_salida'],$sender);
                if (($obser1 == false) && ($obser2 == false))
                {
                    $item->evie->ForeColor = "Red";
                    $item->evie->Text = $this->msg_inasistente;
                }
                else
                {
                    $item->evie->Text = $obser1['descripcion_tipo_justificacion'];
                    $item->evie->ForeColor = "Green";
                }
            }
        }
        else
        {
            if ($item->evie->Text == "evie")
            {
                $item->evie->Text = "Vie ".cambiaf_a_normal($estafecha);
                $item->evie->ColumnSpan = 2;
                $item->evie->Width=$this->ancho_encabezado;
                $item->svie->Visible = False;
            }

            if (($item->evie->Text != "Vie ".cambiaf_a_normal($estafecha)) and (($item->evie->Text != "")))
            {
                if (strtotime($item->evie->Text) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                                                       $horario_vigente[0]['holgura_entrada']." minutes")))
                   {
                       if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->evie->Text,$sender) != false)
                         {
                             $item->evie->ForeColor = "Green";
                         }
                       else
                         {
                             $item->evie->ForeColor = "Red";
                         }
                       $item->evie->Font->Bold = "true";
                  }
                 $item->evie->Text = date("h:i:s a",strtotime($item->evie->Text));
            }

            if (($item->svie->Text != "svie") and ($item->svie->Text != ""))
            {
                if (strtotime($item->svie->Text) < strtotime($horario_vigente[0]['hora_salida']))
                {
                   if (esta_justificado($this->justificaciones,$item->cedula->Text,cambiaf_a_normal($estafecha),$item->svie->Text,$sender) != false)
                       {
                           $item->svie->ForeColor = "Green";
                       }
                   else
                       {
                           $item->svie->ForeColor = "Red";
                       }
                   $item->svie->Font->Bold = "true";
                }
                $item->svie->Text = date("h:i:s a",strtotime($item->svie->Text));
                if ($item->evie->Text == $item->svie->Text)
                {
                     $item->svie->Text = "";
                }
            }
        }


    }


/* Esta función se encarga de generar un PDF con la información necesaria para
 * imprimir el reporte de aistencia por cédula y fechas.
 */
    public function imprimir_asistencia($sender, $param)
    {
        require('/var/www/tcpdf/tcpdf.php');
        $cod_organizacion = usuario_actual('cod_organizacion');

        $desde=cambiaf_a_mysql($this->txt_fecha_desde->Text);
        $hasta=cambiaf_a_mysql(suma_dias($this->txt_fecha_desde->Text, 4));
        $dir = $this->drop_direcciones->SelectedValue;
        $cod_organizacion = usuario_actual('cod_organizacion');
        $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
        $nombre_dir = $resultado_drop[2]; // se extrae el texto seleccionado



        $sql1="SELECT (p.cedula) AS cedula_integrantes, p.nombres, p.apellidos, e.fecha,
                              MIN(e.hora) AS entrada, MAX(e.hora) AS salida
                       FROM organizacion.personas AS p
                       LEFT JOIN (SELECT *
                                  FROM asistencias.entrada_salida AS j
                                  WHERE ((j.fecha >= '$desde') AND (j.fecha <= '$hasta'))) AS e ON p.cedula = e.cedula
                       WHERE ((p.cedula IN ( SELECT s.cedula
                                             FROM asistencias.personas_status_asistencias s, organizacion.personas_nivel_dir as n
                                             WHERE (s.status_asistencia =1) and (p.cedula = n.cedula) and (n.cod_direccion LIKE '$dir%') ))
                             AND (p.fecha_ingreso <= '$desde'))
                       GROUP BY p.cedula, e.fecha
                       ORDER BY p.nombres, p.apellidos, e.fecha";
        $asistencia=cargar_data($sql1,$sender);

        $sql="Select * from organizacion.dias_no_laborables";
        $no_laborables=cargar_data($sql,$sender);

        $sql3="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*,
               tf.descripcion as descripcion_falta, tj.descripcion as descripcion_tipo_justificacion
                        FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                             asistencias.justificaciones_personas as jp, organizacion.personas as p,
                             organizacion.personas_nivel_dir as n, asistencias.tipo_faltas tf, asistencias.tipo_justificaciones tj
                        WHERE ((p.cedula = jp.cedula) and
                               ((
                                 (jd.fecha_desde <= '$desde') and
                                 (jd.fecha_hasta >= '$desde')
                                 ) Or
                                (
                                 (jd.fecha_desde <= '$hasta') and
                                 (jd.fecha_hasta >= '$hasta')
                               ) or
                               (
                                 (jd.fecha_desde >= '$desde') and
                                 (jd.fecha_hasta <= '$hasta')
                                )) and
                               (j.estatus='1') and (n.cod_direccion LIKE '$dir%') and
                               (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                               (p.cedula = n.cedula) and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
                        ORDER BY jd.fecha_desde";
        $this->justificaciones=cargar_data($sql3,$sender);

        /*Con las siguientes líneas, intento conseguir esta estructura en el arreglo:
         * cedula/nombre/elun/slun/emar/smar/emie/smie/ejue/sjue/evie/svie
         * siendo entrada y salida por cada dia.
         */

        $datos=array();
        foreach ($asistencia as $undato)
           {
              $indice = $undato['cedula_integrantes'];
              $datos[$indice]['cedula']=$undato['cedula_integrantes'];
              $datos[$indice]['nombres']=$undato['nombres']." ".$undato['apellidos'];
              $datos[$indice]['fecha']=$undato['fecha'];

              list($anio, $mes, $dia) = split("-", $undato['fecha']);
              $anio=intval($anio); $mes=intval($mes); $dia=intval($dia);
              $dia_semana = date("D", mktime(0, 0, 0, $mes, $dia, $anio));
              switch ($dia_semana)
              {
                  case "Mon":
                    $datos[$indice]['elun']=$undato['entrada'];
                    $datos[$indice]['slun']=$undato['salida'];
                    break;
                  case "Tue":
                    $datos[$indice]['emar']=$undato['entrada'];
                    $datos[$indice]['smar']=$undato['salida'];
                    break;
                  case "Wed":
                    $datos[$indice]['emie']=$undato['entrada'];
                    $datos[$indice]['smie']=$undato['salida'];
                    break;
                  case "Thu":
                    $datos[$indice]['ejue']=$undato['entrada'];
                    $datos[$indice]['sjue']=$undato['salida'];
                    break;
                  case "Fri":
                    $datos[$indice]['evie']=$undato['entrada'];
                    $datos[$indice]['svie']=$undato['salida'];
                    break;
              }
           }


        $pdf=new TCPDF('l', 'mm', 'legal', true, 'utf-8', false);
        $pdf->SetFillColor(205, 205, 205);//color de relleno gris

        $info_adicional= "Reporte de Asistencia Semanal, Dirección: ".$nombre_dir." Del: ".cambiaf_a_normal($desde)." Al: ".cambiaf_a_normal($hasta)."\n".
                         "Impreso el ". date("d/m/Y")." por el usuario: ".usuario_actual('login');
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 12);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->SetAuthor('Proyecto SIMON');
        $pdf->SetTitle('Reporte de Asistencia Semanal');
        $pdf->SetSubject('Reporte de Asistencia Semanal por Dirección');

        $pdf->AddPage();

        $asistentes_header = array('Cédula', 'Nombres', $this->txt_fecha_desde->Text, suma_dias($this->txt_fecha_desde->Text, 1), suma_dias($this->txt_fecha_desde->Text, 2), suma_dias($this->txt_fecha_desde->Text, 3), suma_dias($this->txt_fecha_desde->Text, 4));
        $justificaciones_header = array('Observaciones a la asistencia');

        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(0, 6, "Listado de Asistencias e Inasistencias", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de asistentes en el PDF
        $pdf->SetFillColor(210,210,210);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        // Header
        $w  = array(20, 80, 46, 46, 46, 46, 46);
        $wd = array(20, 80, 23, 23);
        $wa = array(44); // ancho alterno en caso de necesitarse sólo dos columnas
        for($i = 0; $i < count($asistentes_header); $i++)
        $pdf->Cell($w[$i], 7, $asistentes_header[$i], 1, 0, 'C', 1);
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $fill = 0;
        foreach($datos as $row) {
            $pdf->Cell($w[0], 6, $row['cedula'], 'LR', 0, 'C', $fill);
            $pdf->Cell($w[1], 6, $row['nombres'], 'LR', 0, 'L', $fill);
            $pdf->SetTextColor(0); // iniciamos con el color negro
            // Para dibujar las columnas de las horas se comprueba si llegaron tarde o no
            // ********************************************************************************************   LUNES
            $estafecha = $this->txt_fecha_desde->Text;
            $horario_vigente_rpt = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($estafecha),$sender);
            if (($row['elun'] == "") and ($row['slun'] == ""))
            { // si no tiene ni entrada ni salida, se coloca la observacion con el ancho alterno

                $feriado = es_feriado($estafecha,$no_laborables,$sender);
                if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
                {
                    $pdf->SetTextColor(0,130,0);
                    $pdf->Cell($w[2], 6, $this->msg_no_laborable, 'LR', 0, 'C', $fill);
                }
                else
                {
                    $obser1 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_entrada'],$sender);
                    $obser2 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_salida'],$sender);
                    if (($obser1 == false) && ($obser2 == false))
                    {
                        $pdf->SetTextColor(200,50,50);
                        $pdf->Cell($w[2], 6, $this->msg_inasistente, 'LR', 0, 'C', $fill);
                    }
                    else
                    {
                        $pdf->SetTextColor(0,130,0);
                        $pdf->Cell($w[2], 6, $obser1['descripcion_tipo_justificacion'], 'LR', 0, 'C', $fill);
                    }
                }
            }
            else
            {
                if (strtotime($row['elun'])>= (strtotime($horario_vigente_rpt[0]['hora_entrada']." + ".
                                                            $horario_vigente_rpt[0]['holgura_entrada']." minutes")))
                   {// si llegó tarde, ahora se comprueba la justificacion
                       if (esta_justificado($this->justificaciones,$row['cedula'],$this->txt_fecha_desde->Text,$row['elun'],$sender) != false)
                         { $pdf->SetTextColor(0,130,0);} // si esta justificado se coloca en verde
                       else
                         { $pdf->SetTextColor(200,50,50); }// si no esta justificado, se coloca en rojo
                   }
                 $pdf->Cell($wd[2], 6, date("h:i:s a",strtotime($row['elun'])), 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);


                if (strtotime($row['slun'])< strtotime($horario_vigente_rpt[0]['hora_salida']))
                { // si salió temprano, se comprueban las justificaciones
                   if (esta_justificado($this->justificaciones,$row['cedula'],$this->txt_fecha_desde->Text,$row['slun'],$sender) != false)
                     { $pdf->SetTextColor(0,130,0); } // si esta justificada la salida temprana se coloca en verde
                   else
                     {$pdf->SetTextColor(200,50,50); }// si no esta justificada, se coloca en rojo
                }
                $pdf->Cell($wd[3], 6, date("h:i:s a",strtotime($row['slun'])), 'LR', 0, 'C', $fill);
            }

            $pdf->SetTextColor(0);


            // ********************************************************************************************   MARTES
            $estafecha = suma_dias($this->txt_fecha_desde->Text, 1);
            $horario_vigente_rpt = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($estafecha),$sender);
            if (($row['emar'] == "") and ($row['smar'] == ""))
            { // si no tiene ni entrada ni salida, se coloca la observacion con el ancho alterno

                $feriado = es_feriado($estafecha,$no_laborables,$sender);
                if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
                {
                    $pdf->SetTextColor(0,130,0);
                    $pdf->Cell($w[2], 6, $this->msg_no_laborable, 'LR', 0, 'C', $fill);
                }
                else
                {
                    $obser1 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_entrada'],$sender);
                    $obser2 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_salida'],$sender);
                    if (($obser1 == false) && ($obser2 == false))
                    {
                        $pdf->SetTextColor(200,50,50);
                        $pdf->Cell($w[2], 6, $this->msg_inasistente, 'LR', 0, 'C', $fill);
                    }
                    else
                    {
                        $pdf->SetTextColor(0,130,0);
                        $pdf->Cell($w[2], 6, $obser1['descripcion_tipo_justificacion'], 'LR', 0, 'C', $fill);
                    }
                }
            }
            else
            {
                if (strtotime($row['emar'])>= (strtotime($horario_vigente_rpt[0]['hora_entrada']." + ".
                                                            $horario_vigente_rpt[0]['holgura_entrada']." minutes")))
                   {// si llegó tarde, ahora se comprueba la justificacion
                       if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['emar'],$sender) != false)
                         { $pdf->SetTextColor(0,130,0);} // si esta justificado se coloca en verde
                       else
                         { $pdf->SetTextColor(200,50,50); }// si no esta justificado, se coloca en rojo
                   }
                 $pdf->Cell($wd[2], 6, date("h:i:s a",strtotime($row['emar'])), 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);


                if (strtotime($row['smar'])< strtotime($horario_vigente_rpt[0]['hora_salida']))
                { // si salió temprano, se comprueban las justificaciones
                   if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['smar'],$sender) != false)
                     { $pdf->SetTextColor(0,130,0); } // si esta justificada la salida temprana se coloca en verde
                   else
                     {$pdf->SetTextColor(200,50,50); }// si no esta justificada, se coloca en rojo
                }
                $pdf->Cell($wd[3], 6, date("h:i:s a",strtotime($row['smar'])), 'LR', 0, 'C', $fill);
            }

            $pdf->SetTextColor(0);

            // ***************************************************************************************   MIERCOLES
            $estafecha = suma_dias($this->txt_fecha_desde->Text, 2);
            $horario_vigente_rpt = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($estafecha),$sender);
            if (($row['emie'] == "") and ($row['smie'] == ""))
            { // si no tiene ni entrada ni salida, se coloca la observacion con el ancho alterno

                $feriado = es_feriado($estafecha,$no_laborables,$sender);
                if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
                {
                    $pdf->SetTextColor(0,130,0);
                    $pdf->Cell($w[2], 6, $this->msg_no_laborable, 'LR', 0, 'C', $fill);
                }
                else
                {
                    $obser1 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_entrada'],$sender);
                    $obser2 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_salida'],$sender);
                    if (($obser1 == false) && ($obser2 == false))
                    {
                        $pdf->SetTextColor(200,50,50);
                        $pdf->Cell($w[2], 6, $this->msg_inasistente, 'LR', 0, 'C', $fill);
                    }
                    else
                    {
                        $pdf->SetTextColor(0,130,0);
                        $pdf->Cell($w[2], 6, $obser1['descripcion_tipo_justificacion'], 'LR', 0, 'C', $fill);
                    }
                }
            }
            else
            {
                if (strtotime($row['emie'])>= (strtotime($horario_vigente_rpt[0]['hora_entrada']." + ".
                                                            $horario_vigente_rpt[0]['holgura_entrada']." minutes")))
                   {// si llegó tarde, ahora se comprueba la justificacion
                       if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['emie'],$sender) != false)
                         { $pdf->SetTextColor(0,130,0);} // si esta justificado se coloca en verde
                       else
                         { $pdf->SetTextColor(200,50,50); }// si no esta justificado, se coloca en rojo
                   }
                 $pdf->Cell($wd[2], 6, date("h:i:s a",strtotime($row['emie'])), 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);


                if (strtotime($row['smie'])< strtotime($horario_vigente_rpt[0]['hora_salida']))
                { // si salió temprano, se comprueban las justificaciones
                   if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['smie'],$sender) != false)
                     { $pdf->SetTextColor(0,130,0); } // si esta justificada la salida temprana se coloca en verde
                   else
                     {$pdf->SetTextColor(200,50,50); }// si no esta justificada, se coloca en rojo
                }
                $pdf->Cell($wd[3], 6, date("h:i:s a",strtotime($row['smie'])), 'LR', 0, 'C', $fill);
            }

            $pdf->SetTextColor(0);


            // ***************************************************************************************   JUEVES
            $estafecha = suma_dias($this->txt_fecha_desde->Text, 3);
            $horario_vigente_rpt = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($estafecha),$sender);
            if (($row['ejue'] == "") and ($row['sjue'] == ""))
            { // si no tiene ni entrada ni salida, se coloca la observacion con el ancho alterno

                $feriado = es_feriado($estafecha,$no_laborables,$sender);
                if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
                {
                    $pdf->SetTextColor(0,130,0);
                    $pdf->Cell($w[2], 6, $this->msg_no_laborable, 'LR', 0, 'C', $fill);
                }
                else
                {
                    $obser1 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_entrada'],$sender);
                    $obser2 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_salida'],$sender);
                    if (($obser1 == false) && ($obser2 == false))
                    {
                        $pdf->SetTextColor(200,50,50);
                        $pdf->Cell($w[2], 6, $this->msg_inasistente, 'LR', 0, 'C', $fill);
                    }
                    else
                    {
                        $pdf->SetTextColor(0,130,0);
                        $pdf->Cell($w[2], 6, $obser1['descripcion_tipo_justificacion'], 'LR', 0, 'C', $fill);
                    }
                }
            }
            else
            {
                if (strtotime($row['ejue'])>= (strtotime($horario_vigente_rpt[0]['hora_entrada']." + ".
                                                            $horario_vigente_rpt[0]['holgura_entrada']." minutes")))
                   {// si llegó tarde, ahora se comprueba la justificacion
                       if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['ejue'],$sender) != false)
                         { $pdf->SetTextColor(0,130,0);} // si esta justificado se coloca en verde
                       else
                         { $pdf->SetTextColor(200,50,50); }// si no esta justificado, se coloca en rojo
                   }
                 $pdf->Cell($wd[2], 6, date("h:i:s a",strtotime($row['ejue'])), 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);


                if (strtotime($row['sjue'])< strtotime($horario_vigente_rpt[0]['hora_salida']))
                { // si salió temprano, se comprueban las justificaciones
                   if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['sjue'],$sender) != false)
                     { $pdf->SetTextColor(0,130,0); } // si esta justificada la salida temprana se coloca en verde
                   else
                     {$pdf->SetTextColor(200,50,50); }// si no esta justificada, se coloca en rojo
                }
                $pdf->Cell($wd[3], 6, date("h:i:s a",strtotime($row['sjue'])), 'LR', 0, 'C', $fill);
            }

            $pdf->SetTextColor(0);


            // ***************************************************************************************   VIERNES
            $estafecha = suma_dias($this->txt_fecha_desde->Text, 4);
            $horario_vigente_rpt = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($estafecha),$sender);
            if (($row['evie'] == "") and ($row['svie'] == ""))
            { // si no tiene ni entrada ni salida, se coloca la observacion con el ancho alterno

                $feriado = es_feriado($estafecha,$no_laborables,$sender);
                if ((is_array($feriado) == true) && (isset($feriado[0])) &&($feriado[0] == 1))
                {
                    $pdf->SetTextColor(0,130,0);
                    $pdf->Cell($w[2], 6, $this->msg_no_laborable, 'LR', 0, 'C', $fill);
                }
                else
                {
                    $obser1 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_entrada'],$sender);
                    $obser2 = esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$horario_vigente_rpt[0]['hora_salida'],$sender);
                    if (($obser1 == false) && ($obser2 == false))
                    {
                        $pdf->SetTextColor(200,50,50);
                        $pdf->Cell($w[2], 6, $this->msg_inasistente, 'LR', 0, 'C', $fill);
                    }
                    else
                    {
                        $pdf->SetTextColor(0,130,0);
                        $pdf->Cell($w[2], 6, $obser1['descripcion_tipo_justificacion'], 'LR', 0, 'C', $fill);
                    }
                }
            }
            else
            {
                if (strtotime($row['evie'])>= (strtotime($horario_vigente_rpt[0]['hora_entrada']." + ".
                                                            $horario_vigente_rpt[0]['holgura_entrada']." minutes")))
                   {// si llegó tarde, ahora se comprueba la justificacion
                       if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['evie'],$sender) != false)
                         { $pdf->SetTextColor(0,130,0);} // si esta justificado se coloca en verde
                       else
                         { $pdf->SetTextColor(200,50,50); }// si no esta justificado, se coloca en rojo
                   }
                 $pdf->Cell($wd[2], 6, date("h:i:s a",strtotime($row['evie'])), 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);


                if (strtotime($row['svie'])< strtotime($horario_vigente_rpt[0]['hora_salida']))
                { // si salió temprano, se comprueban las justificaciones
                   if (esta_justificado($this->justificaciones,$row['cedula'],$estafecha,$row['svie'],$sender) != false)
                     { $pdf->SetTextColor(0,130,0); } // si esta justificada la salida temprana se coloca en verde
                   else
                     {$pdf->SetTextColor(200,50,50); }// si no esta justificada, se coloca en rojo
                }
                $pdf->Cell($wd[3], 6, date("h:i:s a",strtotime($row['svie'])), 'LR', 0, 'C', $fill);
            }

            $pdf->SetTextColor(0);



            $pdf->Ln();
            $fill=!$fill;
        }

        // Se añaden las observaciones a la asistencia
        // Separación
         $pdf->Ln(); $pdf->Ln();
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(0, 0, "Observaciones a la Asistencia", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de inasistentes en el PDF
        $pdf->SetFillColor(210,210,210);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        // Header
        $w = array(330);
        for($i = 0; $i < count($justificaciones_header); $i++)
        $pdf->Cell($w[$i], 7, $justificaciones_header[$i], 1, 0, 'C', 1);
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $fill = 0;
        foreach($this->justificaciones as $row) {
            $observacion = $row['cedula']." / ".$row['nombres']." ".$row['apellidos']." - Código: ".$row['codigo'].", Desde el: ".cambiaf_a_normal($row['fecha_desde']).", Hasta el: ".cambiaf_a_normal($row['fecha_hasta']).
                ", de: ".date("h:i:s a",strtotime($row['hora_desde']))." a: ".date("h:i:s a",strtotime($row['hora_hasta'])).", Tipo: ".$row['descripcion_tipo_justificacion'].
                ", Falta: ".$row['descripcion_falta'].", Motivo: ".$row['observaciones'];
            $pdf->MultiCell($w[0], 0, $observacion, 1, 'J',$fill,'1','','',true,0,false);
            $fill=!$fill;
        }
        $pdf->Output("asistencia_semanal_del_".$desde."_al_".$hasta.".pdf",'D');
    }




}

?>