<?php 
class reporte_individual extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {

            }
        }



         public function buscar_cedula($sender, TCallbackEventParameter $param)
            {

                $token = $param->getToken();
                $sql="select distinct cedula from nomina.integrantes where cedula like '$token%'";
                $data = cargar_data($sql,$this);
                $this->cedula->setDataSource($data);
                $this->cedula->dataBind();
            }


        public function verificar_cedula($sender,$param)//verifica que exista la cedula en la nómina creada previamente
        {
            $cedula=$this->cedula->Text;
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            $sql="select  cedula from nomina.nomina where cod='$cod' and cedula='$cedula'";
            $datos_tipo_nomina=cargar_data($sql,$this);
            //var_dump (count($datos_tipo_nomina));
            if (count($datos_tipo_nomina)<1)//
            {
                $param->IsValid=False;
            }

        }


        public function mostrar($sender, $param)
        { if ($this->IsValid)
           {
            require('/var/www/tcpdf/tcpdf.php');
            $tipo_nomina=usuario_actual('tipo_nomina');
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";//datos nomina actual
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];//cod nomina
            $cedula=$this->cedula->Text;
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            $pdf->SetFont('courier','B',16);

            $pdf->Cell(100,5,$datos_nomina[0][titulo],1,1);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Cod Nómina: ');
            $pdf->Cell(20,5,$datos_nomina[0]['cod']);
            $pdf->Cell(25,5,'Desde: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']));
            $pdf->Cell(25,5,'Hasta: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),0,1);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'RECIBO DE PAGO', 1, 1, 'C');

            $sql="select  p.apellidos, p.nombres,i.cod as cod_nomina
            from nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
            inner join organizacion.personas p on p.cedula=n.cedula
            where i.cedula='$cedula' and n.tipo_nomina='$tipo_nomina'
            ";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
            $integrantes=cargar_data($sql,$this);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(10,5,'COD',1);
            $pdf->Cell(85,5,'Nombres y Apellidos',1);
            $pdf->Cell(20,5,'Cédula',1);
            $pdf->Cell(70,5,'Descripción',1);
            $pdf->Cell(25,5,'Asignaciones',1);
            $pdf->Cell(25,5,'Deducciones',1);
            $pdf->Cell(25,5,'Totales',1,1);

            $pdf->Cell(10,5,$integrantes[0]['cod_nomina']);//cod nomina
            $pdf->Cell(85,5,$integrantes[0]['nombres'].' '.$integrantes[0]['apellidos']);
            $pdf->Cell(20,5,$cedula,1,1);


            $sql2="select n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo from nomina.nomina n where n.cedula='$cedula' and n.cod='$cod' and n.tipo_nomina='$tipo_nomina' order by n.tipo asc";
            $result2=cargar_data($sql2,$this);
                foreach ($result2 as $key2=>$incidencias)
                {
                    if (($incidencias['tipo']=='CREDITO')||($incidencias['tipo']=='DEBITO'))
                    {//si es credito o debito
                        $pdf->Cell(115,5,'');
                        $pdf->Cell(70,5,$incidencias['descripcion'],1);

                       if ($incidencias['tipo']=='CREDITO')
                            $pdf->Cell(25,5,$incidencias['monto_incidencia'],1,1,'R');

                       if ($incidencias['tipo']=='DEBITO')
                            {
                             $pdf->Cell(25,5,"",1);
                             $pdf->Cell(25,5,$incidencias['monto_incidencia'],1,1,'R');
                            }
                    }//si es credito o debito

                    if ($incidencias['tipo']=='ASIGNACION')$asignacion=$incidencias['monto_incidencia'];
                    else
                        if ($incidencias['tipo']=='DEDUCCION')$deduccion=$incidencias['monto_incidencia'];
                }//foreach asignaciones y deducciones
                $pdf->Cell(185,5,'');
                $pdf->Cell(25,5,$asignacion,1,0,'R');
                $pdf->Cell(25,5,$deduccion,1,0,'R');
                $pdf->Cell(25,5,$asignacion-$deduccion,1,1,'R');

       
            $pdf->Output('reporte_individual.pdf','D');

           }
        }
    }
 ?>