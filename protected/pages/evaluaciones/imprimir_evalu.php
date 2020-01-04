<?php
class imprimir_evalu extends TPage
    {
        public function onLoad($param)
        {                                            
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
                //llena el drop de cedula
                $sql="select * from evaluaciones.datos_evaluados";
                $resultado=cargar_data($sql,$this);
                $this->drop_cedula->DataSource=$resultado;
                $this->drop_cedula->dataBind();
            }
        }
        public function buscar_evaluacion($sender, $param)
        {
            $cedula=$this->drop_cedula->text;
            $sql="select * from evaluaciones.datos_evaluados where(cedula='$cedula') order by codigo";
            $resultado=cargar_data($sql, $sender);
            $this->drop_evaluacion->DataSource=$resultado;
            $this->drop_evaluacion->dataBind();
        }
        public function buscar_datos($sender, $param)
        {            
            $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
            require('/var/www/tcpdf/tcpdf.php');//ruta de la clase tcpdf
            $evaluacion=$this->drop_evaluacion->text;
            //este join junta las tablas de la db evaluaciones
            $sql="select distinct * from evaluaciones.datos_evaluados join evaluaciones.detalle_evaluacion on codigo=codigo_evaluacion 
                  join evaluaciones.evaluacioncontinua on codigo_evaluacion=evaluacion_asociada 
                  join evaluaciones.calculo_evaluacion on evaluacion_asociada=cod_evaluacion 
                  join evaluaciones.calc_comp on calculo_evaluacion.cod_evaluacion=calc_comp.cod_evaluacion 
                  join evaluaciones.calificaciones on calc_comp.cod_evaluacion=calificaciones.evaluacion_asociada
                  where codigo='$evaluacion'";
            $resultado=cargar_data($sql, $sender);
            //guarda la cedula del evaluado y busca su codigo de nomina en la db nomina
            $evaluado=$resultado[0]['evaluado'];
            $sql="select * from nomina.integrantes where(cedula='$evaluado')";
            $resul_cod_nomina_evaluado=cargar_data($sql, $sender);
            //con la cedula del evaluado se buscan otros datos en la db organizacion
            $sql="select nombres, apellidos, denominacion, cod_direccion, nombre_abreviado
            from organizacion.personas join organizacion.personas_cargo
            on organizacion.personas.cedula=organizacion.personas_cargo.cedula
            join organizacion.personas_nivel_dir
            on organizacion.personas.cedula=organizacion.personas_nivel_dir.cedula
            join organizacion.direcciones
            on organizacion.personas_nivel_dir.cod_direccion=organizacion.direcciones.codigo
            where(personas.cedula='$evaluado')";
            $resul_datos_evaluado=cargar_data($sql, $sender);
            //busca el grado y el codigo de clase dependiendo del cargo del evaluado
            $cargo_evaluado=$resultado[0]['cargo'];
            $sql="select codigo, grado from organizacion.cargos where(denominacion='$cargo_evaluado')";
            $resul_cod_car_evaluado=cargar_data($sql, $sender);
            //******************************************************************
            //guarda la cedula del evaluador y busca su codigo de nomina en la db nomina
            $evaluador=$resultado[0]['evaluador'];            
            $sql="select * from nomina.integrantes where(cedula='$evaluador')";
            $resul_cod_nomina_evaluador=cargar_data($sql, $sender);
            //con la cedula del evaluador se buscan otros datos en la db organizacion
            $sql="select nombres, apellidos, denominacion, cod_direccion, nombre_abreviado
            from organizacion.personas join organizacion.personas_cargo
            on organizacion.personas.cedula=organizacion.personas_cargo.cedula
            join organizacion.personas_nivel_dir
            on organizacion.personas.cedula=organizacion.personas_nivel_dir.cedula
            join organizacion.direcciones
            on organizacion.personas_nivel_dir.cod_direccion=organizacion.direcciones.codigo
            where(personas.cedula='$evaluador')";
            $resul_datos_evaluador=cargar_data($sql, $sender);
            //busca el grado y el codigo de clase dependiendo del cargo del evaluador
            $cargo_evaluador=$resul_datos_evaluador[0]['denominacion'];
            $sql="select codigo, grado from organizacion.cargos where(denominacion='$cargo_evaluador')";
            $resul_cod_car_evaluador=cargar_data($sql, $sender);
            //******************************************************************
            //busca el codigo de nomina del supervisor en la db nomina
            $supervisor=$resultado[0]['supervisor'];            
            $sql="select * from nomina.integrantes where(cedula='$supervisor')";
            $resul_cod_nomina_supervisor=cargar_data($sql, $sender);
            //con la cedula del evaluado se buscan otros datos en la db organizacion
            $sql="select nombres, apellidos, denominacion, cod_direccion, nombre_abreviado
            from organizacion.personas join organizacion.personas_cargo
            on organizacion.personas.cedula=organizacion.personas_cargo.cedula
            join organizacion.personas_nivel_dir
            on organizacion.personas.cedula=organizacion.personas_nivel_dir.cedula
            join organizacion.direcciones
            on organizacion.personas_nivel_dir.cod_direccion=organizacion.direcciones.codigo
            where(personas.cedula='$supervisor')";
            $resul_datos_supervisor=cargar_data($sql, $sender);
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();            
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Evaluación de Desempeño', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Desde: '.$resultado[0]['desde']);
            $pdf->Cell(0,5,'Hasta: '.$resultado[0]['hasta'], 0, 1);
            $pdf->Ln(3);
            //datos evaluado****************************************************
            $pdf->Cell(0, 0,'Evaluado', 1, 1, 'C', 1);            
            $pdf->Cell(0, 5,'Apellidos/Nombres: '.$resultado[0]['apellidos'].'/'.$resultado[0]['nombres'], 0, 1);
            $pdf->Cell(40, 5,'Cedula: '.$resultado[0]['cedula'], 0, 1);
            $pdf->Cell(0, 5,'Código Nomina: '.$resul_cod_nomina_evaluado[0]['cod'], 0, 1);
            $pdf->Cell(180, 5,'Titulo de Cargo: '.$resultado[0]['cargo'], 0, 1);
            $pdf->Cell(35, 5,'Grado: '.$resul_cod_car_evaluado[0]['grado'], 0, 1);
            $pdf->Cell(0, 5,'Código de Clase: '.$resul_cod_car_evaluado[0]['codigo'], 0, 1);
            $pdf->Cell(0, 5,'Ubicacion Administrativa: '.$resul_datos_evaluado[0]['nombre_abreviado'], 0, 1);
            $pdf->Ln(3);
            //datos evaluador***************************************************
            $pdf->Cell(0, 0,'Evaluador', 1, 1, 'C', 1);
            $pdf->Cell(0, 5,'apellidos/Nombres: '.$resul_datos_evaluador[0]['apellidos'].'/'.$resul_datos_evaluador[0]['nombres'], 0, 1);
            $pdf->Cell(40, 5,'Cedula: '.$evaluador, 0, 1);
            $pdf->Cell(0, 5,'Código Nomina: '.$resul_cod_nomina_evaluador[0]['cod'], 0, 1);
            $pdf->Cell(180, 5,'Titulo de Cargo: '.$resul_datos_evaluador[0]['denominacion'], 0, 1);
            $pdf->Cell(35, 5,'Grado: '.$resul_cod_car_evaluador[0]['grado'], 0, 1);
            $pdf->Cell(0, 5,'Código de Clase: '.$resul_cod_car_evaluador[0]['codigo'], 0, 1);
            $pdf->Cell(0, 5,'Ubicacion Administrativa: '.$resul_datos_evaluador[0]['nombre_abreviado'], 0, 1);
            $pdf->Ln(3);
            //datos supervisor evaluador****************************************
            $pdf->Cell(0, 0,'Supervisor del Evaluador', 1, 1, 'C', 1);
            $pdf->Cell(0, 5,'apellidos/Nombres: '.$resul_datos_supervisor[0]['apellidos'].'/'.$resul_datos_supervisor[0]['nombres'], 0, 1);
            $pdf->Cell(40, 5,'Cedula: '.$supervisor, 0, 1);
            $pdf->Cell(0, 5,'Titulo de Cargo: '.$resul_datos_supervisor[0]['denominacion']);
            //pagina 2**********************************************************
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección B', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'Objetivo de Desempeño Individual (O.D.I.): ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Se refiere a los logros que cada funcionario debe alcanzar durante un período específico. El objetivo de desempeño', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'individual (ODI) debe guardar relación con el objetivo funcional de la unidad entendiéndose por objetivo funcional', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'la razón de ser de la unidad dentro del organismo.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '- En esta columna se indicarán los (ODI) fijados con previo acuerdo entre el supervisor y el supervisado', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '- Los objetivos deben ser medibles, observables y verificables', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '- Al definir los objetivos se debe tomar en cuenta el que y el cuando', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '- No deben fijarse para cada empleado más de cinco (5) objetivos, ni menos de tres (3)', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '- El supervisor debe fijar los lineamientos generales para alcanzar los objetivos', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Peso: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Es la ponderación del (ODI)expresada en puntos', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '- En esta columna se debe indicar el peso para cada ODI, en función de su importancia con el objetivo funcional', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '- El peso total es cincuenta (50) puntos, el cual debe distribuirse entre los objetivos fijados', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '- El peso asignado a un objetivo no debe ser inferior a cinco (5) puntos ni superior a veinticinco (25)', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Rangos: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Es la expresión cuantitativa del cumplimiento de los ODI alcanzados por el funcionario', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '- En este cuadro debe seleccionar y marcar con una equis (x) el rango que mejor describa el comportamiento del evaluado', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->ln(3);
            $pdf->SetFont('courier','B',10);            
            $pdf->Cell(15, 0, 'RANGOS', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(65, 0, 'DESCRIPCION', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(170, 0, 'DEFINICION', 1, 1, 'l', 1, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(15, 0, '1', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Muy por debajo de lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'No cumple con los objetivos', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '2', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Por debajo de lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'Cumple parcialmente el logro de los objetivos propuestos', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '3', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Dentro de lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'Cumple con todos los objetivos asignados', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '4', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Sobre lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'Cumple con los objetivos asignados y en ocasiones obtienen logros adicionales', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '5', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Excepcional', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'Constantemente obtiene logros adicionales', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Peso X Rango: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'En esta columna proceda a colocar el resultado de multiplicar el peso fijado a cada ODI por el rango', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'obtenido por el funcionario', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Total: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Coloque en la casilla correspondiente la sumatoria de los puntajes de la columna peso x rango', 0, 1, 'l', 0, 0, 0, 0);
            //pagina 3**********************************************************
             $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección B', 1, 1, 'C', 0, 0, 0, 0);
            $pdf->SetFont('courier','B',10);            
            $pdf->Cell(0, 0, 'ESTABLECIMIENTO Y EVALUACIÓN DE OBJETIVOS DE DESEMPEÑO INDIVIDUAL', 0, 1, 'C', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'En esta sección se establecen los objetivos de desempeño individual que el funcionario debe cumplir en el período a evaluar', 0, 1, 'C', 0, 0, 0, 0);
            $pdf->Ln(3);                        
            $pdf->MultiCell(195, 0, 'Objetivo deDesempeño Individual', 1, 'L', 1, 0, '' , '', true);
            $pdf->MultiCell(15, 0, 'Peso', 1, 'L', 1, 0, '' , '', true);
            $pdf->MultiCell(20, 0, 'Rango', 1, 'L', 1, 0, '' , '', true);
            $pdf->MultiCell(30, 0, 'Peso X Rango', 1, 'L', 1, 1, '' , '', true);
            //datos odi1
            $o1=$resultado[0]['cod_odi1'];
            $sql="select * from evaluaciones.odi where(codigo='$o1')";
            $resul=cargar_data($sql,$sender);
            $pdf->MultiCell(195, 0, $resul[0]['descripcion'], 1, 'L', 0, 0, '' , '', true);
            $altoodi1=$pdf->getLastH();
            $pdf->MultiCell(15, 0, $resultado[0]['peso_odi1'], 1, 'C', 0, 0, '' , '', true);
            $pdf->MultiCell(20, 0, $resultado[0]['desem_odi1'], 1, 'C', 0, 0, '' , '', true);
            $pdf->MultiCell(30, 0, $resultado[0]['pesoxrango_odi1'], 1, 'C', 0, 1, '' , '', true);            
            //datos odi2
            $o2=$resultado[0]['cod_odi2'];
            $sql="select * from evaluaciones.odi where(codigo='$o2')";
            $resul=cargar_data($sql,$sender);
            $pdf->MultiCell(195, 0, $resul[0]['descripcion'], 1, 'L', 0, 0, '', $altoodi1+$pdf->getY()-5, false);
            $altoodi2=$pdf->getLastH();
            $pdf->MultiCell(15, 0, $resultado[0]['peso_odi2'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(20, 0, $resultado[0]['desem_odi2'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(30, 0, $resultado[0]['pesoxrango_odi2'], 1, 'C', 0, 1, '' ,'', true);            
            //datos odi3
            $o3=$resultado[0]['cod_odi3'];
            $sql="select * from evaluaciones.odi where(codigo='$o3')";
            $resul=cargar_data($sql,$sender);
            $pdf->MultiCell(195, 0, $resul[0]['descripcion'], 1, 'L', 0, 0, '', $altoodi2+$pdf->getY()-5, false);
            $altoodi3=$pdf->getLastH();
            $pdf->MultiCell(15, 0, $resultado[0]['peso_odi3'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(20, 0, $resultado[0]['desem_odi3'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(30, 0, $resultado[0]['pesoxrango_odi3'], 1, 'C', 0, 1, '' ,'', true);            
            //datos doi4
            $o4=$resultado[0]['cod_odi4'];
            $sql="select * from evaluaciones.odi where(codigo='$o4')";
            $resul=cargar_data($sql,$sender);
            $pdf->MultiCell(195, 0, $resul[0]['descripcion'], 1, 'L', 0, 0, '', $altoodi3+$pdf->getY()-5, false);
            $altoodi4=$pdf->getLastH();
            $pdf->MultiCell(15, 0, $resultado[0]['peso_odi4'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(20, 0, $resultado[0]['desem_odi4'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(30, 0, $resultado[0]['pesoxrango_odi4'], 1, 'C', 0, 1, '' ,'', true);            
            //datos odi5
            $o5=$resultado[0]['cod_odi5'];
            $sql="select * from evaluaciones.odi where(codigo='$o5')";
            $resul=cargar_data($sql,$sender);
            $pdf->MultiCell(195, 0, $resul[0]['descripcion']."\n", 1, 'L', 0, 0, '', $altoodi4+$pdf->getY()-5, true);
            $altoodi5=$pdf->getLastH();
            $pdf->MultiCell(15, 0, $resultado[0]['peso_odi5'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(20, 0, $resultado[0]['desem_odi5'], 1, 'C', 0, 0, '' ,'', true);
            $pdf->MultiCell(30, 0, $resultado[0]['pesoxrango_odi5'], 1, 'C', 0, 1, '' ,'', true);           
            //totales
            //$pdf->Cell(200, 0, 'Total', 1, 0, 'r', 0, 0, 0, 0);
            $totalpeso=$resultado[0]['peso_odi1']+$resultado[0]['peso_odi2']+$resultado[0]['peso_odi3']+$resultado[0]['peso_odi4']+$resultado[0]['peso_odi5'];
            //$pdf->Cell(10, 0, $totalpeso, 1, 0, 'l', 0, 0, 0, 0);
            //$pdf->Cell(20, 0, 'Total', 1, 0, 'r', 0, 0, 0, 0);
            $totalpesoxrango=$resultado[0][pesoxrango_odi1]+$resultado[0][pesoxrango_odi2]+$resultado[0][pesoxrango_odi3]+$resultado[0][pesoxrango_odi4]+$resultado[0][pesoxrango_odi5];
            //$pdf->Cell(30, 0, $totalpesoxrango, 1, 1, 'l', 0, 0, 0, 0);
            $pdf->MultiCell(195, 0, 'Total', 1, 'L', 0, 0, '' , $altoodi5+$pdf->getY()-5, true);
            $pdf->MultiCell(15, 0, $totalpeso, 1, 'C', 0, 0, '' , '', true);
            $pdf->MultiCell(20, 0, 'Total', 1, 'C', 0, 0, '' , '', true);
            $pdf->MultiCell(30, 0, $totalpesoxrango, 1, 'C', 0, 1, '' , '', true);
            $pdf->Ln(3);
            $pdf->Cell(25, 0, 'Conforme: ', 1, 0, 'r', 1, 0, 0, 0);
            $pdf->Cell(0, 0, ' ', 1, 1, 'r', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(25, 0, 'Evaluado: ', 1, 0, 'r', 1, 0, 0, 0);
            $pdf->Cell(0, 0, ' ', 1, 1, 'r', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(25, 0, 'Supervisor: ', 1, 0, 'r', 1, 0, 0, 0);
            $pdf->Cell(0, 0, ' ', 1, 1, 'r', 0, 0, 0, 0);
            $pdf->Ln(3);
            //pagina 4**********************************************************
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección C', 1, 1, 'C', 0, 0, 0, 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'EVALUACIÓN DE LAS COMPETENCIAS', 0, 1, 'C', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'En esta sección se ponderan las competencias con la relación al cargo y se evalúan de acuerdo al grado en que estén', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'presentes en el evaluado.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(100, 0, 'Objetivo de Desempeño Individual', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(20, 0, 'Peso', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(20, 0, 'Rango', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(30, 0, 'Peso X Rango', 1, 1, 'l', 1, 0, 0, 0);
            $ced_evaluado=$resultado[0]['evaluado'];
            $sql="select * from evaluaciones.tipoempleado join evaluaciones.competencias on tipo=nivel where(cedula='$ced_evaluado')";
            $resul=cargar_data($sql, $sender);
            //desempeño1
            $pdf->Cell(100, 0, $resul[0]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[0]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[0]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[0]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //desempeño2
            $pdf->Cell(100, 0, $resul[1]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[1]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[1]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[1]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //desempeño3
            $pdf->Cell(100, 0, $resul[2]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[2]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[2]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[2]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //desempeño4
            $pdf->Cell(100, 0, $resul[3]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[3]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[3]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[3]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //desempeño5
            $pdf->Cell(100, 0, $resul[4]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[4]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[4]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[4]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //desempeño6
            $pdf->Cell(100, 0, $resul[5]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[5]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[5]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[5]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //desempeño7
            $pdf->Cell(100, 0, $resul[6]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[6]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[6]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[6]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //desempeño8
            $pdf->Cell(100, 0, $resul[7]['nombre_corto'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[7]['peso_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $resultado[7]['desem_comp'], 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(30, 0, $resultado[7]['pesoxrango_comp'], 1, 1, 'l', 0, 0, 0, 0);
            //totales
            $pdf->Cell(100, 0, 'Total', 1, 0, 'l', 0, 0, 0, 0);
            $total_peso=$resultado[0]['peso_comp']+$resultado[1]['peso_comp']+$resultado[2]['peso_comp']+$resultado[3]['peso_comp']+$resultado[4]['peso_comp']+$resultado[5]['peso_comp']+$resultado[6]['peso_comp']+$resultado[7]['peso_comp'];
            $pdf->Cell(20, 0, $total_peso, 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, 'Total', 1, 0, 'l', 0, 0, 0, 0);
            $total_pesoxrango=$resultado[0]['pesoxrango_comp']+$resultado[1]['pesoxrango_comp']+$resultado[2]['pesoxrango_comp']+$resultado[3]['pesoxrango_comp']+$resultado[4]['pesoxrango_comp']+$resultado[5]['pesoxrango_comp']+$resultado[6]['pesoxrango_comp']+$resultado[7]['pesoxrango_comp'];
            $pdf->Cell(30, 0, $total_pesoxrango, 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(25, 0, 'Conforme: ', 1, 0, 'r', 1, 0, 0, 0);
            $pdf->Cell(0, 0, ' ', 1, 1, 'r', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(25, 0, 'Evaluado: ', 1, 0, 'r', 1, 0, 0, 0);
            $pdf->Cell(0, 0, ' ', 1, 1, 'r', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(25, 0, 'Supervisor: ', 1, 0, 'r', 1, 0, 0, 0);
            $pdf->Cell(0, 0, ' ', 1, 1, 'r', 0, 0, 0, 0);
            $pdf->Ln(3);
            //pagina 5**********************************************************
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección C', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'Competencias: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Son los factores de desempeño que facilitan al evaluado la consecución de los ODI', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Peso: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Es la ponderación de la competencia expresada en puntos', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '-  En esta columna el supervisor debe indicar el peso de cada competencia, en función al cargo que ocupa el evaluado', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '-  El peso total es de cincuenta (50) puntos', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '-  El peso de las tres (3) primeras competencias ha sido previamente establecido, siendo su sumatoria de veinte (20) puntos', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '-  Los treinta (30) puntos restantes deben ser distribuidos entre las demás competencias', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '-  El peso que se le asigne a cada competencias puede ser igual o inferior a siete (7) puntos, pero nunca mayor', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '-  Se deben ponderar todas las competencias', 0, 1, 'l', 0, 0, 0, 0);            
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Rangos: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Es la expresión cuantitativa de la presencia de la competencia en el desempeño del evaluado', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '-  En este cuadro debe seleccionar y marcar con una equis (x) el rango que mejor describa la presencia de la competencia', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '   en el comportamiento del evaluado', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(15, 0, 'RANGOS', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(65, 0, 'DESCRIPCION', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(170, 0, 'DEFINICION DE LOS RANGOS DE ACTUACIÓN', 1, 1, 'l', 1, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(15, 0, '1', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Muy por debajo de lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'No esta presente en el desempeño del funcionario', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '2', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Por debajo de lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'Esta presente parcialmente u ocasionalmente', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '3', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Dentro de lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'Esta presente', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '4', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Sobre lo esperado', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'esta presente, en ocasiones por encima', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(15, 0, '5', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(65, 0, 'Excepcional', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(170, 0, 'Esta presente consistentemente por encima', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Peso X Rango: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'En esta columna proceda a colocar el resultado de multiplicar el peso fijado a la competencia por el rango', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'obtenido por el funcionario', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Total: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Coloque en la casilla correspondiente la sumatoria de los puntajes de la columna peso x rango', 0, 1, 'l', 0, 0, 0, 0);                        
            //pagina 6**********************************************************
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección D', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'Calificación final: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Es la sumatoria de los puntajes obtenidos en la sección “B” y “C”.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Total Sección “B”: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Coloque el total del puntaje obtenido en dicha sección.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Total Sección “C”: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Coloque el total del puntaje obtenido en dicha sección.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Puntaje final (B+C): ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Coloque la sumatoria del total de la sección “B” más el total de la sección “C”', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Rango de actuación: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Es la expresión cualitativa del desempeño del funcionario.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '-  Ubique el puntaje final en la escala cuantitativa, para obtener el rango de actuación', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '-  Coloque el resultado en el espacio de rango de actuación.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(45, 0, 'Escala cuantitativa', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(90, 0, 'Rango de actuación', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(0, 0, 'Definición de los rangos de actuación', 1, 1, 'l', 1, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(45, 0, '100-179', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, 'Actuación muy por debajo de lo esperado.', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'Desempeño deficiente, no cumple con los objetivos', 'L,T,R', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'asignados.', 'L,R,B', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '180-259', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, 'Actuación por debajo de lo esperado.', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'Desempeño que lo lleva a cumplir parcialmente el logro', 'L,T,R', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'de los objetivos propuestos.', 'L,R,B', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '260-339', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, 'Actuación por dentro de lo esperado.', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'Desempeño satisfactorio, cumple con todos los objetivos', 'L,T,R', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'asignados.', 'L,R,B', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '340-419', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, 'Actuación sobre lo esperado.', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'Desempeño por encima de lo esperado y contribuye al logro', 'L,T,R', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '', 'L,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, '', 'L,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'de los objetivos propuestos, en ocasiones obtiene logros', 'L,R', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'adicionales.', 'L,R,B', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '420-500', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, 'Desempeño excepcional.', 'L,T,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'Desempeño consistentemente extraordinario y contribuye a', 'L,T,R', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '', 'L,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, '', 'L,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'logros adicionales no implícitos en sus objetivos de', 'L,R', 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(45, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(90, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'desempeño individual.', 'L,R,B', 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección E', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'Comentarios del supervisor: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Indique cualquier observación que considere pertinente mencionar sobre los resultados de la evaluación del funcionario,', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'así como aquellas actividades que acuerden el supervisor y el supervisado, a fin de mantener e incrementar ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'las fortalezas demostradas o para corregir las áreas débiles encontradas.', 0, 1, 'l', 0, 0, 0, 0);
            //pagina 7**********************************************************
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección D', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'En esta sección se obtendrá el rango de actuación del Evaluado.', 0, 1, 'c', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(70, 0, 'Calificación final', 1, 1, 'l', 1, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(50, 0, 'Total sección B', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $totalpesoxrango, 1, 1, 'l', 0, 1, 0, 0);
            $pdf->Cell(50, 0, 'Total sección C', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $total_pesoxrango, 1, 1, 'l', 0, 1, 0, 0);
            $puntaje_final=$totalpesoxrango+$total_pesoxrango;
            $pdf->Cell(50, 0, 'Puntaje final (B+C)', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(20, 0, $puntaje_final, 1, 1, 'l', 0, 1, 0, 0);
            if($puntaje_final<=179)
            {
                $actuacion='Muy por debajo de lo esperado.';
            }
            if(($puntaje_final>=180)and($puntaje_final<=259))
            {
                $actuacion='Por debajo de lo esperado.';
            }
            if(($puntaje_final>=260)and($puntaje_final<=339))
            {
                $actuacion='Dentro de lo esperado.';
            }
            if(($puntaje_final>=340)and($puntaje_final<=419))
            {
                $actuacion='Sobre lo esperado.';
            }
            if(($puntaje_final>=420)and($puntaje_final<=500))
            {
                $actuacion='Desempeño excepcional.';
            }
            $pdf->Ln(3);
            $pdf->Cell(50, 0, 'Rango de actuación', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(150, 0, $actuacion, 1, 1, 'l', 0, 1, 0, 0);
            $pdf->Ln(3);
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Sección E', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'En esta sección, exprese comentarios con respecto a los resultados de la evaluación del funcionario,', 0, 1, 'c', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'así como las acciones a seguir para mejora el desempeño.', 0, 1, 'c', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Comentarios del supervisor', 1, 1, 'c', 1, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            //pagina 8**********************************************************
            $pdf->AddPage();
            $pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Firmas', 1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'Supervisor inmediato: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Firma del Evaluador.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Jefe inmediato del supervisor: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Firma del Jefe Inmediato del Evaluador.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Fecha: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'Indique día, mes y año en que se realizó la evaluación.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Firmas', 1, 1, 'l', 1, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(50, 0, 'Supervisor inmediato', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(70, 0, '', 1, 0, 'l', 0, 1, 0, 0);
            $pdf->Cell(70, 0, 'Jefe inmediato del supervisor', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 1, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Al ser llenado por el evaluado: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'El evaluado indicará en la casilla correspondiente su acuerdo o no acerca de los resultados de su evaluación.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Comentarios: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'El evaluado podrá expresar cualquier observación adicional que considere pertinente sobre su evaluación.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40, 0, 'Esta de acuerdo', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(10, 0, 'Si', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(10, 0, '', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(10, 0, 'No', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(10, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, 'Comentarios', 1, 1, 'c', 1, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 0, 'Firma del evaluado: ', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->SetFont('courier','',10);
            $pdf->Cell(0, 0, 'El evaluado deberá formar en señal de haber sido notificado de los resultados de su evaluación sin que su firma', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Cell(0, 0, 'implique aceptación o no de los resultados.', 0, 1, 'l', 0, 0, 0, 0);
            $pdf->Ln(3);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40, 0, '', 'L,T,R', 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(80, 0, '', 'T', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(50, 0, 'fecha de evaluación', 1, 1, 'l', 1, 0, 0, 0);
            $pdf->Cell(40, 0, 'Firma del evaluado', 'L,R', 0, 0, 1, 0, 0, 0);
            $pdf->Cell(80, 0, '', 'L,R', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(17, 0, 'dia', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(17, 0, 'mes', 1, 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(16, 0, 'año', 1, 1, 'l', 1, 0, 0, 0);
            $pdf->Cell(40, 0, '', 'L,R,B', 0, 'l', 1, 0, 0, 0);
            $pdf->Cell(80, 0, '', 'L,R,B', 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(17, 0, '', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(17, 0, '', 1, 0, 'l', 0, 0, 0, 0);
            $pdf->Cell(16, 0, '', 1, 1, 'l', 0, 0, 0, 0);
            $pdf->Output("Evaluacion.pdf",'D');
        }
    }
?>