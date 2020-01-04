<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra un listado de los Objetivos Específicos asociados a un
 *              Objetivo Operativo de un Plan Operativo Anual.
 *****************************************************  FIN DE INFO
*/
class listar_objetivos extends TPage
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

              $cedula = usuario_actual('cedula');
              $resultado = vista_usuario($cedula,$cod_organizacion,'D',$this);

              $todos = array('codigo'=>'X', 'nombre'=>'TODAS LAS DIRECCIONES');
              array_unshift($resultado, $todos);

              $this->drop_direcciones->DataSource=$resultado;
              $this->drop_direcciones->dataBind();
          }
    }


    public function actualizar_listado_plan_operativo($sender,$param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $cod_plan_estrategico = $this->drop_plan->SelectedValue;
        $sql="Select po.cod_plan_operativo, po.nombre
            From planificacion.planes_operativos po
            Where (po.cod_organizacion = '$cod_organizacion') and
                  (po.cod_plan_estrategico = '$cod_plan_estrategico')";
        $resultado=cargar_data($sql,$this);

        $opcion_adicional = array('cod_plan_operativo'=>'X', 'nombre'=>'Seleccione');
        array_unshift($resultado, $opcion_adicional);

        $this->drop_plan_operativo->Datasource = $resultado;
        $this->drop_plan_operativo->dataBind();
    }

    public function actualizar_obj_operativo ($sender)
    {
      $cod_organizacion = usuario_actual('cod_organizacion');
      $cod_plan = $this->drop_plan->SelectedValue;
      $cod_plan_operativo = $this->drop_plan_operativo->SelectedValue;
      $cod_direccion = $this->drop_direcciones->SelectedValue;
      if ($cod_plan_operativo == "X")
      { // si no se selecciono ningun plan, se vacia el listado
          $this->DataGrid->DataSource=null;
          $this->DataGrid->dataBind();
      }
      else
      {
          if ($cod_direccion == "***")
              { $adicional = ""; }
          else
            {
               $adicional = "and (cod_direccion  = '$cod_direccion')";
            }
          $sql="select * from planificacion.objetivos_operativos
                       where ((cod_organizacion = '$cod_organizacion')
                             and (cod_plan_estrategico = '$cod_plan') and
                             (cod_plan_operativo = '$cod_plan_operativo') $adicional)
                order by cod_direccion, cod_objetivo_operativo";
          $resultado=cargar_data($sql,$sender);
          $this->drop_obj_ope->DataSource=$resultado;
          $this->drop_obj_ope->dataBind();
      }


    }


    public function actualizar_listado ($sender)
    {
      $cod_organizacion = usuario_actual('cod_organizacion');
      $cod_plan = $this->drop_plan->SelectedValue;
      $cod_plan_operativo = $this->drop_plan_operativo->SelectedValue;
      $cod_obj_operativo = $this->drop_obj_ope->SelectedValue;
      if ($cod_plan_operativo == "X")
      { // si no se selecciono ningun plan, se vacia el listado
          $this->DataGrid->DataSource=null;
          $this->DataGrid->dataBind();
      }
      else
      {

          $sql="select * from planificacion.objetivos_especificos
                       where ((cod_organizacion = '$cod_organizacion') and 
                             (cod_plan_operativo = '$cod_plan_operativo') and
                             (cod_objetivo_operativo = '$cod_obj_operativo'))
                order by cod_objetivo_operativo, cod_objetivo_especifico";
          $resultado=cargar_data($sql,$sender);
          $this->DataGrid->DataSource=$resultado;
          $this->DataGrid->dataBind();
      }

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
        $this->actualizar_listado($sender);
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Página: ');
	}


/* función para imprimir el listado de Planes Estratégicos*/
    public function imprimir_listado($sender, $param)
    {

      $cod_organizacion = usuario_actual('cod_organizacion');
      $cod_plan = $this->drop_plan->SelectedValue;
      $cod_plan_operativo = $this->drop_plan_operativo->SelectedValue;
      $cod_obj_operativo = $this->drop_obj_ope->SelectedValue;
      if ($cod_plan_operativo == "X")
      { // si no se selecciono ningun plan, se vacia el listado
          $this->DataGrid->DataSource=null;
          $this->DataGrid->dataBind();
      }
      else
      {

          $sql="select * from planificacion.objetivos_especificos
                       where ((cod_organizacion = '$cod_organizacion') and
                             (cod_plan_operativo = '$cod_plan_operativo') and
                             (cod_objetivo_operativo = '$cod_obj_operativo'))
                order by cod_objetivo_operativo, cod_objetivo_especifico";
      }


        $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
        $nombre_direccion = $resultado_drop[2]; // se extrae el texto seleccionado

        $resultado_drop = obtener_seleccion_drop($this->drop_plan_operativo);
        $nombre_plan = $resultado_drop[2]; // se extrae el texto seleccionado

        $resultado_drop = obtener_seleccion_drop($this->drop_obj_ope);
        $nombre_obj = $resultado_drop[2]; // se extrae el texto seleccionado


        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Objetivos Específicos del Plan: ".$nombre_plan."\n".
                             "Dirección: ".$nombre_direccion.", Fecha:".date("d/m/Y")."\n".
                             "Objetivos Operativo: ".$nombre_obj;
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
            $pdf->SetTitle('Listado de Objetivos Específicos del Obj. Operativo: '.$nombre_obj);
            $pdf->SetSubject('Listado de Objetivos de Planes Operativos Registrados');

            $pdf->AddPage();

            $listado_header = array('Objetivo Específicos', 'Avance');

 //           $pdf->SetFont('helvetica', '', 14);
//            $pdf->SetFillColor(255, 255, 255);
   //         $pdf->Cell(0, 6, "Listado de Objetivos Específicos de un Obj. Operativo", 0, 1, '', 1);
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
            $pdf->Output("listado_objetivos_especificos.pdf",'D');
        }
    }


}

?>
