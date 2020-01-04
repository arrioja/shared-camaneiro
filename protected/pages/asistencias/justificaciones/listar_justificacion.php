<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald A. Salazar C.
 * Descripción: Mediante esta página, El usuario puede eliminar y modificar
 *              la fecha hasta de una justificacion
 *****************************************************  FIN DE INFO
*/

class listar_justificacion extends TPage
{
    var $conteo =1;
    var $cedula_conteo = "";
    var $permisos;
    public function onLoad($param)
    {
        parent::onLoad($param);
        if ((!$this->IsPostBack) && (!$this->IsCallBack))
        {
             $cod_organizacion = usuario_actual('cod_organizacion');
            
            $this->consulta_permisos($this, $param);
             // funcionarios activos de asistencia
            $sql="select p.cedula,  CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_organizacion'))
                        ORDER BY p.nombres, p.apellidos";

           $resultado=cargar_data($sql,$this);
           $this->drop_cedula->DataSource=$resultado;
           $this->drop_cedula->dataBind();
        }
    }

/* Esta función se encarga de mostrar el listado con las solicitudes de vacaciones pendientes para la
 * dirección seleccionada.
 */
    public function consulta_permisos($sender, $param)

    {
        $cedula= $this->drop_cedula->SelectedValue;
        $codigo= $this->txt_codigo->Text;

        if($this->drop_cedula->SelectedValue!="") $AND=" AND jp.cedula ='$cedula' ";

        if($this->txt_codigo->Text!="") $AND.=" AND j.codigo ='$codigo' ";

        if($this->txt_fecha->Text!="") $AND.=" AND jd.fecha_desde ='".cambiaf_a_mysql($this->txt_fecha->Text)."' ";

        $sql ="SELECT j.codigo as id,j.codigo, jd.fecha_desde AS desde, jd.fecha_hasta AS hasta, jd.hora_desde, jd.hora_hasta, tf.descripcion AS falta, tj.descripcion AS tipo
            FROM asistencias.justificaciones AS j
            INNER JOIN asistencias.justificaciones_personas AS jp ON ( jp.codigo_just = j.codigo )
            INNER JOIN asistencias.justificaciones_dias AS jd ON ( jd.codigo_just = jp.codigo_just )
            INNER JOIN asistencias.tipo_faltas AS tf ON ( tf.codigo = jd.codigo_tipo_falta )
            INNER JOIN asistencias.tipo_justificaciones AS tj ON ( tj.codigo = jd.codigo_tipo_justificacion )
            WHERE j.estatus = '1' $AND
            GROUP BY j.codigo
            ORDER BY j.codigo DESC ";
        $this->permisos=cargar_data($sql,$sender);
        $this->DataGrid->DataSource=$this->permisos;
        $this->DataGrid->dataBind();
        

    }


    public function interrumpir_click($sender,$param)
    {
        $id=$sender->CommandParameter;

        $sql="Select codigo_tipo_justificacion as tipo FROM asistencias.justificaciones_dias WHERE codigo_just='$id'";
        $resultado=cargar_data($sql,$this);

        //si no es vacacion se puede eliminar
        if ($resultado[0]['tipo']!="001"){

        // se elimina la justificacion
        $sql="DELETE FROM asistencias.justificaciones WHERE codigo='$id' ";
        $resultado=modificar_data($sql,$sender);
        $sql="DELETE FROM asistencias.justificaciones_dias WHERE codigo_just='$id' ";
        $resultado=modificar_data($sql,$sender);
        $sql="DELETE FROM asistencias.justificaciones_personas WHERE codigo_just='$id' ";
        $resultado=modificar_data($sql,$sender);
        $this->consulta_permisos($sender, $param);

         //Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha Eliminado Permiso: #".$id;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        }//fin si eliminar
    
    }

    public function modificar_click($sender,$param)
    {
        $id=$sender->CommandParameter;


        $sql="Select codigo_tipo_justificacion as tipo FROM asistencias.justificaciones_dias WHERE codigo_just='$id'";
        $resultado=cargar_data($sql,$this);
        
        //si no es vacacion se puede modificar
        if ($resultado[0]['tipo']!="001")
        $this->Response->Redirect( $this->Service->constructUrl('asistencias.justificaciones.modificar_justificacion',array('numero'=>$id)));


    }

/* Esta función se encarga de contar cuántas cédulas existen en el arreglo que
 * coincidan con la dada, el número de cédulas será usado para ajustar el RowCount
 * del grid de datos.
 */
    public function cuenta_cedulas($datos, $cedula)
    {
        $contador=0;
        foreach ($datos as $undato)
        {
            if ($undato['cedula'] == $cedula)
            {$contador++;}
        }
        return $contador;
    }


/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $color1 = array("#E6ECFF","#BFCFFF");
        $item=$param->Item;

        if ($item->funcionario->Text != "Funcionario")
        {
             $sql ="SELECT p.cedula,CONCAT(p.nombres,' ',p.apellidos) as nombres
                FROM asistencias.justificaciones_personas AS jp 
                INNER JOIN organizacion.personas AS p ON (p.cedula=jp.cedula)
                WHERE ( jp.codigo_just ='".$item->codigo->Text."' )
               ";
             $resultado=cargar_data($sql,$sender);
              foreach ($resultado as $funcionario)
              $item->funcionario->Text.="C.I. $funcionario[cedula] $funcionario[nombres]</br> ";
        }

        if ($item->hasta->Text != "Hasta")
        {

            //hora hasta
            $horaf=split(':',$item->hora_hasta->Text);
            
            if($horaf[0]==12)$hora_hasta=$item->hora_hasta->Text." PM";
            elseif($horaf[0]>12)$hora_hasta=($horaf[0]-12).":$horaf[1]:$horaf[2] PM";
            else $hora_hasta=$item->hora_hasta->Text." AM";



            $item->hasta->Text = cambiaf_a_normal($item->hasta->Text)." ".$hora_hasta;
            
        }
        if ($item->desde->Text != "Desde")
        {
             //hora hasta
            $horae=split(':',$item->hora_desde->Text);if($horae[0]==12)$hora_desde=$item->hora_desde->Text." PM";

            if($horae[0]==12)$hora_desde=$item->hora_desde->Text." PM";
            elseif(($horae[0]>12)&&($horae[0]!=12))$hora_desde=($horae[0]-12).":$horae[1]:$horae[2] PM";
            else $hora_desde=$item->hora_desde->Text." AM";



            $item->desde->Text = cambiaf_a_normal($item->desde->Text)." ".$hora_desde;
        }

        
            
    }
    public function changePage($sender,$param)
	{
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->consulta_permisos($sender, $param);
	}

    public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');     
	}

}
?>