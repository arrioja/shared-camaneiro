<?php
class reporte_general extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
            require('/var/www/tcpdf/tcpdf.php');
            $tipo_nomina=usuario_actual('tipo_nomina');
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

            $pdf->Cell(100,5,$datos_nomina[0]['titulo'],1,1);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Cod Nómina: ');
            $pdf->Cell(20,5,$datos_nomina[0]['cod']);
            $pdf->Cell(25,5,'Desde: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']));
            $pdf->Cell(25,5,'Hasta: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),0,1);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Nómina de: '.$tipo_nomina, 1, 1, 'C');


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
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina')
                order by ond.cod_direccion asc,cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
            $res_cedula=cargar_data($sql,$this);

            //$pdf->Cell(90,5,$sql,1,1);
            $dir="";//variable para verificar el cambio de direccion

            foreach ($res_cedula as $key=>$integrantes)
            {
            $pdf->SetFont('courier','B',7);
            $pdf->Cell(10,5,'COD',1);
            $pdf->Cell(60,5,'Nombres y Apellidos',1);
            $pdf->Cell(15,5,'Cédula',1);
            $pdf->Cell(50,5,'Descripción',1);
            $pdf->Cell(20,5,'Asignaciones',1);
            $pdf->Cell(20,5,'Deducciones',1);
            $pdf->Cell(21,5,'Totales',1,1);
        if (($tipo_nomina!="PENSIONADOS")&&($tipo_nomina!="JUBILADOS"))
            if ($dir!=$integrantes['nombre_completo'])//si cambia la direccion
                $pdf->Cell(85,5,$integrantes['nombre_completo'],1,1);
            $pdf->SetFont('courier','',7);
            $pdf->Cell(10,5,$integrantes['cod_nomina'],1);//cod nomina
            $pdf->Cell(60,5,$integrantes['nombres'].' '.$integrantes['apellidos'],1);
            $pdf->Cell(15,5,$integrantes['cedula'],1,1);


            $sql2="select n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo from nomina.nomina n where n.cedula='".$integrantes['cedula']."' and n.cod='$cod' and n.tipo_nomina='$tipo_nomina' order by n.tipo asc";
            $result2=cargar_data($sql2,$this);
                foreach ($result2 as $key2=>$incidencias)
                {
                    if (($incidencias['tipo']=='CREDITO')||($incidencias['tipo']=='DEBITO'))
                    {//si es credito o debito
                        $pdf->Cell(85,5,'');
                        $pdf->Cell(50,5,$incidencias['descripcion'],1);

                       if ($incidencias['tipo']=='CREDITO')
                            $pdf->Cell(20,5,$incidencias['monto_incidencia'],1,1,'R');

                       if ($incidencias['tipo']=='DEBITO')
                            {
                             $pdf->Cell(20,5,"",1);
                             $pdf->Cell(20,5,$incidencias['monto_incidencia'],1,1,'R');
                            }
                    }//si es credito o debito

                    if ($incidencias['tipo']=='ASIGNACION')$asignacion=$incidencias['monto_incidencia'];
                    else
                        if ($incidencias['tipo']=='DEDUCCION')$deduccion=$incidencias['monto_incidencia'];
                }//foreach asignaciones y deducciones
                $pdf->Cell(135,5,'');
                $pdf->SetFont('courier','B',7);
                $pdf->Cell(20,5,$asignacion,1,0,'R');
                $pdf->Cell(20,5,$deduccion,1,0,'R');
                $pdf->Cell(21,5,$asignacion-$deduccion,1,1,'R');

           /* <td></td><td align="right" class="style5"><?php echo $asignacion;?></td>
            <td align="right" class="style2"><?php echo $deduccion;?></td><td align="right" class="style5"><?php echo $asignacion-$deduccion;?></td>
*/
   $pdf->Cell(225,5,"-----------------------------------------------------------------------------------------------------------------------------------",0,1);
   //$pdf->Cell(225,5,"-------------------------------------------------------------------------------------------------------------------------------",0,1);

            }//foreach integrantes

            $pdf->Output('reporte_general.pdf','D');

            }
        }
    }
?>