<?php
class recibo_general extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {               
            $tipo_nomina=usuario_actual('tipo_nomina');
            require('/var/www/tcpdf/tcpdf.php');
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            $sql="select nombre from organizacion.organizaciones where codigo='$cod_org'";
            $datos_organizacion=cargar_data($sql,$this);
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************

            $par=2;
            if ($tipo_nomina=="PENSIONADOS"||$tipo_nomina=="JUBILADOS")
                $sql="select distinct p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula
                from nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
                inner join organizacion.personas p on p.cedula=n.cedula
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina')
                order by cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina q son pensionados o jubilados
            else
                $sql="select distinct p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula,d.nombre_completo
                from nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
                inner join organizacion.personas p on p.cedula=n.cedula
                INNER JOIN organizacion.personas_nivel_dir ond on ond.cedula=p.cedula
                INNER JOIN organizacion.direcciones d on ond.cod_direccion=d.codigo
                where n.cod='$cod' and n.tipo_nomina='$tipo_nomina'
                order by ond.cod_direccion asc,cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
            $res_cedula=cargar_data($sql,$this);


            foreach ($res_cedula as $key=>$integrantes)
            {

                if ($par%2==0)
                    $pdf->AddPage();
             
            $pdf->SetFont('courier','B',12);
            $pdf->Cell(0,5,$datos_organizacion[0]['nombre'],1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0,5,$datos_nomina[0]['titulo'],1, 1, 'C');
            $pdf->Cell(0, 5, 'Nómina de: '.$tipo_nomina, 1, 1, 'C');
            $pdf->SetFont('courier','B',9);
            $pdf->Cell(40,5,'Cod Nómina: ',1);
            $pdf->Cell(20,5,$datos_nomina[0]['cod'],1);
            $pdf->Cell(25,5,'Desde: ',1);
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']),1);
            $pdf->Cell(25,5,'Hasta: ',1);
            $pdf->Cell(51,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),1,1);
            if (($tipo_nomina!="PENSIONADOS")&&($tipo_nomina!="JUBILADOS"))//muestra la direccion si no es jubilado o pensionado
                $pdf->Cell(0,5,"Dirección: ".$integrantes['nombre_completo'],1,1);
            $pdf->Cell(0,5,$integrantes['nombres'].' '.$integrantes['apellidos'].' C.I.'.$integrantes['cedula'],1,1,'C');
            $pdf->SetFont('courier','B',7);
            $pdf->Cell(25,5,'Codigo',1,0,'C');
            $pdf->Cell(80,5,'Descripción',1);
            $pdf->Cell(25,5,'Asignaciones',1);
            $pdf->Cell(25,5,'Deducciones',1,1);
            

            $sql2="select n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo from nomina.nomina n where n.cedula='".$integrantes['cedula']."' and n.cod='$cod' and n.tipo_nomina='$tipo_nomina' order by n.tipo asc";
            $result2=cargar_data($sql2,$this);
                foreach ($result2 as $key2=>$incidencias)
                {
                    if (($incidencias['tipo']=='CREDITO')||($incidencias['tipo']=='DEBITO'))
                    {//si es credito o debito
                        $pdf->Cell(25,3,$incidencias['cod_incidencia'],1,0,'C');
                        $pdf->Cell(80,3,$incidencias['descripcion'],1);

                       if ($incidencias['tipo']=='CREDITO')
                            $pdf->Cell(25,3,$incidencias['monto_incidencia'],1,1,'R');

                       if ($incidencias['tipo']=='DEBITO')
                            {
                             $pdf->Cell(25,3,"",1);
                             $pdf->Cell(25,3,$incidencias['monto_incidencia'],1,1,'R');
                            }
                    }//si es credito o debito

                    if ($incidencias['tipo']=='ASIGNACION')$asignacion=$incidencias['monto_incidencia'];
                    else
                        if ($incidencias['tipo']=='DEDUCCION')$deduccion=$incidencias['monto_incidencia'];
                }//foreach asignaciones y deducciones
                $pdf->Cell(105,5,'   Totales  ',1,0,'R');
                $pdf->Cell(25,5,$asignacion,1,0,'R');
                $pdf->Cell(25,5,$deduccion,1,1,'R');
                $pdf->SetFont('courier','B',10);
                $pdf->Cell(155,5,'   Total General  ',1,0,'R');

                $pdf->Cell(25,5,$asignacion-$deduccion,1,0,'R');
                $pdf->SetFont('courier','B',7);
                $pdf->Cell(225,20,"                                                                                                                        ",0,1);
   $pdf->Cell(225,6,"                                            _______________________________                                                                 ",0,1);
   $pdf->Cell(225,3,"                                                    Recibí Conforme                                                                    ",0,1);
   $pdf->Cell(225,3,"                                                    C.I.$integrantes[cedula]                                                                    ",0,1);
    $pdf->Cell(225,4,"                                                                                                                        ",0,1);
    if ($par%2==0)
        {$pdf->Cell(225,15,"-------------------------------------------------------------------------------------------------------------------------------",0,1);
         $pdf->Cell(225,4,"                                                                                                                        ",0,1);
        }
   $par=$par+1;
            }//foreach integrantes

            $pdf->Output('recibo_general.pdf','D');

            }
        }
    }
?>