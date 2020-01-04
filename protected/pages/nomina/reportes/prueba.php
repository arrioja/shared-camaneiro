<?php
class prueba extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {

            }
        }

        public function verificar_tipo_nomina($sender,$param)//verifica que exista la nómina creada para el tipo de nómina seleccionado
        {
            $tipo_nomina=$this->drop_tipo_nomina->Text;
            $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            $sql="select distinct cedula from nomina.nomina where cod='$cod' and tipo_nomina='$tipo_nomina' and cod_organizacion='$cod_org'";
            $datos_tipo_nomina=cargar_data($sql,$this);
            //var_dump (count($datos_tipo_nomina));
            if (count($datos_tipo_nomina)<1)//
            {
                $param->IsValid=False;
            }

        }

        public function mostrar($sender, $param)
        {
       /*    if ($this->IsValid)
           {
            require('/var/www/tcpdf/tcpdf.php');
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            //$datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
           // $tipo_nomina=$this->drop_tipo_nomina->Text;
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);

            $pdf->Cell(70,5,$datos_nomina[0]['titulo'],1,1);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Cod Nómina: ');
            $pdf->Cell(20,5,$datos_nomina[0]['cod']);
            $pdf->Cell(25,5,'Desde: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']));
            $pdf->Cell(25,5,'Hasta: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),0,1);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Reporte Resumen de Conceptos', 1, 1, 'C');


            $sql="select distinct cod_incidencia, descripcion, tipo, sum(monto_incidencia) suma,count(cod_incidencia) num
            from nomina.nomina where tipo_nomina='$tipo_nomina' and (tipo='DEBITO' OR tipo='CREDITO') and cod='$cod'
            group by cod_incidencia order by tipo ";
            
            //$res_conceptos=cargar_data($sql,$this);
            $pdf->SetFont('courier','B',12);
            $pdf->Cell(15,10,'COD',1,0,'C');
            $pdf->Cell(110,10,'Descripción del Concepto',1,0,'C');
            $pdf->Cell(35,10,'Asignaciones',1,0,'C');
            $pdf->Cell(35,10,'Deducciones',1,0,'C');
            $pdf->Cell(35,10,'N° Personas',1,1,'C');
            $pdf->SetFont('courier','',10);
            $asignaciones=0;
            $deducciones=0;
            $num=0;
            foreach ($res_conceptos as $key=>$conceptos)
            {
                $pdf->Cell(15,7,$conceptos['cod_incidencia'],1,0,'C');//cod nomina
                $pdf->Cell(110,7,$conceptos['descripcion'],1);
                if ($conceptos['tipo']=='CREDITO')
                    {$pdf->Cell(35,7,$conceptos['suma'],1,0,'R');$pdf->Cell(35,7,'',1,0,'R');
                    $tot_asignaciones=$tot_asignaciones+$conceptos['suma'];

                    }
                else
                    {$pdf->Cell(35,7,'',1,0,'R');$pdf->Cell(35,7,$conceptos['suma'],1,0,'R');
                        $tot_deducciones=$tot_deducciones+$conceptos['suma'];
                    }
                $pdf->Cell(35,7,$conceptos['num'],1,1,'C');
            $tot=$tot+$conceptos['num'];
            }//foreach integrantes
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(15,10,'',0,0,'C');
            $pdf->Cell(110,10,'TOTALES',0,0,'C');
            $pdf->Cell(35,10,$tot_asignaciones,0,0,'C');
            $pdf->Cell(35,10,$tot_deducciones,0,0,'C');
            $pdf->Cell(35,10,$tot,0,0,'C');


            $pdf->Output('example_001.pdf', 'D');
           }*/

 

//============================================================+
// File name   : example_001.php
// Begin       : 2008-03-04
// Last Update : 2010-08-14
//
// Description : Example 001 for TCPDF class
//               Default Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com s.r.l.
//               Via Della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Default Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */
//require('/var/www/tcpdf/tcpdf.php');
require_once('/var/www/tcpdf/config/lang/eng.php');
require_once('/var/www/tcpdf/tcpdf.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 001');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
$pdf->setLanguageArray($l);

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// Set some content to print
$html = <<<EOD
<h1>Welcome to <a href="http://www.tcpdf.org" style="text-decoration:none;background-color:#CC0000;color:black;">&nbsp;<span style="color:black;">TC</span><span style="color:white;">PDF</span>&nbsp;</a>!</h1>
<i>This is the first example of TCPDF library.</i>
<p>This text is printed using the <i>writeHTMLCell()</i> method but you can also use: <i>Multicell(), writeHTML(), Write(), Cell() and Text()</i>.</p>
<p>Please check the source code documentation and other examples for further information.</p>
<p style="color:#CC0000;">TO IMPROVE AND EXPAND TCPDF I NEED YOUR SUPPORT, PLEASE <a href="http://sourceforge.net/donate/index.php?group_id=128076">MAKE A DONATION!</a></p>
EOD;

// Print text using writeHTMLCell()
$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
        }
    }
?>