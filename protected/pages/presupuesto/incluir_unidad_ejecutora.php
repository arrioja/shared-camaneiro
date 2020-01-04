<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: En esta pagina se asocia a una organizacion inscrita en el sistema
 * como una Unidad Ejecutora de Presupuesto y se asocia a su correspondiente
 * código presupuestario de la forma Sector(2)-Programa(2)-SubPrograma(2)-Proyecto(2)-Actividad(2).
 *****************************************************  FIN DE INFO
*/
class incluir_unidad_ejecutora extends TPage
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

/* Esta función valida la existencia del código en la tabla,
 * se usa un custom validator en el control para
 * responder al usuario si existe o no el mismo.
 */
    public function validar_codigo($sender, $param)
    {
       $ano = $this->drop_ano->SelectedValue;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $sql = "select * from presupuesto.unidad_ejecutora
                    where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))";
       $existe = cargar_data($sql,$this);
       $param->IsValid = empty($existe);
    }


    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // se capturan los valores de los controles
            $ano = $this->drop_ano->SelectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->getUnmaskedData(),'28','0'));

            // se inserta en la base de datos
            $sql = "insert into presupuesto.unidad_ejecutora
                    (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad)
                    values ('$cod_organizacion','$ano','$codigo[sector]','$codigo[programa]','$codigo[subprograma]',
                            '$codigo[proyecto]','$codigo[actividad]')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Insertada Unidad Ejecutora de Presupuesto Cod: ".$codigo['sector'].
                               "-".$codigo['programa']."-".$codigo['subprograma']."-".$codigo['proyecto']."-".$codigo['actividad'];
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }

    }
}
?>
