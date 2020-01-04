<?php
include("protected/comunes/libchart/classes/libchart.php");
require('/var/www/tcpdf/tcpdf.php');
class estadisticas3 extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
                //llena el drop de cedula
                $sql="select distinct cedula from evaluaciones.calificaciones";
                $resultado=cargar_data($sql, $this);
                $this->drop_cedula->DataSource=$resultado;
                $this->drop_cedula->dataBind();
            }
        }
        public function buscar_datos($sender, $param)
        {
            //guarda la cedula en una variable
            $cedula=$this->drop_cedula->text;
            //busco los campos desde, hasta y total que coincidan con la cedula
            $sql="select de.desde, de.hasta, ca.total, ca.actuacion
                 from evaluaciones.datos_evaluados de join evaluaciones.calificaciones ca
                 on de.codigo=ca.evaluacion_asociada
                 where(de.cedula='$cedula') ";
            $resultado=cargar_data($sql, $sender);
            $aleatorio=rand(100,99999);
            //crea el grafico
            $grafico=new verticalbarchart();
            //crea un arreglo con los valores del grafico
            $datos = new XYDataSet();
            //añade elementos al arreglo que contiene los valores del grafico
            foreach ($resultado as $unperiodo)
            {
               $datos->addPoint(new Point($unperiodo['desde']." ".$unperiodo['hasta'], $unperiodo['total']));
            }
            $grafico->setDataSet($datos);
            //pone el titulo del grafico
            $grafico->setTitle("Grafica de las categorías de actuación");
            //genera el grafico
            $grafico->render("imagenes/temporales/".$demo."_E3.png");
            $this->grafico->ImageUrl = "imagenes/temporales/".$demo."_E3.png";
        }
        public function imprimir()
        {
            $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Desempeño de funcionario por Período', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico->ImageUrl,'','',"170","70",'','','',false, '', 'C'));
            $pdf->Output("Estadisticas.pdf",'D');
        }
    }
?>
