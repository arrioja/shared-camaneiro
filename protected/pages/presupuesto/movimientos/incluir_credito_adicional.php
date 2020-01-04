<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: En esta página incluyen créditos adicionales al presupuesto
 *              de gastos.
 *****************************************************  FIN DE INFO
*/
class incluir_credito_adicional extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_codigo_temporal->Text = aleatorio_traslados($this);
              $cod_organizacion = usuario_actual('cod_organizacion');

              $codigo = codigo_unidad_ejecutora($this);
              if (empty($codigo) == false)
              { $this->txt_codigo->Mask = $codigo."-###-##-##-##-#####"; }

              // coloca el año presupuestario "Presente"
              $ano = ano_presupuesto($cod_organizacion,1,$this);
              $this->lbl_ano->Text = $ano[0]['ano'];
              
              $this->txt_fecha->Text = date("d/m/Y");
              $this->lbl_total_aumento->Text="0.00";

              $this->actualiza_listado();
          }
    }

/* Esta función valida la existencia del código presupuestario en la tabla
 * de presupuestos, de no existir, se muestra un mensaje de error.
 */
    public function validar_existencia($sender, $param)
    {
       $ano = $this->lbl_ano->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $sql = "select * from presupuesto.presupuesto_gastos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                            (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
                            (ordinal = '$codigo[ordinal]') and (acumulado = '0'))";
       $existe = cargar_data($sql,$this);
       $param->IsValid = !(empty($existe));
    }

/* Esta función evita que se incluya el mismo codigo presupuestario 2 veces en
 * el mismo listado para créditos adicionales.
 */
    public function validar_incluido($sender, $param)
    {
       $ano = $this->lbl_ano->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $aleatorio = $this->lbl_codigo_temporal->Text;
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $sql = "select * from presupuesto.temporal_creditos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (numero = '$aleatorio') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                            (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
                            (ordinal = '$codigo[ordinal]'))";
       $existe = cargar_data($sql,$this);
       $param->IsValid = empty($existe);
    }

/* Esta funcion actualiza el listado de los codigos presupuestarios que se
 * muestran en el ActiveDataGrid.
 */
    function actualiza_listado()
    {
        // se actualiza el listado
        $cod_organizacion = usuario_actual('cod_organizacion');
        $numero = $this->lbl_codigo_temporal->Text;
        $sql = "select id, CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto_aumento from presupuesto.temporal_creditos
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))
              order by codigo";
        $datos = cargar_data($sql,$this);
        $this->DataGrid->Datasource = $datos;
        $this->DataGrid->dataBind();
        
        // si no hay nada que listar, se deshabilita el boton incluir
        $this->btn_incluir->Enabled = !(empty($datos));

        // se actualiza la sumatoria total
        $sql = "select SUM(monto_aumento) as total_aumento
                from presupuesto.temporal_creditos
                where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))";
        $datos = cargar_data($sql,$this);
       // para evitar que se muestre null cuando viene vacio // aumentos
        if (empty($datos[0]['total_aumento']) == false)
        { $this->lbl_total_aumento->Text=$datos[0]['total_aumento'];}
        else
        { $this->lbl_total_aumento->Text="0.00";}

    }

/* esa funcion añade el codigo presupuestario en la tabla temporal con el fin
 * de conservarlo ahí hasta que se incluya definitivamente en la orden.
 */
    function anadir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
            $numero = $this->lbl_codigo_temporal->Text;
            $ano=$this->lbl_ano->Text;

            $cod_organizacion = usuario_actual('cod_organizacion');
            $monto = $this->txt_monto->Text;

            $sql = "insert into presupuesto.temporal_creditos
                        (ano, cod_organizacion, numero, sector, programa, subprograma, proyecto, actividad, partida,
                         generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto_aumento)
                        values ('$ano','$cod_organizacion','$numero','$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                                '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                                '$codigo[subespecifica]','$codigo[ordinal]','00','$monto')";
            $resultado=modificar_data($sql,$sender);
            $this->actualiza_listado();
            // esto borra los datos de los controles de codigo y monto, no es muy
            // elegante pero funciona.  Lo que no funciona es el envio del foco al control de codigo.
            $this->txt_monto->Text="0.00";
            $codigo = codigo_unidad_ejecutora($this);
            if (empty($codigo) == false)
            {
                $this->txt_codigo->Mask = $codigo."-###-##-##-##-#####";
                $this->txt_codigo->Text=$codigo."-___-__-__-__-_____";
            }
            else
            {
                $this->txt_codigo->Mask = $codigo."##-##-##-##-##-###-##-##-##-#####";
                $this->txt_codigo->Text=$codigo."__-__-__-__-__-___-__-__-__-_____";
            }

            $this->setFocus($this->txt_codigo);
        }
    }

/* esta funcion elimina un codigo presupuestario del listado */
	public function eliminar($sender,$param)
	{
		$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM presupuesto.temporal_creditos WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();
	}

    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            // captura de datos desde los controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano=$this->lbl_ano->Text;
            $fecha=cambiaf_a_mysql($this->txt_fecha->Text);
            $numero_documento = $this->txt_numero_doc->Text;
            $aleatorio = $this->lbl_codigo_temporal->Text;
            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                          'ano' => $ano);
            $numero=proximo_numero("presupuesto.maestro_creditos","numero",$criterios_adicionales,$this);
            $numero=rellena($numero,6,"0");
            $motivo=$this->txt_motivo->Text;
            $monto_total=$this->lbl_total_aumento->Text;

            // insersión en la base de datos de la información del maestro.
            $sql = "insert into presupuesto.maestro_creditos
                    (cod_organizacion, ano, numero, fecha, num_documento, motivo, monto_total)
                    values ('$cod_organizacion','$ano','$numero','$fecha','$numero_documento','$motivo','$monto_total')";
            $resultado=modificar_data($sql,$sender);

            // Se incluye lo que esta en temporal a la tabla detalle
            $sql = "insert into presupuesto.detalle_creditos
                    (ano, cod_organizacion, numero, sector, programa, subprograma, proyecto, actividad, partida,
                     generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto_aumento)
	   					select t.ano, t.cod_organizacion, t.numero, t.sector, t.programa, t.subprograma, t.proyecto, t.actividad, t.partida,
                        t.generica, t.especifica, t.subespecifica, t.ordinal, t.cod_fuente_financiamiento, t.monto_aumento
                        from presupuesto.temporal_creditos as t where (t.numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            // Se cambia el numero aleatorio por el numero que realmente que debe tener.
            $sql = "update presupuesto.detalle_creditos set numero='$numero' where (numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            // antes de eliminar los de la tabla temporal, modifico los acumulados correspondientes
            $sql = "select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo, monto_aumento
              from presupuesto.temporal_creditos
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$aleatorio'))
              order by codigo";
            $datos = cargar_data($sql,$this);
            if (empty($datos[0]['codigo']) == false)
            {
                foreach ($datos as $undato)
                {
                    $codi= solo_numeros($undato['codigo']);
                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'aumentos', '+', $undato['monto_aumento'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '+', $undato['monto_aumento'], $this);
                }
            }
              // Se eliminan los registros aleatorios que habia en la tabla temporal.
            $sql = "delete from presupuesto.temporal_creditos where (numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido el Crédito adicional: ".$numero. " / año: ".$ano.
                               " por un monto total de Bs. ".$monto_total." Motivo: ".$motivo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->Response->redirect($this->Service->constructUrl('Home'));
        }
    }
}
?>