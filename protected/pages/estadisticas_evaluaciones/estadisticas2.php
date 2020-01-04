<?php
include("protected/comunes/libchart/classes/libchart.php");
require('/var/www/tcpdf/tcpdf.php');
class estadisticas2 extends TPage
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
            $sql="select count(*)
                  from evaluaciones.datos_evaluados join evaluaciones.calificaciones
                  on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada
                  where(datos_evaluados.cedula='$cedula')";
            //cuenta el total de registro que contengan la cedula y que esten dentro del periodo            
            $resultado=cargar_data($sql, $sender);            
            //cuenta el total de registros que contengan la cedula, que esten dentro del periodo y con actuacion muy por debajo
            $sql="select count(*)
                  from evaluaciones.datos_evaluados join evaluaciones.calificaciones
                  on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada
                  where(datos_evaluados.cedula='$cedula' and actuacion='Muy por debajo de lo esperado')";            
            $resultado=cargar_data($sql, $sender);
            $muy_por_debajo=$resultado[0]['count(*)'];
            //cuenta el total de registros que contengan la cedula, que esten dentro del periodo y con actuacion por debajo
            $sql="select count(*)
                  from evaluaciones.datos_evaluados join evaluaciones.calificaciones
                  on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada
                  where(datos_evaluados.cedula='$cedula' and actuacion='Por debajo de lo esperado')";            
            $resultado=cargar_data($sql, $sender);
            $por_debajo=$resultado[0]['count(*)'];
            //cuenta el total de registros que contengan la cedula, que esten dentro del periodo y con actuacion dentro de lo esperado
            $sql="select count(*)
                  from evaluaciones.datos_evaluados join evaluaciones.calificaciones
                  on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada
                  where(datos_evaluados.cedula='$cedula' and actuacion='Dentro de lo esperado')";            
            $resultado=cargar_data($sql, $sender);
            $dentro_lo_esperado=$resultado[0]['count(*)'];
            //cuenta el total de registros que contengan la cedula, que esten dentro del periodo y con actuacion sobre lo esperado
            $sql="select count(*)
                  from evaluaciones.datos_evaluados join evaluaciones.calificaciones
                  on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada
                  where(datos_evaluados.cedula='$cedula' and actuacion='Sobre lo esperado')";
            $resultado=cargar_data($sql, $sender);
            $sobre_lo_esperado=$resultado[0]['count(*)'];
            //cuenta el total de registros que contengan la cedula, que esten dentro del periodo y con actuacion por sobre lo esperado
            $sql="select count(*)
                  from evaluaciones.datos_evaluados join evaluaciones.calificaciones
                  on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada
                  where(datos_evaluados.cedula='$cedula' and actuacion='Sobre lo esperado')";            
            $resultado=cargar_data($sql, $sender);
            $excepcional=$resultado[0]['count(*)'];
            //cuenta el total de registros que contengan la cedula, que esten dentro del periodo y con actuacion excepcional
            $sql="select count(*)
                  from evaluaciones.datos_evaluados join evaluaciones.calificaciones
                  on evaluaciones.datos_evaluados.codigo=evaluaciones.calificaciones.evaluacion_asociada
                  where(datos_evaluados.cedula='$cedula' and actuacion='Excepcional')";            
            $resultado=cargar_data($sql, $sender);
            //genera un aleatorio para asignarselo al nombre del .png del grafico
            $aleatorio=rand(100,99999);
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
            $grafico->render("imagenes/temporales/".$demo."_E2.png");
            $this->grafico->ImageUrl = "imagenes/temporales/".$demo."_E2.png";
        }
        public function imprimir()
        {
            $pdf->image->paling='C';
            $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Desempeño de Funcionario', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Cell($pdf->Image("/var/www/cene/".$this->grafico->ImageUrl,'','',"170","70",'','','',false, '', 'C'));
            $pdf->Output("Estadisticas.pdf",'D');
        }
    }
?>
