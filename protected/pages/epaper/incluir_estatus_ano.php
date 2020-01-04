<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Implementa el estatus del año activo  (sea cual sea su tipo)
 *              para lo cual existen 24 posibles estatus presupuestarios:
 *              PA: "Pasado". Son los años que se han cerrado, no es posible
 *                  volver a colocarlos como "Presentes" o "Futuro" o "No Disponible"
 *                  una vez que se cierran y solo permiten consultas, todas las
 *                  operaciones que intenten modificarlo no aplican en él.
 *              PR: "Presente". Es el año actual, le aplican todas las
 *                  operaciones normales que tiendan a modificarlo, y ejecutarlo.
 *                  Solo permite un cambio posible de estatus
 *                  a "Pasado" una vez que el año fiscal haya oficialmente concluido.
 *              FU: "Futuro". Son los años que aún no se pueden ejecutar
 *                  y que solo se encuentran en fase de formulación.
 *                  Su cambio posible de estatus es a: "Presente"
 *                  siempre y cuando no exista un año "Presente" previamente,
 *                  lo que quiere decir que antes de que el año "Futuro"
 *                  se convierta en "Presente", el "Presente debio previamente
 *                  haberse convertido en "Pasado".
 * Nota General:
 *      Para efectos de esta ventana, solo se acepta que se incluya un nuevo
 *      año en la empresa en su estatus "FUTURO" para evitar conflictos de tener
 *      dos años en estatus "Presente"
 *****************************************************  FIN DE INFO
*/

class incluir_estatus_ano extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
             // todos los años posibles para el sistema, no muchos, con 20 esta bien (por ahora).
             for ($xanos = 2009 ; $xanos <= 2020 ; $xanos++)
             {
                $anos[$xanos]=$xanos;
             }
             // se obtiene el codigo de la organizacion y se buscan los años que
             // tiene la organicacion dentro de estatus_presupuesto para evitar
             // mostrarlos de nuevo y asi me aseguro de incluir solo años que
             // no existan previamente en la organizacion.
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql="select ano from organizacion.estatus_ano_documentos
                    where cod_organizacion = '$cod_organizacion' order by ano";
              $anos_existentes=cargar_data($sql,$this);
              // aqui elimino los años que existen del arreglo para que se
              // muestren solo los que no existen.
              foreach($anos_existentes as $indice)
              {
                  unset($anos[$indice['ano']]);
              }
          
              $this->drop_ano->DataSource=$anos;
              $this->drop_ano->dataBind();
              
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
          }
    }

    
    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // se capturan los valores de los controles
            $ano = $this->drop_ano->Text;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $resultado_drop = obtener_seleccion_drop($this->drop_estatus);
            $estatus = $resultado_drop[1]; // el value del drop

            // se inserta en la base de datos
            $sql = "insert into organizacion.estatus_ano_documentos
                    (cod_organizacion, ano, estatus)
                    values ('$cod_organizacion','$ano','$estatus')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Insertado estatus ".$estatus." de año para control de documentos para el año:".$ano;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            // para asegurarme de autorecargar la pagina hago un llamado a ella misma.
            $this->Response->redirect($this->Service->constructUrl('Home'));
        }

    }



}


?>
