<?php
require('/var/www/tcpdf/tcpdf.php');
class antecedentes extends TPage
{    
    public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            //llena el listbox con las cedulas
            $sql="select distinct i.cedula, p.cedula, p.nombres, p.apellidos
                  from nomina.integrantes i, organizacion.personas p
                  where (i.cedula=p.cedula) order by i.cedula asc";
            $resultado=cargar_data($sql, $this);            
            $this->ddl1->DataSource=$resultado;
            $this->ddl1->dataBind();            
        }
    }
    public function nya($sender, $param)
    {
        $cedula=$this->ddl1->SelectedValue;
        $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula') ";
        $resultado=cargar_data($sql, $this);
        $this->t1->text=$resultado[0]['nombres'].' '.$resultado[0]['apellidos'];
    }
     public function imprimir($sender, $param)
    {
        /*consulta para obtener los conceptos de una persona determinada //ojo no borrar esta buena//
         * SELECT * FROM nomina.integrantes_conceptos where(cedula='$cedula')
         //select nic.cod_concepto, p.cedula
         //from nomina.integrantes_conceptos nic, organizacion.personas p
         //where(nic.cedula='12506760') group by nic.cod_concepto
         */
        /*consulta para obtener las constantes de una persona determinada /ojo no borrar esta buena/
         * SELECT * FROM nomina.integrantes_constantes where(cedula='$cedula')
         //select nict.cod_constantes, nict.monto, p.cedula
         //from nomina.integrantes_constantes nict, organizacion.personas p
         //where(nict.cedula='12506760')group by nict.cod_constantes
         */
        $hoy=date("d/m/Y");
        $cedula=$this->ddl1->SelectedValue;
        $codigobarra = rand(1,100);
        //---------------------------
        $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula') ";
        $resultado=cargar_data($sql, $this);
        //---------------------------        
        $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
        $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
        $info_adicional= "Dirección de Recursos Humanos"."\n".'Fecha emisión : '.$hoy;
        $pdf->SetHeaderData("LogoCENE.gif", 15, usuario_actual('nombre_organizacion')." - Sistema de Información Automatizado", $info_adicional);        
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));        
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);        
        $pdf->AddPage();
        $pdf->Ln(10);
        $pdf->SetFont('helvetica','B',14);
        $pdf->MultiCell(0, 0, 'Antecedentes de Servicio', '1', 'C');
        $pdf->SetFont('helvetica','',12);
        //----------------------------cedula de la persona
        $pdf->MultiCell(150, 0, 'Nombres y Apellidos: '.$resultado[0]['nombres'].' '.$resultado[0]['apellidos'], '1', 'L', '0', '0');
        $pdf->MultiCell(36, 0, 'Cédula: '.$cedula, '1', 'L', '0', '1');
        //---------------------------
        $pdf->SetFont('helvetica','B',12);
        $pdf->MultiCell(93, 0, 'Ingreso', '1', 'C', '0', '0');
        $pdf->MultiCell(93, 0, 'Egreso', '1', 'C', '0', '1');
        $pdf->SetFont('helvetica','',10);
        //---------------------------busca la fecha de ingreso de la persona
        //$sql1="select fecha_ingreso from organizacion.personas where(cedula='$cedula')";
        $sql_ingreso="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_ingreso asc";
        $resultado1=cargar_data($sql_ingreso, $this);
        $fi=cambiaf_a_normal($resultado1[0]['fecha_ingreso']);
        $pdf->MultiCell(34, 0, 'Fecha: '.$fi, '1', 'L', '0', '0');
        //---------------------------busca el primer cargo de la persona
        //$sql1="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_ingreso asc";
        $resultado2=cargar_data($sql_ingreso, $this);
        $pdf->MultiCell(59, 0, 'Cargo: '.$resultado2[0]['cargo'], '1', 'L', '0', '0');
        //---------------------------busca la fecha de egreso de la persona        
        $sql_egreso="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_egreso desc";
        $resultado3=cargar_data($sql_egreso, $this);
        $fe=cambiaf_a_normal($resultado3[0]['fecha_egreso']);
        $pdf->MultiCell(34, 0, 'Fecha: '.$fe, '1', 'L', '0', '0');
        //---------------------------busca el cargo de egreso de la persona
        //$sql4="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_egreso desc";
        $resultado4=cargar_data($sql_egreso, $this);
        $pdf->MultiCell(59, 0, 'Cargo: '.$resultado4[0]['cargo'], '1', 'L', '0', '1');
        //---------------------------busca el sueldo de ingreso de la persona
        //$sql5="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_egreso asc";
        $resultado5=cargar_data($sql_ingreso, $this);
        $pdf->MultiCell(93, 0, 'Sueldo Básico : Bs '.$resultado5[0]['sueldo'], '1', 'L', '0', '0');
        //---------------------------busca el sueldo de egreso de la persona
        //$sql6="select * from antecedentes.cargos_anteriores where (cedula='$cedula') order by fecha_egreso desc";
        $resultado6=cargar_data($sql_egreso, $this);
        $pdf->MultiCell(93, 0, 'Sueldo Básico : Bs '.$resultado6[0]['sueldo'], '1', 'L', '0', '1');
        //---------------------------
        $sql7="select comp_ingreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado7=cargar_data($sql7, $this);
        $pdf->MultiCell(93, 0, 'Compensación : Bs '.$resultado7[0]['comp_ingreso'], '1', 'L', '0', '0');
        //---------------------------
        $sql8="select comp_egreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado8=cargar_data($sql8, $this);
        $pdf->MultiCell(93, 0, 'Compensación : Bs '.$resultado8[0]['comp_egreso'], '1', 'L', '0', '1');
        //---------------------------
        $sql9="select prima_prof_ingreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado9=cargar_data($sql9, $this);
        $pdf->MultiCell(93, 0, 'Prima Prof : Bs '.$resultado9[0]['prima_prof_ingreso'], '1', 'L', '0', '0');
        //---------------------------
        $sql10="select prima_prof_egreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado10=cargar_data($sql10, $this);
        $pdf->MultiCell(93, 0, 'Prima Prof : Bs '.$resultado10[0]['prima_prof_egreso'], '1', 'L', '0', '1');
        //---------------------------        
        $sql11="select prima_res_ingreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado11=cargar_data($sql11, $this);
        $pdf->MultiCell(93, 0, 'Prima Resp : Bs '.$resultado11[0]['prima_res_ingreso'], '1', 'L', '0', '0');
        //---------------------------
        $sql12="select prima_res_egreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado12=cargar_data($sql12, $this);
        $pdf->MultiCell(93, 0, 'Prima Resp : Bs '.$resultado12[0]['prima_res_egreso'], '1', 'L', '0', '1');
        //---------------------------
        $sql13="select prima_ant_ingreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado13=cargar_data($sql13, $this);
        $pdf->MultiCell(93, 0, 'Prima Antig : Bs '.$resultado13[0]['prima_ant_ingreso'], '1', 'L', '0', '0');
        //---------------------------
        $sql14="select prima_ant_egreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado14=cargar_data($sql14, $this);
        $pdf->MultiCell(93, 0, 'Prima Antig : Bs '.$resultado14[0]['prima_ant_egreso'], '1', 'L', '0', '1');
        //---------------------------
        $sql15="select total_ingreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado15=cargar_data($sql15, $this);
        $pdf->MultiCell(93, 0, 'Total : Bs '.$resultado15[0]['total_ingreso'], '1', 'L', '0', '0');
        //---------------------------
        $sql16="select total_egreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado16=cargar_data($sql16, $this);
        $pdf->MultiCell(93, 0, 'Total : Bs '.$resultado16[0]['total_egreso'], '1', 'L', '0', '1');
        //---------------------------
        $pdf->SetFont('helvetica','B',14);
        $pdf->MultiCell(0, 0, 'Causa del Egreso', '1', 'C');
        $pdf->SetFont('helvetica','',10);
        $sql17="select causa_egreso from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado17=cargar_data($sql17, $this);
        //---------------------------
        if($resultado17[0]['causa_egreso']=='r')
        {
            $pdf->MultiCell(93, 0, 'Por: Renuncia', '1', 'L', '0', '0');
        }
        //---------------------------
        if($resultado17[0]['causa_egreso']=='rp')
        {
            $pdf->MultiCell(93, 0, 'Por: Red. Personal', '1', 'L', '0', '0');
        }
        //---------------------------
        if($resultado17[0]['causa_egreso']=='p')
        {
            $pdf->MultiCell(93, 0, 'Por: Pensión', '1', 'L', '0', '0');
        }
        //---------------------------
        if($resultado17[0]['causa_egreso']=='rr')
        {
            $pdf->MultiCell(93, 0, 'Por: Remoción y/o Retiro', '1', 'L', '0', '0');
        }
        //---------------------------
        if($resultado17[0]['causa_egreso']=='d')
        {
            $pdf->MultiCell(93, 0, 'Por: Destitución', '1', 'L', '0', '0');
        }
        //---------------------------
        if($resultado17[0]['causa_egreso']=='j')
        {
            $pdf->MultiCell(93, 0, 'Por: Jubilación', '1', 'L', '0', '1');
        }
        //---------------------------
        if($resultado17[0]['causa_egreso']=='f')
        {
            $pdf->MultiCell(93, 0, 'Por: Fallecimiento', '1', 'L', '0', '0');
        }
        //---------------------------
        if($resultado17[0]['causa_egreso']=='cc')
        {
            $pdf->MultiCell(93, 0, 'Por: Culminación de Contrato', '1', 'L', '0', '0');
        }
        //---------------------------
        $sql_anticipo="select anticipos from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado_anticipo=cargar_data($sql_anticipo, $this);
        $pdf->MultiCell(93, 0, 'Anticipos : '.$resultado_anticipo[0]['anticipos'].' Bs', '1', 'L', '0', '1');
        //---------------------------
        $sql18="select djp from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado18=cargar_data($sql18, $this);
        $pdf->MultiCell(93, 0, 'Presento Certificado Electronico de D.J.P : '.$resultado18[0]['djp'], '1', 'L', '0', '0');
        //---------------------------
        $sql19="select pago from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado19=cargar_data($sql19, $this);
        $pdf->MultiCell(93, 0, 'Pago Prestaciones Sociales : '.$resultado19[0]['pago'], '1', 'L', '0', '1');
        //---------------------------
        $pdf->SetFont('helvetica','B',14);
        $pdf->MultiCell(0, 0, 'Actuación del Funcionario C.E.N.E', '1', 'C');
        $pdf->SetFont('helvetica','B',12);
        //$pdf->MultiCell(103, 0, 'Ingreso', '1', 'C', '0', '0');
        //$pdf->MultiCell(103, 0, 'Egreso', '1', 'C', '0', '1');
        $pdf->SetFont('helvetica','',10);
        //---------------------------        
        $pdf->MultiCell(30, 0, 'Fecha Ingreso: ', '1', 'L', '0', '0');
        $pdf->MultiCell(30, 0, 'Fecha Egreso: ', '1', 'L', '0', '0');
        $pdf->MultiCell(96, 0, 'Cargo : ', '1', 'L', '0', '0');
        $pdf->MultiCell(30, 0, 'Sueldo: ', '1', 'L', '0', '1');
        //--- busca los ultimos 5 cargos del funcionario comenzando por el ultimo
        $sql20="select * from antecedentes.cargos_anteriores where(cedula='$cedula') order by fecha_egreso desc limit 5";
        $resultado20=cargar_data($sql20, $this);        
        //---
        foreach($resultado20 as $cargo)
        {
            //print_r($cargo['sueldo']);
            //echo('<br>');
            $fi=cambiaf_a_normal($cargo['fecha_ingreso']);
            $pdf->MultiCell(30, 0, ''.$fi, '1', 'L', '0', '0');
            $fe=cambiaf_a_normal($cargo['fecha_egreso']);
            $pdf->MultiCell(30, 0, ''.$fe, '1', 'L', '0', '0');
            $pdf->MultiCell(96, 0, ''.$cargo['cargo'], '1', 'L', '0', '0');
            $pdf->MultiCell(30, 0, 'Bs '.$cargo['sueldo'], '1', 'L', '0', '1');
        }       
        //---------------------------
        $sql21="select observaciones from antecedentes.antecedentes where(cedula='$cedula')";
        $resultado21=cargar_data($sql21, $this);
        $pdf->MultiCell(0, 0, 'Observaciones : '.$resultado21[0]['observaciones']."\n", '1', 'L', '0', '1');
        //---------------------------
        $sql22="select cedula from organizacion.personas_nivel_dir where(nivel='90')";
        $resultado22=cargar_data($sql22, $this);
        $ced=$resultado22[0]['cedula'];
        $sql23="select nombres, apellidos from organizacion.personas  where(cedula='$ced')";
        $resultado23=cargar_data($sql23, $this);
        $pdf->MultiCell(103, 25, 'Contralor del Estado : '."\n".$resultado23[0]['nombres'].' '.$resultado23[0]['apellidos'], '1', 'L', '0', '0');        
        //$pdf->MultiCell(40, 0, 'Sello : '."\n"." ", 'R');
        //---------------------------
        $sql24="select cedula from organizacion.personas_cargos where(cod_direccion='01002' and denominacion='director')";
        $resultado24=cargar_data($sql24, $this);
        $ced2=$resultado24[0]['cedula'];
        $sql25="select nombres, apellidos from organizacion.personas  where(cedula='$ced2')";
        $resultado25=cargar_data($sql25, $this);
        $pdf->MultiCell(0, 25, 'Directora de Recursos Humanos : '."\n".$resultado25[0]['nombres'].' '.$resultado25[0]['apellidos'], '1', 'L', '0', '1');
        $pdf->MultiCell(103, 0, 'Fecha : '."\n"." ", '1', 'L', '0', '0');
        $pdf->MultiCell(0, 0, 'Fecha : '."\n"." ", '1', 'L', '0', '0');
        //$pdf->MultiCell(40, 0, ''."\n"." ", 'RB');
        //---------------------------
        //$pdf->Ln(97);
        $pdf->SetFont('helvetica','',8);
        $codigobarra = rand(1,100);
        $pdf->setBarcode($codigobarra);
        $pdf->MultiCell(0, 0, 'Av. Simón Bolívar, antigua Av. Constitucion, Edificio Sede Administrativa de la Gobernación, Piso 1, Municipio Arismendi, Estado Nueva Esparta'."\n".'Telf: (0295) 242.26.92/242.42.06/265.9813', '0', 'C', '0', '0', 0, 252);
        //---------------------------
        //* Se incluye el rastro en el archivo de bitácora */
        $login=usuario_actual('login');
        $descripcion_log = "El usuario ".$login." imprimio la hoja de antecedntes de ".$resultado[0]['nombres'].' '.$resultado[0]['apellidos'];
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        //---------------------------
        $pdf->Output('Antecedentes.pdf','D');
    }
}
?>
