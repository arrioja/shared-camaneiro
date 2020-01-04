<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: En esta página se registran los movimientos bancarios(nota credito, Ingreso, Egreso,nota debito, transferencia).
 *****************************************************  FIN DE INFO
*/
class incluir_movimiento extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_codigo_temporal->Text = aleatorio_traslados($this);
              $cod_organizacion = usuario_actual('cod_organizacion');

              $codigo = codigo_unidad_ejecutora($this);
             

              // coloca el año presupuestario "Presente"
              $ano = ano_presupuesto($cod_organizacion,1,$this);
              $this->lbl_ano->Text = $ano[0]['ano'];
              
              $this->txt_fecha->Text = date("d/m/Y");

              $sql="select * from presupuesto.bancos where cod_organizacion='$cod_organizacion'";
              $res_banco=cargar_data($sql,$this);
              $this->drop_banco->DataSource=$res_banco;
              $this->drop_banco->dataBind();

              $sql="select * from presupuesto.bancos where cod_organizacion='$cod_organizacion'";
              $res_banco=cargar_data($sql,$this);
              $this->drop_banco2->DataSource=$res_banco;
              $this->drop_banco2->dataBind();

              $cod_org = usuario_actual('cod_organizacion');
              $sql2 = "select * from presupuesto.tipo_movimiento where cod_organizacion='$cod_org' LIMIT 0 , 5 ";
              $datos2 = cargar_data($sql2,$this);
              $this->drop_tipo->Datasource = $datos2;
              $this->drop_tipo->dataBind();

            $this->txt_referencia->Enabled=false;
            $this->drop_banco->Enabled=false;
            $this->drop_cuentas->Enabled=false;
            $this->txt_referencia2->Enabled=false;
            $this->drop_banco2->Enabled=false;
            $this->drop_cuentas2->Enabled=false;
          }
    }

    /* Funcion que llena el drop con los numeros de cuentas asociadas al drop_banco*/
     public function cargar_cuentas()
    {
      $cod_org = usuario_actual('cod_organizacion');
      $cod_banco=$this->drop_banco->SelectedValue;
      $sql2 = "select * from presupuesto.bancos_cuentas where cod_organizacion='$cod_org' and cod_banco='$cod_banco' ";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_cuentas->Datasource = $datos2;
      $this->drop_cuentas->dataBind();
     

    }
    /* Funcion que llena el drop con los numeros de cuentas asociadas al drop_banco*/
     public function cargar_cuentas2()
    {
      $cod_org = usuario_actual('cod_organizacion');
      $cod_banco=$this->drop_banco2->SelectedValue;
      $sql2 = "select * from presupuesto.bancos_cuentas where cod_organizacion='$cod_org' and cod_banco='$cod_banco' ";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_cuentas2->Datasource = $datos2;
      $this->drop_cuentas2->dataBind();

    }
    /* Funcion que habilita */
     public function habilitar_operacion($sender,$param)
    {
         $tipo=$this->drop_tipo->SelectedValue;
        if (($tipo=="ND")||($tipo=="E")){//-
            $this->txt_referencia->Enabled=true;
            $this->drop_banco->Enabled=true;
            $this->drop_cuentas->Enabled=true;
            $this->txt_referencia2->Enabled=false;
            $this->drop_banco2->Enabled=false;
            $this->drop_cuentas2->Enabled=false;
        }elseif (($tipo=="NC")||($tipo=="I")||($tipo=="D")){//+
            $this->txt_referencia2->Enabled=true;
            $this->drop_banco2->Enabled=true;
            $this->drop_cuentas2->Enabled=true;
            $this->txt_referencia->Enabled=false;
            $this->drop_banco->Enabled=false;
            $this->drop_cuentas->Enabled=false;
        }elseif ($tipo=="T"){
            $this->txt_referencia->Enabled=true;
            $this->drop_banco->Enabled=true;
            $this->drop_cuentas->Enabled=true;
            $this->txt_referencia2->Enabled=true;
            $this->drop_banco2->Enabled=true;
            $this->drop_cuentas2->Enabled=true;
        }//FIN SI

    }


    public function  incluir_click($sender,$param){

        if (($this->IsValid)&&( $this->btn_incluir->Enabled)&&($this->txt_monto->Text>0))
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $referencia_cedente=$this->txt_referencia->Text;
            $referencia_receptor=$this->txt_referencia2->Text;
            $tipo=$this->drop_tipo->SelectedValue;
            $ano =  $this->lbl_ano->Text;
            $motivo=$this->txt_motivo->Text;
            $fecha_arre=split("/",$this->txt_fecha->Text);
            $fecha=$fecha_arre[2]."-".$fecha_arre[1]."-".$fecha_arre[0];
            $monto=$this->txt_monto->Text;
            $banco_cedente=$this->drop_banco->SelectedValue;
            $cuenta_cedente=$this->drop_cuentas->SelectedValue;
            $banco_receptor=$this->drop_banco2->SelectedValue;
            $cuenta_receptor=$this->drop_cuentas2->SelectedValue;
            $error=false;
           // $saldo_actual=disponibilidad($cod_organizacion,$banco_cedente,$cuenta_cedente,$sender);
           // $this->mensaje->setErrorMessage($sender, "hola el saldo de $banco_cedente-$cuenta_cedente BS.$saldo_actual", 'grow');

            if (($tipo=="ND")||($tipo=="E")){//-
                    if((strlen($referencia_cedente)>0)&&(strlen($banco_cedente)>0)){
                       
                       $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $ano);
                       $no_existe_movimientos = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$referencia_cedente,$criterios_adicionales,$sender);

                      if($no_existe_movimientos){
                         $saldo_actual=disponibilidad_bancaria($ano,$cod_organizacion,$banco_cedente,$cuenta_cedente,$sender);
                            if($saldo_actual>$monto){//se verifica disponibilidad en cuenta cedente
                                //se registra el movimiento en las cuenta bancaria seleccionada
                                registrar_movimiento($ano,$cod_organizacion,$banco_cedente,$cuenta_cedente,$monto,'haber',$motivo,$referencia_cedente,$this,$tipo,$fecha,"");
                                $referencia=$referencia_cedente;
                            }else{
                                 $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente en cuenta $cuenta_cedente !</br> Saldo actual: Bs. ".number_format($saldo_actual, 2, ',', '.'), 'grow');
                                 $error=true;
                             }//fin si
                      }else{
                     
                           $this->mensaje->setErrorMessage($sender, "¡Referencia $referencia_cedente Existe !", 'grow');
                           $error=true;
                       }//fin si
              }else{
               $this->mensaje->setErrorMessage($sender, "¡Datos Invalidos¡", 'grow');
               $error=true;
              }//fin si
                     
            }//fin si

            if (($tipo=="NC")||($tipo=="I")||($tipo=="D")){//+
                if((strlen($referencia_receptor)>0)&&(strlen($banco_receptor)>0)){
                     $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $ano);
                       $no_existe_movimientos = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$referencia_receptor,$criterios_adicionales,$sender);

                      if($no_existe_movimientos){
                            //se registra el movimiento en las cuenta bancaria seleccionada
                            registrar_movimiento($ano,$cod_organizacion,$banco_receptor,$cuenta_receptor,$monto,'debe',$motivo,$referencia_receptor,$this,$tipo,$fecha,"");
                            $referencia=$referencia_receptor;
                        }else{
                           $this->mensaje->setErrorMessage($sender, "¡Referencia $referencia_receptor Existe !", 'grow');
                           $error=true;
                       }//fin si
               }else{
               $this->mensaje->setErrorMessage($sender, "¡Datos Invalidos¡", 'grow');
               $error=true;
              }//fin si
               
            }//fin si
            
            if ($tipo=="T"){

                 if((strlen($referencia_receptor)>0)&&(strlen($banco_receptor)>0)&&(strlen($referencia_receptor)>0)&&(strlen($banco_receptor)>0)&&($cuenta_cedente!=$cuenta_receptor)&&($referencia_cedente!=$referencia_receptor)){
                       $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,'ano' => $ano);
                       $no_existe_movimientos = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$referencia_receptor,$criterios_adicionales,$sender);
                       $no_existe_movimientos2 = verificar_existencia('presupuesto.bancos_cuentas_movimientos','referencia',$referencia_cedente,$criterios_adicionales,$sender);

                        if((!$no_existe_movimientos)or(!$no_existe_movimientos2)){
                           $this->mensaje->setErrorMessage($sender, "¡Referencia Existe!", 'grow');
                           $error=true;
                       }//fin si
                       if(!$error){
                              $saldo_actual=disponibilidad_bancaria($ano,$cod_organizacion,$banco_cedente,$cuenta_cedente,$sender);

                               if($saldo_actual>$monto){//se verifica disponibilidad en cuenta cedente

                               $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                                         'ano' => $ano);
                                $numero_movimiento=proximo_numero("presupuesto.bancos_cuentas_movimientos","cod_movimiento",$criterios_adicionales,$sender);
                                $cod_movimiento=rellena($numero_movimiento,10,"0");

                                //se registra el movimiento en las cuenta bancaria seleccionada
                                registrar_movimiento($ano,$cod_organizacion,$banco_cedente,$cuenta_cedente,$monto,'haber',$motivo,$referencia_cedente,$this,'CH',$fecha,$cod_movimiento);
                                 //se registra el cheque sin orden al beneficiario cene
                                registrar_cheque($ano,$cod_movimiento,'SIN ORDEN','000225',$sender);
                                //se registra el movimiento en las cuenta bancaria seleccionada
                                registrar_movimiento($ano,$cod_organizacion,$banco_receptor,$cuenta_receptor,$monto,'debe',$motivo,$referencia_receptor,$this,'D',$fecha,$cod_movimiento);
                                $referencia=$referencia_cedente." - ".$referencia_receptor;
                               }else{
                                     $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente en cuenta $cuenta_cedente !</br> Saldo actual: Bs.".number_format($saldo_actual, 2, ',', '.'), 'grow');
                                    $error=true;
                               }//fin si
                     }//fin si
             }else{
               $this->mensaje->setErrorMessage($sender, "¡Datos Invalidos¡", 'grow');
               $error=true;
            }//FIN SI

            }//FIN SI
            

            //se registra el movimiento en las cuenta bancaria seleccionada
           // registrar_movimiento($cod_organizacion,$banco,$cuenta,$monto_total,'haber',$motivo,$num_cheque,$this,$tipo);

            // Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha incluido el Movimiento: ".$tipo." # ".$referencia. " / año: ".$ano.
                               " Fecha:$fecha por un monto total de Bs. ".$monto."  Detalle: ".$motivo;
             // Se incluye el rastro en el archivo de bitácora
          if(!$error){
              inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
              $this->mensaje->setSuccessMessage($sender, "Movimiento registrado exitosamente!!</br> $descripcion_log", 'grow');
           $this->btn_incluir->Enabled=false;
          }//fin si

        }

    }


}
?>