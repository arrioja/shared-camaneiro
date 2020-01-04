<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página se encarga de incluir las cuentas bancarias en los
 *              bancos que se encuentren registrados. Es necesario que se
 *              indique el valor del saldo inicial con que se registra la
 *              cuenta en el sistema para proceder a realizar los balances.
 *****************************************************  FIN DE INFO
*/

class incluir_cuenta_bancaria extends TPage
{

    public function onLoad($param)
    {
                parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            /* para llenar el drop down*/
            $sql="select * from presupuesto.bancos order by nombre";
            $bancos=cargar_data($sql,$this);
            $this->drop_bancos->DataSource=$bancos;
            $this->drop_bancos->dataBind();
          }
    }


    /* Esta función implementa el evento incluir del botón que se encuentra en
     * la ventana.
     */
    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo_banco = $this->drop_bancos->SelectedValue;
            $numero_cuenta = $this->txt_numero_cuenta->Text;
            $tipo_cuenta = $this->drop_tipo->SelectedValue;
            $fecha_apertura = cambiaf_a_mysql($this->txt_fecha->Text);
            $saldo = $this->txt_monto->Text;

            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                          'cod_banco' => $codigo_banco,
                                          'numero_cuenta' => $numero_cuenta);
            $numero_movimiento=proximo_numero("presupuesto.bancos_cuentas_movimientos","cod_movimiento",$criterios_adicionales,$sender);
            $cod_movimiento=rellena($numero_movimiento,10,"0");

            /* Se guarda en la base de datos */
            $sql="insert into presupuesto.bancos_cuentas (cod_organizacion, cod_banco, numero_cuenta, fecha_apertura, tipo_cuenta, saldo)
                  values ('$cod_organizacion','$codigo_banco','$numero_cuenta','$fecha_apertura','$tipo_cuenta','$saldo')";
            $resultado=modificar_data($sql,$sender);

            $sql="insert into presupuesto.bancos_cuentas_movimientos (cod_movimiento, cod_organizacion, referencia, cod_banco, numero_cuenta, debe, haber, saldo, descripcion)
                  values ('$cod_movimiento','$cod_organizacion', '$cod_movimiento','$codigo_banco','$numero_cuenta','$saldo','0', '$saldo', 'Saldo Inicial (Nueva Cuenta).')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluida la Cuenta: ".$numero_cuenta." en el banco ".$codigo_banco;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }

    }


}

?>
