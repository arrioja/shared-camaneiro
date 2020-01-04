<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Muestra una ventana para la inclusión de actividades dentro de
 *              uno de los objetivos específicos del plan Operativo. Ejemplo
 *              de actividades es: En un Obj Especifico que seria Realizar una
 *              Auditoria a un ente X, las actividades serian, planificacion,
 *              ejecucion, elaboracion de informe, etc.
 *****************************************************  FIN DE INFO
*/

class incluir_actividades extends TPage
{
    public function onLoad($param)
    { 
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql="Select po.cod_plan_operativo, po.nombre
                    From planificacion.planes_operativos po, planificacion.planes_estrategicos pe
                    Where (po.cod_organizacion = '$cod_organizacion') and
                          (po.cod_plan_estrategico = pe.cod_plan_estrategico) and
                          ((pe.estatus = 'ACTIVO') OR (pe.estatus = 'FUTURO'))";
              $resultado=cargar_data($sql,$this);
              $this->drop_plan->Datasource = $resultado;
              $this->drop_plan->dataBind();

              $cedula = usuario_actual('cedula');
              $resultado = vista_usuario($cedula,$cod_organizacion,'D',$this);

              // Se enlaza el nuevo arreglo con el listado de Direcciones
              $this->drop_direcciones->DataSource=$resultado;
              $this->drop_direcciones->dataBind();

              $dias = generar_rango(1,365,3);
              $this->drop_dias->DataSource=$dias;
              $this->drop_dias->dataBind();
          }
    }

/* este procedimiento actualiza el drop de los objetivos especificos a los cuales
 * se le esta incluyendo las actividades
 */
	public function actualizar_obj_estrategicos($sender, $param)
	{
        $cod_organizacion = usuario_actual('cod_organizacion');
        $cod_plan_operativo = $this->drop_plan->SelectedValue;
        $cod_direccion = $this->drop_direcciones->SelectedValue;

        $sql="Select oe.cod_objetivo_especifico, oe.nombre
            From planificacion.objetivos_especificos oe, planificacion.objetivos_operativos oo
            Where (oe.cod_organizacion = '$cod_organizacion') and
                  (oo.cod_direccion = '$cod_direccion') and
                  (oo.cod_objetivo_operativo = oe.cod_objetivo_operativo) and
                  (oe.cod_plan_operativo = '$cod_plan_operativo')";
        $resultado=cargar_data($sql,$this);

        $todos = array('cod_objetivo_especifico'=>'X', 'nombre'=>'Seleccione');
        array_unshift($resultado, $todos);

        $this->drop_objetivo->Datasource = $resultado;
        $this->drop_objetivo->dataBind();
    }


/* Se actualizan las fechas de fin dependiendo del numero de dias habiles seleccionados*/
	public function actualizar_dias_y_fechas($sender, $param)
	{
        $fecha_inicio = $this->txt_fecha_inicio->Text;
        $numd = $this->drop_dias->SelectedValue;
        if (($fecha_inicio == "") || ($numd == "X"))
        {
            $this->lbl_hasta->Text = "??/??/????";
        }
        else
        {
            $dias_feriados = dias_feriados($sender);
            $fecha_fin  = suma_dias_habiles($fecha_inicio, $numd, $dias_feriados, $sender);
            $this->lbl_hasta->Text = $fecha_fin;
        }
    }

/* Se actualiza el listado de las actvidades relacionadas con el objetivo especifico*/
	public function actualizar_listado_actividades($sender, $param)
	{
        $cod_plan_operativo = $this->drop_plan->SelectedValue;
        $cod_objetivo_especifico = $this->drop_objetivo->SelectedValue;
        
      if ($cod_plan_operativo == "X")
      { // si no se selecciono ningun plan, se vacia el listado
          $this->DataGrid->DataSource=null;
          $this->DataGrid->dataBind();
      }
      else
      {
        $sql="Select *
            From planificacion.actividades a
            Where (a.cod_objetivo_especifico = '$cod_objetivo_especifico') and
                  (a.cod_plan_operativo = '$cod_plan_operativo')";
          $resultado=cargar_data($sql,$sender);
          $this->DataGrid->DataSource=$resultado;
          $this->DataGrid->dataBind();
      }
    }


/* Elimina una actividad*/
	public function eliminar_actividad($sender, $param)
	{
        $id = $sender->CommandParameter;
        $sql="delete from planificacion.actividades where (id = '$id')";
        $resultado=modificar_data($sql,$sender);
        $this->actualizar_listado_actividades($sender, $param);
       // $this->actualizar_molestias($sender, $param);
       // $this->actualizar_listado_responsabilidades($sender);
    }

    public function formato($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            switch ($item->estatus->Text) {
                case "Pendiente":
                    $item->detalle->estatus_img->ImageUrl = "imagenes/iconos/led_00.png";
                    break;
                case "En Proceso":
                    $item->detalle->estatus_img->ImageUrl = "imagenes/iconos/led_01.png";
                    break;
                case "Finalizado":
                    $item->detalle->estatus_img->ImageUrl = "imagenes/iconos/led_02.png";
                    break;
                case "No Ejecutado":
                    $item->detalle->estatus_img->ImageUrl = "imagenes/iconos/led_03.png";
                    break;
                default:
                    break;
            }
            // marca el registro como con atraso
            if (($item->estatus->Text == "Pendiente") && ($item->fecha_fin->Text < date("Y-m-d")))
            { $item->BackColor="#ff5454"; }

            // marca el registro como fecha actual
            if (($item->fecha_inicio->Text <= date("Y-m-d")) && ($item->fecha_fin->Text >= date("Y-m-d")))
            { $item->BackColor="#ffee63"; }

            $item->fecha_inicio->Text = cambiaf_a_normal($item->fecha_inicio->Text);
            $item->fecha_fin->Text = cambiaf_a_normal($item->fecha_fin->Text);
        }
    }

	public function limpiar_controles($sender, $param)
	{
        $this->txt_nombre_completo->Text="";
        $this->txt_fecha_inicio->Text = "";
        $this->lbl_hasta->Text = "??/??/????";
        $this->drop_dias->SelectedIndex = 0;
    }

	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $cod_plan_operativo = $this->drop_plan->SelectedValue;
            $cod_objetivo_especifico = $this->drop_objetivo->SelectedValue;

            $nombre = $this->txt_nombre_completo->Text;
            $dias =  $this->drop_dias->SelectedValue;
            $fecha_inicio = cambiaf_a_mysql($this->txt_fecha_inicio->Text);
            $fecha_fin = cambiaf_a_mysql($this->lbl_hasta->Text);

            $codigo = proximo_numero("planificacion.actividades","cod_actividad",null,$sender);
            $codigo = rellena($codigo,6,"0");

            /* Se Inicia el procedimiento para guardar en la base de datos  */
            $sql="insert into planificacion.actividades
                    (cod_plan_operativo, cod_objetivo_especifico,
                     cod_actividad, nombre, dias, fecha_inicio, fecha_fin)
                  value ('$cod_plan_operativo','$cod_objetivo_especifico',
                         '$codigo','$nombre','$dias','$fecha_inicio','$fecha_fin')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido la actividad: ".$titulo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            $this->actualizar_listado_actividades($sender, $param);
            $this->limpiar_controles($sender, $param);
        }
	}


/* función para imprimir el listado */
    public function btn_imprimir_click($sender, $param)
    {

      $cod_plan_operativo = $this->drop_plan->SelectedValue;
      $cod_objetivo_especifico = $this->drop_objetivo->SelectedValue;

      if ($cod_plan_operativo == "X")
      { // si no se selecciono ningun plan, se vacia el listado
          $this->DataGrid->DataSource=null;
          $this->DataGrid->dataBind();
      }
      else
      {
        $sql="Select *
            From planificacion.actividades a
            Where (a.cod_objetivo_especifico = '$cod_objetivo_especifico') and
                  (a.cod_plan_operativo = '$cod_plan_operativo')";
      }

        $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
        $nombre_direccion = $resultado_drop[2]; // se extrae el texto seleccionado

        $resultado_drop = obtener_seleccion_drop($this->drop_plan);
        $nombre_plan = $resultado_drop[2]; // se extrae el texto seleccionado

        $resultado_drop = obtener_seleccion_drop($this->drop_objetivo);
        $nombre_obj = $resultado_drop[2]; // se extrae el texto seleccionado


        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Actividades Planificadas para: ".$nombre_obj."\n".
                             "Dirección: ".$nombre_direccion.", Fecha:".date("d/m/Y")."\n".
                             "Plan Operativo:".$nombre_plan;
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
            $pdf->SetTitle('Actividades Planificadas para un Objetivos Específico');
            $pdf->SetSubject('Actividades Planificadas para un Objetivos Específico');

            $pdf->AddPage();

            $listado_header = array('Descripcion de la actividad','Días','Inicio','Fin','Estatus');

            $pdf->SetFont('helvetica', '', 12);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(105,10,20,20,30);
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
                $pdf->Cell($w[1], 6, $row['dias'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6,cambiaf_a_normal($row['fecha_inicio']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 6,cambiaf_a_normal($row['fecha_fin']), $borde, 0, 'C', $fill);
                $pdf->Cell($w[4], 6, $row['estatus'], $borde, 0, 'C', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Output("listado_actividades.pdf",'D');
        }
    }

}

?>
