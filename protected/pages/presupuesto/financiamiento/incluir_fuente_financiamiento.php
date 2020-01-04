<?php
/*
 * Esta página se encarga de la inclusión de fuentes de financiamiento
 * en el sistema, cada una de ellas se asocia con un codigo presupuestario
 * de ingreso, las fuentes de fincanciamiento se utilizan en la etapa de pago,
 * por lo cual también se encuentran asociadas con cuentas bancarias.
 *
 */
class incluir_fuente_financiamiento extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
            $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
            // se llena el primer drop con los datos de los codigos presupuestarios de ingresos
            $cod_organizacion = usuario_actual('cod_organizacion');
            // Para saber el año actual
            $ano = ano_presupuesto($cod_organizacion,1,$this);
            $ano = $ano[0]['ano'];

            $sql="select CONCAT(ramo,'-',generica,'-',especifica,'-',subespecifica,' / ',descripcion,' / Bs: ',monto) as mostrar,
                cod_presupuesto_ingreso from presupuesto.presupuesto_ingresos
                where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))
                order by ramo, generica, especifica, subespecifica";
            $resultado=cargar_data($sql,$this);
            $this->drop_ingreso->DataSource=$resultado;
            $this->drop_ingreso->dataBind();

            $sql="select c.numero_cuenta, CONCAT(b.nombre,' - ',c.tipo_cuenta,' / Nro: ',c.numero_cuenta) as mostrar
                  from presupuesto.bancos b, presupuesto.bancos_cuentas c
                  where (b.cod_banco = c.cod_banco) and (c.cod_organizacion = '$cod_organizacion')
                  order by b.nombre, c.tipo_cuenta, c.numero_cuenta";
            $resultado=cargar_data($sql,$this);
            $this->drop_cuenta->DataSource=$resultado;
            $this->drop_cuenta->dataBind();

            $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),1,$this);
            $this->drop_ano->dataBind();
          }
    }

    
    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cod_organizacion = usuario_actual('cod_organizacion');

            $ano = $this->drop_ano->SelectedValue;
            $descripcion = $this->txt_descripcion->Text;
            $ingreso = $this->drop_ingreso->SelectedValue;
            $numero_cuenta = $this->drop_cuenta->SelectedValue;

            /* se genera el próximo codigo de la fuente de financiamiento
             * dependiendo de la organización y el año.  Nota: no uso la funcion
             * que determina el siguiente codigo en una tabla y campo porque este
             * valor especificamente depende de mas de un campo.
             */
            $sql = "SELECT Max(cod_fuente_financiamiento) as cod
                    FROM presupuesto.fuentes_financiamiento
                    WHERE ((ano='$ano') and (cod_organizacion = '$cod_organizacion'))";
            $maximo = cargar_data($sql,$this);
            $cod_fuente = rellena($maximo[0]['cod']+1,'2','0');


            /* Se guarda la fuente de financiamiento */
            $sql="insert into presupuesto.fuentes_financiamiento (ano, cod_fuente_financiamiento, cod_organizacion, descripcion, cod_presupuesto_ingreso)
                  values ('$ano','$cod_fuente','$cod_organizacion','$descripcion','$ingreso')";
            $resultado=modificar_data($sql,$sender);

            /* Se asocia la fuente al numero de cuenta seleccionada */
            $sql="insert into presupuesto.fuentes_financiamiento_cuentas (ano, cod_fuente_financiamiento, cod_organizacion, numero_cuenta)
                  values ('$ano','$cod_fuente','$cod_organizacion','$numero_cuenta')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluida la fuente de Financiamiento: ".$descripcion." y asociada a la cuenta: ".$numero_cuenta;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
    }
}
?>
