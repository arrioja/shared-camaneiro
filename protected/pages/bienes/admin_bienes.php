<?php

class admin_bienes extends TPage
{
    		function createMultiple($link, $array) {
                        $item = $link->Parent->Data;
                        $return = array();
                        foreach($array as $key) {
                              $return[] = $item[$key];
                         }
                         return implode(",", $return);
                }
    public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select * from bienes.bienes_muebles where cod_organizacion='$cod_org' and grupo<>'' and fecha_desincorporacion is NULL";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

  public function filtrar($serder, $param)
    {
     $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
     $sql="select * from bienes.bienes_muebles where cod_organizacion='$cod_org' and grupo<>'' and fecha_desincorporacion is NULL";
        if($this->rad_dir->Checked)
        {
            $cod_direccion = $this->drop_direcciones->SelectedValue;
            if (($cod_direccion == "-1") ||($cod_direccion == "***"))
            $sql="select * from bienes.bienes_muebles where cod_organizacion='$cod_org' and grupo<>'' and fecha_desincorporacion is NULL";
            else
            $sql=$sql." and (cod_direccion  = '$cod_direccion')";
        }
        if ($this->rad_codigo->Checked)
           if ($this->txt_cadena->Text!='')
           {
           $cod=$this->txt_cadena->Text;
           $sql="select * from bienes.bienes_muebles where cod_organizacion='$cod_org' and grupo<>'' and codigo like '$cod%'";
           }
   /*     if($this->rad_clas->Checked)
           if ($this->txt_cadena->Text!='')
           {
           $cod=$this->txt_cadena->Text;
           $sql="select * from bienes.bienes_muebles where cod_organizacion='$cod_org' and codigo like '%$cod'";
           }

*/
        $resultado=cargar_data($sql,$this);
    return $resultado;
    }

public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
        $this->DataGrid->DataSource=$this->filtrar($serder, $param);
		$this->DataGrid->dataBind();


        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $resultado = cargar_direcciones($cod_org,'D',$this);
        $todos = array('codigo'=>'***', 'nombre'=>'TODAS LAS DIRECCIONES');

        array_unshift($resultado, $todos);

              // Se enlaza el nuevo arreglo con el listado de Direcciones
              $this->drop_direcciones->DataSource=$resultado;
              $this->drop_direcciones->dataBind();

        }

   }


        public function cargar_direcciones()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select * from organizacion.direcciones where codigo_organizacion='$cod_org'";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }


     public function actualiza_listado()
    {

        $this->DataGrid->DataSource=$this->filtrar($serder, $param);
		$this->DataGrid->dataBind();
    }
     public function busc_codigo()
    {

        $this->DataGrid->DataSource=$this->filtrar($serder, $param);
		$this->DataGrid->dataBind();
    }

   public function chequear($sender,$param)
    {
    if($this->rad_codigo->Checked||$this->rad_clas->Checked)
        {
        $this->drop_direcciones->Visible="False";
        $this->txt_cadena->Visible="True";
        $this->btn_buscar->Visible="True";
        }
    if($this->rad_dir->Checked)
        {
        $this->drop_direcciones->Visible="True";
        $this->txt_cadena->Visible="False";
        $this->btn_buscar->Visible="False";
        }

    }

    public function detalle_bien($sender,$param)
    {

   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $id=$datos[0];//id
   $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $this->Response->redirect($this->Service->constructUrl('bienes.detalle_bien',array('id'=>$id)));

    }

    public function movimientos($sender,$param)
    {

   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $id=$datos[0];//id
   //$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $this->Response->redirect($this->Service->constructUrl('bienes.movimientos',array('id'=>$id)));

    }

    public function desincorporacion($sender,$param)
    {

   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $id=$datos[0];//id
   //$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $this->Response->redirect($this->Service->constructUrl('bienes.movimientos.desincorporacion',array('id'=>$id)));

    }

    public function eliminar($sender,$param)
    {

    $id=$sender->CommandParameter;
    $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

    $sql="delete from bienes.bienes_muebles where id='$id'";
    $resultado=modificar_data($sql,$this);
    $this->DataGrid->DataSource=$this->filtrar($serder, $param);
    $this->DataGrid->dataBind();
    }



 public function imprimir_listado($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');
        //$sql="select * from planificacion.planes_estrategicos
                   //where cod_organizacion = '$cod_organizacion' order by ano_inicio, ano_fin";
        $resultado_rpt=$this->filtrar($serder, $param);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $array_direccion=obtener_seleccion_drop($this->drop_direcciones);
            $direccion=$array_direccion[2];
            
            $info_adicional= "Listado de Bienes Muebles $direccion\n".
                             "para la fecha: ".date("d/m/Y");
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Listado de Bienes Muebles Registrados');
            $pdf->SetSubject('Listado de Bienes Muebles');

            $pdf->AddPage();

            $listado_header = array('G', 'SG', 'S','Cant','Serial','Código', 'Descripción','Valor' );

            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B',8);
            // Header
            $w = array(7, 7, 7, 9,20,20,105,15);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',7);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->MultiCell($w[0], 6, $row['grupo'], $borde, 'C', $fill, 0, '', '', true, 0);
                $pdf->MultiCell($w[1], 6, $row['subgrupo'], $borde, 'C', $fill, 0, '', '', true, 0);
                $pdf->MultiCell($w[2], 6, $row['secciones'], $borde, 'C', $fill, 0, '', '', true, 0);
                $pdf->MultiCell($w[3], 6, $row['cantidad'], $borde, 'C', $fill, 0, '', '', true, 0);
                $pdf->MultiCell($w[4], 6, $row['serial'], $borde, 'C', $fill, 0, '', '', true, 0);
                $pdf->MultiCell($w[5], 6, $row['codigo'], $borde, 'C', $fill, 0, '', '', true, 0);
                $pdf->SetFont('','',5);
                $pdf->MultiCell($w[6], 6, $row['descripcion'], $borde, 'JL', $fill, 0, '', '', true, 0);
                $pdf->SetFont('','',7);
                $pdf->MultiCell($w[7], 6, $row['precio_incorporacion'], $borde, 'R', $fill, 0, '', '', true, 0);
                
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Output("listado_bienes_muebles.pdf",'D');
        }
    }

    public function editItem($sender,$param)
    {$this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
       $this->DataGrid->DataSource=$this->filtrar($serder, $param);
       $this->DataGrid->dataBind();

    }

    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created
        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->grupo->TextBox->Columns=2;
            $item->subgrupo->TextBox->Columns=2;
            $item->secciones->TextBox->Columns=2;
            $item->cantidad->TextBox->Columns=2;
            $item->serial->TextBox->Columns=4;
            $item->grupo->TextBox->ReadOnly='True';
            $item->subgrupo->TextBox->ReadOnly='True';
            $item->secciones->TextBox->ReadOnly='True';
            //$item->subgrupo->TextBox->ReadOnly='True';
        }

    }
    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->filtrar($serder, $param);
        $this->DataGrid->dataBind();
    }
    public function saveItem($sender,$param)
    {
        $item=$param->Item;

        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        $descripcion=$item->descripcion->TextBox->Text;
        $serial=$item->serial->TextBox->Text;
        $valor=$item->valor->TextBox->Text;
        $codigo=$item->codigo->TextBox->Text;


		$sql="UPDATE bienes.bienes_muebles set codigo='$codigo',  descripcion='$descripcion', serial='$serial', precio_incorporacion='$valor'  where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->filtrar($serder, $param);
        $this->DataGrid->dataBind();

    }

    public function changePage($sender,$param)
	{
       		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid->DataSource=$this->filtrar($serder, $param);
            $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'P&aacute;gina: ');
	}

public function imprimir_movimientos($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');
        $cod_bien=$sender->CommandParameter;

        $sql="select m.fecha,m.motivo,d.nombre_completo,c.descripcion,bm.descripcion as descripcion2 from bienes.movimientos_bienes m
            inner join bienes.conceptos c on m.tipo_movimiento=c.cod
            inner join bienes.bienes_muebles bm on bm.codigo=m.cod_bien
            inner join organizacion.direcciones d on m.cod_direccion=d.codigo
             where m.cod_organizacion = '$cod_organizacion' and m.cod_bien='$cod_bien' order by fecha asc";
        $resultado_rpt=cargar_data($sql,$sender);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            $info_adicional= "Listado de Movimientos de Bienes Muebles\n".
                             "para la fecha: ".date("d/m/Y");
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Listado de Movimientos de Bienes Muebles');
            $pdf->SetSubject('Listado de Movimientos de Bienes Muebles');

            $pdf->AddPage();

            $listado_header = array('Dirección', 'Fecha','Descripcion','Motivo');

            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Movimientos de: ".$resultado_rpt[0]['descripcion2']." código: ".$cod_bien, 0, 1, 'C', 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(80,30,60);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',7);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 10, $row['nombre_completo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 10, cambiaf_a_normal($row['fecha']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 10, $row['descripcion'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 10, $row['motivo'], $borde, 0, 'C', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Output("listado_movimientos.pdf",'D');
        }
        else
        {
             /*   $this->LTB->titulo->Text = "Movimientos Bienes";
                $this->LTB->texto->Text = "No hay datos para mostrar";
                $this->LTB->imagen->Imageurl = "imagenes/botones/mal.png";
                $params = array('mensaje');
                $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);*/
        }
    }
}

?>
