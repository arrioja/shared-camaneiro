<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar. C.
 * Descripción: Este es un reporte de los tickets de alimentacion, por funcionario o todos
 *              que calcula los dias que tiene ticket el funcionarios
 *              y los dias que no con sus respectivas observaciones
 *****************************************************  FIN DE INFO
*/
include("protected/comunes/libchart/classes/libchart.php");
class ticket_alimentacion extends TPage
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
            $cod_organizacion = usuario_actual('cod_organizacion');
               
            // funcionarios activos de asistencia y que no sean pasantes (nive!=10)
            $sql="select p.cedula,  CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion')
                                and (nd.nivel!='10'))
                        ORDER BY p.nombres, p.apellidos";

            $resultado=cargar_data($sql,$this);
            $todos = array('codigo'=>'0', 'nombre'=>'TODOS LOS FUNCIONARIOS');
            array_unshift($resultado, $todos);
            $this->drop_direcciones->DataSource=$resultado;
            $this->drop_direcciones->dataBind();

             // se toma año de los registros de entrada_salida
             $this->drop_ano->Datasource = ano_asistencia($this);
             $this->drop_ano->dataBind();
             $cod_organizacion = usuario_actual('cod_organizacion');
             
             //meses
             $arreglo=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
             $this->drop_mes->DataSource=$arreglo;
             $this->drop_mes->dataBind();
            }
        }


/* Esta función devuelve acumulado de segundos no laborados; se devuelve en segundos por que es la unidad
 * mas básica con la que se puede trabajar y manipular (sumar, restar).
 * se pasa como parámetros la cedula del funcionario y fecha
 */
    public function acumulado_segundos($fecha,$cedula,$sender,$param)
    {
      //$cedula = $this->txt_cedula->Text;
      $desde=cambiaf_a_mysql($fecha);
      $hasta=cambiaf_a_mysql($fecha);
      $fecha = cambiaf_a_mysql($fecha);
      //$this->mensaje_v->setSuccessMessage($sender,"$desde $hasta", 'grow');


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
              WHERE (e.fecha = '$fecha') and (e.cedula = '$cedula') order by e.hora";
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
            $result  = esta_justificado($this->justificaciones,$cedula,cambiaf_a_normal($fecha),$horario_vigente[0]['hora_salida'],$sender);

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

              //if($fecha=="2012-07-11" && $cedula=="13729622")
             //  $this->mensaje_v->setSuccessMessage($sender, "TOTAL SEGUNDOS:".$acumulados_no_laborados." > ".$ultima_hora_registrada, 'grow');

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
                }
                else
                { // aqui se toma la segunda hora y si no esta justificada se realizan los cálculos.

                    $cuenta_primera = 1;
                    $hora_just2 = $horas[$xcont]['hora'];
                    $acumular = true;


                    $result  = esta_justificado($this->justificaciones,$cedula,cambiaf_a_normal($fecha),$hora_just1,$sender);
                    $result2 = esta_justificado($this->justificaciones,$cedula,cambiaf_a_normal($fecha),$hora_just2,$sender);
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

        /* Para convertir en HMS.*/

      
        $Horas = floor(($acumulados_no_laborados / 60)/60);
        $Minutos = floor($acumulados_no_laborados / 60) - ($Horas * 60);
        $Segundos = $acumulados_no_laborados - ($Minutos * 60) - ($Horas * 60 * 60);

         /* Con este condicional simplemente mejoro la visualización en el listado*/
        if (($Horas == 0) && ($Minutos == 0) && ($Segundos == 0))
        {$resultado = "---";}
        else
        {$resultado =  $Horas." h, ".$Minutos." m, ".$Segundos." s";}

              

        return $acumulados_no_laborados;



    }

/* Esta función Realiza la consulta y muestra el listado de asistentes e
 * inasistentes con los correspondientes gráficos y observaciones*/
    public function consulta_asistencia($sender, $param)
    {
       
        // if ($this->IsValid)
       // {
           $cod_organizacion = usuario_actual('cod_organizacion');
           //contruye dias
           //$desde="01/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;
           //$hasta=ultimo_dia_mes($this->drop_mes->SelectedValue,$this->drop_ano->SelectedValue)."/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;
           $desde1=$this->txt_fecha_desde->Text;
           $hasta1=$this->txt_fecha_hasta->Text;

           $dir = $this->drop_direcciones->SelectedValue;
           
           if($this->drop_direcciones->SelectedValue=="TODOS LOS FUNCIONARIOS"){
            $AND=" AND (n.cod_direccion LIKE '0%')";
           }else{ $AND=" AND (p.cedula='$dir') ";}

            $desde=cambiaf_a_mysql($desde1);
            $hasta=cambiaf_a_mysql($hasta1);

           //$this->mensaje_v->setSuccessMessage($sender, "desde: $desde/ hasta:$hasta", 'grow');

           $sql1="SELECT (p.cedula) AS cedula_integrantes, p.nombres, p.apellidos, e.fecha,
								  MIN(e.hora) AS entrada, MAX(e.hora) AS salida
						   FROM organizacion.personas AS p
						   LEFT JOIN (SELECT *
									  FROM asistencias.entrada_salida AS j
									  WHERE ((j.fecha >= '$desde') AND (j.fecha <= '$hasta'))) AS e ON p.cedula = e.cedula
						   WHERE ((p.cedula IN ( SELECT s.cedula
												 FROM asistencias.personas_status_asistencias s, organizacion.personas_nivel_dir as n
												 WHERE (s.status_asistencia =1) and (p.cedula = n.cedula) $AND))
								 AND (p.fecha_ingreso <= '$desde'))
						   GROUP BY p.cedula, e.fecha
						   ORDER BY p.nombres, p.apellidos, e.fecha ";
            $asistencia=cargar_data($sql1,$sender);


            $sql="Select * from organizacion.dias_no_laborables";
            $no_laborables=cargar_data($sql,$sender);

           // Con las siguientes líneas, intento conseguir esta estructura en el arreglo:
             //cedula/nombre/elun/slun/emar/smar/emie/smie/ejue/sjue/evie/svie
            // siendo entrada y salida por cada dia.
             
            $datos=array();
            $arreglo_feriado = dias_feriados($sender);
           
            $arre_id=array("0"=>"L","1"=>"M","2"=>"M","3"=>"J","4"=>"V","5"=>"S","6"=>"D");
            $sqlct="SELECT minutos_para_restar as mtc FROM asistencias.opciones_ticket
                     WHERE  vigencia_hasta >= '$hasta'
                     ORDER BY status DESC LIMIT 0,1";
            $mtc=cargar_data($sqlct,$sender);
            $segundos_quitar_ticket= $mtc[0]['mtc']*60;
            $modificaciones_tickets==array();
            foreach ($asistencia as $undato)
               {


                  // $arre_id=array("0"=>"L","1"=>"M","2"=>"M","3"=>"J","4"=>"V","5"=>"S","6"=>"D");
                    $arre_id=array("1"=>"L","2"=>"M","3"=>"M","4"=>"J","5"=>"V","6"=>"S","0"=>"D");
                    //$arre_id = array('',Domingo, Lunes, Martes, Miércoles, Jueves, Viernes);

                  $indice = $undato['cedula_integrantes'];
                  $datos[$indice]['Cedula']=$undato['cedula_integrantes'];
                  $datos[$indice]['Nombres']=$undato['nombres']." ".$undato['apellidos'];
                 
                  $suma_tickes=0;
                  $fechas=dias_entre_fechas(cambiaf_a_normal($desde),cambiaf_a_normal($hasta),2, NULL, $sender);
                   //$arre_id=array("1"=>"L","2"=>"M","3"=>"M","4"=>"J","5"=>"V","6"=>"S","0"=>"D");
                 // se rellena los dias del mes en el activedata y se coloca el calculo de tickets
                 for($i=0;$i<=num_dias_entre_fechas(cambiaf_a_normal($desde),cambiaf_a_normal($hasta));$i++){

                      $cero=0;$justificado=false;$asistio=false;
                      list($dia2,$mes,$anio) = split("/", $fechas[$i]['fecha']);
                      $anio=intval($anio); $mes=intval($mes); $dia2=intval($dia2);
                      $dia_semana = date("w", mktime(0, 0, 0, $mes, $dia2, $anio));
                      //Inicial del dia de semana y numero del dia
                      $num=$arre_id[$dia_semana]."</br> ".($dia2);
                      $dia_n=$fechas[$i]['fecha'];
                      $dia=cambiaf_a_mysql($dia_n);
                      $laborable=es_feriado($dia_n, null, $sender);

                      //$sqlct="fecha:$mes-$dia2-$anio  dia_semana:$dia_semana dia:$dia  dia_n:$dia_n";
                      //$prueba=cargar_data($sqlct,$sender);
                    
                      if($laborable==0){//si es laborable dia habil

                   
                        $sql="SELECT e.cedula, e.fecha,MIN(e.hora) as entrada, MAX(e.hora) as salida FROM asistencias.entrada_salida as e
                           WHERE ((e.cedula = '$indice') AND (e.fecha = '$dia'))";
                           $asistencia2=cargar_data($sql,$sender);

                        if($asistencia2[0]['cedula']!=''){// si registro hora en sistema
                         //$datos[$indice][$num].="-A";

                           $sql="select cedula FROM asistencias.excluidos WHERE cedula='$indice'";
                           $excluido=cargar_data($sql,$sender);
                           //si esta excluido no se toman los segudos no laborados
                           if($excluido[0]['cedula']=='')
                           $segundos_no_trabajo=$this->acumulado_segundos($dia_n,$indice,$sender,$param)   ;

                           $asistio=true;

                        }else{  // Si no esta la fecha quiere decir que es un dia laborable, pero
                           
                                    // se obtienen las justificaciones del rango de fechas seleccionado
                                    $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*, tj.descripcion as descripcion_tipo_justificacion
                                FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                     asistencias.justificaciones_personas as jp, organizacion.personas as p, asistencias.tipo_justificaciones tj
                                WHERE (
                                       (p.cedula = jp.cedula) and
                                       (p.cedula = '$indice') and
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
                                ORDER BY jd.descuenta_ticket,jd.fecha_desde";
                                $this->justificaciones=cargar_data($sql,$sender);
                                //jd.descuenta_ticket,
                                // el funcionario no marco ni entrada ni salida, por lo que se busca alguna justificación.
                                $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($dia_n), $sender);
                                $result  = esta_justificado($this->justificaciones,$indice,$dia_n,$horario_vigente[0]['hora_entrada'],$sender);
                                $result2 = esta_justificado($this->justificaciones,$indice,$dia_n,$horario_vigente[0]['hora_salida'],$sender);
                                if (($result != false) && ($result2 != false)){
                                    // si esta justificado pero aun así se descuenta ticket, se procesa el acumulado.
                                     if ($result['descuenta_ticket'] == 'No')$justificado=true;
                                }//fin si justificado
                                
                            }//fin si no asistio



                         // se verifica la suma de tickets
                         if($asistio==true){
                              if(($segundos_no_trabajo>=$segundos_quitar_ticket)){// se le descuenta por tiempo de salidas es mayor al del tiempo descuento tickets
                                  $datos[$indice][$num]="0";
                              }else{//asistio pero no supero las salidas con el tiempo de descuento tickets
                                  $datos[$indice][$num]="1";$suma_tickes++;
                              }//finsi segundo no trabajo
                              
                         }else{
                             if($justificado==true){// si no laboro pero esta justificado( esto verifico arriba si la justificacion no descuenta ticketc)
                                 $datos[$indice][$num]="1";$suma_tickes++;
                             }else{
                                 $datos[$indice][$num]="0";
                             }//fin si justificado
                         }//fin si asistio


                      }else{
                          $datos[$indice][$num]=" ";
                         
                      }//fin si es laborable
                     
                      
                 }//fin para

                     // se consulta los si tiene tickets para sumar o restar
                     $sql="SELECT SUM(cantidad) as n FROM asistencias.tickets  WHERE  cedula='$undato[cedula_integrantes]'
                           AND ano='".$this->drop_ano->SelectedValue."' AND mes='".$this->drop_mes->SelectedValue."' AND tipo='SUMA'";
                     $tickets=cargar_data($sql,$sender);
                     $suma_tickes=$suma_tickes+$tickets[0]["n"];
                      // se consulta los si tiene tickets para restar
                     $sql="SELECT SUM(cantidad) as n FROM asistencias.tickets  WHERE  cedula='$undato[cedula_integrantes]'
                           AND ano='".$this->drop_ano->SelectedValue."' AND mes='".$this->drop_mes->SelectedValue."' AND tipo='RESTA' ";
                     $ticketr=cargar_data($sql,$sender);
                     $suma_tickes=$suma_tickes-$ticketr[0]["n"];

                     $datos[$indice]["Total Mes"]="$suma_tickes";
                      //  if($x>300)break;else$x++;
               }//fin foreach


            $this->DataGrid2->DataSource=$datos;
            $this->DataGrid2->dataBind();
            
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
								   (j.estatus='1') $AND and
                                   (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                                   (p.cedula = n.cedula) and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
							ORDER BY jd.fecha_desde";
            $this->Repeater->DataSource =  cargar_data($sql3,$sender);
            $this->Repeater->dataBind();
           
            $sql="SELECT t.*,p.nombres,p.apellidos
            FROM asistencias.tickets AS t
            INNER JOIN organizacion.personas AS p ON ( p.cedula = t.cedula )
            INNER JOIN asistencias.personas_status_asistencias AS s ON ( s.cedula = t.cedula )
            WHERE s.status_asistencia ='1'
            AND t.ano ='".$this->drop_ano->SelectedValue."'
            AND t.mes ='".$this->drop_mes->SelectedValue."'
            ORDER BY t.cedula, t.ano, t.mes ";
            // se carga las modificaciones de los tickes
            $this->Repeater1->DataSource =  cargar_data($sql,$sender);
            $this->Repeater1->dataBind();

        }
   // }


/* Esta función se encarga de generar un PDF con la información necesaria para
 * imprimir el reporte de aistencia por cédula y fechas.
 */
    public function imprimir_asistencia($sender, $param)
    {
        require('/var/www/tcpdf/tcpdf.php');
        $cod_organizacion = usuario_actual('cod_organizacion');
          //contruye dias
          //$desde="01/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;
          //$hasta=ultimo_dia_mes($this->drop_mes->SelectedValue,$this->drop_ano->SelectedValue)."/".$this->drop_mes->SelectedValue."/".$this->drop_ano->SelectedValue;
          $desde=$this->txt_fecha_desde->Text;
          $hasta=$this->txt_fecha_hasta->Text;
          $dir = $this->drop_direcciones->SelectedValue;

           if($this->drop_direcciones->SelectedValue=="TODOS LOS FUNCIONARIOS"){
            $AND=" AND (n.cod_direccion LIKE '0%') ";
           }else{ $AND=" AND (p.cedula='$dir') ";}



            $desde=cambiaf_a_mysql($desde);
            $hasta=cambiaf_a_mysql($hasta);

            //$this->mensaje_v->setSuccessMessage($sender, "desde: $desde/ hasta:$hasta", 'grow');


        /* consultas*/
        $pdf=new TCPDF('l', 'mm', 'legal', true, 'utf-8', false);
        $pdf->SetFillColor(205, 205, 205);//color de relleno gris

        $info_adicional= "Reporte de Ticket Alimentacion, Del: ".cambiaf_a_normal($desde)." Al: ".cambiaf_a_normal($hasta)."\n".
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

        //meses
        $arreglo_meses=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');

        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(0, 6, "Reporte de Ticket Alimentacion: ".$arreglo_meses[$this->drop_mes->SelectedValue]." del ".$this->drop_ano->SelectedValue." - ".count(dias_entre_fechas($this->txt_fecha_desde->Text,$this->txt_fecha_hasta->Text, 0, NULL, $sender))." Dias Laborables ", 'B', 1, 'C', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de asistentes en el PDF
        $pdf->SetFillColor(210,210,210);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.1);
        $pdf->SetFont('', 'B','9');
        
        //Header
        $pdf->Cell('20', 9,  "Cedula", 1, 0, 'C', 1);
        $pdf->Cell('105', 9,  "Nombres y Apellidos", 1, 0, 'C', 1);
        //dias de la semana
        //$arre_id=array("0"=>"L","1"=>"M","2"=>"M","3"=>"J","4"=>"V","5"=>"S","6"=>"D");
        $fechas=dias_entre_fechas(cambiaf_a_normal($desde),cambiaf_a_normal($hasta),2, NULL, $sender);
        $arre_id=array("0"=>"D","1"=>"L","2"=>"M","3"=>"M","4"=>"J","5"=>"V","6"=>"S");

        $pdf->SetFont('', 'B','8');
        //se llena el header con los titulos
        for($i=0;$i<=num_dias_entre_fechas(cambiaf_a_normal($desde),cambiaf_a_normal($hasta));$i++){
            list($dia,$mes,$anio) = split("/",$fechas[$i]['fecha']);
            $dia_semana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
            //$pdf->Cell('6', 7,  "$arre_id[$dia_semana]-".($i+1), 1, 0, 'C', 1);
            $pdf->MultiCell(6, 9, "$arre_id[$dia_semana]  ".($dia), 1, 'JL', 1, 0, '', '', true, 0);
        }//fin si header
        
        $pdf->SetFont('', 'B','9');
        $pdf->Cell('15', 9,  "Total Mes", 1, 0, 'C', 1);
        $pdf->Ln(9);
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $fill = 0;

        /*consultas*/
        // funcionarios activos de asistencia y que no sean pasantes (nive!=10)
        $sql1="SELECT (p.cedula) AS cedula_integrantes, p.nombres, p.apellidos, e.fecha,
								  MIN(e.hora) AS entrada, MAX(e.hora) AS salida
						   FROM organizacion.personas AS p
						   LEFT JOIN (SELECT *
									  FROM asistencias.entrada_salida AS j
									  WHERE ((j.fecha >= '$desde') AND (j.fecha <= '$hasta'))) AS e ON p.cedula = e.cedula
						   WHERE ((p.cedula IN ( SELECT s.cedula
												 FROM asistencias.personas_status_asistencias s, organizacion.personas_nivel_dir as n
												 WHERE (s.status_asistencia =1) and (p.cedula = n.cedula)  and (n.nivel!='10') $AND ))
								 AND (p.fecha_ingreso <= '$desde'))
						   GROUP BY p.cedula, e.fecha
						   ORDER BY p.nombres, p.apellidos, e.fecha ";
            $asistencia=cargar_data($sql1,$sender);


           $sql="Select * from organizacion.dias_no_laborables";
            $no_laborables=cargar_data($sql,$sender);

            $datos=array();
            $arreglo_feriado = dias_feriados($sender);

            $sqlct="SELECT minutos_para_restar as mtc FROM asistencias.opciones_ticket
                     WHERE  vigencia_hasta >= '$hasta'
                     ORDER BY status DESC LIMIT 0,1";
            $mtc=cargar_data($sqlct,$sender);
            $segundos_quitar_ticket= $mtc[0]['mtc']*60;
            $modificaciones_tickets==array();
            $cedula_1="";$cont=0;
            foreach ($asistencia as $undato)
            {
                $indice = $undato['cedula_integrantes'];
                if($cedula_1!=$indice){
                    $pdf->SetFont('', 'B','9');
                     
                      $cedula_1=$undato['cedula_integrantes'];
                      $pdf->Cell(20, 6, $undato['cedula_integrantes'], 'T', 0, 'C', $fill);
                      $pdf->Cell(105, 6, $undato['nombres']." ".$undato['apellidos'], 'T', 0, 'L', $fill);
                      $pdf->SetTextColor(0);//color negro

                        $suma_tickes=0;
                        $pdf->SetFont('', 'B','8');
                     // se rellena los dias del mes en las celdas y se coloca el total de tickets
                     for($i=0;$i<=num_dias_entre_fechas(cambiaf_a_normal($desde),cambiaf_a_normal($hasta));$i++){

                          $cero=0;$justificado=false;$asistio=false;$segundos_no_trabajo=0;
                          list($dia2,$mes,$anio) = split("/", $fechas[$i]['fecha']);
                          $anio=intval($anio); $mes=intval($mes); $dia2=intval($dia2);
                          $dia_semana = date("w", mktime(0, 0, 0, $mes, $dia2, $anio));
                          //Inicial del dia de semana y numero del dia
                          $num=$arre_id[$dia_semana]."</br> ".($dia2);
                          $dia_n=$fechas[$i]['fecha'];
                          $dia=cambiaf_a_mysql($dia_n);
                          $laborable=es_feriado($dia_n, null, $sender);

                          if($laborable==0){//si es laborable dia habil

                            $sql="SELECT e.cedula, e.fecha,MIN(e.hora) as entrada, MAX(e.hora) as salida FROM asistencias.entrada_salida as e
                               WHERE ((e.cedula = '$indice') AND (e.fecha = '$dia'))";
                               $asistencia2=cargar_data($sql,$sender);

                            if($asistencia2[0]['cedula']!=''){// si registro hora en sistema
                             //$datos[$indice][$num].="-A";
                              //se verifica la exclusion de la asistencia del funcionario
                               $sql="select cedula FROM asistencias.excluidos WHERE cedula='$indice'";
                               $excluido=cargar_data($sql,$sender);
                               //si esta excluido no se toman los segudos no laborados
                               if($excluido[0]['cedula']=='')
                               $segundos_no_trabajo=$this->acumulado_segundos($dia_n,$indice,$sender,$param);

                               $asistio=true;

                            }else{  // Si no esta la fecha quiere decir que es un dia laborable, pero

                                        // se obtienen las justificaciones del rango de fechas seleccionado
                                        $sql="SELECT (p.cedula) as cedula_just, p.nombres, p.apellidos, j.*, jp.*, jd.*, tj.descripcion as descripcion_tipo_justificacion
                                            FROM asistencias.justificaciones as j, asistencias.justificaciones_dias as jd,
                                                 asistencias.justificaciones_personas as jp, organizacion.personas as p, asistencias.tipo_justificaciones tj
                                            WHERE (
                                                   (p.cedula = jp.cedula) and
                                                   (p.cedula = '$indice') and
                                                   ((
                                                     (jd.fecha_desde <= '$desde') and
                                                     (jd.fecha_hasta >= '$desde')
                                                     ) or
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
                                               ORDER BY jd.descuenta_ticket,jd.fecha_desde";
                                    $this->justificaciones=cargar_data($sql,$sender);

                                    // el funcionario no marco ni entrada ni salida, por lo que se busca alguna justificación.
                                    $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($dia_n), $sender);
                                    $result  = esta_justificado($this->justificaciones,$indice,$dia_n,$horario_vigente[0]['hora_entrada'],$sender);
                                    $result2 = esta_justificado($this->justificaciones,$indice,$dia_n,$horario_vigente[0]['hora_salida'],$sender);
                                    if (($result != false) && ($result2 != false)){
                                        // si esta justificado pero aun así se descuenta ticket, se procesa el acumulado.
                                         if ($result['descuenta_ticket'] == 'No')$justificado=true;
                                    }//fin si justificado

                                }//fin si no asistio



                             // se verifica la suma de tickets
                             if($asistio){
                                  if(($segundos_no_trabajo>=$segundos_quitar_ticket)){// se le descuenta por tiempo de salidas es mayor al del tiempo descuento tickets
                                      //$datos[$indice][$num]="0";
                                      $pdf->Cell(6, 7, '0', 'TB', 0, 'C', $fill);
                                  }else{//asistio pero no supero las salidas con el tiempo de descuento tickets
                                      //$datos[$indice][$num]="1";
                                       $suma_tickes++;
                                       $pdf->Cell(6, 7, '1', 'TB', 0, 'C', $fill);
                                  }//finsi segundo no trabajo

                             }else{
                                 if($justificado){// si no laboro pero esta justificado( esto verifico arriba si la justificacion no descuenta ticketc)
                                     $datos[$indice][$num]="1";
                                      $suma_tickes++;
                                       $pdf->Cell(6, 7, '1', 'TB', 0, 'C', $fill);
                                 }else{
                                     //$datos[$indice][$num]="0";
                                      $pdf->Cell(6, 7, '0', 'TB', 0, 'C', $fill);
                                 }//fin si judtificado
                             }//fin si asistio


                          }else{
                              //$datos[$indice][$num]=" ";

                               $pdf->SetFillColor(210,210,210);
                              $pdf->Cell(6, 7, ' ', 'TB', 0, 'C', 1);
                              $pdf->SetFillColor(224, 235, 255);

                          }//fin si es laborable

                     }//fin para

                         // se consulta los si tiene tickets para sumar o restar
                         $sql="SELECT SUM(cantidad) as n FROM asistencias.tickets  WHERE  cedula='$undato[cedula_integrantes]'
                               AND ano='".$this->drop_ano->SelectedValue."' AND mes='".$this->drop_mes->SelectedValue."' AND tipo='SUMA'";
                         $tickets=cargar_data($sql,$sender);
                         $suma_tickes=$suma_tickes+$tickets[0]["n"];
                          // se consulta los si tiene tickets para restar
                         $sql="SELECT SUM(cantidad) as n FROM asistencias.tickets  WHERE  cedula='$undato[cedula_integrantes]'
                               AND ano='".$this->drop_ano->SelectedValue."' AND mes='".$this->drop_mes->SelectedValue."' AND tipo='RESTA' ";
                         $ticketr=cargar_data($sql,$sender);
                         $suma_tickes=$suma_tickes-$ticketr[0]["n"];

                        // $datos[$indice]["Total dias"]="$suma_tickes";
                          $pdf->Cell('15', 7,  $suma_tickes,'TB', 0, 'C', 1);

                    $pdf->Ln();$cont++;
                    $fill=!$fill;

                }///fin si mostrar


                if($cont==23){
                 $cont=0;

                   //Header
                    $pdf->SetFont('helvetica', '', 12);
                    $pdf->SetFillColor(210,210,210);
                    $pdf->SetTextColor(0);
                    $pdf->SetDrawColor(41, 22, 11);
                    $pdf->SetLineWidth(0.1);
                    $pdf->SetFont('', 'B','9');
                    $pdf->Cell('20', 9,  "Cedula", 1, 0, 'C', 1);
                    $pdf->Cell('105', 9,  "Nombres y Apellidos", 1, 0, 'C', 1);
                    //dias de la semana
                    //$arre_id=array("0"=>"L","1"=>"M","2"=>"M","3"=>"J","4"=>"V","5"=>"S","6"=>"D");
                    $fechas=dias_entre_fechas(cambiaf_a_normal($desde),cambiaf_a_normal($hasta),2, NULL, $sender);
                    $arre_id=array("0"=>"D","1"=>"L","2"=>"M","3"=>"M","4"=>"J","5"=>"V","6"=>"S");

                    $pdf->SetFont('', 'B','8');
                    //se llena el header con los titulos
                    for($i=0;$i<=num_dias_entre_fechas(cambiaf_a_normal($desde),cambiaf_a_normal($hasta));$i++){
                        list($dia,$mes,$anio) = split("/",$fechas[$i]['fecha']);
                        $dia_semana = date("w", mktime(0, 0, 0, $mes, $dia, $anio));
                        //$pdf->Cell('6', 7,  "$arre_id[$dia_semana]-".($i+1), 1, 0, 'C', 1);
                        $pdf->MultiCell(6, 9, "$arre_id[$dia_semana]  ".($dia), 1, 'JL', 1, 0, '', '', true, 0);
                    }//fin si header

                    $pdf->SetFont('', 'B','9');
                    $pdf->Cell('15', 9,  "Total Mes", 1, 0, 'C', 1);
                    $pdf->Ln(9);
                    // Color and font restoration
                    $pdf->SetFillColor(224, 235, 255);
                    $pdf->SetTextColor(0);
                    $pdf->SetFont('','',10);
                    // Data
                    $fill = 0;
              }//fin si header*/

            }//fin para funcionarios


        // Se añaden las observaciones a la asistencia

        //$asistentes_header = array('Cédula', 'Nombres', $desde, $hasta, suma_dias($this->txt_fecha_desde->Text, 2), suma_dias($this->txt_fecha_desde->Text, 3), suma_dias($this->txt_fecha_desde->Text, 4));
        $justificaciones_header = array('Observaciones a la asistencia');
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
								   (j.estatus='1') $AND and
                                   (jd.codigo_tipo_falta = tf.codigo) and (tj.id = jd.codigo_tipo_justificacion) and
                                   (p.cedula = n.cedula) and (j.codigo=jd.codigo_just) and (j.codigo=jp.codigo_just))
							ORDER BY jd.fecha_desde";
            $justificaciones=cargar_data($sql3,$sender);

             if(!empty($justificaciones)){
        // Separación
         $pdf->Ln(); $pdf->Ln();
       // $pdf->AddPage();
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
         //$pdf->MultiCell($w[0], 0, count($justificaciones_header), 1, 'J',$fill,'1','','',true,0,true);
        foreach($justificaciones as $row) {
            $observacion = $row['cedula']." / ".$row['nombres']." ".$row['apellidos']." - Código: ".$row['codigo'].", Desde el: ".cambiaf_a_normal($row['fecha_desde']).", Hasta el: ".cambiaf_a_normal($row['fecha_hasta']).
                ", de: ".date("h:i:s a",strtotime($row['hora_desde']))." a: ".date("h:i:s a",strtotime($row['hora_hasta'])).", Tipo: ".$row['descripcion_tipo_justificacion'].
                ", Falta: ".$row['descripcion_falta'].", Motivo: ".$row['observaciones'];
            $pdf->MultiCell($w[0], 0, $observacion, 1, 'J',$fill,'1','','',true,0,false);
            $fill=!$fill;
        }
        
        
        }//fin si empty
        
        
        
        
         // se carga las modificaciones de los tickes
        
        $sql2="SELECT t.*,p.nombres,p.apellidos
            FROM asistencias.tickets AS t
            INNER JOIN organizacion.personas AS p ON ( p.cedula = t.cedula )
            INNER JOIN asistencias.personas_status_asistencias AS s ON ( s.cedula = t.cedula )
            WHERE s.status_asistencia ='1'
            AND t.ano ='".$this->drop_ano->SelectedValue."'
            AND t.mes ='".$this->drop_mes->SelectedValue."'
            ORDER BY t.cedula, t.ano, t.mes ";
        $justificaciones2=cargar_data($sql2,$sender);

        if(!empty($justificaciones2)){
          $header = array('Observaciones a los tickets');
         // Separación
          $pdf->Ln(); $pdf->Ln();
       // $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(0, 0, "Observaciones a lo Tickets", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        // se realiza el listado de inasistentes en el PDF
        $pdf->SetFillColor(210,210,210);
        $pdf->SetTextColor(0);
        $pdf->SetDrawColor(41, 22, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('', 'B');
        // Header
        $w = array(330);
        for($i = 0; $i < count($header); $i++)
        $pdf->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
        $pdf->Ln();
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $fill = 0;
         //$pdf->MultiCell($w[0], 0, count($justificaciones_header), 1, 'J',$fill,'1','','',true,0,true);
        foreach($justificaciones2 as $row) {
            $observacion = $row['cedula']." / ".$row['nombres']." ".$row['apellidos']." - Año: ".$row['ano'].", Mes: ".$row['mes']." Tipo: ".$row['tipo'].
                ", Cantidad: ".$row['cantidad'].", Motivo: ".$row['motivo'];
            $pdf->MultiCell($w[0], 0, $observacion, 1, 'J',$fill,'1','','',true,0,false);
            $fill=!$fill;
        }
        }//fin si empty
        
        $pdf->Output("Ticket Alimentacion ".$desde."_al_".$hasta.".pdf",'D');
    }

    public function changePage($sender,$param)
	{
        $this->DataGrid2->CurrentPageIndex=$param->NewPageIndex;
        $this->consulta_asistencia($sender, $param);
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}


}
?>