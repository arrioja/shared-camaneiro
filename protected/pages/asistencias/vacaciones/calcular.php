<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta página se encarga de calcular las vacaciones correspondientes a los
 *              funcionarios y funcionarias del sistema, este proceso de debe hacer sólo una
 *              vez y al principio de cada año fiscal.
 *****************************************************  FIN DE INFO
*/
class calcular extends TPage
{

    public function onLoad($param)
    {
        parent::onLoad($param);
        if ((!$this->IsPostBack) && (!$this->IsCallBack))
        {
            $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
            $this->drop_ano->Datasource = ano_presupuesto(usuario_actual('cod_organizacion'),2,$this);
            $this->drop_ano->dataBind();
        }
    }

/* Se buscan las personas a las que se les debe realizar el calculo de sus vacaciones.
 */
    public function calcular_vacaciones($sender, $param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql ="select pc.fecha_ini as fecha_ingreso,p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombres, i.*
				from organizacion.personas as p, nomina.integrantes_tipo_nomina as i,
                     organizacion.personas_nivel_dir as n, nomina.integrantes as it
                     ,organizacion.personas_cargos as pc

				where (pc.cedula=i.cedula) and
                      (it.status='1' and it.cedula=i.cedula) and (i.tipo_nomina<>'PENSIONADOS') and
					  (i.cedula = p.cedula) and (n.cedula = p.cedula) and
                      (n.cod_organizacion = '$cod_organizacion') and
					  (i.tipo_nomina<>'JUBILADOS')  group by pc.cedula order by p.nombres, p.apellidos, pc.fecha_ini ";
        $resultado=cargar_data($sql,$sender);

        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
    }

/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $item=$param->Item;
        if ($item->fecha_ingreso->Text != "fecha_ingreso")
        {
            //Obtengo algunos datos que necesito para el cálculo
            list($ano_ingreso,$mes_ingreso,$dia_ingreso) = explode("-",$item->fecha_ingreso->Text);
            $ano_actual=$this->drop_ano->SelectedValue;
            $periodo=($ano_actual-1)."-".$ano_actual;
            $cedula=$item->cedula->Text;
            // Se comprueba que la persona no tenga registros de vacaciones para el periodo seleccionado
            $sql ="select * from asistencias.vacaciones as v where (v.cedula='$cedula' and v.periodo='$periodo')";
            $resultado=cargar_data($sql,$sender);
            if (!empty($resultado))
            { // si ya tiene datos, se coloca mensaje de error
                $item->estatus->ForeColor = "Red";
                $item->estatus->Text = "Período ".$periodo." <strong>obviado</strong>, ya existen datos.";
            }
            else
            { // si no tiene datos, se incluyen las vacaciones en la tabla y se muestra la info.
            $antiguedad_otro=0;
                $fecha_calculo=$dia_ingreso."/".$mes_ingreso."/".$ano_actual;
                $fecha_ingreso = cambiaf_a_normal($item->fecha_ingreso->Text);
                $dias_servicio=intval(num_dias_entre_fechas($fecha_ingreso,$fecha_calculo));
                $anos_servicio_cene=intval($dias_servicio/365);
                if ($anos_servicio_cene > 30) { $anos_servicio_cene=30; }

                // Se comprueba que la persona no tenga registros de vacaciones para el periodo seleccionado
                $sql2 ="select anos_antiguedad_otro from asistencias.vacaciones  where (cedula='$cedula')  order by periodo desc limit 1";
                $resultado_años_otro=cargar_data($sql2,$sender);
                $antiguedad_otro=($resultado_años_otro[0]['anos_antiguedad_otro']);
                // se calculan los dias de vacaciones de la siguiente manera (segun la LOT)
                // un dia por cada año de servicio comenzando por quince dias el primer año hasta máximo treinta días.


                $dias_calculados=$anos_servicio_cene+14+$antiguedad_otro;
                if ($dias_calculados > 30) { $dias_calculados=30; }
                $disponible_desde=cambiaf_a_mysql($fecha_calculo);

                // if($cedula=="12225307")$r=cargar_data("anos_servicio_cene=".$anos_servicio_cene." + antiguedad_otro=$antiguedad_otro"." + 14= dias calculados ".$dias_calculados,$sender);

	 
                // se insertan los valores en la tabla vacaciones
                $sql="insert into asistencias.vacaciones (cedula,anos_antiguedad,anos_antiguedad_otro,
                             dias,disfrutados,pendientes,periodo,disponible_desde)
                      values ('$cedula','$anos_servicio_cene','$antiguedad_otro','$dias_calculados','0',
                              '$dias_calculados','$periodo','$disponible_desde')" ;
                $resultado=modificar_data($sql,$this);

                $item->estatus->ForeColor = "Green";
                $item->estatus->Text = "Período ".$periodo.", días: <strong>$dias_calculados</strong>, Status: OK.";

                // se incluye el registro en la bitácora de eventos
                $descripcion_log = "Insertados ".$dias_calculados." dias disponibles de vacación del periodo ".$periodo." para ".$item->nombres->Text.", C.I.: ".$item->cedula->Text;
                inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            }
        }

    }


}

?>
