<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Incluye un nuevo Oficio en la Dirección seleccionada.
 *****************************************************  FIN DE INFO
*/
class incluir_oficio extends TPage
{

	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
               $this->txt_fecha->Text = date("d/m/Y");
              // Llena la lista de años
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              // coloca el año "Presente"
              $ano = ano_documentos(usuario_actual('cod_organizacion'),1,$this);
              $this->lbl_ano->Text = $ano[0]['ano'];
              // Llena la lista de Direcciones
              $cod_organizacion = usuario_actual('cod_organizacion');
              $sql="select codigo, nombre_completo as nombre from organizacion.direcciones where (codigo_organizacion='$cod_organizacion' ) order by nombre_completo";
              $resultado=cargar_data($sql,$this);
              $this->drop_direcciones->DataSource=$resultado;
              $this->drop_direcciones->dataBind();
          }
	}

/* Valida que la fecha que esté colocando el usuario como fecha del oficio
 * no sea menor a fechas anteriormente incluidas en la base de datos para esa
 * dirección en ese año, con esto se evita de que un memo numero 0005 sea
 * registrado despues (en fecha) que un memo nro 0006
 */
    function validar_fecha ($sender, $param)
    {
        $cod_direccion = $this->drop_direcciones->SelectedValue;
        $ano = $this->lbl_ano->Text;
        $fecha = cambiaf_a_mysql ($this->txt_fecha->Text);
        $sql = "select max(fecha) as fecha from organizacion.oficios
                    where ((ano = '$ano') and (direccion = '$cod_direccion'))";
        $fecha_db = cargar_data($sql,$this);
        if ($fecha < $fecha_db[0]['fecha'])
        { $param->IsValid = False;}
        else
        { $param->IsValid = True;}
    }

/* Incluye el nuevo memorando en la base de datos */
    function incluir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            $this->incluir->Enabled=False;//Se deshabilita boton incluir
            // se capturan los valores de los controles
            $cod_direccion = $this->drop_direcciones->SelectedValue;
            
            // para obtener las siglas de la direccion seleccionada
            $sql = "select siglas from organizacion.direcciones
                        where (codigo = '$cod_direccion')";
            $siglas_result = cargar_data($sql,$this);
            $cod_direccion=substr($cod_direccion, 1);
            $siglas = $siglas_result[0]['siglas'];
            $ano = $this->lbl_ano->Text;
            $criterios_adicionales=array('direccion' => $cod_direccion,
                                         'ano' => $ano);
            $numero=proximo_numero("organizacion.oficios","correlativo",$criterios_adicionales,$this);
            $numero=rellena($numero,4,"0");
            $fecha = cambiaf_a_mysql ($this->txt_fecha->Text);
            $asunto = str_replace("'", '\\\'', $this->txt_asunto->Text );
            $destinatario = str_replace("'", '\\\'', $this->txt_destinatario->Text );
            $siglas_usuario = usuario_actual('siglas_direccion');
            $cedula_usuario= usuario_actual('cedula');
            $cod_direccion_usuario = usuario_actual('cod_direccion');
            
            // se inserta en la base de datos
            $sql = "insert into organizacion.oficios
                    (direccion, siglas, correlativo, ano, fecha, asunto, destinatario, 
                     cod_dir_solicitante, dir_solicitante, cedula_solicitante, status)
                    values ('$cod_direccion','$siglas','$numero','$ano','$fecha','$asunto',
                            '$destinatario','$cod_direccion_usuario','$siglas_usuario',
                            '$cedula_usuario','1')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluido el Oficio: ".$siglas."-".$numero."-".$ano;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->LTB->titulo->Text = "Solicitud de número de Oficio";
            $this->LTB->texto->Text = "Se ha registrado exitosamente el nuevo Oficio con el ".
                                      "número: <strong>".$siglas."-".$numero."-".$ano."</strong>";
            $this->LTB->imagen->Imageurl = "imagenes/botones/memoranda.png";
            $this->LTB->redir->Text = "epaper.incluir_oficio";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

             //$this->mensaje2->setSuccessMessage($sender, "Se ha registrado exitosamente el nuevo Oficio con el numero:".$siglas."-".$numero."-".$ano, 'grow');


        }

    }
}
?>