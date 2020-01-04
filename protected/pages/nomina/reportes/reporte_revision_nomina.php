<?php
class reporte_revision_nomina extends TPage
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
            $pdf->Cell(0, 17, 'Reporte de Revisión: Nómina de '.$tipo_nomina, 1, 1, 'C');



            $sql="select p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula, n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo,itn.tipo_nomina
            from nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula 
            inner join organizacion.personas p on p.cedula=n.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina' and itn.tipo_nomina='$tipo_nomina') and(n.cod_incidencia='7001' or n.cod_incidencia='7002' or n.cod_incidencia='7003' ) order by cod_nomina asc, n.cod_incidencia asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
            $res_cedula=cargar_data($sql,$this);
            $pdf->SetFont('courier','B',9);
            $pdf->Cell(10,10,'COD',1,0,'C');
            $pdf->Cell(82,10,'Nombres y Apellidos',1,0,'C');
            $pdf->Cell(20,10,'Cédula',1,0,'C');
            $pdf->Cell(28,10,'Asignaciones',1,0,'C');
            $pdf->Cell(28,10,'Deducciones',1,0,'C');
            $pdf->Cell(28,10,'Total Neto',1,1,'C');
            $ced='';
            $pdf->SetFont('courier','',8);
            $asignaciones=0;
            $deducciones=0;
            foreach ($res_cedula as $key=>$integrantes)
            {
                if (($ced=='')||($ced!=$integrantes[cedula]))
                {
                $pdf->Cell(10,7,$integrantes['cod_nomina'],1,0,'C');//cod nomina
                $pdf->Cell(82,7,$integrantes['nombres'].' '.$integrantes['apellidos'],1);
                $pdf->Cell(20,7,$integrantes['cedula'],1,0,'C');
                $ced=$integrantes['cedula'];
                }

                if ($integrantes[cod_incidencia]=='7003')//neto
                    {$pdf->Cell(28,7,$integrantes['monto_incidencia'],1,1,'R');}
                else
                    {
                    $pdf->Cell(28,7,$integrantes['monto_incidencia'],1,0,'R');
                        if ($integrantes['tipo']=="ASIGNACION")
                        {
                        $asignaciones=$asignaciones+$integrantes['monto_incidencia'];
                        $asignaciones=round($asignaciones,2);
                        }
                        else
                            if ($integrantes['tipo']=="DEDUCCION")
                            {
                            $deducciones=$deducciones+$integrantes['monto_incidencia'];
                            $deducciones=round($deducciones,2);
                            }
                    }
            }//foreach integrantes
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(112,7,"TOTALES",1,0,'R');
            $pdf->Cell(28,7,$asignaciones,1,0,'R');
            $pdf->Cell(28,7,$deducciones,1,0,'R');
            $pdf->Cell(28,7,$asignaciones-$deducciones,1,0,'R');
            $pdf->Output('reporte_revision_nomina.pdf','D');
            }
        }


    }
?>