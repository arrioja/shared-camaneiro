<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: En esta página se guardan los datos concernientes al presupuesto
 *              de gastos de la institución.
 *****************************************************  FIN DE INFO
*/
class incluir_articulo extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $cod_organizacion = usuario_actual('cod_organizacion');
              // Para colocar el codigo de la unidad ejecutora (convertir esto en una funcion)
              $ano = ano_presupuesto($cod_organizacion,1,$this);
              $ano = $ano[0]['ano'];

              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),1,$this);
              $this->drop_ano->dataBind();

              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');

              $codigo = codigo_unidad_ejecutora($this);

          }
    }




/* Esta función valida la existencia del código presupuestario en la tabla
 * se usa un custom validator en el control para
 * responder al usuario si existe o no el mismo.
 */
    public function validar_codigo($sender, $param)
    {
     /*  $ano = $this->drop_ano->selectedValue;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $sql = "select * from presupuesto.presupuesto_gastos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                           (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and (ordinal = '$codigo[ordinal]'))";
       
       $existe = cargar_data($sql,$this);
       $param->IsValid = !empty($existe);*/
    }

    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        { $this->incluir->Enabled=False;
            // se capturan los valores de los controles
            $ano = $this->drop_ano->selectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            //$codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
            $descripcion = $this->txt_descripcion->Text;
            $precio = floatval($this->txt_precio->Text);
            $es_consumible = 0;//consumible por defecto=0, sino es artículo e igual a '1'

            if ($this->bien->Checked)
            {
                $es_consumible = 1;
            }

            $cod_articulo=codigo_aleatorio("10","presupuesto.articulos","cod",$this);
            // se inserta en la base de datos
            $sql = "insert into presupuesto.articulos
                    (cod,ano,cod_organizacion,descripcion, tipo,precio)
                    values ('$cod_articulo','$ano','$cod_organizacion','$descripcion','$es_consumible','$precio')";
            $resultado=modificar_data($sql,$sender);
            $this->mensaje->setSuccessMessage($sender, " Artículo Guardado Exitosamente!!", 'grow');
            //$this->generar_acumulados_presupuesto_gasto($codigo,$monto,$es_retencion);

            /* Se incluye el rastro en el archivo de bitácora */
            //$descripcion_log = "Insertado Presupuesto de Gasto. /año:".$ano." /Cod: ".$this->txt_codigo->Text." /Desc: ".$descripcion." /Monto: ".$monto;
            //inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            // para asegurarme de autorecargar la pagina hago un llamado a ella misma.
            //$this->Response->redirect($this->Service->constructUrl($this->Page->getPagePath()));
        }

    }
}
?>
