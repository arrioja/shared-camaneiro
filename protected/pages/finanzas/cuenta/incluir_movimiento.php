<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página se encarga de incluir las cuentas bancarias en los
 *              bancos que se encuentren registrados. Es necesario que se
 *              indique el valor del saldo inicial con que se registra la
 *              cuenta en el sistema para proceder a realizar los balances.
 *****************************************************  FIN DE INFO
*/

class incluir_movimiento extends TPage
{

    public function onLoad($param)
    {
        /* para llenar el drop down*/
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $sql="select * from presupuesto.bancos order by nombre";
            $bancos=cargar_data($sql,$this);
            $this->drop_bancos->DataSource=$bancos;
            $this->drop_bancos->dataBind();
          }
    }

    public function cargar_cuentas()
    {
      $cod_org = usuario_actual('cod_organizacion');
      $cod_banco=$this->drop_bancos->SelectedValue;
      $sql2 = "select * from presupuesto.bancos_cuentas where cod_organizacion='$cod_org' and cod_banco='$cod_banco' ";
      $datos2 = cargar_data($sql2,$this);
      $this->drop_cuentas->Datasource = $datos2;
      $this->drop_cuentas->dataBind();
    }


    /* Esta función implementa el evento incluir del botón que se encuentra en
     * la ventana.
     */
    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        { $this->btn_incluir->Enabled="False";
            // Se capturan los datos provenientes de los Controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo_banco = $this->drop_bancos->SelectedValue;
            $numero_cuenta = $this->drop_cuentas->SelectedValue;
            $tipo=$this->drop_tipo->SelectedValue;
            
            $monto = $this->txt_monto->Text;
            $descripcion = $this->txt_descripcion->Text;
            $referencia = $this->txt_referencia->Text;
            registrar_movimiento($cod_organizacion,$codigo_banco,$numero_cuenta,$monto,$tipo,$descripcion,$referencia,$this);
            $this->mensaje->setSuccessMessage($sender, "Movimiento de Cuenta Guardado exitosamente!!", 'grow');
            //$this->Response->redirect($this->Service->constructUrl('Home'));
        }

    }


}

?>
