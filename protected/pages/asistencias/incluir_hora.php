<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Esta página registra horas en la asistencia
 *****************************************************  FIN DE INFO
*/
include("protected/comunes/libchart/classes/libchart.php");
class incluir_hora extends TPage
{
    var $justificaciones; // info de las justificaciones 
    var $asistentes;
    var $inasistentes;

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            $this->txt_fecha_desde->Text = date("d/m/Y",strtotime("-1 day"));
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select u.id, u.status_asistencia, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
						ORDER BY p.nombres, p.apellidos";
            $resultado=cargar_data($sql,$this);
         
            // Se enlaza el nuevo arreglo con el listado de Direcciones
            $this->drop_funcionario->DataSource=$resultado;
            $this->drop_funcionario->dataBind();
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

            $cedula=$this->drop_funcionario->SelectedValue;
            $fecha = cambiaf_a_mysql($this->txt_fecha_desde->Text);
            $hora= date("H:i",strtotime($this->txt_hora_desde->Text));
            $sql="insert into asistencias.entrada_salida (cedula, fecha, hora)
                  values ('$cedula','$fecha', '$hora')";
            $resultado=modificar_data($sql,$sender);

            // Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha incluido la hora:".$this->txt_hora_desde->Text.
                               " del Funcionario Cedula Nro: ".$cedula.
                               " de fecha: ".$this->txt_fecha_desde->Text;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            $this->mensaje->setSuccessMessage($sender, $descripcion_log, 'grow');

        }
 	}

     public function btn_cancelar_click($sender, $param){

        $this->Response->Redirect( $this->Service->constructUrl('asistencias.incluir_hora'));//

    }

}

?>