<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Inclusión de nuevo cheque sin orden de pago, solo afecta finanzas
 *****************************************************  FIN DE INFO
*/
class incluir_cheque_solo extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {   
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_codigo_temporal->Text = aleatorio_pagado($this);
              $cod_organizacion = usuario_actual('cod_organizacion');
             
              // para llenar el listado de Beneficiarios
              $this->carga_proveedores();

              // coloca el año presupuestario "Presente"
              $ano_array = ano_presupuesto($cod_organizacion,1,$this);
              $this->lbl_ano->Text = $ano_array[0]['ano'];
              $ano=$ano_array[0]['ano'];

              // coloca la fecha
              $this->lbl_fecha->Text = date("d/m/Y");
              //$this->lbl_total->Text="0.00";
           
        //$this->txt_motivo->Enabled="False";
          // coloca la fecha
          $this->lbl_fecha->Text = date("d/m/Y");

          $this->txt_fecha_cheque->Text = date("d/m/Y");

          $sql="select * from presupuesto.bancos where cod_organizacion='$cod_organizacion'";
          $res_banco=cargar_data($sql,$this);
          $this->drop_banco->DataSource=$res_banco;
          $this->drop_banco->dataBind();

              //------firmas--------------//
           $this->txt_preparado->Text="EP";
           $this->txt_revisado->Text="MN";
           $this->txt_aprobado->Text="JFS";
           $this->txt_auxiliar->Text="IR";
           $this->txt_diario->Text="";
            //------firmas--------------//
          }
    }


    public function carga_proveedores()
    {
      // para llenar el listado de Beneficiarios
      $cod_organizacion = usuario_actual('cod_organizacion');
      $ano = ano_presupuesto($cod_organizacion,1,$this);
      $ano = $ano[0]['ano'];
      $sql2 = "select distinct p.cod_proveedor, CONCAT(p.nombre,' / ',p.rif) as nomb from presupuesto.proveedores p
             
                where p.cod_organizacion = '$cod_organizacion'   order by p.nombre";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_proveedor->Datasource = $datos2;
      $this->drop_proveedor->dataBind();
    }

   public function cargar_cuentas()
    {
      $cod_org = usuario_actual('cod_organizacion');
      $cod_banco=$this->drop_banco->SelectedValue;
      $sql2 = "select * from presupuesto.bancos_cuentas where cod_organizacion='$cod_org' and cod_banco='$cod_banco' ";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_cuentas->Datasource = $datos2;
      $this->drop_cuentas->dataBind();
    }



/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha->Text = cambiaf_a_normal($item->fecha->Text);
            $item->monto->Text = "Bs ".number_format(abs($item->monto->Text), 2, ',', '.');
          
        }
    }





    function incluir_click($sender, $param)
    {
          //if (($this->IsValid)&&( $this->btn_incluir->Enabled))
          if ($this->btn_incluir->Enabled)
        {
            $num_cheque=$this->txt_numero_cheque->Text;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $this->lbl_ano->Text);
            $no_existe_movimientos = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$num_cheque,$criterios_adicionales,$sender);
           
            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $this->lbl_ano->Text,'tipo'=>'R');
            $existe_reverso = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$num_cheque,$criterios_adicionales,$sender);
           
            $banco=$this->drop_banco->SelectedValue;
            $cuenta=$this->drop_cuentas->SelectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano=$this->lbl_ano->Text;
            $monto_total=$this->txt_total->Text;
            
            

             
        
                    $saldo_actual=disponibilidad_bancaria($ano,$cod_organizacion,$banco,$cuenta,$sender);
                 if($saldo_actual>$monto_total)//se verifica disponibilidad en cuenta
                 {

                        if ($no_existe_movimientos==true)// sino existe el numero cheque
                         {

                            $this->btn_incluir->Enabled=false;
                            // captura de datos desde los controles
                            $cod_proveedor = $this->drop_proveedor->SelectedValue;
                            $fecha=cambiaf_a_mysql($this->lbl_fecha->Text);
                            $motivo=$this->txt_motivo->Text;
                            $num_cheque=$this->txt_numero_cheque->Text;
                            $fecha_cheque=cambiaf_a_mysql($this->txt_fecha_cheque->Text);
                           

                                $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                                         'ano' => $ano);
                                $numero_movimiento=proximo_numero("presupuesto.bancos_cuentas_movimientos","cod_movimiento",$criterios_adicionales,$sender);
                                $cod_movimiento=rellena($numero_movimiento,10,"0");

                                
                                //se registra el movimiento en las cuenta bancaria seleccionada
                                registrar_movimiento($ano,$cod_organizacion,$banco,$cuenta,$monto_total,'haber',$motivo,$num_cheque,$this,'CH',$fecha_cheque,$cod_movimiento);
                                //se registra el movimiento de egresos
                                registrar_cheque($ano,$cod_movimiento,'SIN ORDEN',$cod_proveedor,$sender);

                                // Se incluye el rastro en el archivo de bitácora
                                $descripcion_log = "Se ha incluido el cheque sin orden  # ".$num_cheque. " / año: ".$ano.
                                                   " a favor del proveedor Nro: ".$cod_proveedor.
                                                   " por un monto total de Bs. ".$monto_total." Motivo: ".$motivo;
                                inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
                                $this->mensaje->setSuccessMessage($sender, "Cheque Nº $num_cheque guardado exitosamente!!", 'grow');

                        }else{

                            if($error==false)$this->mensaje->setErrorMessage($sender, "Operacion Invalida, Cheque $num_cheque ya registrado!", 'grow');

                         }// fin si existe
                 }else{
                    $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente en cuenta $cuenta !</br> Saldo actual: Bs.".number_format($saldo_actual, 2, ',', '.'), 'grow');
                }//fin si
            
        }//fin si 
    }


/* Esta función imprime el detalle sobre el cheque y sobre el comprobante una vez
 * que se haya guardado el cheque.
 */

    public function imprimir_item($sender, $param)
    {


       $cheque=$this->txt_numero_cheque->Text;
       $cod_proveedor=$this->drop_proveedor->SelectedValue;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $ano = $this->lbl_ano->Text;
       $cuenta=$this->drop_cuentas->SelectedValue;
         //consulto pagos realizados en el año
       $sql = "SELECT m.*,b.nombre as nombre_banco, p.nombre, p.rif FROM presupuesto.bancos_cuentas_movimientos as m
            INNER JOIN presupuesto.bancos as b ON ( b.cod_banco=m.cod_banco)
             JOIN presupuesto.proveedores as p ON (p.cod_proveedor='$cod_proveedor')
            WHERE (  m.referencia='$cheque'
                    AND m.numero_cuenta='$cuenta' AND m.ano='$ano' AND m.tipo='CH')";
        $resultado = cargar_data($sql,$this);
        
        $motivo = $resultado[0]['descripcion'];
        $monto_total = $resultado[0]['haber'];
         $fecha_cheque=cambiaf_a_normal($resultado[0]['fecha']);
        $fecha=cambiaf_a_mysql( $fecha_cheque);
        $fecha=split('[/.-]', $fecha_cheque);
        $dia_mes = $fecha[0]."-".$fecha[1];//dia mes actual
        $ano_cheque = $fecha[2];//año actual
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];
        $banco=$resultado[0]['nombre_banco'];
       


        if (!empty ($resultado))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('p', 'mm', 'legal', true, 'utf-8', false);
            $info_adicional= "Detalle de Recibo de Pago \n".
                             "Número: ".$numero.", Año: ".$ano;
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //set auto page breaks
            $pdf->SetAutoPageBreak(FALSE, 12);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Imprimir Cheque y Recibo de pago');
            $pdf->SetSubject('Imprimir Cheque y Recibo de pago');

            $pdf->AddPage();
            $pdf->SetFillColor(255, 255, 255);
            $listado_header = array('Código Presupuestario','Descripción', 'Monto');
            $pdf->SetFont('helvetica', '', 12);
           //monto en numero
           $pdf->SetXY(131, 33);
           $pdf->Cell(20, 6, "**".number_format($monto_total, 2, ',', '.')."**", 0, 0, 'L', 1);
           //nombre
           $pdf->SetXY(26, 47);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           //monto en letras
           $pdf->SetXY(28, 53);
           //$pdf->Cell(20, 6, num_a_letras($monto_total+100000), 0, 0, 'L', 1);

            $centimos=substr($monto_total, -2); // devuelve "d"
           $pdf->MultiCell(140, 0, strtoupper(num_a_letras($monto_total))." CON $centimos/100", 0, 'JL', 0, 0, '', '', true, 0);
              //lugar emision
           $pdf->SetXY(13, 65);
           $pdf->Cell(11, 6, "La Asunción", 0, 0, 'L', 1);
           
           //fecha dia-mes
           $pdf->SetXY(38, 65);
           $pdf->Cell(20, 6, $dia_mes, 0, 0, 'L', 1);
           //fecha año
           $pdf->SetXY(68, 65);
           $pdf->Cell(20, 6, $ano_cheque, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 12);
            //numero cheque
           $pdf->SetXY(32, 125);
           $pdf->Cell(20, 6, $cheque, 0, 0, 'L', 1);
           //nombre banco
           $pdf->SetXY(85, 125);
           $pdf->Cell(20, 6, $banco, 0, 0, 'L', 1);
           //fecha dia-mes-año
           $pdf->SetXY(150, 125);
           $pdf->Cell(20, 6, $dia_mes."-".$ano_cheque, 0, 0, 'L', 1);
           //nombre beneficiario
           $pdf->SetXY(32, 131);
           $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 1);
           $pdf->SetFont('helvetica', '', 10);
           //concepto
           $pdf->SetXY(43, 138);
           $pdf->MultiCell(132, 16, $motivo.'.', 0, 'JL', 0, 0, '', '', true, 0);

            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);

            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(210,210,210);
            // Data
            $fill = 0;

           
            $eje_x=45;
            $eje_y=172;// comienza en la segunda linea

           
            
            // se muestra las retenciones agrupadas por codigo
          
          
                
            $eje_y=$eje_y+8;
           

             
            $eje_x=45;
            $eje_y=$eje_y;
            //muestra codigos ingresos
                //obtener descripcion y codigo
                $sql="SELECT pi.descripcion, CONCAT(pi.ramo,'-',pi.generica,'-',pi.especifica,'-',pi.subespecifica) as codigo FROM presupuesto.presupuesto_ingresos pi,presupuesto.fuentes_financiamiento_cuentas ffc, presupuesto.fuentes_financiamiento ff WHERE  (ffc.numero_cuenta='".$cuenta."')AND(ff.cod_fuente_financiamiento=ffc.cod_fuente_financiamiento) AND (pi.cod_presupuesto_ingreso=ff.cod_presupuesto_ingreso) AND (ff.ano='$ano')";
                $res2=cargar_data($sql,$sender);
                //codigo
                $pdf->SetXY(13, $eje_y);
                $pdf->Cell(20, 6,$res2[0]["codigo"], 0, 0, 'L', 0);
                //detalle
                $pdf->SetXY(43, $eje_y+1);
                $pdf->SetFont('helvetica','',8);
                $descripcion=$res2[0]["descripcion"];
                $descripcion=myTruncate($descripcion, 160, ' ', '.');
                $pdf->MultiCell(70, 0, ucfirst(strtolower($descripcion)), 0, 'JL', 0, 0, '', '', true, 0);
                $pdf->SetFont('helvetica','',10);
                //monto total por partida
                $pdf->SetXY(157, $eje_y);
                $pdf->Cell(20, 6, number_format($monto_total, 2, ',', '.'), 0, 0, 'R', 0);
                //fin muestra codigos ingreso
                $x=1;
               
            $pdf->SetFont('helvetica','',8);
            //------observaciones ------//
            //nombre
            $pdf->SetXY(13, 251);
            $pdf->Cell(20, 6, $nom_beneficiario, 0, 0, 'L', 0);
            //tipo orden pago - numero
            //$pdf->SetXY(13, 256);
            //$pdf->MultiCell(75, 256,"Pago de Retencion: ".$descripcion_retencion, 0, 'JL', 0, 0, '', '', true, 0);
			//------observaciones ------//
            $pdf->SetFont('helvetica','',10);
            //------firmas--------------//
            //preparado
            $pdf->SetXY(30, 282);
            $pdf->Cell(20, 6,strtoupper($this->txt_preparado->Text), 0, 0, 'L', 0);
            //revisado
            $pdf->SetXY(75, 282);
            $pdf->Cell(20, 6, strtoupper($this->txt_revisado->Text), 0, 0, 'L', 0);
            //aprobado
            $pdf->SetXY(120, 282);
            $pdf->Cell(20, 6, strtoupper($this->txt_aprobado->Text), 0, 0, 'L', 0);
            //auxiliar
            $pdf->SetXY(142, 285);
            $pdf->Cell(20, 6, strtoupper($this->txt_auxiliar->Text), 0, 0, 'L', 0);
            //diario
            $pdf->SetXY(164, 285);
            $pdf->Cell(20, 6,strtoupper($this->txt_diario->Text), 0, 0, 'L', 0);
            //------firmas--------------//
            $pdf->Output("detalle_cheque_".$numero."_".$ano.".pdf",'D');

        }//FIN PARA
    }
}
?>