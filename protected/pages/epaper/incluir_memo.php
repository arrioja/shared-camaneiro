<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Incluye un nuevo Memorando en la Dirección a la que pertenece
 *              el usuario.
 *****************************************************  FIN DE INFO
*/
class incluir_memo extends TPage
{
	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          {
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_direccion->Text = usuario_actual('nombre_direccion');
              // coloca el año "Presente"
              $ano = ano_documentos(usuario_actual('cod_organizacion'),1,$this);
              $this->lbl_ano->Text = $ano[0]['ano'];
              $this->txt_fecha->Text = date("d/m/Y");
              $aleatorio = $this->numero->Text;
              if ($aleatorio == '')
              {
                  $aleatorio=rand(0000,9999);//genero un aleatorio
                  $sql="select correlativo from organizacion.adjuntos where(correlativo='$aleatorio')";//lo busco en la db
                  $resultado=cargar_data($sql, $this);
                  while($aleatorio==$resultado[0]['correlativo'])//mientras que el aleatorio=correlativo entra en el bucle
                  {
                    $aleatorio=rand(0000,9999);//genero un aleatorio
                    $sql="select correlativo from organizacion.adjuntos where(correlativo='$aleatorio')";//lo busco en la db
                    $resultado=cargar_data($sql, $this);//lo busco en la db y vuelvo al inicio del bucle
                    //si los aleatorios se agotan da un error de tiempo maximo de ejecucion
                  }
                  $this->numero->Text = $aleatorio;
              }
          }
	}
/* Valida que la fecha que esté colocando el usuario como fecha del memorando
 * no sea menor a fechas anteriormente incluidas en la base de datos para esa
 * dirección en ese año, con esto se evita de que un memo numero 0005 sea
 * registrado despues (en fecha) que un memo nro 0006
 */
    function validar_fecha ($sender, $param)
    {
        $cod_direccion = usuario_actual('cod_direccion');
        $ano = $this->lbl_ano->Text;
        $fecha = cambiaf_a_mysql ($this->txt_fecha->Text);
        $sql = "select max(fecha) as fecha from organizacion.memoranda
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
            $aleatorio = $this->numero->Text;
            $cod_direccion = usuario_actual('cod_direccion');
            $cod_direccion=substr($cod_direccion, 1);
            $siglas = usuario_actual('siglas_direccion');
            $ano = $this->lbl_ano->Text;
            $asunto = str_replace("'", '\\\'', $this->txt_asunto->Text );
            $destinatario = str_replace("'", '\\\'', $this->txt_destinatario->Text );
            $enviadopor=usuario_actual('login');
            $fecha = cambiaf_a_mysql ($this->txt_fecha->Text);
            //$memo=$this->html1->text;
            //busca el nombre del remitente
            $sql3="select cedula from intranet.usuarios where(login='$enviadopor')";
            $resultado3=cargar_data($sql3, $sender);
            $cedula=$resultado3[0]['cedula'];
            $sql3="select nombres, apellidos from organizacion.personas where(cedula='$cedula')";
            $resultado3=cargar_data($sql3, $sender);
            $remitente=$resultado3[0]['nombres'].' '.$resultado3[0]['apellidos'];
            $criterios_adicionales=array('direccion' => $cod_direccion, 'ano' => $ano);
            $numero=proximo_numero("organizacion.memoranda","correlativo",$criterios_adicionales,$this);
            $numero=rellena($numero,4,"0");
            // se inserta en la base de datos
            $sql = "insert into organizacion.memoranda
                    (direccion, siglas, correlativo, ano, fecha, asunto, destinatario, status, memo, remitente)
                    values ('$cod_direccion','$siglas','$numero','$ano','$fecha','$asunto','$destinatario','1','$memo', '$remitente')";            
            $resultado=modificar_data($sql,$sender);
            /*$sql2="update organizacion.adjuntos set correlativo='$numero' where (correlativo='$aleatorio')";
            $resultado2=modificar_data($sql2, $sender);*/            
            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluido el Memorando: ".$siglas."-".$numero."-".$ano;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            $this->LTB->titulo->Text = "Solicitud de número de Memorando";
            $this->LTB->texto->Text = "Se ha registrado exitosamente el nuevo Memorando con el ".
                                      "número: <strong>".$siglas."-".$numero."-".$ano."</strong>";
            $this->LTB->imagen->Imageurl = "imagenes/botones/memoranda.png";
            $this->LTB->redir->Text = "epaper.incluir_memo";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

            //$this->mensaje2->setSuccessMessage($sender, "Se ha registrado exitosamente el nuevo Memorando con el numero:".$siglas."-".$numero."-".$ano, 'grow');

        }
    }
    /*function cargar($sender, $param)
    {        
        //el adjunto sera subido al presionar el boton cargar, el boton incluir solo inserta en la db el resto de los controles
        if($sender->HasFile)
       	{
            $aleatorio = $this->numero->Text;            
            $cod_direccion = usuario_actual('cod_direccion');            
            $siglas = usuario_actual('siglas_direccion');
            $ano = $this->drop_ano->Text;
            $ruta="attach/";//ruta donde se guardan los adjuntos que se han subido
            $codigo=rand(0000000,99999999);//genera un aleatorio de 8 digitos que representa el nombre del adjunto        
            while(file_exists($ruta.$codigo))//mientras exista un archivo con el mismo nombre del codigo
            {
                $codigo=rand(00000000,99999999);//genero otro aleatorio
            }
            if(file_exists($ruta.$codigo))
            {
                echo "error";//da un error si todos lo aleatorios ya existen
            }
            else//si no existe el archivo con el aleatorio inserto los datos en la db y subo el archivo al servidor
            {
                $sql="insert into organizacion.adjuntos (direccion, siglas, correlativo, ano, nom_adjunto,cod_adjunto)
                      values('$cod_direccion', '$siglas', '$aleatorio', '$ano', '$sender->FileName','$codigo')";
                $resultado=modificar_data($sql, $sender);                
                $this->t1->text=$this->t1->text.$codigo."-".$sender->FileName."\n";
                $sender->saveAs($ruta.$codigo);
            }
       	}
    }*/
}
?>