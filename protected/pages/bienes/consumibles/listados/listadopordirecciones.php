<?php
require('/var/www/tcpdf/tcpdf.php');
class listadopordirecciones extends TPage
{
    public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            //llena el listbox con las direcciones disponibles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select distinct d.codigo, d.nombre_completo
                from organizacion.direcciones d, bienes.consumibles_entregados ce
                where (d.codigo_organizacion = '$cod_organizacion') and (d.codigo = ce.direccion_entrega)";
            $resultado=cargar_data($sql, $this);
            // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
            $todos = array('codigo'=>'0', 'nombre_completo'=>'TODAS LAS DIRECCIONES');
            array_unshift($resultado, $todos);
            $this->ddl1->DataSource=$resultado;
            $this->ddl1->dataBind();
        }
    }
    public function listar($sender, $param)
    {   
        $direccion=$this->ddl1->SelectedValue;
        if ($direccion == '0')//todas las direcciones sin fechas
        {
            $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d where (ce.direccion_entrega= d.codigo)";
        }
        else//direcciones especificas sin fechas
        {
            $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d
            where (ce.direccion_entrega='$direccion') and (d.codigo = '$direccion')";
        }
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->DataSource=$resultado2;
		$this->dg1->dataBind();
        return $direccion;
    }
    public function listarconfecha($sender, $param)
    {
        $direccion=$this->ddl1->text;
        $fecha_i=cambiaf_a_mysql($this->dp_ini->text);
        $fecha_f=cambiaf_a_mysql($this->dp_fin->text);        
        if($direccion == '0')//todas las direcciones con fechas
        {
            $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                   from bienes.consumibles_entregados ce, organizacion.direcciones d
                   where (ce.direccion_entrega= d.codigo) and ((f_entrega>='$fecha_i')and(f_entrega<='$fecha_f'))";
        }
        else// direccion especifica con fechas
        {
            $sql2="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
            from bienes.consumibles_entregados ce, organizacion.direcciones d
            where (ce.direccion_entrega='$direccion') and (d.codigo = '$direccion') and
                  ((f_entrega >= '$fecha_i') and (f_entrega <= '$fecha_f'))";
        }
        $resultado2=cargar_data($sql2, $this);
        $this->dg1->DataSource=$resultado2;
		$this->dg1->dataBind();
    }
/* Esta función da formato al listado */
	public function formatear_listado_entregados($sender, $param)
	{
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
        }
    }
    public function imprimir($sender, $param)
    {
//      $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
        $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
        $direccion=$this->ddl1->SelectedValue;
        if($direccion==0)
        {
            $resultado0[0]['nombre_completo']=" Todas";            
        }        
        else
        {
            $sql0="select nombre_completo from organizacion.direcciones where(codigo='$direccion')";
            $resultado0=cargar_data($sql0, $this);            
        }
        $info_adicional= "Listado de Consumibles por Direcciones"."\n".                         
                         "Direcciones:".$resultado0[0]['nombre_completo']."\n".
                         "Desde: ".$this->dp_ini->text." Hasta: ".$this->dp_fin->text;
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->AddPage();
        $pdf->Ln(10);        
        $pdf->SetFont('helvetica','B',14);
        if($this->dp_ini->text=="" and $this->dp_fin->text=="")//si no hay fechas
        {
            $direccion=$this->ddl1->SelectedValue;
            if ($direccion == '')// vacio
            {
                echo "error, direccion vacia, seleccione una";
            }
            if ($direccion == '0')// todas las direcciones y fechas vacias
            {                
                $sql3="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d where (ce.direccion_entrega= d.codigo)";
                $resultado3=cargar_data($sql3, $this);                
                $pdf->Cell(20, 0, 'fecha', 0, 0, 'L');
                $pdf->Cell(105, 0, 'direccion', 0, 0, 'L');
                $pdf->Cell(25, 0, 'cantidad', 0, 0, 'L');
                $pdf->Cell(70, 0, 'descripcion', 0, 0, 'L');
                $pdf->Cell(40, 0, 'partida', 0, 1, 'L');
                $pdf->SetFont('helvetica','',10);
                $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
                $fill=0;
                foreach($resultado3 as $fila3)
                {
                    $fecha_nueva=cambiaf_a_normal($fila3['f_entrega']);
                    $pdf->Cell(20, 0, $fecha_nueva, 'R', 0, 'L', $fill);
                    $pdf->Cell(105, 0, $fila3['nombre_completo'], 'LR', 0, 'L', $fill);
                    $pdf->Cell(25, 0, $fila3['cantidad'], 'LR', 0, 'L', $fill);
                    $pdf->Cell(70, 0, $fila3['descripcion'], 'LR', 0, 'L', $fill);
                    $pdf->Cell(40, 0, $fila3['concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)'], 'R', 1, 'L', $fill);
                    if ($fill==0)
                    {
                        $fill=1;
                    }
                    else
                    {
                        $fill=0;
                    }
                }
                    $pdf->Output('Listado_por_Direcciones.pdf','D');
            }
            if (($direccion !== '0')and($direccion !== ''))// alguna direccion y fechas vacias
            {
                $sql4="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                from bienes.consumibles_entregados ce, organizacion.direcciones d where (ce.direccion_entrega='$direccion') and
                (d.codigo = '$direccion')";
                $resultado4=cargar_data($sql4, $this);
                //$pdf->Cell(50, 0, 'Direccion: '.$resultado4[0]['nombre_completo'], 0, 1, 'L');
                //$pdf->Cell(50, 0, 'Desde: - Hasta: -', 0, 1, 'L');
                $pdf->Cell(20, 0, 'fecha', 0, 0, 'L');
                $pdf->Cell(105, 0, 'direccion', 0, 0, 'L');
                $pdf->Cell(30, 0, 'cantidad', 0, 0, 'L');
                $pdf->Cell(50, 0, 'descripcion', 0, 0, 'L');
                $pdf->Cell(30, 0, 'partida', 0, 1, 'L');
                $pdf->SetFont('helvetica','',10);
                $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
                $fill=0;
                foreach($resultado4 as $fila4)
                {
                    $fecha_nueva=cambiaf_a_normal($fila4['f_entrega']);
                    $pdf->Cell(20, 0, $fecha_nueva, 'L', 0, 'L', $fill);
                    $pdf->Cell(105, 0, $fila4['nombre_completo'], 'LR', 0, 'L', $fill);
                    $pdf->Cell(30, 0, $fila4['cantidad'], 'LR', 0, 'L', $fill);
                    $pdf->Cell(50, 0, $fila4['descripcion'], 'Lr', 0, 'L', $fill);
                    $pdf->Cell(40, 0, $fila4['concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)'], 'L', 1, 'L', $fill);
                    if ($fill==0)
                    {
                        $fill=1;
                    }
                    else
                    {
                        $fill=0;
                    }
                }
                    $pdf->Output('Listado_por_Direcciones.pdf','D');
            }
        }
        else// si hay fechas
        {            
            $direccion=$this->ddl1->text;
            $fecha_i=cambiaf_a_mysql($this->dp_ini->text);
            $fecha_f=cambiaf_a_mysql($this->dp_fin->text);            
            if($direccion == '0')//todas las direcciones con fechas
            {
                $sql5="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                   from bienes.consumibles_entregados ce, organizacion.direcciones d
                   where (ce.direccion_entrega= d.codigo) and ((f_entrega>='$fecha_i')and(f_entrega<='$fecha_f'))";
            }
            else//alguna direccion con fechas
            {
                $sql5="select ce.*, d.nombre_completo, concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)
                       from bienes.consumibles_entregados ce, organizacion.direcciones d
                       where (ce.direccion_entrega='$direccion') and (d.codigo = '$direccion') and
                       ((f_entrega >= '$fecha_i') and (f_entrega <= '$fecha_f'))";
            }
            $resultado5=cargar_data($sql5, $this);
            //$inicio=cambiaf_a_normal($inicio);
            //$fin=cambiaf_a_normal($fin);            
            $pdf->Cell(20, 0, 'fecha', 0, 0, 'L');
            $pdf->Cell(105, 0, 'direccion', 0, 0, 'L');
            $pdf->Cell(30, 0, 'cantidad', 0, 0, 'L');
            $pdf->Cell(50, 0, 'descripcion', 0, 0, 'L');
            $pdf->Cell(30, 0, 'partida', 0, 1, 'L');
            $pdf->SetFont('helvetica','',10);
            $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
            $fill=0;
            foreach($resultado5 as $fila5)
            {
                $fecha_nueva=cambiaf_a_normal($fila5['f_entrega']);
                $pdf->Cell(20, 0, $fecha_nueva, 'L', 0, 'L', $fill);
                $pdf->Cell(105, 0, $fila5['nombre_completo'], 'LR', 0, 'L', $fill);
                $pdf->Cell(30, 0, $fila5['cantidad'], 'LR', 0, 'L', $fill);
                $pdf->Cell(50, 0, $fila5['descripcion'], 'LR', 0, 'L', $fill);
                $pdf->Cell(40, 0, $fila5['concat(ce.ano, ce.partida, ce.generica, ce.especifica, ce.subespecifica)'], 'R', 1, 'L', $fill);
                if ($fill==0)
                {
                    $fill=1;
                }
                else
                {
                    $fill=0;
                }
            }
            $pdf->Output('Listado_por_Direcciones.pdf','D');
        }        
    }
}
?>