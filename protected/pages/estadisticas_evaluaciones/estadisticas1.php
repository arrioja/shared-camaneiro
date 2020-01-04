<?php
include("protected/comunes/libchart/classes/libchart.php");
require('/var/www/tcpdf/tcpdf.php');
class estadisticas1 extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
                //llena el drop rangos
                $sql="select distinct concat_ws(' / ', desde, hasta) from evaluaciones.datos_evaluados";
                $resultado=cargar_data($sql,$this);
                $this->drop_rango->DataSource=$resultado;
                $this->drop_rango->dataBind();                
            }
        }
        public function buscar_rango($sender, $param)
        {
            //obtengo el rango y calculo el inicio y el fin del periodo
            $cadena=$this->drop_rango->text;            
            $desde=substr($cadena, 0, 10);            
            $hasta=substr($cadena, 13);                        
            //obtengo la cuenta del total de registros que esten entre las fechas desde/hasta
            $sql="select count(*) from evaluaciones.datos_evaluados join evaluaciones.calificaciones on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada where(desde='$desde' and hasta='$hasta')";
            $resultado=cargar_data($sql, $sender);            
            //obtengo la cuenta del total de registros que esten entre las fechas desde/hasta yque ademas tenga actuacion muy por debajo de lo esperado
            $sql="select count(*) from evaluaciones.datos_evaluados join evaluaciones.calificaciones on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada where(actuacion='Muy por debajo de lo esperado' and desde='$desde' and hasta='$hasta')";
            $resultado=cargar_data($sql, $sender);
            $muy_por_debajo=$resultado[0]['count(*)'];                        
            //obtengo la cuenta del total de registros que esten entre las fechas desde/hasta yque ademas tenga actuacion por debajo de lo esperado
            $sql="select count(*) from evaluaciones.datos_evaluados join evaluaciones.calificaciones on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada where(actuacion='Por debajo de lo esperado' and desde='$desde' and hasta='$hasta')";
            $resultado=cargar_data($sql, $sender);
            $por_debajo=$resultado[0]['count(*)'];            
            //obtengo la cuenta del total de registros que esten entre las fechas desde/hasta yque ademas tenga actuacion dentro de lo esperado
            $sql="select count(*) from evaluaciones.datos_evaluados join evaluaciones.calificaciones on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada where(actuacion='Dentro de lo esperado' and desde='$desde' and hasta='$hasta')";
            $resultado=cargar_data($sql, $sender);
            $dentro_lo_esperado=$resultado[0]['count(*)'];            
            //obtengo la cuenta del total de registros que esten entre las fechas desde/hasta yque ademas tenga actuacion sobre lo esperado
            $sql="select count(*) from evaluaciones.datos_evaluados join evaluaciones.calificaciones on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada where(actuacion='Sobre lo esperado' and desde='$desde' and hasta='$hasta')";
            $resultado=cargar_data($sql, $sender);
            $sobre_lo_esperado=$resultado[0]['count(*)'];            
            //obtengo la cuenta del total de registros que esten entre las fechas desde/hasta yque ademas tenga actuacion excepcional
            $sql="select count(*) from evaluaciones.datos_evaluados join evaluaciones.calificaciones on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada where(actuacion='Excepcional' and desde='$desde' and hasta='$hasta')";
            $resultado=cargar_data($sql, $sender);
            $excepcional=$resultado[0]['count(*)'];            
            //genera un aleatorio para asignarselo al nombre del .png del grafico
            $demo=rand(100,99999);
            //crea el grafico
            $grafico=new verticalbarchart();
            //crea un arreglo con los valores del grafico
            $datos = new XYDataSet();
            //añade elementos al arreglo que contiene los valores del grafico
            $datos->addPoint(new Point("Muy por debajo de lo esperado", $muy_por_debajo));
            $datos->addPoint(new Point("Por debajo", $por_debajo));
            $datos->addPoint(new Point("Dentro lo esperado", $dentro_lo_esperado));
            $datos->addPoint(new Point("sobre lo esperado", $sobre_lo_esperado));
            $datos->addPoint(new Point("Excepcional", $excepcional));
            $grafico->setDataSet($datos);
            //pone el titulo del grafico
            $grafico->setTitle("Grafica de las categorías de actuación");
            //genera el grafico
            $grafico->render("imagenes/temporales/".$demo."_E1.png");
            $this->grafico->ImageUrl = "imagenes/temporales/".$demo."_E1.png";
        }
        public function imprimir($sender, $param)
        {
            $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);                        
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Evaluaciones por Categorías de Desempeño', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico->ImageUrl,'','',"170","70",'','','',false, '', 'C'));
            $pdf->Output("Estadisticas.pdf",'D');
        }
    }
?>