<?php
require('/var/www/tcpdf/tcpdf.php');
class listado_denuncia extends TPage
{
    public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {     
            $sql="select distinct ano from denuncias.denuncia";
            $resultado=cargar_data($sql, $this);            
            $this->ddl1->DataSource=$resultado;
            $this->ddl1->dataBind();
            //
            $sql="select distinct tipo from denuncias.denuncia";
            $resultado=cargar_data($sql, $this);            
            $this->ddl2->DataSource=$resultado;
            $this->ddl2->dataBind();                        
        }
    }    
    public function listar($sender, $param)
    {
        $ano=$this->ddl1->selectedvalue;
        $tipo=$this->ddl2->selectedvalue;
        if($ano!='')
        {
            //lista por ano
            $sql1="select * from denuncias.denuncia where(ano='$ano')";
            $resultado1=cargar_data($sql1, $this);
            $this->dg1->DataSource=$resultado1;
            $this->dg1->dataBind();
        }
        if($tipo!='')
        {
            //lista por tipo
            $sql="select * from denuncias.denuncia where(tipo='$tipo')";
            $resultado=cargar_data($sql, $this);
            $this->dg1->DataSource=$resultado;
            $this->dg1->dataBind();
        }
        if($tipo!='' and $ano!='')
        {
            //lista por ano y tipo
            $sql="select * from denuncias.denuncia where(ano='$ano' and tipo='$tipo')";
            $resultado=cargar_data($sql, $this);
            $this->dg1->DataSource=$resultado;
            $this->dg1->dataBind();
        }
    }
    public function nuevo_item($sender,$param)
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
        $pdf->SetDisplayMode('fullpage', 'SinglePage', 'UseNone');
        $ano=$this->ddl1->selectedvalue;
        $tipo=$this->ddl2->selectedvalue;
        $info_adicional= "Listado de Actuación"."\n".
                         "Año: ".$ano."\n".
                         "Tipo: ".$tipo;
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->AddPage('L', 'LEGAL');
        $pdf->Ln(10);
        $pdf->SetFont('helvetica','B',14);
        $pdf->Cell(20, 0, 'número', 0, 0, 'L');
        $pdf->Cell(25, 0, 'fecha', 0, 0, 'L');
        $pdf->Cell(40, 0, 'denunciantes', 0, 0, 'L');
        $pdf->Cell(40, 0, 'motivo', 0, 0, 'L');
        $pdf->Cell(30, 0, 'ubicación', 0, 0, 'L');
        $pdf->Cell(30, 0, 'organismos', 0, 0, 'L');
        $pdf->Cell(40, 0, 'consignados', 0, 0, 'L');
        $pdf->Cell(40, 0, 'limitaciones', 0, 0, 'L');
        $pdf->Cell(30, 0, 'estado', 0, 0, 'L');
        $pdf->Cell(30, 0, 'observación', 0, 1, 'L');
        $pdf->SetFont('helvetica','',10);
        $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
        $fill=0;
        if ($ano!='' and $tipo=='')
        {
            //lista por ano
            $sql1="select * from denuncias.denuncia where(ano='$ano')";
            $resultado1=cargar_data($sql1, $this);            
            foreach($resultado1 as $fila1)
            {
                $pdf->MultiCell(20, 0, $fila1['numero'], 'LR', 'L', $fill, 0);
                $pdf->MultiCell(25, 0, $fila1['fecha'], 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila1['denunciantes']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila1['motivo']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila1['ubicacion']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila1['organismos']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila1['documentos_consignados']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila1['limitaciones']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila1['estado']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(50, 0, $fila1['observacion']."\n", 'LR', 'L', $fill, 1);
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }
            $pdf->Output('Listado_de_Actuacion.pdf','D');
        }
        if ($tipo!='' and $ano=='')
        {
            //lista por tipo
            $sql2="select * from denuncias.denuncia where(tipo='$tipo')";
            $resultado2=cargar_data($sql2, $this);            
            foreach($resultado2 as $fila2)
            {
                $pdf->MultiCell(20, 0, $fila2['numero'], 'LR', 'L', $fill, 0);
                $pdf->MultiCell(25, 0, $fila2['fecha'], 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila2['denunciantes']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila2['motivo']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila2['ubicacion']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila2['organismos']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila2['documentos_consignados']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila2['limitaciones']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila2['estado']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(50, 0, $fila2['observacion']."\n", 'LR', 'L', $fill, 1);
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }
            $pdf->Output('Listado_de_Actuacion.pdf','D');
        }
        if ($ano!='' and $tipo!='')
        {
            //lista por ano y tipo
            $sql3="select * from denuncias.denuncia where(ano='$ano' and tipo='$tipo')";
            $resultado3=cargar_data($sql3, $this);            
            foreach($resultado3 as $fila3)
            {
                $pdf->MultiCell(20, 0, $fila3['numero'], 'LR', 'L', $fill, 0);
                $pdf->MultiCell(25, 0, $fila3['fecha'], 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila3['denunciantes']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila3['motivo']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila3['ubicacion']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila3['organismos']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila3['documentos_consignados']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(40, 0, $fila3['limitaciones']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(30, 0, $fila3['estado']."\n", 'LR', 'L', $fill, 0);
                $pdf->MultiCell(50, 0, $fila3['observacion']."\n", 'LR', 'L', $fill, 1);
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }            
        }
        $pdf->Output('Listado_de_Actuacion.pdf','D');
    }
}
?>