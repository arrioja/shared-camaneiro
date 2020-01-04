<?php
require('/var/www/tcpdf/tcpdf.php');
class listadoporpartida extends TPage
{
    public function onload($param)
    {
         parent::onLoad($param);
         if(!$this->IsPostBack)
         {            
            $sql="select distinct ano from bienes.consumibles_entregados";
            $resultado=cargar_data($sql, $this);
            $this->ddl1->DataSource=$resultado;
            $this->ddl1->dataBind();            
         }
    }
    public function listar_ano($sender, $param)
    {
        $ano=$this->ddl1->text;
        $this->listar($ano, $sql);
        $sql2="select distinct partida from bienes.consumibles_entregados where(ano='$ano')";
        $resultado2=cargar_data($sql2, $this);
        $this->ddl2->DataSource=$resultado2;
		$this->ddl2->dataBind();
    }
    public function listar_partida($sender, $param)
    {
        $ano=$this->ddl1->text;
        $partida=$this->ddl2->text;
        $this->listar($ano, $partida, $sql);
        $sql3="select distinct generica from bienes.consumibles_entregados where(ano='$ano' and partida='$partida')";
        $resultado3=cargar_data($sql3, $this);
        $this->ddl3->DataSource=$resultado3;
		$this->ddl3->dataBind();        
    }
    public function listar_generica($sender, $param)
    {
        $ano=$this->ddl1->text;
        $partida=$this->ddl2->text;
        $generica=$this->ddl3->text;
        $this->listar($ano, $partida, $generica, $sql);
        $sql4="select distinct especifica from bienes.consumibles_entregados where(ano='$ano' and partida='$partida' and generica='$generica')";
        $resultado4=cargar_data($sql4, $this);
        $this->ddl4->DataSource=$resultado4;
		$this->ddl4->dataBind();
    }
    public function listar_especifica($sender, $param)
    {
        $ano=$this->ddl1->text;
        $partida=$this->ddl2->text;
        $generica=$this->ddl3->text;
        $especifica=$this->ddl4->text;
        $this->listar($ano, $partida, $generica, $especifica, $sql);        
        $sql5="select distinct subespecifica from bienes.consumibles_entregados where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica')";
        $resultado5=cargar_data($sql5, $this);
        $this->ddl5->DataSource=$resultado5;
		$this->ddl5->dataBind();
    }
    public function listar_subespecifica($sender, $param)
    {
        $ano=$this->ddl1->text;
        $partida=$this->ddl2->text;
        $generica=$this->ddl3->text;
        $especifica=$this->ddl4->text;
        $subespecifica=$this->ddl5->text;
        $this->listar($ano, $partida, $generica, $especifica, $subespecifica, $sql);                
    }
    public function listar($sender, $param)
    {
        $ano=$this->ddl1->text;
        $partida=$this->ddl2->text;
        $generica=$this->ddl3->text;
        $especifica=$this->ddl4->text;
        $subespecifica=$this->ddl5->text;
        if($ano!='')
        {
            $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d
            where(ano='$ano' and ce.direccion_entrega= d.codigo)";
            $resultado6=cargar_data($sql6, $this);
            $this->dg1->DataSource=$resultado6;
            $this->dg1->dataBind();
        }
        if($ano!='' and $partida!='')
        {
            $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d
            where(ano='$ano' and partida='$partida' and ce.direccion_entrega= d.codigo)";
            $resultado6=cargar_data($sql6, $this);
            $this->dg1->DataSource=$resultado6;
            $this->dg1->dataBind();
        }
        if($ano!='' and $partida!='' and $generica!='')
        {
            $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d
            where(ano='$ano' and partida='$partida' and generica='$generica' and ce.direccion_entrega= d.codigo)";
            $resultado6=cargar_data($sql6, $this);
            $this->dg1->DataSource=$resultado6;
            $this->dg1->dataBind();
        }
        if($ano!='' and $partida!='' and $generica!='' and $especifica!='')
        {
            $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d
            where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and ce.direccion_entrega= d.codigo)";
            $resultado6=cargar_data($sql6, $this);
            $this->dg1->DataSource=$resultado6;
            $this->dg1->dataBind();
        }
        if($ano!='' and $partida!='' and $generica!='' and $especifica!=''and $subespecifica!='')
        {
            $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d
            where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and subespecifica='$subespecifica' and ce.direccion_entrega= d.codigo)";
            $resultado6=cargar_data($sql6, $this);
            $this->dg1->DataSource=$resultado6;
            $this->dg1->dataBind();
        }        
    }
    public function listarconfecha($sender, $param)
    {        
            $ano=$this->ddl1->text;
            $partida=$this->ddl2->text;
            $generica=$this->ddl3->text;
            $especifica=$this->ddl4->text;
            $subespecifica=$this->ddl5->text;
            $inicio=cambiaf_a_mysql($this->dp_ini->text);
            $fin=cambiaf_a_mysql($this->dp_fin->text);
            if($ano!='')
            {
                $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";
                $resultado2=cargar_data($sql2, $this);
                $this->dg1->DataSource=$resultado2;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='')
            {
                $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";
                $resultado2=cargar_data($sql2, $this);
                $this->dg1->DataSource=$resultado2;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='' and $generica!='')
            {
                $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";
                $resultado2=cargar_data($sql2, $this);
                $this->dg1->DataSource=$resultado2;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='' and $generica!='' and $especifica!='')
            {
                $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";
                $resultado2=cargar_data($sql2, $this);
                $this->dg1->DataSource=$resultado2;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='' and $generica!='' and $especifica!=''and $subespecifica!='')
            {
                $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and subespecifica='$subespecifica' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";
                $resultado2=cargar_data($sql2, $this);
                $this->dg1->DataSource=$resultado2;
                $this->dg1->dataBind();
            }
    }
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
        $ano=$this->ddl1->text;
        $partida=$this->ddl2->text;
        $generica=$this->ddl3->text;
        $especifica=$this->ddl4->text;
        $subespecifica=$this->ddl5->text;
        $info_adicional= "Listado de Consumibles por Partida"."\n"."Desde: ".$this->dp_ini->text." Hasta: ".$this->dp_fin->text.
                         "\n"."Partida: ".$ano.'-'.$partida.'-'.$generica.'-'.$especifica.'-'.$subespecifica;
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->AddPage();
        $pdf->Ln(10);        
        $pdf->SetFont('helvetica','B',14);        
        if($this->dp_ini->text=="" and $this->dp_fin->text=="")//si las fechas estan vacias
        {
            $ano=$this->ddl1->text;
            $partida=$this->ddl2->text;
            $generica=$this->ddl3->text;
            $especifica=$this->ddl4->text;
            $subespecifica=$this->ddl5->text;            
            if($ano!='')
            {
                $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and ce.direccion_entrega= d.codigo)";
                $resultado6=cargar_data($sql6, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();                
            }
            if($ano!='' and $partida!='')
            {
                $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and ce.direccion_entrega= d.codigo)";
                $resultado6=cargar_data($sql6, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();                
            }
            if($ano!='' and $partida!='' and $generica!='')
            {
                $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and ce.direccion_entrega= d.codigo)";
                $resultado6=cargar_data($sql6, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();                
            }
            if($ano!='' and $partida!='' and $generica!='' and $especifica!='')
            {
                $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and ce.direccion_entrega= d.codigo)";
                $resultado6=cargar_data($sql6, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();                
            }
            if($ano!='' and $partida!='' and $generica!='' and $especifica!=''and $subespecifica!='')
            {
                $sql6="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and subespecifica='$subespecifica' and ce.direccion_entrega= d.codigo)";
                $resultado6=cargar_data($sql6, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();                
            }            
            $pdf->Cell(25, 0, 'fecha', 0, 0, 'L');
            $pdf->Cell(110, 0, 'direccion', 0, 0, 'L');
            $pdf->Cell(25, 0, 'cantidad', 0, 0, 'L');
            $pdf->Cell(100, 0, 'descripcion', 0, 1, 'L');
            $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
            $fill=0;
            foreach($resultado6 as $fila6)
            {
                $pdf->SetFont('helvetica','',12);
                $fecha_nueva=cambiaf_a_normal($fila6['f_entrega']);
                $pdf->Cell(25, 0, $fecha_nueva, 'L', 0, 'L', $fill);
                $pdf->Cell(110, 0, $fila6['nombre_completo'], 'LR', 0, 'L', $fill);
                $pdf->Cell(25, 0, $fila6['cantidad'], 'LR', 0, 'L', $fill);
                $pdf->Cell(100, 0, $fila6['descripcion'], 'LR', 1, 'L', $fill);                
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }
            $pdf->Output('Listado_por_Partida.pdf','D');
        }
        else// si las fechas estan llenas
        {
            $ano=$this->ddl1->text;
            $partida=$this->ddl2->text;
            $generica=$this->ddl3->text;
            $especifica=$this->ddl4->text;
            $subespecifica=$this->ddl5->text;                    
            $inicio=cambiaf_a_mysql($this->dp_ini->text);
            $fin=cambiaf_a_mysql($this->dp_fin->text);
            if($ano!='')
            {
                $sql7="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";
                $resultado6=cargar_data($sql7, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='')
            {
                $sql7="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";                                
                $resultado6=cargar_data($sql7, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='' and $generica!='')
            {
                $sql7="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";
                $resultado6=cargar_data($sql7, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='' and $generica!='' and $especifica!='')
            {
                $sql7="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";                
                $resultado6=cargar_data($sql7, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();
            }
            if($ano!='' and $partida!='' and $generica!='' and $especifica!=''and $subespecifica!='')
            {
                $sql7="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d
                where(ano='$ano' and partida='$partida' and generica='$generica' and especifica='$especifica' and subespecifica='$subespecifica' and ce.direccion_entrega= d.codigo and f_entrega>='$inicio' and f_entrega<='$fin')";                
                $resultado7=cargar_data($sql7, $this);
                $this->dg1->DataSource=$resultado6;
                $this->dg1->dataBind();
            }            
            $resultado7=cargar_data($sql7, $this);
            $inicio=cambiaf_a_normal($inicio);
            $fin=cambiaf_a_normal($fin);            
            $pdf->Cell(25, 0, 'fecha', 0, 0, 'L');
            $pdf->Cell(110, 0, 'direccion', 0, 0, 'L');
            $pdf->Cell(25, 0, 'cantidad', 0, 0, 'L');
            $pdf->Cell(100, 0, 'descripcion', 0, 1, 'L');
            $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
            $fill=0;
            foreach($resultado7 as $fila7)
            {                
                $pdf->SetFont('helvetica','',12);
                $fecha_nueva=cambiaf_a_normal($fila7['f_entrega']);                
                $pdf->Cell(25, 0, $fecha_nueva, 'L', 0, 'L', $fill);
                $pdf->Cell(110, 0, $fila7['nombre_completo'], 'LR', 0, 'L', $fill);
                $pdf->Cell(25, 0, $fila7['cantidad'], 'LR', 0, 'LR', $fill);
                $pdf->Cell(100, 0, $fila7['descripcion'], 'LR', 1, 'L', $fill);                
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }
            $pdf->Output('Listado_por_Partida','D');
        }        
    }
}
?>