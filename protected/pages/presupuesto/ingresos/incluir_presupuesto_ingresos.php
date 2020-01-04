<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: En esta página se guardan los datos concernientes al presupuesto
 *              de ingresos de la institución.
 *****************************************************  FIN DE INFO
*/
class incluir_presupuesto_ingresos extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),1,$this);
              $this->drop_ano->dataBind();
          }
    }

/*  Esta función valida la existencia del código presupuestario en la tabla
 * descripcion_presupuesto, se usa un custom validator en el control para
 * responder al usuario si existe o no el mismo.
 */
    public function validar_codigo($sender, $param)
    {
       $ano = $this->drop_ano->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_ingreso(rellena_derecha($this->txt_codigo->getUnmaskedData(),'9','0'));

       $sql = "select * from presupuesto.presupuesto_ingresos
                    where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano') and (ramo = '$codigo[ram]') and (generica = '$codigo[gen]') and
                           (especifica = '$codigo[esp]') and (subespecifica = '$codigo[sub]'))";
       $existe = cargar_data($sql,$this);
       $param->IsValid = empty($existe);
    }


    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // se capturan los valores de los controles
            $ano = $this->drop_ano->Text;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo = descomponer_codigo_ingreso(rellena_derecha($this->txt_codigo->getUnmaskedData(),'9','0'));

            $descripcion = $this->txt_descripcion->Text;
            $monto = floatval($this->txt_monto->Text);

            /* se genera el próximo codigo de la fuente de financiamiento
             * dependiendo de la organización y el año.  Nota: no uso la funcion
             * que determina el siguiente codigo en una tabla y campo porque este
             * valor especificamente depende de mas de un campo.
             */
            $sql = "SELECT Max(cod_presupuesto_ingreso) as cod
                    FROM presupuesto.presupuesto_ingresos
                    WHERE ((ano='$ano') and (cod_organizacion = '$cod_organizacion'))";
            $maximo = cargar_data($sql,$this);
            $proximo_codigo = rellena($maximo[0]['cod']+1,'2','0');

            // se inserta en la base de datos
            $sql = "insert into presupuesto.presupuesto_ingresos
                    (cod_organizacion, cod_presupuesto_ingreso, ano, ramo, generica, especifica, subespecifica, monto, descripcion)
                    values ('$cod_organizacion','$proximo_codigo','$ano','$codigo[ram]','$codigo[gen]','$codigo[esp]','$codigo[sub]','$monto','$descripcion')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Insertado Presupuesto de ingreso Cod: ".$codigo['ram']."-".$codigo['gen']."-".$codigo['esp']."-".$codigo['sub']." / ".$descripcion." / ".$monto;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }

    }
}
?>
