<?php
require('/var/www/tcpdf/tcpdf.php');
class listadoporfecha extends TPage
{
    public function listar($sender, $param)
    {
        //$fecha_i=$this->dp_ini->text;
        //list($dia_i, $mes_i, $ano_i)=explode("-", $fecha_i);
        //$inicio=$ano_i.'-'.$mes_i.'-'.$dia_i;
        $inicio=cambiaf_a_mysql($this->dp_ini->text);
        $fin=cambiaf_a_mysql($this->dp_fin->text);
        //$fecha_f=$this->dp_fin->text;
        //list($dia_f, $mes_f, $ano_f)=explode("-", $fecha_f);
        //$fin=$ano_f.'-'.$mes_f.'-'.$dia_f;
        $sql="select * from bienes.consumibles_entregados where(f_entrega>='$inicio' and f_entrega<='$fin')";
        if($this->dp_ini->text=="" and $this->dp_fin->text=="")// si las fechas estan vacias
        {
            $sql="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d where (ce.direccion_entrega= d.codigo)";
        }
        else// si las fechas estan llenas
        {
            $sql="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d 
            where (ce.direccion_entrega= d.codigo) and (f_entrega>='$inicio' and f_entrega<='$fin')";
        }        
        $resultado=cargar_data($sql, $this);
        $this->dg1->DataSource=$resultado;
		$this->dg1->dataBind();
    }
    /* Esta función da formato al listado */
	public function formatear_fecha($sender, $param)
	{
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
        }
    }
    public function imprimir($sender, $param)
    {
        $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
        $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
        $info_adicional= "Listado de Consumibles por Fechas"."\n"."Desde: ".$this->dp_ini->text." Hasta: ".$this->dp_fin->text;
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->AddPage();
        $pdf->Ln(10);
        //$pdf->Image($ruta.'LogoCENE.gif', 11, 11, 16, 15);
        $pdf->SetFont('courier','B',16);
        //$pdf->Cell(0, 17, 'Listado de Consumibles por Fechas', 1, 1, 'C');
        if($this->dp_ini->text=="" and $this->dp_fin->text=="")// si las fechas estan vacias
        {
            $pdf->SetFont('courier','B',14);
            //$pdf->Cell(50, 0, 'Desde: - Hasta: -', 0, 1, 'L');
            $pdf->Cell(20, 0, 'Fecha', 0, 0, 'L');
            $pdf->Cell(105, 0, 'Direccion', 0, 0, 'L');
            $pdf->Cell(95, 0, 'Descripcion', 0, 0, 'L');
            $pdf->Cell(30, 0, 'entregados', 0, 1, 'L');
            $sql="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d where (ce.direccion_entrega= d.codigo)";
            $resultado=cargar_data($sql, $this);
            $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
            $fill=0;
            foreach($resultado as $fila)
            {                                
                $pdf->SetFont('helvetica', '', 10);
                $fecha_nueva=cambiaf_a_normal($fila['f_entrega']);
                $pdf->Cell(20, 0, $fecha_nueva, 'L', 0, 'L', $fill);
                $pdf->Cell(105, 0, $fila['nombre_completo'], 'LR', 0, 'L', $fill);
                $pdf->Cell(95, 0, $fila['descripcion'], 'LR', 0, 'L', $fill);
                $pdf->Cell(40, 0, $fila['cantidad'], 'LR', 1, 'L', $fill);
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }
            $pdf->Output('Listado_por_Fechas.pdf','D');
        }
        else// si las fechas estan llenas
        {
            //$fecha_i=$this->dp_ini->text;
            //list($dia_i, $mes_i, $ano_i)=explode("-", $fecha_i);
            //$inicio=$ano_i.'-'.$mes_i.'-'.$dia_i;
            //$fecha_f=$this->dp_fin->text;
            //list($dia_f, $mes_f, $ano_f)=explode("-", $fecha_f);
            //$fin=$ano_f.'-'.$mes_f.'-'.$dia_f;
            $inicio=cambiaf_a_mysql($this->dp_ini->text);
            $fin=cambiaf_a_mysql($this->dp_fin->text);
            $sql="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d 
            where (ce.direccion_entrega= d.codigo) and (f_entrega>='$inicio' and f_entrega<='$fin')";
            $resultado=cargar_data($sql, $this);
            $inicio=cambiaf_a_normal($inicio);
            $fin=cambiaf_a_normal($fin);
            $pdf->SetFont('courier','B',14);
            //$pdf->Cell(50, 0, 'Desde: '.$inicio.' Hasta: '.$fin, 0, 1, 'L');
            $pdf->Cell(20, 0, 'Fecha', 0, 0, 'L');
            $pdf->Cell(105, 0, 'Direccion', 0, 0, 'L');
            $pdf->Cell(95, 0, 'Descripcion', 0, 0, 'L');
            $pdf->Cell(30, 0, 'entregados', 0, 1, 'L');
            $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
            $fill=0;
            foreach($resultado as $fila)
            {
                $pdf->SetFont('helvetica', '', 10);
                $fecha_nueva=cambiaf_a_normal($fila['f_entrega']);
                $pdf->Cell(20, 0, $fecha_nueva, 'L', 0, 'L', $fill);
                $pdf->Cell(105, 0, $fila['nombre_completo'], 'LR', 0, 'L', $fill);
                $pdf->Cell(95, 0, $fila['descripcion'], 'LR', 0, 'L', $fill);
                $pdf->Cell(40, 0, $fila['cantidad'], 'LR', 1, 'L', $fill);
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }
            $pdf->Output('Listado_por_Fechas.pdf','D');
        }
    }
}
?>
