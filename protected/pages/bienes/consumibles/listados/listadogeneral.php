<?php
require('/var/www/tcpdf/tcpdf.php');
class listadogeneral extends TPage
{
    function onload($param)
    {
        $sql="select *, concat(ano, partida, generica, especifica, subespecifica) from bienes.consumibles order by ano, partida, generica, especifica, subespecifica, descripcion";
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
        //return $resultado;
    }
    public function listar($sender, $param)
    {
        $fecha_i=$this->dp_ini->text;
        list($dia_i, $mes_i, $ano_i)=explode("-", $fecha_i);
        $inicio=$ano_i.'-'.$mes_i.'-'.$dia_i;
        $fecha_f=$this->dp_fin->text;
        list($dia_f, $mes_f, $ano_f)=explode("-", $fecha_f);
        $fin=$ano_f.'-'.$mes_f.'-'.$dia_f;        
        $sql2="select *, concat(ano, partida, generica, especifica, subespecifica) from bienes.consumibles";
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->DataSource=$resultado2;
		$this->dg1->dataBind();
        //return $resultado2;
    }    
    public function imprimir($sender, $param)
    {        
        $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
        $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
        $info_adicional= "Listado General de Consumibles";
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->AddPage();
        $pdf->Ln(10);        
        $pdf->SetFont('helvetica','B',14);        
        $pdf->Cell(50, 0, 'descripcion', '0', 0, 'L');
        $pdf->Cell(25, 0, 'marca', 0, 0, 'L');
        $pdf->Cell(25, 0, 'costo', 0, 0, 'L');
        $pdf->Cell(25, 0, 'maximo', 0, 0, 'L');
        $pdf->Cell(25, 0, 'minimo', 0, 0, 'L');
        $pdf->Cell(25, 0, 'actual', 0, 0, 'L');
        $pdf->Cell(40, 0, 'referencia', 0, 0, 'L');
        $pdf->Cell(50, 0, 'partida', 0, 1, 'L');
        $sql="select *, concat(ano, partida, generica, especifica, subespecifica) from bienes.consumibles";
        $resultado=cargar_data($sql, $this);
        $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
        $fill=0;
        $pdf->SetFont('helvetica','',12);
        foreach($resultado as $fila)
        {
            $pdf->Cell(50, 0, $fila['descripcion'], 'R', 0, 'L', $fill);
            $pdf->Cell(25, 0, $fila['marca'], 'LR', 0, 'L', $fill);
            $pdf->Cell(25, 0, $fila['costo'], 'LR', 0, 'L', $fill);
            $pdf->Cell(25, 0, $fila['maximo'], 'LR', 0, 'L', $fill);
            $pdf->Cell(25, 0, $fila['minimo'], 'LR', 0, 'L', $fill);
            $pdf->Cell(25, 0, $fila['actual'], 'LR', 0, 'L', $fill);
            $pdf->Cell(40, 0, $fila['ref_producto'], 'LR', 0, 'L', $fill);
            $pdf->Cell(50, 0, $fila['concat(ano, partida, generica, especifica, subespecifica)'], 'L', 1, 'L', $fill);
            if ($fill==0)
            {
                $fill=1;
            }
            else
            {
                $fill=0;
            }
        }               
        $pdf->Output('Listado_General.pdf','D');
    }
}
?>