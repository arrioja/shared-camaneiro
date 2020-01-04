<?php
class resumen_de_conceptos extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {               
            $tipo_nomina=usuario_actual('tipo_nomina');
            require('/var/www/tcpdf/tcpdf.php');
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);

            $pdf->Cell(80,5,$datos_nomina[0]['titulo'],1,1);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Cod Nómina: ');
            $pdf->Cell(20,5,$datos_nomina[0]['cod']);
            $pdf->Cell(25,5,'Desde: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']));
            $pdf->Cell(25,5,'Hasta: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),0,1);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(195, 17, 'Reporte Resumen de Conceptos', 1, 1, 'C');


            $sql="select distinct cod_incidencia, descripcion, tipo, sum(monto_incidencia) suma,count(cod_incidencia) num
            from nomina.nomina where tipo_nomina='$tipo_nomina' and (tipo='DEBITO' OR tipo='CREDITO') and cod='$cod'
            group by cod_incidencia order by tipo";

            $res_conceptos=cargar_data($sql,$this);
            $pdf->SetFont('courier','B',11);
            $pdf->Cell(15,10,'COD',1,0,'C');
            $pdf->Cell(90,10,'Descripción del Concepto',1,0,'C');
            $pdf->Cell(30,10,'Asignaciones',1,0,'C');
            $pdf->Cell(30,10,'Deducciones',1,0,'C');
            $pdf->Cell(30,10,'N° Personas',1,1,'C');
            $pdf->SetFont('courier','',10);
            $tot_asignaciones=0;
            $tot_deducciones=0;
            $num=0;
            foreach ($res_conceptos as $key=>$conceptos)
            {
                $pdf->Cell(15,7,$conceptos['cod_incidencia'],1,0,'C');//cod nomina
                $pdf->Cell(90,7,$conceptos['descripcion'],1);
                if ($conceptos['tipo']=='CREDITO')
                    {$pdf->Cell(30,7,$conceptos['suma'],1,0,'R');$pdf->Cell(30,7,'',1,0,'R');
                    $tot_asignaciones=$tot_asignaciones+$conceptos['suma'];
                    $tot_asignaciones=round($tot_asignaciones,2);
                    }
                else
                    {$pdf->Cell(30,7,'',1,0,'R');$pdf->Cell(30,7,$conceptos['suma'],1,0,'R');
                        $tot_deducciones=$tot_deducciones+$conceptos['suma'];
                        $tot_deducciones=round($tot_deducciones,2);
                    }
                $pdf->Cell(30,7,$conceptos['num'],1,1,'C');
            }//foreach integrantes
            $pdf->SetFont('courier','B',12);
            $pdf->Cell(15,10,'',0,0,'C');
            $pdf->Cell(90,10,'TOTALES',0,0,'C');
            $pdf->Cell(30,10,$tot_asignaciones,0,0,'C');
            $pdf->Cell(30,10,$tot_deducciones,0,0,'C');

            $pdf->Output('resumen_de_conceptos.pdf','D');
            }
        }
    }
?>
