<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Este es un reporte de asistencias e inasistencias para un funcionario
 *              y un rango de fechas dadas.
 *****************************************************  FIN DE INFO
*/
include("protected/comunes/libchart/classes/libchart.php");
class asistencia_cedula extends TPage
{
    var $justificaciones; // info de las justificaciones
    // Variables para los gráficos
    var $ind_asistentes=0;    // el total de veces que se vino a trabajar
    var $ind_asistentes_tarde_no_just=0;  // las veces que llego tarde sin justificacion
    var $ind_asistentes_tarde_si_just=0;  // las llegadas tardes justificadas
    var $ind_inasistentes_no_just=0;
    var $ind_inasistentes_si_just=0;
    var $ind_asistentes_salidas_no_just=0;
    var $ind_asistentes_salidas_si_just=0;
    var $ind_asistentes_salidas_tot=0;
    var $dias_semana_retrasos=array();
    var $dias_semana_retrasos_si_just=array();
    var $dias_semana_retrasos_no_just=array();
    var $dias_semana_salidas_si_just=array();
    var $dias_semana_salidas_no_just=array();
    var $dias_semana_salidas_tot=array();
    var $diasasistencia; // contiene los datos de la asistencia, asi como su entrada, salida y observaciones.

    var $nombre_funcionario_reporte; // nombre del funcionario objeto del reporte

        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
                $this->txt_fecha_desde->Text = date("d/m/Y",strtotime("-1 day"));
                $this->txt_fecha_hasta->Text = date("d/m/Y",strtotime("-1 day"));
            $cod_org = usuario_actual('cod_organizacion');
                // funcionarios activos de asistencia
            $sql="select p.cedula,  CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_org'))
                        ORDER BY p.nombres, p.apellidos";

            $resultado=cargar_data($sql,$this);
            $this->drop_funcionario->DataSource=$resultado;
            $this->drop_funcionario->dataBind();
            }
        }



/* Esta función Realiza la consulta y muestra el listado de asistentes e
 * inasistentes con los correspondientes gráficos y observaciones*/
    public function consulta_asistencia($sender, $param)
    {
        if ($this->IsValid)
        {
            $cedula = $this->drop_funcionario->SelectedValue;
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
                            WHERE ((e.cedula = '$cedula') and (e.fecha <= '$hasta') and (e.fecha >= '$desde'))
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
                        $this->ind_asistentes++;
                        $esta = true;
                    }
                    $contador++;
                }
            }

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
                            ORDER BY jd.fecha_desde";
            $this->justificaciones=cargar_data($sql,$this);

            
            // ahora se complementan el arreglo con las justificaciones de los dias que no
            // siendo feriados no los laboró
            $contador = 0;
            while ($contador < count($this->diasasistencia))
            {
                if (($this->diasasistencia[$contador]['entrada'] == "") && ($this->diasasistencia[$contador]['entrada'] == "") &&
                    ($this->diasasistencia[$contador]['observacion'] == ""))
                    {
                        $esta2 = false;
                        $contador2 = 0;
                        $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($this->diasasistencia[$contador]['fecha']),$sender);

                        
                       while (($contador2 < count($this->justificaciones)) && ($esta2 == false))
                        {
                             // se obtienen las justificaciones del rango de fechas seleccionado

                            $result  = esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$this->diasasistencia[$contador]['fecha'],$horario_vigente[0]['hora_entrada'],$sender);
                            $result2 = esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$this->diasasistencia[$contador]['fecha'],$horario_vigente[0]['hora_salida'],$sender);

                            //$sql="$result-$result2";
                           // $prueba=cargar_data($sql,$this);
                            
                            if (($result != false) && ($result2 != false))
                             {
                                $this->diasasistencia[$contador]['observacion'] = $result['descripcion_tipo_justificacion'].", Cód: ".$result['codigo'];
                                $esta2 = true;
                             }
                            $contador2++;
                        }
                    }
                $contador++;
            }

            $this->DataGrid->Caption="Reporte de Asistencias del ".$this->txt_fecha_desde->Text." al ".$this->txt_fecha_hasta->Text;
            $this->DataGrid->DataSource=$this->diasasistencia;
            $this->DataGrid->dataBind();

            $this->Repeater->DataSource =  $this->justificaciones;
            $this->Repeater->dataBind();

            $xale=rand(100,99999);
            // Se realiza la construcción de los gráficos e indicadores.

            $chart = new PieChart();
            $dataSet = new XYDataSet();
            if ($this->ind_asistentes>=1) {$dataSet->addPoint(new Point("Asistencias: (".$this->ind_asistentes.")", $this->ind_asistentes));};
            if ($this->ind_inasistentes_no_just>=1) {$dataSet->addPoint(new Point("Inasistencias NO JUSTIFICADOS: (".$this->ind_inasistentes_no_just.")", $this->ind_inasistentes_no_just));};
            if ($this->ind_inasistentes_si_just>=1) {$dataSet->addPoint(new Point("Inasistencias JUSTIFICADOS: (".$this->ind_inasistentes_si_just.")", $this->ind_inasistentes_si_just));};
            $chart->setDataSet($dataSet);
            $chart->setTitle("Asistencias / Inasistencias de: ".$this->drop_funcionario->SelectedValue);
            elimina_grafico($xale."_03.png");
            $chart->render("imagenes/temporales/".$xale."_03.png");
            $this->grafico1->ImageUrl = "imagenes/temporales/".$xale."_03.png";

            if ($this->ind_asistentes>=1)
              {  // si hay datos suficientes, creo el gr�fico, si no no (para no cargar al servidor)
                  $chart2 = new PieChart();
                  $dataSet2 = new XYDataSet();
                  $ind_asistentes_no_retrasados=$this->ind_asistentes-$this->ind_asistentes_tarde_no_just-$this->ind_asistentes_tarde_si_just;
                  if ($ind_asistentes_no_retrasados>=1) {$dataSet2->addPoint(new Point("Puntuales: (".$ind_asistentes_no_retrasados.")",
                                                                                                    $ind_asistentes_no_retrasados));};
                  if ($this->ind_asistentes_tarde_no_just>=1) {$dataSet2->addPoint(new Point("Impuntuales NO JUSTIFICADOS: (".$this->ind_asistentes_tarde_no_just.")",
                                                                                                                      $this->ind_asistentes_tarde_no_just));};
                  if ($this->ind_asistentes_tarde_si_just>=1) {$dataSet2->addPoint(new Point("Impuntuales JUSTIFICADOS: (".$this->ind_asistentes_tarde_si_just.")",
                                                                                                                   $this->ind_asistentes_tarde_si_just));};                 
                  $chart2->setDataSet($dataSet2);
                  $chart2->setTitle("Porcentajes de Retrasos para: ".$this->nombre_funcionario_reporte);
                  elimina_grafico($xale."_04.png");
                  $chart2->render("imagenes/temporales/".$xale."_04.png");
                  $this->grafico2->ImageUrl = "imagenes/temporales/".$xale."_04.png";
              } 


            if ($this->ind_asistentes_salidas_tot>=1)
              {  // si hay datos suficientes, creo el gr�fico, si no no (para no cargar al servidor)
                  $chart4 = new PieChart();
                  $dataSet4 = new XYDataSet();
                  $ind_asistentes_salidas=$this->ind_asistentes_salidas_tot-$this->ind_asistentes_salidas_no_just-$this->ind_asistentes_salidas_si_just;
                  if ($ind_asistentes_salidas>=1) {$dataSet4->addPoint(new Point("Salidas Tempranas: (".$ind_asistentes_salidas.")", $ind_asistentes_salidas));};
                  if ($this->ind_asistentes_salidas_no_just>=1)
                  {$dataSet4->addPoint(new Point("Salidas Tempranas NO JUSTIFICADAS: (".$this->ind_asistentes_salidas_no_just.")", $this->ind_asistentes_salidas_no_just));};
                  if ($this->ind_asistentes_salidas_si_just>=1)
                  {$dataSet4->addPoint(new Point("Salidas Tempranas JUSTIFICADOS: (".$this->ind_asistentes_salidas_si_just.")", $this->ind_asistentes_salidas_si_just));};
                  $chart4->setDataSet($dataSet4);
                  $chart4->setTitle("Salidas Tempranas para: ".$this->nombre_funcionario_reporte);
                  elimina_grafico($xale."_05.png");
                  $chart4->render("imagenes/temporales/".$xale."_05.png");
                  $this->grafico3->ImageUrl = "imagenes/temporales/".$xale."_05.png";
              }

            if ((isset($this->dias_semana_retrasos['Mon'])) || (isset($this->dias_semana_retrasos['Tue'])) || (isset($this->dias_semana_retrasos['Wed'])) || (isset($this->dias_semana_retrasos['Thu'])) || (isset($this->dias_semana_retrasos['Fri'])))
              {  // si hay datos suficientes, creo el grafico, si no no (para no cargar al servidor)
                  $chart3 = new VerticalBarChart();
                  $serie1 = new XYDataSet();
                  if (isset($this->dias_semana_retrasos['Mon'])) {$serie1->addPoint(new Point("Lunes", $this->dias_semana_retrasos['Mon']));}
                  else {$serie1->addPoint(new Point("Lunes", 0));};
                  if (isset($this->dias_semana_retrasos['Tue'])) {$serie1->addPoint(new Point("Martes", $this->dias_semana_retrasos['Tue']));}
                  else {$serie1->addPoint(new Point("Martes", 0));};
                  if (isset($this->dias_semana_retrasos['Wed'])) {$serie1->addPoint(new Point("Miércoles", $this->dias_semana_retrasos['Wed']));}
                  else {$serie1->addPoint(new Point("Miércoles", 0));};
                  if (isset($this->dias_semana_retrasos['Thu'])) {$serie1->addPoint(new Point("Jueves", $this->dias_semana_retrasos['Thu']));}
                  else {$serie1->addPoint(new Point("Jueves", 0));};
                  if (isset($this->dias_semana_retrasos['Fri'])) {$serie1->addPoint(new Point("Viernes", $this->dias_semana_retrasos['Fri']));}
                  else {$serie1->addPoint(new Point("Viernes", 0));};


                  $serie2 = new XYDataSet();
                  if (isset($this->dias_semana_retrasos_si_just['Mon'])) {$serie2->addPoint(new Point("Lunes", $this->dias_semana_retrasos_si_just['Mon']));}
                  else {$serie2->addPoint(new Point("Lunes", 0));};
                  if (isset($this->dias_semana_retrasos_si_just['Tue'])) {$serie2->addPoint(new Point("Martes", $this->dias_semana_retrasos_si_just['Tue']));}
                  else {$serie2->addPoint(new Point("Martes", 0));};
                  if (isset($this->dias_semana_retrasos_si_just['Wed'])) {$serie2->addPoint(new Point("Miércoles", $this->dias_semana_retrasos_si_just['Wed']));}
                  else {$serie2->addPoint(new Point("Miércoles", 0));};
                  if (isset($this->dias_semana_retrasos_si_just['Thu'])) {$serie2->addPoint(new Point("Jueves", $this->dias_semana_retrasos_si_just['Thu']));}
                  else {$serie2->addPoint(new Point("Jueves", 0));};
                  if (isset($this->dias_semana_retrasos_si_just['Fri'])) {$serie2->addPoint(new Point("Viernes", $this->dias_semana_retrasos_si_just['Fri']));}
                  else {$serie2->addPoint(new Point("Viernes", 0));};


                  $serie3 = new XYDataSet();
                  if (isset($this->dias_semana_retrasos_no_just['Mon'])) {$serie3->addPoint(new Point("Lunes", $this->dias_semana_retrasos_no_just['Mon']));}
                  else {$serie3->addPoint(new Point("Lunes", 0));};
                  if (isset($this->dias_semana_retrasos_no_just['Tue'])) {$serie3->addPoint(new Point("Martes", $this->dias_semana_retrasos_no_just['Tue']));}
                  else {$serie3->addPoint(new Point("Martes", 0));};
                  if (isset($this->dias_semana_retrasos_no_just['Wed'])) {$serie3->addPoint(new Point("Miércoles", $this->dias_semana_retrasos_no_just['Wed']));}
                  else {$serie3->addPoint(new Point("Miércoles", 0));};
                  if (isset($this->dias_semana_retrasos_no_just['Thu'])) {$serie3->addPoint(new Point("Jueves", $this->dias_semana_retrasos_no_just['Thu']));}
                  else {$serie3->addPoint(new Point("Jueves", 0));};
                  if (isset($this->dias_semana_retrasos_no_just['Fri'])) {$serie3->addPoint(new Point("Viernes", $this->dias_semana_retrasos_no_just['Fri']));}
                  else {$serie3->addPoint(new Point("Viernes", 0));};

                  $dataSet3 = new XYSeriesDataSet();
                  $dataSet3->addSerie("Retrasos Totales", $serie1);
                  $dataSet3->addSerie("Retrasos Justificados", $serie2);
                  $dataSet3->addSerie("Retrasos Injustificados", $serie3);
                  $chart3->setDataSet($dataSet3);
                  $chart3->setTitle("Retrasos por día de la semana: ".$this->nombre_funcionario_reporte);
                  elimina_grafico($xale."_06.png");
                  $chart3->render("imagenes/temporales/".$xale."_06.png");
                  $this->grafico4->ImageUrl = "imagenes/temporales/".$xale."_06.png";
              }


            if ((isset($this->dias_semana_salidas_tot['Mon'])) || (isset($this->dias_semana_salidas_tot['Tue'])) || (isset($this->dias_semana_salidas_tot['Wed'])) || (isset($this->dias_semana_salidas_tot['Thu'])) || (isset($this->dias_semana_salidas_tot['Fri'])))
              {  // si hay datos suficientes, creo el gr�fico, si no no (para no cargar al servidor)
                  $chart5 = new VerticalBarChart();
                  $serieA1 = new XYDataSet();
                  if (isset($this->dias_semana_salidas_tot['Mon'])) {$serieA1->addPoint(new Point("Lunes", $this->dias_semana_salidas_tot['Mon']));}
                  else {$serieA1->addPoint(new Point("Lunes", 0));};
                  if (isset($this->dias_semana_salidas_tot['Tue'])) {$serieA1->addPoint(new Point("Martes", $this->dias_semana_salidas_tot['Tue']));}
                  else {$serieA1->addPoint(new Point("Martes", 0));};
                  if (isset($this->dias_semana_salidas_tot['Wed'])) {$serieA1->addPoint(new Point("Miércoles", $this->dias_semana_salidas_tot['Wed']));}
                  else {$serieA1->addPoint(new Point("Miércoles", 0));};
                  if (isset($this->dias_semana_salidas_tot['Thu'])) {$serieA1->addPoint(new Point("Jueves", $this->dias_semana_salidas_tot['Thu']));}
                  else {$serieA1->addPoint(new Point("Jueves", 0));};
                  if (isset($this->dias_semana_salidas_tot['Fri'])) {$serieA1->addPoint(new Point("Viernes", $this->dias_semana_salidas_tot['Fri']));}
                  else {$serieA1->addPoint(new Point("Viernes", 0));};


                  $serieA2 = new XYDataSet();
                  if (isset($this->dias_semana_salidas_si_just['Mon'])) {$serieA2->addPoint(new Point("Lunes", $this->dias_semana_salidas_si_just['Mon']));}
                  else {$serieA2->addPoint(new Point("Lunes", 0));};
                  if (isset($this->dias_semana_salidas_si_just['Tue'])) {$serieA2->addPoint(new Point("Martes", $this->dias_semana_salidas_si_just['Tue']));}
                  else {$serieA2->addPoint(new Point("Martes", 0));};
                  if (isset($this->dias_semana_salidas_si_just['Wed'])) {$serieA2->addPoint(new Point("Miércoles", $this->dias_semana_salidas_si_just['Wed']));}
                  else {$serieA2->addPoint(new Point("Miércoles", 0));};
                  if (isset($this->dias_semana_salidas_si_just['Thu'])) {$serieA2->addPoint(new Point("Jueves", $this->dias_semana_salidas_si_just['Thu']));}
                  else {$serieA2->addPoint(new Point("Jueves", 0));};
                  if (isset($this->dias_semana_salidas_si_just['Fri'])) {$serieA2->addPoint(new Point("Viernes", $this->dias_semana_salidas_si_just['Fri']));}
                  else {$serieA2->addPoint(new Point("Viernes", 0));};


                  $serieA3 = new XYDataSet();
                  if (isset($this->dias_semana_salidas_no_just['Mon'])) {$serieA3->addPoint(new Point("Lunes", $this->dias_semana_salidas_no_just['Mon']));}
                  else {$serieA3->addPoint(new Point("Lunes", 0));};
                  if (isset($this->dias_semana_salidas_no_just['Tue'])) {$serieA3->addPoint(new Point("Martes", $this->dias_semana_salidas_no_just['Tue']));}
                  else {$serieA3->addPoint(new Point("Martes", 0));};
                  if (isset($this->dias_semana_salidas_no_just['Wed'])) {$serieA3->addPoint(new Point("Miércoles", $this->dias_semana_salidas_no_just['Wed']));}
                  else {$serieA3->addPoint(new Point("Miércoles", 0));};
                  if (isset($this->dias_semana_salidas_no_just['Thu'])) {$serieA3->addPoint(new Point("Jueves", $this->dias_semana_salidas_no_just['Thu']));}
                  else {$serieA3->addPoint(new Point("Jueves", 0));};
                  if (isset($this->dias_semana_salidas_no_just['Fri'])) {$serieA3->addPoint(new Point("Viernes", $this->dias_semana_salidas_no_just['Fri']));}
                  else {$serieA3->addPoint(new Point("Viernes", 0));};

                  $dataSet5 = new XYSeriesDataSet();
                  $dataSet5->addSerie("Salidas Tempranas", $serieA1);
                  $dataSet5->addSerie("Justificadas", $serieA2);
                  $dataSet5->addSerie("Injustificadas", $serieA3);
                  $chart5->setDataSet($dataSet5);

                  $chart5->setTitle("Distribución de Salidas Tempranas: ".$this->nombre_funcionario_reporte);
                  elimina_grafico($xale."_07.png");
                  $chart5->render("imagenes/temporales/".$xale."_07.png");
                  $this->grafico5->ImageUrl = "imagenes/temporales/".$xale."_07.png";
              }
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
                $this->ind_inasistentes_no_just++;
            }
            else
            {
                $item->entrada->ForeColor = "Green";
               // si es dia habil se toma como no justificado
                if(es_feriado($item->fecha->Text,NULL, $sender)==0) $this->ind_inasistentes_si_just++;
            }
        }
        else
        {
            $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($item->fecha->Text),$sender);
            list($diaX ,$mesX, $anioX) = split("/", $item->fecha->Text);
            if (($item->entrada->Text != "Entrada") and (($item->entrada->Text != "")))
            {
                if (strtotime($item->entrada->Text) > (strtotime($horario_vigente[0]['hora_entrada']." + ".
                                                       $horario_vigente[0]['holgura_entrada']." minutes")))
                   {
                       if (esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$item->fecha->Text,$item->entrada->Text,$sender) != false)
                         {
                             $item->entrada->ForeColor = "Green";
                             $this->ind_asistentes_tarde_si_just++;
                             if (isset($this->dias_semana_retrasos_si_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]))
                               { $this->dias_semana_retrasos_si_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]++;}
                             else
                               { $this->dias_semana_retrasos_si_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]=1;}
                         }
                       else
                         {
                             $item->entrada->ForeColor = "Red";
                             $this->ind_asistentes_tarde_no_just++;
                             if (isset($this->dias_semana_retrasos_no_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]))
                               { $this->dias_semana_retrasos_no_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]++;}
                             else
                               { $this->dias_semana_retrasos_no_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]=1;}

                         }
                       $item->entrada->Font->Bold = "true";

                       if (isset($this->dias_semana_retrasos[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]))
                         { $this->dias_semana_retrasos[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]++;}
                       else
                         { $this->dias_semana_retrasos[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]=1;}
                   }
                 $item->entrada->Text = date("h:i:s a",strtotime($item->entrada->Text));
            }

            if (($item->salida->Text != "Salida") and ($item->salida->Text != ""))
            {
                if (strtotime($item->salida->Text) < strtotime($horario_vigente[0]['hora_salida']))
                {
                   if (esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$item->fecha->Text,$item->salida->Text,$sender) != false)
                       {
                           $item->salida->ForeColor = "Green";
                           $this->ind_asistentes_salidas_si_just++;
                           if (isset($this->dias_semana_salidas_si_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]))
                             { $this->dias_semana_salidas_si_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]++; }
                           else
                             { $this->dias_semana_salidas_si_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]=1; }
                       }
                   else
                       {
                           $item->salida->ForeColor = "Red";
                           $this->ind_asistentes_salidas_no_just++;
                           if (isset($this->dias_semana_salidas_no_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]))
                             { $this->dias_semana_salidas_no_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]++; }
                           else
                             { $this->dias_semana_salidas_no_just[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]=1; }
                       }
                   $item->salida->Font->Bold = "true";
                   $this->ind_asistentes_salidas_tot++;
                   if (isset($this->dias_semana_salidas_tot[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]))
                     { $this->dias_semana_salidas_tot[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]++; }
                   else
                     { $this->dias_semana_salidas_tot[date("D", mktime(0, 0, 0, $mesX, $diaX, $anioX))]=1; }
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
        require('/var/www/tcpdf/tcpdf.php');
        $cod_organizacion = usuario_actual('cod_organizacion');


            $cedula = $this->drop_funcionario->SelectedValue;
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
                        $this->ind_asistentes++;
                        $esta = true;
                    }
                    $contador++;
                }
            }

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
                            ORDER BY jd.fecha_desde";
            $this->justificaciones=cargar_data($sql,$this);

            // ahora se complementan el arreglo con las justificaciones de los dias que no
            // siendo feriados no los laboró
            $contador = 0;
            while ($contador < count($this->diasasistencia))
            {
                if (($this->diasasistencia[$contador]['entrada'] == "") && ($this->diasasistencia[$contador]['entrada'] == "") &&
                    ($this->diasasistencia[$contador]['observacion'] == ""))
                    {
                        $esta2 = false;
                        $contador2 = 0;
                        $horario_vigente = obtener_horario_vigente($cod_organizacion,cambiaf_a_mysql($this->diasasistencia[$contador]['fecha']),$sender);
                        while (($contador2 < count($this->justificaciones)) && ($esta2 == false))
                        {
                            $result  = esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$this->diasasistencia[$contador]['fecha'],$horario_vigente[0]['hora_entrada'],$sender);
                            $result2 = esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$this->diasasistencia[$contador]['fecha'],$horario_vigente[0]['hora_salida'],$sender);
                            if (($result != false) && ($result2 != false))
                             {
                                $this->diasasistencia[$contador]['observacion'] = $result['descripcion_tipo_justificacion'].", Cód: ".$result['codigo'];
                                $esta2 = true;
                             }
                            $contador2++;
                        }
                    }
                $contador++;
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
                if (strtotime($row['entrada'])>= (strtotime($horario_vigente_rpt[0]['hora_entrada']." + ".
                                                            $horario_vigente_rpt[0]['holgura_entrada']." minutes")))
                   {// si llegó tarde, ahora se comprueba la justificacion
                       if (esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$row['fecha'],$row['entrada'],$sender) != false)
                         { $pdf->SetTextColor(0,130,0);} // si esta justificado se coloca en verde
                       else
                         { $pdf->SetTextColor(200,50,50); }// si no esta justificado, se coloca en rojo
                   }
                 $pdf->Cell($w[1], 6, date("h:i:s a",strtotime($row['entrada'])), 'LR', 0, 'C', $fill);
                 $pdf->SetTextColor(0);


                if (strtotime($row['salida'])< strtotime($horario_vigente_rpt[0]['hora_salida']))
                { // si salió temprano, se comprueban las justificaciones
                   if (esta_justificado($this->justificaciones,$this->drop_funcionario->SelectedValue,$row['fecha'],$row['salida'],$sender) != false)
                     { $pdf->SetTextColor(0,130,0); } // si esta justificada la salida temprana se coloca en verde
                   else
                     {$pdf->SetTextColor(200,50,50); }// si no esta justificada, se coloca en rojo
                }
                $pdf->Cell($w[2], 6, date("h:i:s a",strtotime($row['salida'])), 'LR', 0, 'C', $fill);
            }

            $pdf->SetTextColor(0);

            $pdf->Ln();
            $fill=!$fill;
        }

        // Se añaden las observaciones a la asistencia
        // Separación
       // $pdf->AddPage();
        $pdf->Ln();$pdf->Ln();
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
        $w = array(186);
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
            $observacion = "Código: ".$row['codigo'].", Desde el: ".cambiaf_a_normal($row['fecha_desde']).", Hasta el: ".cambiaf_a_normal($row['fecha_hasta']).
                ", de: ".date("h:i:s a",strtotime($row['hora_desde']))." a: ".date("h:i:s a",strtotime($row['hora_hasta'])).", Tipo: ".$row['descripcion_tipo_justificacion'].
                ", Falta: ".$row['descripcion_falta'].", Motivo: ".($row['observaciones']);
            $pdf->MultiCell($w[0], 0, $observacion, 1, 'J',$fill,'1','','',true,0,false);
            $fill=!$fill;
        }

        // Separación, se añaden los gráficos
       // $pdf->AddPage();
       $pdf->Ln();$pdf->Ln();
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(0, 0, "Indicadores ", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);

        if($this->grafico1->ImageUrl!=""){
        $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico1->ImageUrl,'','',"170","70",'','','',false));$pdf->Ln(80);
        }//fin si
        if($this->grafico2->ImageUrl!=""){
        $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico2->ImageUrl,'','',"170","70",'','','',false));$pdf->Ln(80);
        }//fin si
        if($this->grafico3->ImageUrl!=""){
        $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico3->ImageUrl,'','',"170","70",'','','',false));$pdf->Ln(80);
        }//fin si
        if($this->grafico4->ImageUrl!=""){
        $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico4->ImageUrl,'','',"170","70",'','','',false));$pdf->Ln(80);
        }//fin si
        if($this->grafico5->ImageUrl!=""){
        $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico5->ImageUrl,'','',"170","70",'','','',false));$pdf->Ln(80);
        }//fin si

        $pdf->Ln();
        
        $pdf->Output("asistencia_por_cedula_".$cedula."_del_".$desde."_al_".$hasta.".pdf",'D');
    }




}

?>