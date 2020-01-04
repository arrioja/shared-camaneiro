<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: En esta página se guardan el saldo inicial de una retencion
 *              de la institución.
 *****************************************************  FIN DE INFO
*/
class incluir_retencion extends TPage
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


   function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // se capturan los valores de los controles
            $ano = $this->drop_ano->selectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $codigo_sin_descomponer=$this->txt_codigo->Text;
            $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));
            $descripcion = strtoupper($this->txt_descripcion->Text);
            $monto = floatval($this->txt_monto->Text);
           
           
            $sql = "SELECT * FROM presupuesto.retencion
                    WHERE (cod_organizacion ='$cod_organizacion' AND ano ='$ano' AND codigo ='$codigo_sin_descomponer')";
            $existe = cargar_data($sql,$this);
            
            if( !empty($existe)) // se actualiza en la base de datos
                $sql = "UPDATE presupuesto.retencion
                    SET saldo_inicial='$monto', descripcion='$descripcion' 
                    WHERE (cod_organizacion ='$cod_organizacion' AND ano ='$ano' AND codigo ='$codigo_sin_descomponer')";
           
            else// se inserta en la base de datos    
                $sql = "INSERT INTO presupuesto.retencion
                    (cod_organizacion, ano, codigo, saldo_inicial, descripcion)
                    VALUES ('$cod_organizacion','$ano','$codigo_sin_descomponer','$monto','$descripcion')";
           
           
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Insertada Retencion. /año:".$ano." /Cod: ".$codigo_sin_descomponer." /Desc: ".$descripcion." /Monto: ".$monto;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            // para asegurarme de autorecargar la pagina hago un llamado a ella misma.
            $this->Response->redirect($this->Service->constructUrl($this->Page->getPagePath()));
        }

    }
}
?>
