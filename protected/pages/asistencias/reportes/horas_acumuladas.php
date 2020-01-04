<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Este reporte muestra el total de horas acumuladas que el
 *              funcionario ha laborado o no en los días seleccionados.
 *****************************************************  FIN DE INFO
*/
class horas_acumuladas extends TPage
{
    var $diasasistencia;
    var $nombre_funcionario_reporte; // nombre del funcionario objeto del reporte
    var $justificaciones; // info de las justificaciones

        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
                $this->txt_fecha_desde->Text = date("d/m/Y",strtotime("-1 day"));
                $this->txt_fecha_hasta->Text = date("d/m/Y",strtotime("-1 day"));
                //si tiene parametros
                if($this->Request[ced]!=""&&$this->Request[fec]!=""){
                $this->txt_cedula->Text = $this->Request[ced];
                $this->txt_fecha_desde->Text=$this->Request[fec];
                $this->txt_fecha_hasta->Text=$this->Request[fec];
                $this->IsValid=true;
                $this->consulta_asistencia($this,$param);
                }
            }
        }

/* Esta función devuelve un arreglo de dos valores con el acumulado de segundos laborados
 * y el acumulado de segundos no laborados; se devuelve en segundos por que es la unidad
 * mas básica con la que se puede trabajar y manipular (sumar, restar).
 * se pasa como parámetros la fecha, el tipo: "LA"=Laborado, "NL"=No Laborado.
 */
    public function acumulado_segundos($fecha, $tipo, $sender)
    {


        $cedula = $this->txt_cedula->Text;
        $desde = cambiaf_a_mysql( $this->txt_fecha_desde->Text);
        $hasta = cambiaf_a_mysql($this->txt_fecha_hasta->Text);
        $fecha = cambiaf_a_mysql($fecha);

            // se obtienen las justificaciones del rango de fechas seleccionado
            $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*, tj.descripcion as descripcion_tipo_justificacion
                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, asistencias.tipo_justificaciones tj
                            WHERE (
                                   (p.cedula = jp.cedula) and
                                   (p.cedula = '$cedula') and
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
                                   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just) and
                                   (jd.codigo_tipo_justificacion = tj.id)
                                  )
                            ORDER BY jd.fecha_desde,jd.hora_desde";
         $this->justificaciones=cargar_data($sql,$this);



       
        $acumulados_laborados = 0; // segundos acumulados laborados en la fecha.
        $acumulados_no_laborados = 0; // segundos acumulados no laborados en la fecha.
        $cod_organizacion = usuario_actual('cod_organizacion');
        $Horas = 0;
        $Minutos = 0;
        $Segundos = 0;

        $horario_vigente = obtener_horario_vigente($cod_organizacion, $fecha, $sender);
        // consulto loas horas de entrada y salida de la fecha
        $sql="SELECT e.hora from asistencias.entrada_salida e
                        WHERE (e.fecha = '$fecha') and (e.cedula = '$cedula') order by e.hora ";
        $horas=cargar_data($sql,$this);
        $primer_registro = true; // esta marca sirve para comprobar la llegada tarde
        $ultimo_registro = false; // esta marca sirve para comprobar la salida temprana
        /* Se acumulan las llegadas tardes */
        foreach ($horas as $estahora)
        {
            $acumular = false; // para saber si acumulo o no esta llegada tarde
            $ultima_hora_registrada = $estahora['hora']; // esta se usa para saber si se fue temprano
            $hora_just1 = ''; $hora_just2='';
            if (((strtotime($estahora['hora'])) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
               $horario_vigente[0]['holgura_entrada']." minutes"))) && ($primer_registro == true))
                {
                    $acumular = true;
                    /* Se verifica que la llegada tarde no sea por cuestiones de trabajo*/
                    $primer_registro = false; // para que no se compruebe de nuevo la llegada tarde en este dia.
                   // $result  = esta_justificado($this->justificaciones,$this->txt_cedula->Text,cambiaf_a_normal($fecha),$estahora['hora'],$sender);
                    $result  = esta_justificado($this->justificaciones,$cedula,cambiaf_a_normal($fecha),$horario_vigente[0]['hora_entrada'],$sender);

                    //$this->mensaje->setSuccessMessage($sender,"CI:".$this->txt_cedula->Text." FECHA: ".cambiaf_a_normal($fecha)." - HORA:".($horario_vigente[0]['hora_entrada'])." J:".$result."-JD:".$result['descuenta_ticket'], 'grow');

                    if ($result != false)
                    {   // se le suma los segundos si llego despues de la justificacion
                        if(strtotime($estahora['hora']) > strtotime($result['hora_hasta'])){
                        list($H2 ,$M2, $S2) = split(":",date("H:i:s", strtotime("00:00:00") + strtotime($estahora['hora']) - strtotime($result['hora_hasta'])));
                        $segundos_entrada_sinjust = ($H2*60*60)+($M2*60)+$S2;
                        $acumulados_no_laborados = $acumulados_no_laborados + $segundos_entrada_sinjust;
                        }//fin si
                        if ($result['descuenta_ticket'] != 'Si')
                        { // si esta justificado pero aun así se descuenta ticket, se procesa el acumulado.
                            $acumular = false;
                        }
                    }

                    if ($acumular == true)
                    { // si se debe acumular quiere decir que no esta justificada la llegada tarde y si esta justificada, se le descuenta ticket igual
                       $hora_entrada_tarde=$estahora['hora'];
                       $hora_con_holgura = date('H:i:s',strtotime($horario_vigente[0]['hora_entrada']." + ".$horario_vigente[0]['holgura_entrada']." minutes"));
                       list($H1 ,$M1, $S1) = split(":",date("H:i:s", strtotime("00:00:00") + strtotime($estahora['hora']) - strtotime($hora_con_holgura)));
                       $segundos_entrada_tarde = ($H1*60*60)+($M1*60)+$S1;
                       $acumulados_no_laborados = $acumulados_no_laborados + $segundos_entrada_tarde;
                    }


                }
            else
             {
                 if ($primer_registro == true) { $primer_registro = false;}
             }
        }
    
            
        /* Se calcula el tiempo de ausencia si se fue temprano */
        if ((strtotime($ultima_hora_registrada)) < (strtotime($horario_vigente[0]['hora_salida'])))
        {
            $acumular = true;
            /* Se verifica que la salida temprana no sea por justificación de trabajo*/
            $result  = esta_justificado($this->justificaciones,$this->txt_cedula->Text,cambiaf_a_normal($fecha),$horario_vigente[0]['hora_salida'],$sender);

            if ($result != false)
            {           // se suma los segundos si salio antes de la justificacion
                       if(strtotime($result['hora_desde']) > strtotime($ultima_hora_registrada)){
                        list($H2 ,$M2, $S2) = split(":",date("H:i:s", strtotime("00:00:00") + strtotime($result['hora_desde']) - strtotime($ultima_hora_registrada)));
                        $segundos_salida_conjust = ($H2*60*60)+($M2*60)+$S2;
                        $acumulados_no_laborados = $acumulados_no_laborados + $segundos_salida_conjust;
                       }// fin si

                if ($result['descuenta_ticket'] != 'Si')
                { // si esta justificado pero aun así se descuenta ticket, se procesa el acumulado.
                    $acumular = false;
                }
            }

            if ($acumular == true)
            { // si se debe acumular quiere decir que no esta justificada la llegada tarde y si esta justificada, se le descuenta ticket igual
              list($H1 ,$M1, $S1) = split(":",date("H:i:s", strtotime("00:00:00") + strtotime($horario_vigente[0]['hora_salida']) - strtotime($ultima_hora_registrada)));
              $segundos_salida_tarde = ($H1*60*60)+($M1*60)+$S1;
              $acumulados_no_laborados = $acumulados_no_laborados + $segundos_salida_tarde;
            }

        }

        /* Se calcula ahora el tiempo no laborado de acuerdo a sus salidas y
         * entradas intrahorarios y se acumulan a las que ya se han calculado
         */
        if (count($horas) > 2)
        {
            $xcont = 1;
            $cuenta_primera = 1;
            while ($xcont < count($horas))
            {
                if ($cuenta_primera == 1)
                { // aqui solo se toma la primera hora para la resta
                    //$time1 = strtotime($horas[$xcont]['hora']);
                    $hora_just1 = $horas[$xcont]['hora'];
                    $cuenta_primera = 2;
                }else{// aqui se toma la segunda hora y si no esta justificada se realizan los cálculos.
                    
                    $cuenta_primera = 1;
                    $hora_just2 = $horas[$xcont]['hora'];
                    $acumular = true;$almuerzo=true;
                    

                    $result  = esta_justificado($this->justificaciones,$this->txt_cedula->Text,cambiaf_a_normal($fecha),$hora_just1,$sender);
                    $result2 = esta_justificado($this->justificaciones,$this->txt_cedula->Text,cambiaf_a_normal($fecha),$hora_just2,$sender);
                    $justificaciones_des.="</br>hora1:$hora_just1 justificada=".$result." - hora2:$hora_just2 justificada=".$result2."";
                    if (($result != false) && ($result2 != false))
                     {
                        if ($result['descuenta_ticket'] != 'Si')
                        { // si esta justificado pero aun así se descuenta ticket, se procesa el acumulado.
                            $acumular = false;
                        }
                     }else{//sino esta justificado
                                 //verificamos si la primera hora de salida esta dentro almuerzo,
                             // se asigna a la primera hora el final del almuerzo
                            if(( (strtotime($hora_just1))>= (strtotime($horario_vigente[0]['almuerzo_salida'])))&&((strtotime($hora_just1))<=(strtotime($horario_vigente[0]['almuerzo_entrada'])))&&((strtotime($hora_just2))>(strtotime($horario_vigente[0]['almuerzo_entrada'])))){
                                $acumular = true;
                                $hora_just1 = $horario_vigente[0]['almuerzo_entrada'];
                            }//fin si

                             //verificamos si la segunda hora de entrada esta dentro del almuerzo,
                             // se asigna a la segunda hora el comienzo del almuerzo
                            if((strtotime($hora_just2))>(strtotime($horario_vigente[0]['almuerzo_salida']))&&(strtotime($hora_just2))<=(strtotime($horario_vigente[0]['almuerzo_entrada']))&&((strtotime($hora_just1))<(strtotime($horario_vigente[0]['almuerzo_salida'])))){
                                $acumular = true;
                                $hora_just2 = $horario_vigente[0]['almuerzo_salida'];
                            }

                             //verificamos si esta dentro de la hora del almuerzo se le resta los minutos
                             //y se procesa de una vez el tiempo por fuera
                            if(( (strtotime($hora_just1))< (strtotime($horario_vigente[0]['almuerzo_salida'])))&&((strtotime($hora_just2))>(strtotime($horario_vigente[0]['almuerzo_entrada'])))){
                                $acumular = false;
                                $almuerzo=true;
                                    list($H1 ,$M1, $S1) = split(":",date("H:i:s", strtotime("00:00:00") + strtotime($horario_vigente[0]['almuerzo_salida']) - strtotime($hora_just1)));
                                    $segundos_salida_tarde = ($H1*60*60)+($M1*60)+$S1;
                                    $acumulados_no_laborados = $acumulados_no_laborados + $segundos_salida_tarde;

                                    list($H2 ,$M2, $S2) = split(":",date("H:i:s", strtotime("00:00:00") + strtotime($hora_just2) - strtotime($horario_vigente[0]['almuerzo_entrada'])));
                                    $segundos_salida_tarde = ($H2*60*60)+($M2*60)+$S2;
                                    $acumulados_no_laborados = $acumulados_no_laborados + $segundos_salida_tarde;
                            }

                            //verificamos si es la hora del almuerzo
                            if(( (strtotime($hora_just1))>= (strtotime($horario_vigente[0]['almuerzo_salida'])))&&((strtotime($hora_just2))<=(strtotime($horario_vigente[0]['almuerzo_entrada'])))){
                                $acumular = false;
                                $justificaciones_des.=" es almuerzo";
                            }
                     }//finsi

                    

                    if ($acumular == true)
                    { // si se debe acumular quiere decir que no esta justificada la llegada tarde y si esta justificada, se le descuenta ticket igual
                        $time1 = strtotime($hora_just1);
                        $time2 = strtotime($hora_just2);
                        $time11 = date("H:i:s",$time1);
                        list($H1 ,$M1, $S1) = split(":",$time11);

                        $time22 = date("H:i:s",$time2);
                        list($H2 ,$M2, $S2) = split(":",$time22);

                        $sa11 = ($H1*60*60)+($M1*60)+$S1;
                        $sa22 = ($H2*60*60)+($M2*60)+$S2;
                        $sa33 = $sa22 - $sa11;

                        $acumulados_no_laborados = $acumulados_no_laborados + $sa33;
                    }
                }
                $xcont++;
            }
        }

 

        /* Luego de toda la acumulación de segundos, ahora se resta el tiempo dado para el almuerzo
         * si es que el funcionario se pasó del mismo y la hora por fuera es menor a la del almuerzo
         */
       
        /*if (($almuerzo==false))
        {
          $segundos_almuerzo = $horario_vigente[0]['almuerzo_minutos']*60;
          $acumulados_no_laborados = $acumulados_no_laborados - $segundos_almuerzo;
        }*/


/*$this->mensaje->setSuccessMessage($sender,
 "$acumulados_no_laborados Entrada Tarde :hora entrada vigente con Holgura $hora_con_holgura - Entrada Tarde $hora_entrada_tarde=$segundos_entrada_tarde
                </br> Salida temprano: hora Salida $ultima_hora_registrada - hora salida vigente ".$horario_vigente[0]['hora_salida']."= $segundos_salida_tarde
                </br>$justificaciones_des", 'grow');*/



        /* Para convertir en HMS.*/
        $Horas = floor(($acumulados_no_laborados / 60)/60);
        $Minutos = floor($acumulados_no_laborados / 60) - ($Horas * 60);
        $Segundos = $acumulados_no_laborados - ($Minutos * 60) - ($Horas * 60 * 60);

         /* Con este condicional simplemente mejoro la visualización en el listado*/
        if (($Horas == 0) && ($Minutos == 0) && ($Segundos == 0))
        {$resultado = "---";}
        else
        {$resultado =  $Horas." h, ".$Minutos." m, ".$Segundos." s";}
       
        return $resultado;
    }

/* Esta función Realiza la consulta y muestra el listado de asistentes e
 * inasistentes con los correspondientes gráficos y observaciones*/
    public function consulta_asistencia($sender, $param)
    {
        if ($this->IsValid)
        {
            $cedula = $this->txt_cedula->Text;
            $desde = cambiaf_a_mysql($this->txt_fecha_desde->Text);
            $hasta = cambiaf_a_mysql($this->txt_fecha_hasta->Text);
            $cod_organizacion = usuario_actual('cod_organizacion');
            $dias_feriados = dias_feriados($sender);
            $this->diasasistencia = dias_entre_fechas($this->txt_fecha_desde->Text,$this->txt_fecha_hasta->Text, 0, $dias_feriados, $sender);


            // se obtienen las justificaciones del rango de fechas seleccionado
            $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*, tj.descripcion as descripcion_tipo_justificacion
                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, asistencias.tipo_justificaciones tj
                            WHERE (
                                   (p.cedula = jp.cedula) and
                                   (p.cedula = '$cedula') and
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
                                   (j.estatus='1') and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just) and
                                   (jd.codigo_tipo_justificacion = tj.id)
                                  )
                            ORDER BY jd.fecha_desde ";
            $this->justificaciones=cargar_data($sql,$this);


            $sqlpers="SELECT CONCAT(p.nombres,' ',p.apellidos) as nombre_persona
                            FROM organizacion.personas p
                            WHERE (p.cedula = '$cedula')";
            $persona=cargar_data($sqlpers,$sender);
            $this->nombre_funcionario_reporte = $persona[0]['nombre_persona'];

            $sql="SELECT e.cedula, e.fecha,
                                   MIN(e.hora) as entrada, MAX(e.hora) as salida
                            FROM asistencias.entrada_salida as e
                            WHERE ((e.cedula = '$cedula') and
                                   (e.fecha <= '$hasta') and
                                   (e.fecha >= '$desde'))
                            GROUP BY fecha ORDER BY fecha      ";
            $asistencia2=cargar_data($sql,$sender);

          

            $diasasistencia = dias_entre_fechas($this->txt_fecha_desde->Text,$this->txt_fecha_hasta->Text, 2, $dias_feriados, $sender);
            
            foreach ($diasasistencia as $todos_dias)
            {
                $tipo_dia=es_feriado2($todos_dias['fecha'], NULL, $sender);
                //$this->mensaje->setSuccessMessage($sender,$todos_dias['fecha']."-".$tipo_dia, 'grow');
                 $contador=0;
             
               // tipo de dia
               switch($tipo_dia){
                   // 0: si es laborable (ni feriado ni fin de semana)
                   case 0:
              $sql="SELECT e.cedula, e.fecha,MIN(e.hora) as entrada, MAX(e.hora) as salida
                            FROM asistencias.entrada_salida as e
                            WHERE ((e.cedula = '$cedula') AND (e.fecha = '".cambiaf_a_mysql($todos_dias['fecha'])."'))";
                    $asistencia=cargar_data($sql,$sender);
                   // $contador=0;
                   /* if ($todos_dias['fecha']=="19/06/2012"){

                    }*/
                   while ($this->diasasistencia[$contador]['fecha']!=$todos_dias['fecha']){$contador++;}

                        if($asistencia[0]['cedula']!=''){// si registro hora en sistema
     
                            $this->diasasistencia[$contador]['entrada'] = $asistencia[0]['entrada'];
                            $this->diasasistencia[$contador]['salida'] = $asistencia[0]['salida'];
                            $this->diasasistencia[$contador]['laborado'] = "SI";
                            $this->diasasistencia[$contador]['no_laborado'] = $this->acumulado_segundos($todos_dias['fecha'], "NL", $sender);
                        }else{  // Si no esta la fecha quiere decir que es un dia laborable, pero
                            // el funcionario no marco ni entrada ni salida, por lo que se busca alguna justificación.

                            $horario_vigente = obtener_horario_vigente($cod_organizacion, cambiaf_a_mysql($this->diasasistencia[$contador]['fecha']), $sender);

                            $result  = esta_justificado($this->justificaciones,$this->txt_cedula->Text,$todos_dias['fecha'],$horario_vigente[0]['hora_entrada'],$sender);
                            $result2 = esta_justificado($this->justificaciones,$this->txt_cedula->Text,$todos_dias['fecha'],$horario_vigente[0]['hora_salida'],$sender);
                            
                            //$this->mensaje->setSuccessMessage($sender,$result."-".$result2, 'grow');
                            //$this->diasasistencia[$contador]['no_laborado'] = $this->acumulado_segundos($todos_dias['fecha'], "NL", $sender);

                            if (($result != false) && ($result2 != false))
                            $this->diasasistencia[$contador]['observacion'] = $result['descripcion_tipo_justificacion'].", Cód: ".$result['codigo'];
                        }//fin si
                       break;
               }//fin caso       
            }//fin para
           
            $this->DataGrid->Caption="Acumulados del ".$this->txt_fecha_desde->Text." al ".$this->txt_fecha_hasta->Text." para ".$persona[0]['nombre_persona'];
            $this->DataGrid->DataSource=$this->diasasistencia;
            $this->DataGrid->dataBind();
        }
    }


/* Formatea el listado para mejor comprension, fechas, colores, etc. */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        $cod_organizacion = usuario_actual('cod_organizacion');
        if (($item->entrada->Text == "") and ($item->salida->Text == ""))
        {
            $item->entrada->ColumnSpan = 2;
            $item->salida->Visible = False;
            $item->entrada->Text = $item->observacion->Text;
            $item->entrada->Font->Bold = "true";
            if ($item->observacion->Text == "")
            {
                $item->entrada->ForeColor = "Red";
                $item->entrada->Text = "I N A S I S T E N T E";
            }
            else
            {
                $item->entrada->ForeColor = "Green";
            }
        }
        else
        {
            $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($item->fecha->Text),$sender);
            if (($item->entrada->Text != "Entrada") and (($item->entrada->Text != "")))
            {
                if (strtotime($item->entrada->Text) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                                                       $horario_vigente[0]['holgura_entrada']." minutes")))
                   {
                       if (esta_justificado($this->justificaciones,$this->txt_cedula->Text,$item->fecha->Text,$item->entrada->Text,$sender) != false)
                         {
                             $item->entrada->ForeColor = "Green";
                         }
                       else
                         {
                             $item->entrada->ForeColor = "Red";
                         }
                       $item->entrada->Font->Bold = "true";
                   }
                 $item->entrada->Text = date("h:i:s a",strtotime($item->entrada->Text));
            }

            if (($item->salida->Text != "Salida") and ($item->salida->Text != ""))
            {
                if (strtotime($item->salida->Text) < strtotime($horario_vigente[0]['hora_salida']))
                {
                   if (esta_justificado($this->justificaciones,$this->txt_cedula->Text,$item->fecha->Text,$item->salida->Text,$sender) != false)
                       {
                           $item->salida->ForeColor = "Green";
                       }
                   else
                       {
                           $item->salida->ForeColor = "Red";
                       }
                   $item->salida->Font->Bold = "true";
                }
                $item->salida->Text = date("h:i:s a",strtotime($item->salida->Text));
                if ($item->entrada->Text == $item->salida->Text)
                {
                     $item->salida->Text = "";
                }
            }
        }

    }


/* Esta función se encarga de generar un PDF con la información necesaria para
 * imprimir el reporte de aistencia por cédula y fechas.
 */
    public function imprimir_asistencia($sender, $param)
    {
/*        require('/var/www/tcpdf/tcpdf.php');
        $cod_organizacion = usuario_actual('cod_organizacion');
            $cedula = $this->txt_cedula->Text;
            $desde = cambiaf_a_mysql($this->txt_fecha_desde->Text);
            $hasta = cambiaf_a_mysql($this->txt_fecha_hasta->Text);
            $cod_organizacion = usuario_actual('cod_organizacion');
            $dias_feriados = dias_feriados($sender);
            $this->diasasistencia = dias_entre_fechas($this->txt_fecha_desde->Text,$this->txt_fecha_hasta->Text, 1, $dias_feriados, $sender);

            $sqlpers="SELECT CONCAT(p.nombres,' ',p.apellidos) as nombre_persona
                            FROM organizacion.personas p
                            WHERE (p.cedula = '$cedula')";
            $persona=cargar_data($sqlpers,$sender);
            $this->nombre_funcionario_reporte = $persona[0]['nombre_persona'];

            $sql="SELECT e.cedula, e.fecha,
                                   MIN(e.hora) as entrada, MAX(e.hora) as salida
                            FROM asistencias.entrada_salida as e
                            WHERE ((e.cedula = '$cedula') and
                                   (e.fecha <= '$hasta') and
                                   (e.fecha >= '$desde'))
                            GROUP BY fecha ORDER BY fecha";
            $asistencia2=cargar_data($sql,$sender);

            foreach ($asistencia2 as $undia)
            {
                $contador = 0; $esta = false;
                while (($contador < count($this->diasasistencia)) && ($esta == false))
                {
                    if ($this->diasasistencia[$contador]['fecha'] == cambiaf_a_normal($undia['fecha']))
                    {
                        $this->diasasistencia[$contador]['entrada'] = $undia['entrada'];
                        $this->diasasistencia[$contador]['salida'] = $undia['salida'];
                        $esta = true;
                    }
                    $contador++;
                }
            }

        $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
     //   $pdf=new TCPDF('p', 'mm', 'letter', true, 'iso-8859-1', false);
        $pdf->SetFillColor(205, 205, 205);//color de relleno gris

        $info_adicional= "Reporte de Asistencia de: ".$this->nombre_funcionario_reporte."\n Del: ".$this->txt_fecha_desde->Text." Al: ".$this->txt_fecha_hasta->Text;
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
        $pdf->SetAutoPageBreak(TRUE, 15);

        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->SetAuthor('Proyecto SIMON');
        $pdf->SetTitle('Reporte de Asistencia por Cédula y Fechas');
        $pdf->SetSubject('Reporte de Asistencia por Cédula y Fechas');

        $pdf->AddPage();

        $asistentes_header = array('Fecha', 'Entrada', 'Salida');
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
        $w = array(30, 78, 77);
        $wa=array(30,155); // ancho alterno en caso de necesitarse sólo dos columnas
        for($i = 0; $i < count($asistentes_header); $i++)
        $pdf->Cell($w[$i], 7, $asistentes_header[$i], 1, 0, 'C', 1);
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',11);
        // Data
        $fill = 0;
        foreach($this->diasasistencia as $row) {
            $pdf->Cell($w[0], 6, $row['fecha'], 'LR', 0, 'C', $fill);
            $pdf->SetTextColor(0); // iniciamos con el color negro
            // Para dibujar las columnas de las horas se comprueba si llegaron tarde o no
            $horario_vigente_rpt = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($row['fecha']),$sender);
            if (($row['entrada'] == "") and ($row['salida'] == ""))
            { // si no tiene ni entrada ni salida, se coloca la observacion con el ancho alterno
                if ($row['observacion'] == "")
                {
                    $pdf->SetTextColor(200,50,50);
                    $pdf->Cell($wa[1], 6, "I N A S I S T E N T E", 'LR', 0, 'C', $fill);
                }
                else
                {
                    $pdf->SetTextColor(0,130,0);
                    $pdf->Cell($wa[1], 6, $row['observacion'], 'LR', 0, 'C', $fill);
                }
            }
            else
            {
                $pdf->Cell($w[2], 6, date("h:i:s a",strtotime($row['salida'])), 'LR', 0, 'C', $fill);
            }

            $pdf->SetTextColor(0);

            $pdf->Ln();
            $fill=!$fill;
        }

        $pdf->Output("asistencia_por_cedula_".$cedula."_del_".$desde."_al_".$hasta.".pdf",'D');
 */
    }




}

//se verifica los resultado para la suma del ticket
                       


?>