<?php
require('/var/www/tcpdf/tcpdf.php');
class generar_constancia extends TPage
{        
    function onload($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $sql="  SELECT cedula,concat(nombres,' ',apellidos) as nombre
                    FROM organizacion.personas order by nombre asc";            
            $resultado=cargar_data($sql, $this);
            $this->ddl1->DataSource=$resultado;
            $this->ddl1->dataBind();
            $datos_seleccionados=$this->ddl1->SelectedValue;
        }
    }
    public function buscando($sender,$param)
    {
        
        $cedula=$this->ddl1->SelectedValue;
        $nombreapellido=$this->ddl1->Text;
        $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula') ";
        $resultado=cargar_data($sql, $this);
        $sql2="SELECT fecha_ini as fecha_ingreso FROM organizacion.personas_cargos where(cedula='$cedula') order by fecha_ini ASC LIMIT 1";
        $resultado2=cargar_data($sql2, $this);
        $sql3="SELECT denominacion FROM organizacion.personas_cargos where(cedula='$cedula') order by fecha_ini DESC LIMIT 1";
        $resultado3=cargar_data($sql3, $this);
        $sql4="SELECT monto_incidencia FROM nomina.nomina where(cedula='$cedula' and cod_incidencia='1000')order by cod desc";
        $resultado4=cargar_data($sql4, $this);
        $sql5="SELECT monto_incidencia FROM nomina.nomina where(cedula='$cedula' and cod_incidencia='7001')order by cod desc";
        $resultado5=cargar_data($sql5, $this);
        $sql6="SELECT tipo_nomina FROM nomina.nomina where(cedula='$cedula') group by tipo_nomina";
        $resultado6=cargar_data($sql6, $this);
        $this->t1->text=$resultado[0]['nombres'];
        $this->t2->text=$resultado[0]['apellidos'];
        $this->t3->text=cambiaf_a_normal($resultado2[0]['fecha_ingreso']);
        $this->t4->text=$resultado3[0]['denominacion'];
        $this->t5->text=$resultado4[0]['monto_incidencia']*2;
        $this->t6->text=$resultado5[0]['monto_incidencia']*2;
        $this->t7->text=$resultado6[0]['tipo_nomina'];
        if($resultado6[0]['tipo_nomina']=='EMPLEADOS')
        {
            $this->t9->enabled=false;
            $this->t10->enabled=false;
            $this->t11->enabled=false;
            $this->t12->enabled=false;
            $this->t13->enabled=false;
            $this->t14->enabled=false;
            $this->t15->enabled=false;
        }
        if($resultado6[0]['tipo_nomina']=='JUBILADOS')
        {
            $this->t9->enabled=true;
            $this->t10->enabled=true;
            $this->t11->enabled=true;
            $this->t12->enabled=true;
            $this->t13->enabled=true;
            $this->t14->enabled=false;
            $this->t15->enabled=false;
        }
        if($resultado6[0]['tipo_nomina']=='PENSIONADOS')
        {
            $this->t9->enabled=false;
            $this->t10->enabled=false;
            $this->t11->enabled=false;
            $this->t12->enabled=false;
            $this->t13->enabled=false;
            $this->t14->enabled=true;
            $this->t15->enabled=true;
        }
        if($resultado6[0]['tipo_nomina']=='DIRECTORES')
        {
            $this->t9->enabled=false;
            $this->t10->enabled=false;
            $this->t11->enabled=false;
            $this->t12->enabled=false;
            $this->t13->enabled=false;
            $this->t14->enabled=false;
            $this->t15->enabled=false;
        }
    }
    public function generar($sender,$param)
    {                                
        if($this->ddl2->selectedvalue=="basica")
        {
            $fecha=date("d/m/Y");
            list($dia, $mes, $ano) = split('/', $fecha);
            $cargo=$this->t4->text;
            $fecha_ingreso=$this->t3->text;
            list($dia_ingreso, $mes_ingreso, $ano_ingreso) = split('/', $fecha_ingreso);
            if($mes_ingreso=='01')
            {$mes_ingreso='Enero';}
            if($mes_ingreso=='02')
            {$mes_ingreso='Febrero';}
            if($mes_ingreso=='03')
            {$mes_ingreso='Marzo';}
            if($mes_ingreso=='04')
            {$mes_ingreso='Abril';}
            if($mes_ingreso=='05')
            {$mes_ingreso='Mayo';}
            if($mes_ingreso=='06')
            {$mes_ingreso='Junio';}
            if($mes_ingreso=='07')
            {$mes_ingreso='Julio';}
            if($mes_ingreso=='08')
            {$mes_ingreso='Agosto';}
            if($mes_ingreso=='09')
            {$mes_ingreso='Septiembre';}
            if($mes_ingreso=='10')
            {$mes_ingreso='Octubre';}
            if($mes_ingreso=='11')
            {$mes_ingreso='Noviembre';}
            if($mes_ingreso=='12')
            {$mes_ingreso='Diciembre';}
            
            $cedula=$this->ddl1->SelectedValue;
            $nombreapellido=$this->ddl1->Text;
            $nombres=$this->t1->text;
            $apellidos=$this->t2->text;

            //si es menor a 23 caracteres se hace un salto de linea al numero de la cedula
            if (strlen("$nombres $apellidos")< 23) $bajar_ci=" \n ";
            else $bajar_ci='';

            $sueldo_basico=$this->t5->text;            
            $oficio=$this->t8->text;
            $iniciales=$this->t16->text;
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);            
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);            
            $pdf->SetMargins(30, 20, 30, 2);
            $pdf->SetAutoPageBreak(TRUE, 15);            
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 0, 'REPUBLICA BOLIVARIANA DE VENEZUELA', 0, 0, 'L', 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '             ESTADO NUEVA ESPARTA', 0, 0, 'L', 0);
            $pdf->Image('/var/www/tcpdf/images/escudo.png', 50, 25.8, 15, 15, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf->Ln(18);
            $pdf->Cell(0, 3, '           CAMARA DE MANEIRO', 0, 0, 'L', 0);
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 3, 'Nº DC-'.$oficio, 0, 0, 'L', 0);
            $pdf->SetFont('helvetica', 'B, U', 14);
            $pdf->Ln(10);
            $pdf->Cell(0, 0, "Constancia", 0, 0, 'C', 0);
            $pdf->Ln(15);
            $pdf->SetFont('helvetica', '', 13);
            $centimos=substr($sueldo_basico, -2);
            
           
            $cuerpo='El suscrito <B>JOSE FRANCISCO SALAZAR SERRANO</B>, titular de la Cédula de Identidad <B>Nº V-4.650.716</B>, Contralor del Estado Nueva Esparta según Resolución Nº 01-00-00-017 de fecha 09/02/2000, emanada de la Contraloría General de la República Bolivariana de Venezuela Nº 36.891 de fecha 14/02/2000, por medio de la presente hago constar que el(la) Ciudadano(a) <B>'.$nombres.' '.$apellidos.'</B>, titular de la Cédula de  Identidad <B>'.$bajar_ci.'Nº V-'.number_format($cedula, 0, ',', '.').'</B>, presta sus servicios en este Órgano Contralor desde el <B>'.$dia_ingreso.'</B> de <B>'.$mes_ingreso.'</B> de <B>'.$ano_ingreso.'</B>; y actualmente desempeña el cargo de <B>'.$cargo.'</B>, devengando una remuneración básica de <B>'.str_replace("uno", "un", num_a_letras($sueldo_basico)).' bolivares con '.strtolower(num_a_letras($centimos)).' centimos (Bs. '.number_format($sueldo_basico, 2, ',', '.').')</B> mensuales.'."\n";
            $pdf->writeHTML($cuerpo, true, false, false, true, 'J');
           

            $pdf->Ln(13);
            $arreglo_mes=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 0, 'Constancia que se expide a solicitud de la parte interesada en la ciudad de', 0, 2, 'J', 0);
            $pdf->Cell(0, 0, 'La Asunción a los '.strtolower(num_a_letras($dia)).' dias del mes de '.strtolower($arreglo_mes[$mes]).' de '.$ano.'.', 0, 2, 'J', 0);
            //$pdf->MultiCell(0, 0, , 0, 'L');
            $pdf->Ln(25);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 0, "JOSE FRANCISCO SALAZAR SERRANO", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Contralor del Estado Nueva Esparta", 0, 2, 'C', 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Resolución Nº 01-00-00-017 de fecha 09-02-2000", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "G.O.R.B.V Nº 36.891 de fecha 14-02-2000", 0, 2, 'C', 0);
            $pdf->Ln(13);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 0, $iniciales, 0, 'L');
            $pdf->Ln(5);
            $pdf->Cell(0, 0, "Hacia la Consolidación y Fortalecimiento del Sistema Nacional de Control Fiscal", 1, 0, 'C', 0);
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Av. Simón Bolívar. antigua Av. Constitución, sede administrativa de la Gobernación.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Piso I. Contraloría del Estado. La Asunción. Municipio Arismendi. Estado Nueva Esparta.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Rif: G-20001774-0 Teléfonos: (0295)265.98.13", 0, 2, 'C', 0);
            $pdf->Output("expedientes/".$cedula."/".$oficio."_Constancia.pdf",'FD');
            $fecha_temp=cambiaf_a_mysql($fecha);
            $ruta="expedientes/".$cedula."/".$oficio."_Constancia.pdf";
            $sql2="insert into expedientes.constancias_de_trabajo (correlativo, cedula, tipo, fecha, ruta) values ('$oficio', '$cedula', 'basica', '$fecha_temp', '$ruta')";
            $resultado1=modificar_data($sql2, $sender);
        }
        if($this->ddl2->selectedvalue=="integral")
        {
            $fecha=date("d/m/Y");
            list($dia, $mes, $ano) = split('/', $fecha);
            $cargo=$this->t4->text;
            $fecha_ingreso=$this->t3->text;
            list($dia_ingreso, $mes_ingreso, $ano_ingreso) = split('/', $fecha_ingreso);
            if($mes_ingreso=='01')
            {$mes_ingreso='Enero';}
            if($mes_ingreso=='02')
            {$mes_ingreso='Febrero';}
            if($mes_ingreso=='03')
            {$mes_ingreso='Marzo';}
            if($mes_ingreso=='04')
            {$mes_ingreso='Abril';}
            if($mes_ingreso=='05')
            {$mes_ingreso='Mayo';}
            if($mes_ingreso=='06')
            {$mes_ingreso='Junio';}
            if($mes_ingreso=='07')
            {$mes_ingreso='Julio';}
            if($mes_ingreso=='08')
            {$mes_ingreso='Agosto';}
            if($mes_ingreso=='09')
            {$mes_ingreso='Septiembre';}
            if($mes_ingreso=='10')
            {$mes_ingreso='Octubre';}
            if($mes_ingreso=='11')
            {$mes_ingreso='Noviembre';}
            if($mes_ingreso=='12')
            {$mes_ingreso='Diciembre';}
            $cedula=$this->ddl1->SelectedValue;
            //list($cedula, $nombreapellido) = split('-', $this->ddl1->SelectedValue);
            $nombres=$this->t1->text;
            $apellidos=$this->t2->text;            
            $sueldo_integral=$this->t6->text;
            $oficio=$this->t8->text;
            $iniciales=$this->t16->text;
            //si es menor a 23 caracteres se hace un salto de linea al numero de la cedula
            if (strlen("$nombres $apellidos")< 23) $bajar_ci=" \n ";
            else $bajar_ci='';

            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetHeaderData("escudo.jpg", 40);
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(30, 20, 30, 2);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 0, 'REPUBLICA BOLIVARIANA DE VENEZUELA', 0, 0, 'L', 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '             ESTADO NUEVA ESPARTA', 0, 0, 'L', 0);
            $pdf->Image('/var/www/tcpdf/images/escudo.png', 50, 25.8, 15, 15, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf->Ln(18);
            $pdf->Cell(0, 3, '           CONTRALORIA DEL ESTADO', 0, 0, 'L', 0);
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 3, 'Nº DC-'.$oficio, 0, 0, 'L', 0);
            $pdf->SetFont('helvetica', 'B, U', 14);
            $pdf->Ln(10);
            $pdf->Cell(0, 0, "Constancia", 0, 0, 'C', 0);
            $pdf->Ln(15);
            $pdf->SetFont('helvetica', '', 13);
            $centimos=substr($sueldo_integral, -2);
            $cuerpo='El suscrito <B>JOSE FRANCISCO SALAZAR SERRANO</B>, titular de la Cédula de Identidad <B>Nº V-4.650.716</B>, Contralor del Estado Nueva Esparta según Resolución Nº 01-00-00-017 de fecha 09/02/2000, emanada de la Contraloría General de la República Bolivariana de Venezuela Nº 36.891 de fecha 14/02/2000, por medio de la presente hago constar que el(la) Ciudadano(a) <B>'.$nombres.' '.$apellidos.'</B>, titular de la Cédula de Identidad <B>'.$bajar_ci.'Nº V-'.number_format($cedula, 0, ',', '.').'</B>, presta sus servicios en este Órgano Contralor desde el <B>'.$dia_ingreso.'</B> de <B>'.$mes_ingreso.'</B> de <B>'.$ano_ingreso.'</B>; y actualmente desempeña el cargo de <B>'.$cargo.'</B>, devengando una remuneración integral de <B>'.str_replace("uno", "un", num_a_letras($sueldo_integral)).' bolivares con '.strtolower(num_a_letras($centimos)).' centimos  (Bs. '.number_format($sueldo_integral, 2, ',', '.').')</B> mensuales.'."\n";
            $pdf->writeHTML($cuerpo, true, false, true, true, 'J');
            $pdf->Ln(15);
            $pdf->SetFont('helvetica', '', 12);
            $arreglo_mes=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 0, 'Constancia que se expide a solicitud de la parte interesada en la ciudad de', 0, 2, 'J', 0);
            $pdf->Cell(0, 0, 'La Asunción a los '.strtolower(num_a_letras($dia)).' dias del mes de '.strtolower($arreglo_mes[$mes]).' de '.$ano.'.', 0, 2, 'J', 0);
            $pdf->Ln(25);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 0, "JOSE FRANCISCO SALAZAR SERRANO", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Contralor del Estado Nueva Esparta", 0, 2, 'C', 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Resolución Nº 01-00-00-017 de fecha 09-02-2000", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "G.O.R.B.V Nº 36.891 de fecha 14-02-2000", 0, 2, 'C', 0);
            $pdf->Ln(13);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 0, $iniciales, 0, 'L');
            $pdf->Ln(5);
            $pdf->Cell(0, 0, "Hacia la Consolidación y Fortalecimiento del Sistema Nacional de Control Fiscal ", 1, 0, 'C', 0);
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Av. Simón Bolívar. antigua Av. Cosntitución, sede administrativa de la Gobernación.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Piso I. Contraloría del Estado. La Asunción. Municipio Arismendi. Estado Nueva Esparta.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Rif: G-20001774-0 Teléfonos: (0295)265.98.13", 0, 2, 'C', 0);
            $pdf->Output("expedientes/".$cedula."/".$oficio."_Constancia.pdf",'FD');
            $fecha_temp=cambiaf_a_mysql($fecha);
            $ruta="expedientes/".$cedula."/".$oficio."_Constancia.pdf";
            $sql2="insert into expedientes.constancias_de_trabajo (correlativo, cedula, tipo, fecha, ruta) values ('$oficio', '$cedula', 'integral', '$fecha_temp', '$ruta')";
            $resultado1=modificar_data($sql2, $sender);
        }
        if($this->ddl2->selectedvalue=="jubilado")
        {
            $fecha=date("d/m/Y");
            list($dia, $mes, $ano) = split('/', $fecha);
            $cargo=$this->t4->text;
            $fecha_ingreso=$this->t3->text;
            list($dia_ingreso, $mes_ingreso, $ano_ingreso) = split('/', $fecha_ingreso);
            if($mes_ingreso=='01')
            {$mes_ingreso='Enero';}
            if($mes_ingreso=='02')
            {$mes_ingreso='Febrero';}
            if($mes_ingreso=='03')
            {$mes_ingreso='Marzo';}
            if($mes_ingreso=='04')
            {$mes_ingreso='Abril';}
            if($mes_ingreso=='05')
            {$mes_ingreso='Mayo';}
            if($mes_ingreso=='06')
            {$mes_ingreso='Junio';}
            if($mes_ingreso=='07')
            {$mes_ingreso='Julio';}
            if($mes_ingreso=='08')
            {$mes_ingreso='Agosto';}
            if($mes_ingreso=='09')
            {$mes_ingreso='Septiembre';}
            if($mes_ingreso=='10')
            {$mes_ingreso='Octubre';}
            if($mes_ingreso=='11')
            {$mes_ingreso='Noviembre';}
            if($mes_ingreso=='12')
            {$mes_ingreso='Diciembre';}
            //$cedula=$this->ddl1->SelectedValue;
            //list($cedula, $nombreapellido) = split('-', $this->ddl1->SelectedValue);
            list($cedula, $nombreapellido) = split('-', $datos_seleccionados);
            $nombres=$this->t1->text;
            $apellidos=$this->t2->text;
            $sueldo_basico=$this->t5->text;
            $oficio=$this->t8->text;
            $iniciales=$this->t16->text;
            $servicio_desde=$this->t9->text;
            $servicio_hasta=$this->t10->text;
            $jubilado_desde=$this->t11->text;
            $resolucion_jubilacion=$this->t12->text;
            $fecha_resolucion_j=$this->t13->text;
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);           
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(30, 20, 30, 2);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 0, 'REPUBLICA BOLIVARIANA DE VENEZUELA', 0, 0, 'L', 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '             ESTADO NUEVA ESPARTA', 0, 0, 'L', 0);
            $pdf->Image('/var/www/tcpdf/images/escudo.png', 50, 25.8, 15, 15, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf->Ln(18);
            $pdf->Cell(0, 3, '           CONTRALORIA DEL ESTADO', 0, 0, 'L', 0);
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 3, 'Nº DC-'.$oficio, 0, 0, 'L', 0);
            $pdf->SetFont('helvetica', 'B, U', 14);
            $pdf->Ln(15);
            $pdf->Cell(0, 0, "Constancia", 0, 0, 'C', 0);
            $pdf->Ln(15);
            $pdf->SetFont('helvetica', '', 13);
            $centimos=substr($sueldo_basico, -2);
            $cuerpo='El suscrito <B>JOSE FRANCISCO SALAZAR SERRANO</B>, titular de la Cédula de Identidad <B>Nº V-4.650.716</B>, Contralor del Estado Nueva Esparta según Resolución Nº 01-00-00-017 de fecha 09/02/2000, emanada de la Contraloría General de la República Bolivariana de Venezuela Nº 36.891 de fecha 14/02/2000, por medio de la presente hago constar que el(la) Ciudadano(a) <B>'.$nombres.' '.$apellidos.'</B>, titular de la Cédula de Identidad <B>Nº V-'.number_format($cedula, 0, ',', '.').'</B>, prestó sus servicios en este Órgano Contralor desde el '.$servicio_desde.' hasta '.$servicio_hasta.'; y es jubilado por este Órgano Contralor desde el '.$jubilado_desde.', según Resolución emanada del Despacho del Contralor Nº '.$resolucion_jubilacion.' de fecha '.$fecha_resolucion_j.', devengando una remuneración básica de <B>'.str_replace("uno", "un", num_a_letras($sueldo_basico)).' bolivares con '.strtolower(num_a_letras($centimos)).' centimos  (Bs. '.number_format($sueldo_basico, 2, ',', '.').')</B>, mensuales.';
            $pdf->writeHTML($cuerpo, true, false, true, true, 'J');
            $pdf->Ln(25);
            $pdf->SetFont('helvetica', '', 12);
            $arreglo_mes=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 0, 'Constancia que se expide a solicitud de la parte interesada en la ciudad de', 0, 2, 'J', 0);
            $pdf->Cell(0, 0, 'La Asunción a los '.strtolower(num_a_letras($dia)).' dias del mes de '.strtolower($arreglo_mes[$mes]).' de '.$ano.'.', 0, 2, 'J', 0);
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 0, "JOSE FRANCISCO SALAZAR SERRANO", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Contralor del Estado Nueva Esparta", 0, 2, 'C', 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Resolución Nº 01-00-00-017 de fecha 09-02-2000", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "G.O.R.B.V Nº 36.891 de fecha 14-02-2000", 0, 2, 'C', 0);
            $pdf->Ln(13);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 0, $iniciales, 0, 'L');
            $pdf->Ln(5);
            $pdf->Cell(0, 0, "Hacia la Consolidación y Fortalecimiento del Sistema Nacional de Control Fiscal", 1, 0, 'C', 0);
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Av. Simón Bolívar. antigua Av. Cosntitución, sede administrativa de la Gobernación.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Piso I. Contraloría del Estado. La Asunción. Municipio Arismendi. Estado Nueva Esparta.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Rif: G-20001774-0 Teléfonos: (0295)265.98.13", 0, 2, 'C', 0);
            $pdf->Output("expedientes/".$cedula."/".$oficio."_Constancia.pdf",'FD');
            $fecha_temp=cambiaf_a_mysql($fecha);
            $ruta="expedientes/".$cedula."/".$oficio."_Constancia.pdf";
            $sql2="insert into expedientes.constancias_de_trabajo (correlativo, cedula, tipo, fecha, ruta) values ('$oficio', '$cedula', 'jubilado', '$fecha_temp', '$ruta')";
            $resultado1=modificar_data($sql2, $sender);
        }
        if($this->ddl2->selectedvalue=="pensionado")
        {
            $fecha=date("d/m/Y");
            list($dia, $mes, $ano) = split('/', $fecha);
            $cargo=$this->t4->text;
            $fecha_ingreso=$this->t3->text;
            list($dia_ingreso, $mes_ingreso, $ano_ingreso) = split('/', $fecha_ingreso);
            if($mes_ingreso=='01')
            {$mes_ingreso='Enero';}
            if($mes_ingreso=='02')
            {$mes_ingreso='Febrero';}
            if($mes_ingreso=='03')
            {$mes_ingreso='Marzo';}
            if($mes_ingreso=='04')
            {$mes_ingreso='Abril';}
            if($mes_ingreso=='05')
            {$mes_ingreso='Mayo';}
            if($mes_ingreso=='06')
            {$mes_ingreso='Junio';}
            if($mes_ingreso=='07')
            {$mes_ingreso='Julio';}
            if($mes_ingreso=='08')
            {$mes_ingreso='Agosto';}
            if($mes_ingreso=='09')
            {$mes_ingreso='Septiembre';}
            if($mes_ingreso=='10')
            {$mes_ingreso='Octubre';}
            if($mes_ingreso=='11')
            {$mes_ingreso='Noviembre';}
            if($mes_ingreso=='12')
            {$mes_ingreso='Diciembre';}
            //$cedula=$this->ddl1->SelectedValue;
            list($cedula, $nombreapellido) = split('-', $this->ddl1->SelectedValue);
            $nombres=$this->t1->text;
            $apellidos=$this->t2->text;
            $sueldo_basico=$this->t5->text;
            $oficio=$this->t8->text;
            $iniciales=$this->t16->text;
            $resolucion_pension=$this->t14->text;
            $fecha_resolucion_p=$this->t15->text;
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);            
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);            
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(30, 20, 30, 2);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 0, 'REPUBLICA BOLIVARIANA DE VENEZUELA', 0, 0, 'L', 0);
            $pdf->Ln(3);
            $pdf->Cell(0, 0, '             ESTADO NUEVA ESPARTA', 0, 0, 'L', 0);
            $pdf->Image('/var/www/tcpdf/images/escudo.png', 50, 25.8, 15, 15, 'PNG', '', '', true, 300, '', false, false, 0, false, false, false);
            $pdf->Ln(18);
            $pdf->Cell(0, 3, '           CONTRALORIA DEL ESTADO', 0, 0, 'L', 0);
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 3, 'Nº DC-'.$oficio, 0, 0, 'L', 0);
            $pdf->SetFont('helvetica', 'B, U', 14);
            $pdf->Ln(15);
            $pdf->Cell(0, 0, "Constancia", 0, 0, 'C', 0);
            $pdf->Ln(15);
            $pdf->SetFont('helvetica', '', 13);
            $centimos=substr($sueldo_basico, -2);
            $cuerpo='El suscrito <B>JOSE FRANCISCO SALAZAR SERRANO</B>, titular de la Cédula de Identidad <B>Nº V-4.650.716</B>, Contralor del Estado Nueva Esparta según Resolución Nº 01-00-00-017 de fecha 09/02/2000, emanada de la Contraloría General de la República Bolivariana de Venezuela Nº 36.891 de fecha 14/02/2000, por medio de la presente hago constar que el(la) Ciudadano(a) <B>'.$nombres.' '.$apellidos.'</B>, titular de la Cédula de Identidad <B>Nº V-'.number_format($cedula, 0, ',', '.').'</B>, es pensionado de este Órgano Contralor, según Resolución emanada del Despacho del Contralor <B>Nº '.$resolucion_pension.'</B> de fecha '.$fecha_resolucion_p.', con un monto actual de pensión de <B>'.str_replace("uno", "un", num_a_letras($sueldo_basico)).' bolivares con '.strtolower(num_a_letras($centimos)).' centimos  (Bs. '.number_format($sueldo_basico, 2, ',', '.').')</B>, mensuales'."\n";
            $pdf->writeHTML($cuerpo, true, false, true, true, 'J');
            $pdf->Ln(25);
            $arreglo_mes=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 0, 'Constancia que se expide a solicitud de la parte interesada en la ciudad de', 0, 2, 'J', 0);
            $pdf->Cell(0, 0, 'La Asunción a los '.strtolower(num_a_letras($dia)).' dias del mes de '.strtolower($arreglo_mes[$mes]).' de '.$ano.'.', 0, 2, 'J', 0);
            $pdf->Ln(25);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 0, "JOSE FRANCISCO SALAZAR SERRANO", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Contralor del Estado Nueva Esparta", 0, 2, 'C', 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Resolución Nº 01-00-00-017 de fecha 09-02-2000", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "G.O.R.B.V Nº 36.891 de fecha 14-02-2000", 0, 2, 'C', 0);
            $pdf->Ln(13);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->MultiCell(0, 0, $iniciales, 0, 'L');
            $pdf->Ln(5);
            $pdf->Cell(0, 0, "Hacia la Consolidación y Fortalecimiento del Sistema Nacional de Control Fiscal", 1, 0, 'C', 0);
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 0, "Av. Simón Bolívar. antigua Av. Cosntitución, sede administrativa de la Gobernación.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Piso I. Contraloría del Estado. La Asunción. Municipio Arismendi. Estado Nueva Esparta.", 0, 2, 'C', 0);
            $pdf->Cell(0, 0, "Rif: G-20001774-0 Teléfonos: (0295)265.98.13", 0, 2, 'C', 0);
            $pdf->Output("expedientes/".$cedula."/".$oficio."_Constancia.pdf",'FD');
            $fecha_temp=cambiaf_a_mysql($fecha);
            $ruta="expedientes/".$cedula."/".$oficio."_Constancia.pdf";
            $sql2="insert into expedientes.constancias_de_trabajo (correlativo, cedula, tipo, fecha, ruta) values ('$oficio', '$cedula', 'pensionado', '$fecha_temp', '$ruta')";
            $resultado1=modificar_data($sql2, $sender);
        }
    }
}
?>