<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Mediante esta página, El usuario puede aprobar o rechazar las
 *              solicitudes de vacaciones que se encuentren pendientes; es
 *              necesario tener el permiso apropiado y el nivel requerido.
 *****************************************************  FIN DE INFO
*/

class aprobar_rechazar extends TPage
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
            $cedula = usuario_actual('cedula');
            $resultado = vista_usuario($cedula,$cod_organizacion,'D',$this);

            // Se enlaza el nuevo arreglo con el listado de Direcciones
            $this->drop_direcciones->DataSource=$resultado;
            $this->drop_direcciones->dataBind();
        }
    }

/* Esta función se encarga de mostrar el listado con las solicitudes de vacaciones pendientes para la
 * dirección seleccionada.
 */
    public function consulta_permisos($sender, $param)

    {
        $dir = $this->drop_direcciones->SelectedValue;
        if($dir!=""){
            $sql ="SELECT  j.id,jd.fecha_desde as desde,jd.fecha_hasta as hasta ,jd.hora_desde,jd.hora_hasta,tf.descripcion as falta,tj.descripcion as tipo,p.cedula,CONCAT(p.nombres,' ',p.apellidos) as nombres
            FROM asistencias.justificaciones_personas AS jp
            INNER JOIN asistencias.justificaciones_dias AS jd ON ( jd.codigo_just = jp.codigo_just )
            INNER JOIN asistencias.justificaciones AS j ON ( j.codigo = jp.codigo_just )
            INNER JOIN asistencias.tipo_faltas AS tf ON ( tf.codigo = jd.codigo_tipo_falta )
            INNER JOIN organizacion.personas AS p ON (p.cedula=jp.cedula)
            INNER JOIN asistencias.tipo_justificaciones AS tj ON ( tj.codigo = jd.codigo_tipo_justificacion )
            INNER JOIN organizacion.personas_nivel_dir AS pnd ON ( pnd.cod_direccion Like '$dir%' AND pnd.cedula=jp.cedula )
            WHERE j.estatus = '0'
            ORDER BY pnd.cedula,j.id  ";
            $this->permisos=cargar_data($sql,$sender);

            $this->DataGrid->DataSource=$this->permisos;
            $this->DataGrid->dataBind();
        }//fin si

    }


    public function aprobar_rechazar_click($opcion,$sender,$param)
    {
        // se marca el registro como aprobado o rechazado.
        $id=$sender->CommandParameter;
		$sql="UPDATE asistencias.justificaciones set estatus='$opcion' where id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->consulta_permisos($sender, $param);

        // ahora, dependiendo si se aprueba o se rechaza, se toman acciones.
        if ($opcion == 2)
        { // si la solicitud fue rechazada, se muestra el mensaje correspondiente
        // se incluye el registro en la bitácora de eventos
            $descripcion_log = "Se ha rechazado la solicitud de permiso registrada con el id: ".$id;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'D',$descripcion_log,"",$sender);
            $this->LTB->titulo->Text = "Solicitud de Permiso Rechazada";
            $this->LTB->texto->Text = "La Solicitud de permiso ha sido rechazada.";
            $this->LTB->imagen->Imageurl = "imagenes/botones/mal.png";
        }else{

            $sql ="SELECT  j.codigo,j.id,jd.fecha_desde as desde,jd.fecha_hasta as hasta ,jd.hora_desde,jd.hora_hasta,tf.descripcion as falta,tj.descripcion as tipo,p.cedula,CONCAT(p.nombres,' ',p.apellidos) as nombres
                    FROM asistencias.justificaciones_personas AS jp
                    INNER JOIN asistencias.justificaciones AS j ON ( j.codigo = jp.codigo_just )
                    INNER JOIN asistencias.justificaciones_dias AS jd ON ( jd.codigo_just = jp.codigo_just )
                    INNER JOIN asistencias.tipo_faltas AS tf ON ( tf.codigo = jd.codigo_tipo_falta )
                    INNER JOIN organizacion.personas AS p ON (p.cedula=jp.cedula)
                    INNER JOIN asistencias.tipo_justificaciones AS tj ON ( tj.codigo = jd.codigo_tipo_justificacion )
                    INNER JOIN organizacion.personas_nivel_dir AS pnd ON ( pnd.cod_direccion Like '$dir%' AND pnd.cedula=jp.cedula )
                    WHERE  j.id='$id'
                    ORDER BY pnd.cedula,j.id";
            $resultado=cargar_data($sql,$sender);
            $nombres = $resultado[0]['nombres'];
            $cedula = $resultado[0]['cedula'];
            $desde = $resultado[0]['desde'];
            $hasta = $resultado[0]['hasta'];
            $nombres = $resultado[0]['nombres'];
            $codigo_nuevo=$resultado[0]['codigo'];


            // se incluye el registro en la bitácora de eventos
            $descripcion_log = "<strong>Aprobado</strong> permiso de ".$nombres." C.I.: ".$cedula.
                               ", desde ".cambiaf_a_normal($desde)." al ".cambiaf_a_normal($hasta).
                                " <strong> Cód. Observación: ".$codigo_nuevo."</strong> ";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'A',$descripcion_log,"",$sender);
            $this->LTB->titulo->Text = "Aprobado";
            $this->LTB->texto->Text = $descripcion_log;
            $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
        }

        $params = array('mensaje');
        $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

    }

    public function aprobar_click($sender,$param)
    {
        $this->aprobar_rechazar_click('1',$sender, $param);
    }

    public function rechazar_click($sender,$param)
    {
        $this->aprobar_rechazar_click('2',$sender, $param);
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

        if ($item->cedula->Text != "Cedula")
        {
            if ($this->cedula_conteo != $item->cedula->Text )
                {
                    $item->cedula->RowSpan = $this->cuenta_cedulas($this->permisos,$item->cedula->Text);
                    $item->nombres->RowSpan = $item->cedula->RowSpan;
                    $this->cedula_conteo = $item->cedula->Text;
                    $item->cedula->BackColor = $color1[$this->conteo];
                    $item->nombres->BackColor = $color1[$this->conteo];
                    $this->conteo = !$this->conteo;
                }
            else
                {
                    $item->cedula->Visible = false;
                    $item->nombres->Visible = false;
                }
        }

        if ($item->hasta->Text != "Hasta")
        {

            $hora = date_create($item->hora_hasta->Text);
            $item->hora_hasta->Text= date_format($hora, 'h:i:s');
            
            $item->hasta->Text = cambiaf_a_normal($item->hasta->Text)." ".$item->hora_hasta->Text;

        }
        if ($item->desde->Text != "Desde")
        {
            $item->desde->Text = cambiaf_a_normal($item->desde->Text)." ".$item->hora_desde->Text;
        }
    }


}
?>
