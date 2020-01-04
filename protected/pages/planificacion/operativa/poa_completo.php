<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página permite al usuario poder visualizar el POA completo,
 *              desde sus objetivos Operativos, Los Objetivos Específicos, las
 *              actividades planificadas para los mismos y sus respectivos avances.
 *****************************************************  FIN DE INFO
*/
class poa_completo extends TPage
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

              $todos = array('codigo'=>'***', 'nombre'=>'TODAS LAS DIRECCIONES');
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

        $opcion_adicional = array('codigo'=>'X', 'nombre'=>'Seleccione');
        array_unshift($resultado, $opcion_adicional);

        $this->drop_plan_operativo->Datasource = $resultado;
        $this->drop_plan_operativo->dataBind();
    }

    public function actualizar_listado ($sender)
    {
      $cod_organizacion = usuario_actual('cod_organizacion');
      $cod_plan = $this->drop_plan->SelectedValue;
      $cod_plan_operativo = $this->drop_plan_operativo->SelectedValue;
      $cod_direccion = $this->drop_direcciones->SelectedValue;
      if ($cod_plan_operativo == "X")
      { // si no se selecciono ningun plan, se vacia el listado
          $this->Repeater->DataSource=null;
          $this->Repeater->dataBind();
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
                order by cod_direccion, cod_objetivo_estrategico, cod_objetivo_operativo";
          $resultado=cargar_data($sql,$sender);
          $this->Repeater->DataSource=$resultado;
          $this->Repeater->dataBind();
      }
    }


    public function actualizar_listado_2do_nivel ($sender, $param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
              $cod_organizacion = usuario_actual('cod_organizacion');
              $cod_plan = $this->drop_plan->SelectedValue;
              $cod_plan_operativo = $this->drop_plan_operativo->SelectedValue;
              $cod_objetivo_operativo = $item->cod_obj_ope->Text;
              if ($cod_plan_operativo == "X")
              { // si no se selecciono ningun plan, se vacia el listado
                  $item->Repeater2->DataSource=null;
                  $item->Repeater2->dataBind();
              }
              else
              {
                  $sql="select * from planificacion.objetivos_especificos
                               where ((cod_organizacion = '$cod_organizacion') and
                                      (cod_objetivo_operativo = '$cod_objetivo_operativo') and
                                     (cod_plan_operativo = '$cod_plan_operativo'))
                        order by cod_objetivo_operativo";
                  $resultado=cargar_data($sql,$sender);
                  $item->Repeater2->DataSource=$resultado;
                  $item->Repeater2->dataBind();
              }

        }
    }


    public function actualizar_listado_3er_nivel ($sender, $param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
              $cod_organizacion = usuario_actual('cod_organizacion');
              $cod_plan = $this->drop_plan->SelectedValue;
              $cod_plan_operativo = $this->drop_plan_operativo->SelectedValue;
              $cod_objetivo_especifico = $item->cod_obj_espe->Text;
              if ($cod_plan_operativo == "X")
              { // si no se selecciono ningun plan, se vacia el listado
                  $item->Repeater3->DataSource=null;
                  $item->Repeater3->dataBind();
              }
              else
              {
                  $sql="select * from planificacion.actividades
                               where ((cod_objetivo_especifico = '$cod_objetivo_especifico') and
                                     (cod_plan_operativo = '$cod_plan_operativo'))
                        order by cod_actividad";
                  $resultado=cargar_data($sql,$sender);
                  $item->Repeater3->DataSource=$resultado;
                  $item->Repeater3->dataBind();
              }

        }
    }

/* Para dar formato y Color al repeater que muestra los objetivos Operativos */
    public function Item_created_repeater1($sender,$param)
    {
        static $itemIndex=0;
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->Row->BackColor=$itemIndex%2 ? "#BFCFFF" : "#E6ECFF";        
            $itemIndex++;
        }
    }

/* Para dar formato y Color al repeater que muestra los objetivos específicos */
    public function Item_created_repeater2($sender,$param)
    {
        static $itemIndex=0;
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->Row2->BackColor=$itemIndex%2 ? "#ffecdd" : "#ffd2ad";
            $itemIndex++;
        }
    }

/* Para dar formato y Color al repeater que muestra los objetivos específicos */
    public function Item_created_repeater3($sender,$param)
    {
        static $itemIndex=0;
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->Row3->BackColor=$itemIndex%2 ? "#f8ffc1" : "#f5ff9c";
            $itemIndex++;
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

/* función para imprimir el listado de Planes Estratégicos*/
    public function imprimir_listado($sender, $param)
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
      }


        $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
        $nombre_direccion = $resultado_drop[2]; // se extrae el texto seleccionado

        $resultado_drop = obtener_seleccion_drop($this->drop_plan_operativo);
        $nombre_plan = $resultado_drop[2]; // se extrae el texto seleccionado



        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalles del Plan: ".$nombre_plan."\n".
                             "Dirección: ".$nombre_direccion.", Fecha:".date("d/m/Y");
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
            $pdf->SetTitle('Detalles del Plan: '.$nombre_plan);
            $pdf->SetSubject('Detalles del Plan Operativo Anual');

            $pdf->AddPage();

            $listado_header = array('Objetivos', 'Estatus');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Detalles de Objetivos Operativos/Especificos/Actividades del POA", 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(160, 25);
            $w2 = array(5, 155, 25);
            $w3 = array(10, 150, 25);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 1;
            $fill2 = 0;
            $fill3 = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            //se imprime el primer nivel (Objetivos Operativos)
            foreach($resultado_rpt as $row) {
                $pdf->SetFillColor(224, 235, 255);
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['nombre'], $borde, 0, 'L', $fill);
                $pdf->Cell($w[1], 6, $row['porcentaje_completo']." %", $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $pdf->Ln();
                $fill=!$fill;


                // Se imprime el Segundo Nivel (Objetivos Específicos)
                $cod_objetivo_operativo = $row['cod_objetivo_operativo'];
                $sql2="select * from planificacion.objetivos_especificos
                               where ((cod_organizacion = '$cod_organizacion') and
                                      (cod_objetivo_operativo = '$cod_objetivo_operativo') and
                                     (cod_plan_operativo = '$cod_plan_operativo'))
                        order by cod_objetivo_operativo";
                $resultado2=cargar_data($sql2,$sender);

//                $ultimo_elemento2 = end($resultado2);
                foreach($resultado2 as $row2)
                {
    //                if ($row2 == $ultimo_elemento2) {$borde="LRB";}

                    // se encuentra en comentario porque creo que se ve bien si no lo intercalo
                    //$fill2 == 0 ? $pdf->SetFillColor(255, 210, 173) : $pdf->SetFillColor(255, 236, 221);

                    $pdf->SetFillColor(255, 210, 173);
                    $pdf->Cell($w2[0], 6, '', 'L', 0, 'L', 1);
                    $pdf->Cell($w2[1], 6, $row2['nombre'], 'R', 0, 'L', 1);
                    $pdf->Cell($w2[2], 6, $row2['porcentaje_completo']." %", $borde, 0, 'R', 1);
                    $pdf->SetTextColor(0); // iniciamos con el color negro
                    $pdf->Ln();
                    $fill2=!$fill2;








                    // Se imprime el Tercer Nivel (Actividades)
                    $cod_objetivo_especifico = $row2['cod_objetivo_especifico'];
                    $sql3="select * from planificacion.actividades
                               where ((cod_objetivo_especifico = '$cod_objetivo_especifico') and
                                     (cod_plan_operativo = '$cod_plan_operativo'))
                        order by cod_actividad";
                    $resultado3=cargar_data($sql3,$sender);
    //                $ultimo_elemento2 = end($resultado2);
                    foreach($resultado3 as $row3)
                    {
        //                if ($row2 == $ultimo_elemento2) {$borde="LRB";}
                        $fill3 == 0 ? $pdf->SetFillColor(248, 255, 193) : $pdf->SetFillColor(245, 255, 156);
                        $pdf->Cell($w3[0], 6, '', 'L', 0, 'L', 1);
                        $pdf->Cell($w3[1], 6, $row3['nombre'].", ".$row3['dias']." días, (".cambiaf_a_normal($row3['fecha_inicio'])." al ".cambiaf_a_normal($row3['fecha_fin']).")", 'R', 0, 'L', 1);
                        $pdf->Cell($w3[2], 6, $row3['estatus'], $borde, 0, 'C', 1);
                        $pdf->SetTextColor(0); // iniciamos con el color negro
                        $pdf->Ln();
                        $fill3=!$fill3;

                    }
                }
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Output("poa_completo.pdf",'D');
        }
    }


}

?>
