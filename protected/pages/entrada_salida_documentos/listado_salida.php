<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require('/var/www/tcpdf/tcpdf.php');
class listado_salida extends TPage
{
     function onload($param)
     {
         $sql1="select * from entrada_salida_documentos.salida_documento";
         $resultado1=cargar_data($sql1, $this);
         $this->dg1->DataSource=$resultado1;
         $this->dg1->dataBind();
     }
     public function imprimir($sender, $param)
     {
        $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
        $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
        $info_adicional= "Listado de Entrada de Documentos";
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $sql="select * from entrada_salida_documentos.salida_documento";
        $resultado=cargar_data($sql, $this);
        $pdf->AddPage();
        $pdf->Ln(10);
        $pdf->SetFont('helvetica','B',12);
        $pdf->Cell(86, 0, 'Fecha Salida', 'RB', 0, 'L', $fill);
        $pdf->Cell(87, 0, 'A Quien se  Envía', 'LB', 0, 'L', $fill);
        $pdf->Cell(87, 0, 'Quien Recibe', 'LB', 1, 'L', $fill);
        $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
        $fill=0;
        $pdf->SetFont('helvetica','',10);
        foreach($resultado as $fila)
        {
            $pdf->Cell(86, 0, $fila['fecha_salida'], 'R', 0, 'L', $fill);
            $pdf->Cell(87, 0, $fila['a_quien'], 'LR', 0, 'L', $fill);
            $pdf->Cell(87, 0, $fila['quien_recibe'], 'LR', 1, 'L', $fill);
            if ($fill==0)
            {
                $fill=1;
            }
            else
            {
                $fill=0;
            }
        }
        $pdf->Output('salida_documentos.pdf','D');
     }
}
?>
