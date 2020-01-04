<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: Ronald A. Salazar C.
 * Descripción: Esta pagina permite incluir tickets de SUMA o RESTA de un mes y año deseado
 * a un funcionario seleccionado
 *****************************************************  FIN DE INFO
*/
//include("protected/comunes/libchart/classes/libchart.php");
class incluir_ticket extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
               // se toma año de los registros de entrada_salida
             $this->drop_ano->Datasource = ano_asistencia($this);
             $this->drop_ano->dataBind();
             $cod_organizacion = usuario_actual('cod_organizacion');

             //meses
             $arreglo=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
             $this->drop_mes->DataSource=$arreglo;
             $this->drop_mes->dataBind();

             $cod_organizacion = usuario_actual('cod_organizacion');
             $sql="select u.id, u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
						ORDER BY p.nombres, p.apellidos";
            $resultado=cargar_data($sql,$this);
            $this->drop_funcionario->DataSource=$resultado;
            $this->drop_funcionario->dataBind();

            $this->drop_tipo->DataSource=array('SUMA'=>'AGREGAR', 'RESTA'=>'DISMINUIR');
            $this->drop_tipo->dataBind();

            for($i=1;$i<=31;$i++) $arreglo_c[$i]['nombre']=$i;
            $this->drop_cantidad->DataSource=$arreglo_c;
            $this->drop_cantidad->dataBind();
        }
    }

    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            $this->btn_incluir->Enabled=false;
            
            $cod_organizacion = usuario_actual('cod_organizacion');
            $cedula = $this->drop_funcionario->SelectedValue;
            $ano = $this->drop_ano->SelectedValue;
            $mes = $this->drop_mes->SelectedValue;
            $cantidad = $this->drop_cantidad->SelectedValue;
            $tipo = $this->drop_tipo->SelectedValue;
            $motivo = $this->txt_motivo->Text;

            $sql="insert into asistencias.tickets (cedula, ano, mes, cantidad, tipo, motivo)
                  values ('$cedula', '$ano', '$mes', '$cantidad', '$tipo', '$motivo')";
            $resultado=modificar_data($sql,$sender);

            //Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha incluido la cantidad de ".$cantidad." tickets ".$tipo." Fecha ".$ano."-".$mes.
                                       " del Funcionario Cedula Nro: ".$cedula.
                                       " Motivo: ".$motivo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            $this->mensaje->setSuccessMessage($sender, "Datos registrados exitosamente!!", 'grow');

        }
 	}

   

}
?>