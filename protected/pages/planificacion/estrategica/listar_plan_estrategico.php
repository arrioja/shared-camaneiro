<?php

class listar_plan_estrategico extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->cargar_listado($this);
          }
    }

    public function cargar_listado ($sender)
    {
      $cod_organizacion = usuario_actual('cod_organizacion');
      $sql="select * from planificacion.planes_estrategicos
                   where cod_organizacion = '$cod_organizacion' order by ano_inicio, ano_fin";
      $resultado=cargar_data($sql,$sender);
      $this->DataGrid->DataSource=$resultado;
      $this->DataGrid->dataBind();
    }

/* Esta funcion se encarga de colocarme en negrita el registro que cumpla con la
 * condicion.
 */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->porcentaje->Text = $item->porcentaje->Text." %";
        }
    }

    public function nuevo_item2($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->porcentaje2->Text = $item->porcentaje2->Text." %";
        }
    }

	public function changePage($sender,$param)
	{
		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->cargar_listado($sender);
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Página: ');
	}


	public function actualiza_listado2($sender,$param)
	{
		//Busqueda de Registros
        $id2=$sender->CommandParameter;

        $sql="select * from planificacion.planes_estrategicos where (id = '$id2')";
        $resultado=cargar_data($sql,$this);

        $cod_plan = $resultado[0]['cod_plan_estrategico'];
        $cod_organizacion = $resultado[0]['cod_organizacion'];

        $sql="select * from planificacion.objetivos_estrategicos
                   where ((cod_organizacion = '$cod_organizacion')
                         and (cod_plan_estrategico = '$cod_plan'))
            order by cod_direccion, cod_objetivo_estrategico";
        $resultado=cargar_data($sql,$sender);
        $this->DataGrid_obj->DataSource=$resultado;
        $this->DataGrid_obj->dataBind();

    }



/* función para imprimir el listado de Planes Estratégicos*/
    public function imprimir_listado($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');

        $sql="select * from planificacion.planes_estrategicos
                   where cod_organizacion = '$cod_organizacion' order by ano_inicio, ano_fin";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Listado de Planes Estratégicos Registrados\n".
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
            $pdf->SetTitle('Listado de Planes Estratégicos Registrados');
            $pdf->SetSubject('Listado de Planes Estratégicos Registrados');

            $pdf->AddPage();

            $listado_header = array('Nombre del Plan', 'Inicio', 'Fin', 'Completado', 'Estatus');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de Planes Estratégicos y su Avance", 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(105, 15, 15, 30, 20);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['nombre'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['ano_inicio'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, $row['ano_fin'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 6, $row['porcentaje_completo']." %", $borde, 0, 'R', $fill);
                $pdf->Cell($w[4], 6, $row['estatus'], $borde, 0, 'C', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Output("listado_planes_estrategicos.pdf",'D');
        }
    }





/* función para imprimir el listado de bjetivos Estratégicos*/
    public function imprimir_objetivos($sender, $param)
    {
		//Busqueda de Registros
        $id2=$sender->CommandParameter;

        $sql="select * from planificacion.planes_estrategicos where (id = '$id2')";
        $resultado=cargar_data($sql,$this);

        $cod_plan = $resultado[0]['cod_plan_estrategico'];
        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $nombre_plan = $resultado[0]['nombre'];


        $sql="select * from planificacion.objetivos_estrategicos
                   where ((cod_organizacion = '$cod_organizacion')
                         and (cod_plan_estrategico = '$cod_plan'))
            order by cod_direccion, cod_objetivo_estrategico";

        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Objetivos del Plan ".$nombre_plan."\n".
                             "Dirección: TODAS, Fecha:".date("d/m/Y");
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
            $pdf->SetTitle('Listado de Objetivos del Plan: '.$nombre_plan);
            $pdf->SetSubject('Listado de Objetivos de Planes Estratégicos Registrados');

            $pdf->AddPage();

            $listado_header = array('Objetivo Estratégico', 'Estatus');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de objetivos estratégicos y su avance", 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(165, 20);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['nombre'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[1], 6, $row['porcentaje_completo']." %", $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Output("listado_objetivos_estrategicos.pdf",'D');
        }
    }

}

?>
