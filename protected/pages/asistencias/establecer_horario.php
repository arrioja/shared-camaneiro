<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: En este formulario se definen los diversos horarios de la
 *              institución.
 *****************************************************  FIN DE INFO
*/

class establecer_horario extends TPage
{
    var $id_actual; // id del registro que hay que desactivar
	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $fecha=date("Y-m-d");
              $horas = array();
              $ampm = array('AM'=>'AM','PM'=>'PM');
              // se llena el drop de las horas
              for ($x = 1 ; $x <= 12 ; $x++)
              {
                  if ($x < 10)
                  {   $hora ='0'.$x;}
                  else
                  {$hora = $x;}
                  $horas[$hora] = $hora;
              }
              $min = array();
              // se llena el drop de los minutos
              for ($x = 0 ; $x <= 60 ; $x++)
              {
                  if ($x < 10)
                  {   $minu ='0'.$x;}
                  else
                  {$minu = $x;}
                  $min[$minu] = $minu;
              }

            $this->dropholguraentrada->Datasource = $min;
            $this->dropholguraentrada->dataBind();
            $this->dropminalmuerzo->Datasource = $min;
            $this->dropminalmuerzo->dataBind();

            $minseg = $min;
            array_pop($minseg); // elimina el último elemento "60" porque
                                // no se usa en los controles asociados a este arreglo

            // se rellenan los controles de la hora de entrada
            $this->hhe->Datasource = $horas;
            $this->hhe->dataBind();
            $this->mhe->Datasource = $minseg;
            $this->mhe->dataBind();
            $this->she->Datasource = $minseg;
            $this->she->dataBind();
            $this->ahe->Datasource = $ampm;
            $this->ahe->dataBind();

            // se rellenan los controles de la hora de salida
            $this->hhs->Datasource = $horas;
            $this->hhs->dataBind();
            $this->mhs->Datasource = $minseg;
            $this->mhs->dataBind();
            $this->shs->Datasource = $minseg;
            $this->shs->dataBind();
            $this->ahs->Datasource = $ampm;
            $this->ahs->dataBind();

            // se rellenan los controles de la hora de salida de almuerzo
            $this->hsa->Datasource = $horas;
            $this->hsa->dataBind();
            $this->msa->Datasource = $minseg;
            $this->msa->dataBind();
            $this->ssa->Datasource = $minseg;
            $this->ssa->dataBind();
            $this->asa->Datasource = $ampm;
            $this->asa->dataBind();

            // se rellenan los controles de la hora de salida de almuerzo
            $this->hea->Datasource = $horas;
            $this->hea->dataBind();
            $this->mea->Datasource = $minseg;
            $this->mea->dataBind();
            $this->sea->Datasource = $minseg;
            $this->sea->dataBind();
            $this->aea->Datasource = $ampm;
            $this->aea->dataBind();

            /* Se colocan los valores de la base de datos en los Drops respectivos */
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select * from asistencias.opciones
              where ((cod_organizacion = '$cod_organizacion') and (vigencia_desde <= '$fecha') and
                     (vigencia_hasta >= '$fecha'))
              order by status desc Limit 1";
            $opciones=cargar_data($sql,$this);
            $this->dropholguraentrada->SelectedValue = $opciones[0]['holgura_entrada'];
            $this->dropminalmuerzo->SelectedValue = $opciones[0]['almuerzo_minutos'];
            $this->droppermisos->SelectedValue = $opciones[0]['max_pot'];

            $this->mostrar_hora($opciones[0]['hora_entrada'],$this->hhe,$this->mhe,$this->she,$this->ahe);
            $this->mostrar_hora($opciones[0]['hora_salida'],$this->hhs,$this->mhs,$this->shs,$this->ahs);
            $this->mostrar_hora($opciones[0]['almuerzo_salida'],$this->hsa,$this->msa,$this->ssa,$this->asa);
            $this->mostrar_hora($opciones[0]['almuerzo_entrada'],$this->hea,$this->mea,$this->sea,$this->aea);

            $this->dropdiascontar->SelectedValue = $opciones[0]['dias_a_contar'];
            $this->dropdiasrestar->SelectedValue = $opciones[0]['dias_a_restar'];

            $this->campo_id->Value = $opciones[0]['id'];
           // $this->hola->Text= $opciones[0]['id'];

             // se rellenan los controles de los tickets
            $this->htc->Datasource = $horas;
            $this->htc->dataBind();
            $min_t = array();
              // se llena el drop de los minutos
              for ($x = 0 ; $x <= 240 ; $x++)
              {
                  if ($x < 10)
                  {   $minu ='0'.$x;}
                  else
                  {$minu = $x;}
                  $min_t[$minu] = $minu;
              }
            $this->mtc->Datasource = $min_t;
            $this->mtc->dataBind();
            $this->ntc->Datasource = $horas;
            $this->ntc->dataBind();
            /* Se colocan los valores de la base de datos en los Drops respectivos */
            $sql="select * from asistencias.opciones_ticket
              where ((cod_organizacion = '$cod_organizacion') and (vigencia_desde <= '$fecha') and
                     (vigencia_hasta >= '$fecha'))
              order by status desc Limit 1";
            $opciones=cargar_data($sql,$this);
            $this->htc->SelectedValue = $opciones[0]['horas_para_restar'];
            $this->mtc->SelectedValue = $opciones[0]['minutos_para_restar'];
            $this->ntc->SelectedValue = $opciones[0]['tickets_a_restar'];
            $this->campo_id2->Value = $opciones[0]['id'];

          }
    }

	public function mostrar_hora($hora_param, $control_hora, $control_minuto, $control_segundo, $control_am)
	{
        $ampm = "AM"; // valor inicial que cambia si es mayor de medio dia.
        $hcom = $hora_param[0].$hora_param[1];
        if ($hcom > "11") {$ampm = "PM";}
        if ($hcom > "12") {$hcom = $hcom - 12;}
        if (strlen($hcom) < 2) {$hcom = "0".$hcom;}
        $mcom = $hora_param[3].$hora_param[4];
        $scom = $hora_param[6].$hora_param[7];
        $control_hora->SelectedValue = $hcom;
        $control_minuto->SelectedValue = $mcom;
        $control_segundo->SelectedValue = $scom;
        $control_am->SelectedValue = $ampm;
    }

	public function obtener_hora($formato, $control_hora, $control_minuto, $control_segundo, $control_am)
	{
        $hcom = $control_hora->SelectedValue;
        $mcom = $control_minuto->SelectedValue;
        $scom = $control_segundo->SelectedValue;
        $ampm = " ".$control_am->SelectedValue; // este espacio es para ahorrarme un condicional

        if ($formato == "24") // formato en 12 o 24 horas
        {
            if ($ampm == " PM")
            {
                if ($hcom != "12")
                {$hcom = $hcom + 12;}
            }
            if ($ampm == " AM")
            {
                if ($hcom == "12")
                {$hcom = "00";}
            }
            $ampm = ""; // para que no se envie como parte del resultado
        }
        $hora_resultado = $hcom.":".$mcom.":".$scom.$ampm;
        return $hora_resultado;
    }


    public function incluir_click($sender,$param)
    {
        if ($this->IsValid)
        {
            $hora_entrada = $this->obtener_hora("24",$this->hhe,$this->mhe,$this->she,$this->ahe);
            $hora_salida = $this->obtener_hora("24",$this->hhs,$this->mhs,$this->shs,$this->ahs);
            $hora_salida_almuerzo = $this->obtener_hora("24",$this->hsa,$this->msa,$this->ssa,$this->asa);
            $hora_entrada_almuerzo = $this->obtener_hora("24",$this->hea,$this->mea,$this->sea,$this->aea);
            $cod_organizacion = usuario_actual('cod_organizacion');
            $holgura_entrada = $this->dropholguraentrada->SelectedValue;
            $almuerzo_minutos = $this->dropminalmuerzo->SelectedValue;
            $dias_contar = $this->dropdiascontar->SelectedValue;
            $dias_restar = $this->dropdiasrestar->SelectedValue;
            $max_pot = $this->droppermisos->SelectedValue;
            //$vigencia_desde = date("Y-m-d");
            $vigencia_desde = cambiaf_a_mysql($this->txt_fecha_desde->Text);
            $vigencia_anterior = date("Y-m-d",strtotime("$vigencia_desde -1 day"));   
            //$vigencia_anterior = date("Y-m-d",strtotime("-1 day"));
            $vigencia_hasta = "2035-12-31";
            $id = $this->campo_id->Value;//opciones
            //$htc = $this->htc->SelectedValue;
            $mtc = $this->mtc->SelectedValue;
            //$ntc = $this->ntc->SelectedValue;
            $id2 = $this->campo_id2->Value;//opciones_ticket

            $sql="insert into asistencias.opciones (cod_organizacion, hora_entrada, hora_salida,
                  holgura_entrada, almuerzo_salida, almuerzo_entrada, almuerzo_minutos, max_pot,
                  dias_a_contar, dias_a_restar, vigencia_desde, vigencia_hasta, status)
                  values ('$cod_organizacion','$hora_entrada','$hora_salida','$holgura_entrada','$hora_salida_almuerzo',
                          '$hora_entrada_almuerzo','$almuerzo_minutos','$max_pot','$dias_contar','$dias_restar',
                          '$vigencia_desde','$vigencia_hasta','1')";
            $resultado=modificar_data($sql,$sender);

            $sql="update asistencias.opciones set status = '0', vigencia_hasta = '$vigencia_anterior' where id = '$id'";
            $resultado=modificar_data($sql,$sender);

            $sql="insert into asistencias.opciones_ticket (cod_organizacion,horas_para_restar,minutos_para_restar, tickets_a_restar, vigencia_desde, vigencia_hasta, status)
                  values ('$cod_organizacion','','$mtc','01','$vigencia_desde','$vigencia_hasta','1')";
            $resultado=modificar_data($sql,$sender);

            $sql="update asistencias.opciones_ticket set status = '0', vigencia_hasta = '$vigencia_anterior' where id = '$id2'";
            $resultado=modificar_data($sql,$sender);
        }
    }

}

?>