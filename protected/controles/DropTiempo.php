<?php
/* Creado por:  Pedro E. Arrioja M.  pedroarrioja@gmail.com
 * Descripcion: Este Control fue diseñado para facilitar la captura de una hora
 *              en particular.
 */
class DropTiempo extends TTemplateControl
{
    public function getdrophora() {
        $this->ensureChildControls();
        return $this->getRegisteredObject('drophora');
    }

    public function getdropmin() {
        $this->ensureChildControls();
        return $this->getRegisteredObject('dropmin');
    }
    public function getdropseg() {
        $this->ensureChildControls();
        return $this->getRegisteredObject('dropseg');
    }
    public function getdropampm() {
        $this->ensureChildControls();
        return $this->getRegisteredObject('dropampm');
    }

	public function onLoad($param)
	{
//        parent::onLoad($param);
        if (!$parent->IsPostBack)
        {
         //   $this->ss->Text = "aja";
          $horas = array();
          $minutos = array();
          $segundos = array();
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
          $this->drophora->Datasource = $horas;
          $this->drophora->dataBind();

    // se llena el drop de los minutos
          for ($x = 0 ; $x < 60 ; $x++)
          {
              if ($x < 10)
              {   $minuto ='0'.$x;}
              else
              {$minuto = $x;}
              $minutos[$minuto] = $minuto;
          }
          $this->dropmin->Datasource = $minutos;
          $this->dropmin->dataBind();

    // se llena el drop de los minutos
          for ($x = 0 ; $x < 60 ; $x++)
          {
              if ($x < 10)
              {   $segundo ='0'.$x;}
              else
              {$segundo = $x;}
              $segundos[$segundo] = $segundo;
          }
          $this->dropseg->Datasource = $segundos;
          $this->dropseg->dataBind();

          $this->dropampm->Datasource = $ampm;
          $this->dropampm->dataBind();

          // se colocan valores por defecto
        $this->drophora->SelectedValue = "01";
        $this->dropmin->SelectedValue = "01";
        $this->dropseg->SelectedValue = "01";
        $this->dropampm->SelectedValue = "AM";
        }
        else
        {
            $this->ss->Text = "PO";
        }

    }

	public function mostrar_hora($hora_param)
	{
        $ampm = "AM"; // valor inicial que cambia si es mayor de medio dia.
        $hcom = $hora_param[0].$hora_param[1];
        if ($hcom > "11") {$ampm = "PM";}
        if ($hcom > "12") {$hcom = $hcom - 12;}
        if (strlen($hcom) < 2) {$hcom = "0".$hcom;}
        $mcom = $hora_param[3].$hora_param[4];
        $scom = $hora_param[6].$hora_param[7];
        $this->drophora->SelectedValue = $hcom;
        $this->dropmin->SelectedValue = $mcom;
        $this->dropseg->SelectedValue = $scom;
        $this->dropampm->SelectedValue = $ampm;
    }

	public function obtener_hora($formato)
	{
        $hcom = $this->drophora->SelectedValue;
    // $this->drophora->SelectedValue = "55";
        $mcom = $this->dropmin->SelectedValue;
        $scom = $this->dropseg->SelectedValue;
        $ampm = " ".$this->dropampm->SelectedValue; // este espacio es para ahorrarme un condicional

        if ($formato == "24") // formato en 12 o 24 horas
        {
            if ($ampm == " PM")
            {
                $hcom = $hcom + 12;
            }
            $ampm = ""; // para que no se envie como parte del resultado
        }
        $hora_resultado = $hcom.":".$mcom.":".$scom.$ampm;
  //      $hora_resultado = $ampm;
        return $hora_resultado;
    }


}

?>
