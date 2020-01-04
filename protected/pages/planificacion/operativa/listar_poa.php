<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra un listado de los Planes Operativos y su avance
 *              dependiendo de el plan estratégico seleccionado.
 *****************************************************  FIN DE INFO
*/
class listar_poa extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql="Select cod_plan_estrategico, nombre
                    From planificacion.planes_estrategicos
                    Where (cod_organizacion = '$cod_organizacion') and
                          ((estatus = 'ACTIVO') OR (estatus = 'FUTURO'))";
              $resultado=cargar_data($sql,$this);
              $this->drop_plan->Datasource = $resultado;
              $this->drop_plan->dataBind();

          }
    }

    public function actualizar_listado ($sender)
    {
      $cod_organizacion = usuario_actual('cod_organizacion');
      $cod_plan = $this->drop_plan->SelectedValue;

      $sql="select * from planificacion.planes_operativos
                   where ((cod_organizacion = '$cod_organizacion')
                         and (cod_plan_estrategico = '$cod_plan'))
            order by ano desc";
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

	public function changePage($sender,$param)
	{
		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->cargar_listado($sender);
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Página: ');
	}


/* función para imprimir el listado de Planes Operativos*/
    public function imprimir_listado($sender, $param)
    {


      $cod_organizacion = usuario_actual('cod_organizacion');
      $cod_plan = $this->drop_plan->SelectedValue;

      $sql="select * from planificacion.planes_operativos
                   where ((cod_organizacion = '$cod_organizacion')
                         and (cod_plan_estrategico = '$cod_plan'))
            order by ano desc";

        $resultado_drop = obtener_seleccion_drop($this->drop_plan);
        $nombre_plan = $resultado_drop[2]; // se extrae el texto seleccionado

        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Planes Operativos Anuales \n".
                             "del Plan Estratégico: ".$nombre_plan.", Fecha:".date("d/m/Y");
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
            $pdf->SetTitle('Listado de Objetivos de Planes Operativos del Plan '.$nombre_plan);
            $pdf->SetSubject('Listado de Objetivos de Planes Operativos Registrados');

            $pdf->AddPage();

            $listado_header = array('Plan Operativo', 'Progreso');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de Planes Operativos y su avance", 0, 1, '', 1);
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
            $pdf->Output("listado_planes_operativos.pdf",'D');
        }
    }


}

?>
