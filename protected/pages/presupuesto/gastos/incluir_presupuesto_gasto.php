<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M. recalcular_presupuesto_gasto
 * Descripción: En esta página se guardan los datos concernientes al presupuesto
 *              de gastos de la institución.
 *****************************************************  FIN DE INFO
*/
class incluir_presupuesto_gasto extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              // para llenar el drop con los años permitidos (actual).
              $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),1,$this);
              $this->drop_ano->dataBind();

              $cod_organizacion = usuario_actual('cod_organizacion');
              // Para colocar el codigo de la unidad ejecutora (convertir esto en una funcion)
              $ano = ano_presupuesto($cod_organizacion,1,$this);
              $ano = $ano[0]['ano'];
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');

              $codigo = codigo_unidad_ejecutora($this);
              if (empty($codigo) == false)
              { $this->txt_codigo->Mask = $codigo."-###-##-##-##-#####"; }
              
          $this->setFocus($this->txt_codigo);
          }
    }

/* Esta función se encarga de generar todos los codigos presupuestarios acumulados
 * del codigo presupuestario pasado como parámetro. Si el codigo no existe, se crea
 * y si existe, se acumulan sus valores.
 */

    public function generar_acumulados_presupuesto_gasto1($cod,$monto,$retencion)
    {
       $cod_organizacion = usuario_actual('cod_organizacion');
       $ano = $this->drop_ano->selectedValue;
        for ($xcod = 9 ; $xcod >= 1 ; $xcod--)
        {
            switch($xcod)
            {
                case 1:
                    $cod['programa']="00";
                    break;
                case 2:
                    $cod['subprograma']="00"; 
                    break;
                case 3:
                    $cod['proyecto']="00";
                    break;
                case 4:
                    $cod['actividad']="00";
                    break;
                case 5:
                    $cod['partida']="000";
                    break;
                case 6:
                    $cod['generica']="00";
                    break;
                case 7:
                    $cod['especifica']="00";
                    break;
                case 8:
                    $cod['subespecifica']="00";
                    break;
                case 9:
                    $cod['ordinal']="00000";
                    break;
            }

           $sql = "select * from presupuesto.presupuesto_gastos
                        where ((cod_organizacion = '$cod_organizacion') and
                                (ano = '$ano') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                               (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and 
                               (ordinal = '$cod[ordinal]'))";
           $existe = cargar_data($sql,$this);
           $id = $existe[0]['id'];
     
           if (empty($existe) == true)
           {
               // si el codigo no existe, se incluyen los acumulados de todos los codigos
                $sql2 = "insert into presupuesto.presupuesto_gastos
                        (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                         generica, especifica, subespecifica, ordinal, asignado, disponible, descripcion, acumulado, es_retencion)
                        values ('$cod_organizacion','$ano','$cod[sector]','$cod[programa]','$cod[subprograma]','$cod[proyecto]',
                                '$cod[actividad]','$cod[partida]','$cod[generica]','$cod[especifica]',
                                '$cod[subespecifica]','$cod[ordinal]','$monto','$monto','$descripcion','1','$retencion')";
                $resultado=modificar_data($sql2,$this);
                $sql3 = "select max(id) as id from presupuesto.presupuesto_gastos";
                $result_id = cargar_data($sql3,$this);
                $id_incluido = $result_id[0]['id'];
           }
           else
           {
               if (($existe[0]['acumulado'] == '1') && ($id_acumulado != $id) && ($id_incluido != $id))
               {
                   $id_acumulado = $id;
                   // si el codigo ya existe, se suman los acumulados en dicho código.
                   $sql2 = "update presupuesto.presupuesto_gastos set
                           asignado = asignado+'$monto', disponible = disponible+'$monto' where id = '$id'";
                   $resultado=modificar_data($sql2,$this);
               }
           }
        }
    }

/* Esta función valida la existencia del código presupuestario en la tabla
 * se usa un custom validator en el control para
 * responder al usuario si existe o no el mismo.
 */

    public function validar_codigo($sender, $param)
    {
       $ano = $this->drop_ano->selectedValue;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $sql = "select * from presupuesto.presupuesto_gastos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                           (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and (ordinal = '$codigo[ordinal]'))";
       $existe = cargar_data($sql,$this);
       $param->IsValid = empty($existe);
    }

    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // se capturan los valores de los controles
            $ano = $this->drop_ano->selectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
            $descripcion = $this->txt_descripcion->Text;
            $monto = floatval($this->txt_monto->Text);
            $es_retencion = 0;

            if ($this->retencion->Checked)
            {
                $monto = 0;
                $es_retencion = 1;
            }
            // se inserta en la base de datos
            $sql = "insert into presupuesto.presupuesto_gastos
                    (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                     generica, especifica, subespecifica, ordinal, asignado, disponible, descripcion, es_retencion)
                    values ('$cod_organizacion','$ano','$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                            '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                            '$codigo[subespecifica]','$codigo[ordinal]','$monto','$monto','$descripcion','$es_retencion')";
            $resultado=modificar_data($sql,$sender);
            $this->generar_acumulados_presupuesto_gasto1($codigo,$monto,$es_retencion);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Insertado Presupuesto de Gasto. /año:".$ano." /Cod: ".$this->txt_codigo->Text." /Desc: ".$descripcion." /Monto: ".$monto;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            // para asegurarme de autorecargar la pagina hago un llamado a ella misma.
            $this->Response->redirect($this->Service->constructUrl($this->Page->getPagePath()));
        }

    }
}
?>
