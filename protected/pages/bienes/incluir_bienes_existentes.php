<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo implementa la inclusión de integrantes a la nomina;
 *              es requisito que se encuentren inscritos como personas, ya que
 *              es ahi donde se almacenan los datos personales del usuario.
     ****************************************************  FIN DE INFO
*/
class incluir_bienes_existentes extends TPage
{
 function cargar($sender, $param)
   {

        if( $sender->FileType != "image/jpeg" )
               { // si es del tipo incorrecto, se muestra el mensaje
                   $this->lbl_tipo_arch->Visible="True";
                   $this->lbl_tipo_arch->Text = " ERROR: Sólo Archivos .JPG
        (".$sender->FileType.")";
               }
               else
               { // si es tipo JPG, no hay problema
                  $this->lbl_tipo_arch->Text = "";
                   //el adjunto sera subido al presionar el boton cargar, elboton incluir solo inserta en la db el resto de los controles
                   if($sender->HasFile)
                   {
                       $n_filename     = strtolower(str_replace(" ", "_",
        $sender->FileName));

                       //$rutap="imagenes/articulos/peq/";//ruta donde seguardan los adjuntos que se han subido
                       //$rutam="imagenes/articulos/med/";//ruta donde seguardan los adjuntos que se han subido
                       $rutan="imagenes/bienes/";//ruta donde seguardan los adjuntos que se han subido
                       $codigo=rand(0000000,99999999);//genera un aleatoriode 8 digitos que representa el nombre del adjunto
                       while(file_exists($rutan.$codigo))//mientras exista unarchivo con el mismo nombre del codigo
                       {
                           $codigo=rand(00000000,99999999);//genero otro aleatorio
                       }
                      // $nombre_p = $rutap.$codigo."_".$n_filename;
                     //  $nombre_m = $rutam.$codigo."_".$n_filename;
                       $nombre_n = $rutan.$codigo."_".$n_filename;

                       $sender->saveAs($nombre_n);
                       $imagen_info = getimagesize($nombre_n);

                    //   Se redimensionan las imagenes a tamaño mediano
                    /* if ($imagen_info[0] < 400) { $nuevo_ancho =
        $imagen_info[0];} else {$nuevo_ancho=400;}
                      if ($imagen_info[1] < 400) { $nuevo_alto =
        $imagen_info[1];} else {$nuevo_alto=400;}
                       resize_imagen ($nombre_n, $nuevo_ancho, $nuevo_alto,
        $nombre_n, 100);*/
        //$this->lbl_tipo_arch->Text=$param->IsValid;
        $this->img_1->ImageUrl=$nombre_n;


                $cod_bien = $this->txt_codigo->Text;
                $cod_imagen=$codigo."_".$n_filename;

        $sql="insert into bienes.bienes_imagenes (cod_bien,cod_imagen)values('$cod_bien','$cod_imagen')";

        $resultado=modificar_data($sql,$sender);
        $this->uploadimagen1->Enabled="False";
                   }
           }
   }
   function click_img($sender, $param)
   {
        if ($this->img_1->ImageUrl!="imagenes/iconos/upload.png")
        {
          $rutap=$this->img_1->ImageUrl;
          elimina_archivo($rutap);

            $resultado_drop = obtener_seleccion_drop($this->cmb_codigos);
            $cod_bien = $resultado_drop[1];

           $cadena=$this->img_1->ImageUrl;
           $maximo = strlen($cadena);
           $cadena_comienzo = "bienes/";
           $total = strpos($cadena,$cadena_comienzo);
           $nombre_archivo = substr ($cadena,$total+7,$maximo);

          $sql="delete from bienes.bienes_imagenes where cod_bien='$cod_bien' and cod_imagen='$nombre_archivo'";
          $resultado=modificar_data($sql,$sender);
          $this->img_1->ImageUrl="imagenes/iconos/upload.png";
          $this->uploadimagen1->Enabled="True";
          $this->lbl_tipo_arch->Text="";
        }
        else
        $this->lbl_tipo_arch->Text="Seleccione Un Archivo";

   }
   
    public function cargar_subgrupo($param)
    {
        //$this->cmb_subgrupo->DataSource="";
        $resultado_drop = obtener_seleccion_drop($this->cmb_grupo);
        $grupo = $resultado_drop[1]; // el segundo valor
       
        $sql="select subgrupo from bienes.subgrupo where grupo='".$grupo."' and cod_organizacion='".usuario_actual('cod_organizacion')."'";
        $subgrupo=cargar_data($sql,$this);
                        // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
                $todos = array('subgrupo'=>'-1', 'subgrupo'=>'Seleccione Subgrupo');
                array_unshift($subgrupo, $todos);
        $this->cmb_subgrupo->DataSource=$subgrupo;
        $this->cmb_subgrupo->dataBind();       
    }
        public function cargar_secciones($param)
    {
        //$this->cmb_subgrupo->DataSource="";
        $resultado_drop = obtener_seleccion_drop($this->cmb_grupo);
        $grupo = $resultado_drop[1]; // el segundo valor

        $resultado_drop = obtener_seleccion_drop($this->cmb_subgrupo);
        $subgrupo = $resultado_drop[1]; // el segundo valor

        $sql="select secciones from bienes.descripcion_bienes_muebles where grupo='".$grupo."' and subgrupo='".$subgrupo."' and cod_organizacion='".usuario_actual('cod_organizacion')."'";
        $secciones=cargar_data($sql,$this);
                $todos = array('secciones'=>'-1', 'secciones'=>'Seleccione Sección');
                array_unshift($secciones, $todos);
        $this->cmb_secciones->DataSource=$secciones;
        $this->cmb_secciones->dataBind();
    }

       public function activa_imagen($param)
    {

        $this->uploadimagen1->Enabled="True";

    }

   public function cargar_descripcion($param)
    {

        //$this->cmb_subgrupo->DataSource="";
        $resultado_drop = obtener_seleccion_drop($this->cmb_grupo);
        $grupo = $resultado_drop[1]; // el segundo valor

        $resultado_drop = obtener_seleccion_drop($this->cmb_subgrupo);
        $subgrupo = $resultado_drop[1]; // el segundo valor

        $resultado_drop = obtener_seleccion_drop($this->cmb_secciones);
        $seccion = $resultado_drop[1]; // el segundo valor

        $sql="select descripcion from bienes.descripcion_bienes_muebles where grupo='".$grupo."' and subgrupo='".$subgrupo."' and secciones='".$seccion."' and cod_organizacion='".usuario_actual('cod_organizacion')."'";
        $secciones=cargar_data($sql,$this);
   

        $sql="select * from organizacion.direcciones where codigo_organizacion='".usuario_actual('cod_organizacion')."'";
        $direcciones=cargar_data($sql,$this);
                    $todos = array('codigo'=>'-1', 'nombre_completo'=>'Seleccione una Dirección');
                array_unshift($direcciones, $todos);
        $this->cmb_direcciones->DataSource=$direcciones;
        $this->cmb_direcciones->dataBind();

        $this->txt_desc->Text=$secciones[0]["descripcion"];


       /* $sql="select * from bienes.bienes_muebles where grupo='$grupo'and subgrupo='$subgrupo' and secciones='$seccion'";
        $cantidades=cargar_data($sql,$this);
        $this->disponibles->text="Códigos de barra disponibles= ".count($cantidades);*/

    }
     /**

      *
      *  CHECK si existe el código **/


function check_codigo($sender,$param)
   { 
     $cod_org=usuario_actual('cod_organizacion');
     $param->IsValid=verificar_existencia("bienes.bienes_muebles","codigo",$this->txt_codigo->Text,null,$sender);

   }

    public function onLoad($param)
    {
         if (!$this->IsPostBack)
        {
        $sql="select * from bienes.grupo where cod_organizacion='".usuario_actual('cod_organizacion')."'";
        $grupo=cargar_data($sql,$this);
        $this->cmb_grupo->DataSource=$grupo;
        $this->cmb_grupo->dataBind();
        $this->txt_fecha_incorporacion->Text=date('d/m/Y');

        $sql="select * from bienes.conceptos where tipo='0'";
        $conceptos=cargar_data($sql,$this);
        $this->drop_tipo_incorporacion->DataSource=$conceptos;
        $this->drop_tipo_incorporacion->dataBind();
        }
    }

   //***funcion para verificar que la organizacion del usuario logueado
   //**es la misma que  la de la cedula introducida
    public function verificar_organizacion($sender,$param)
    {
        $param->IsValid=True;
        $cedula=$this->txt_cedula->Text;
        $sql="select cod_organizacion from organizacion.personas_nivel_dir where cedula='$cedula'";
        $datos_persona=cargar_data($sql,$sender);
        if($datos_persona[0]['cod_organizacion']==usuario_actual('cod_organizacion'))
            {
            $param->IsValid= true;
            }
        else
            {
            $param->IsValid=false;
            $this->txt_nombre->Text = "";
            $this->txt_apellido->Text = "";
            }
    }



    /*verifica la existencia en la tabla nomina. integrantes*/
    public function verif_existencia_integrantes($sender,$param)
    {$param->IsValid=True;
        $cedula=$this->txt_cedula->Text;

            $param->IsValid=verificar_existencia('nomina.integrantes','cedula',$cedula,null,$this);
    }

    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo exista en el sistema y se colocan los datos de la persona
     * que resulte seleccionada.
     */
    public function validar_cedula($sender, $param)
    {$param->IsValid=True;
        $cedula=$this->txt_cedula->Text;
        $sql="select p.cedula,p.nombres,p.apellidos from organizacion.personas p inner join organizacion.personas_nivel_dir pnd  on p.cedula=pnd.cedula where p.cedula='$cedula'";
        $datos_persona=cargar_data($sql,$sender);
        if ($datos_persona == '')
        { // si no existe, se vacian los controles para forzar validación

            $this->txt_nombre->Text = "";
            $this->txt_apellido->Text = "";


        }
        else
        { // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_nombre->Text = $datos_persona[0]['nombres'];
            $this->txt_apellido->Text = $datos_persona[0]['apellidos'];
        }
    }

    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function incluir($sender, $param)
	{
        if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
        {
        $this->btn_incluir->Enabled=False;
        $cod_org=usuario_actual('cod_organizacion');
       // Se capturan los datos provenientes de los Controles
        $codigo = $this->txt_codigo->Text;
        
        $resultado_drop = obtener_seleccion_drop($this->cmb_grupo);
        $grupo=$resultado_drop[1];
        $resultado_drop = obtener_seleccion_drop($this->cmb_subgrupo);
        $subgrupo=$resultado_drop[1];
        $resultado_drop = obtener_seleccion_drop($this->cmb_secciones);
        $secciones=$resultado_drop[1];
        $resultado_drop = obtener_seleccion_drop($this->cmb_direcciones);
        $direccion=$resultado_drop[1];
        $resultado_drop = obtener_seleccion_drop($this->drop_tipo_incorporacion);
        $concepto_incorporacion=$resultado_drop[1];
     
        $cantidad=$this->txt_cantidad->Text;
        $descripcion=$this->txt_descripcion->Text;
        $p_incorporacion=$this->txt_precio_incorporacion->Text;
        //$p_desincorporacion=$this->txt_precio_desincorporacion->Text;
        $serial = $this->txt_serial->Text;
        $f_incorporacion = cambiaf_a_mysql($this->txt_fecha_incorporacion->Text);

        //$f_desincorporacion = $this->txt_fecha_desincorporacion->Text;

        $a_vida_util = $this->txt_a_vida_util->Text;
        $m_vida_util = $this->txt_meses_vida_util->Text;
        $d_vida_util = $this->txt_dias_vida_util->Text;
        $f_movimiento=date("Y-m-d");
                //Se actualizan los datos del bien.
	/*	$sql="update bienes.bienes_muebles set grupo='$grupo',subgrupo='$subgrupo',secciones='$secciones',cantidad='$cantidad',cod_direccion='$direccion',descripcion='$descripcion',
              concepto_incorporacion='$concepto_incorporacion', precio_incorporacion='$p_incorporacion',serial='$serial',fecha_incorporacion='$f_incorporacion',a_vida_util='$a_vida_util',meses_vida_util='$m_vida_util',dias_vida_util='$d_vida_util'
            where codigo='$codigo' and cod_organizacion='$cod_org'"; */
		$sql="insert into bienes.bienes_muebles (cod_organizacion,codigo,grupo,subgrupo,secciones,cantidad,cod_direccion,descripcion,concepto_incorporacion,precio_incorporacion,serial,fecha_incorporacion,a_vida_util,meses_vida_util,dias_vida_util)
        values ('$cod_org','$codigo','$grupo','$subgrupo','$secciones','$cantidad','$direccion','$descripcion','$concepto_incorporacion','$p_incorporacion','$serial','$f_incorporacion','$a_vida_util','$m_vida_util','$d_vida_util')";

        $resultado=modificar_data($sql,$sender);
        $sql2="insert into bienes.movimientos_bienes (tipo_movimiento,cod_organizacion,cod_bien,cod_direccion,fecha) values('$concepto_incorporacion','$cod_org','$codigo','$direccion','$f_movimiento')";
        $resultado=modificar_data($sql2,$sender);
        
                $this->LTB->titulo->Text = "Guardar Bienes Muebles";
                $this->LTB->texto->Text = "Se ha Guardado exitosamente el nuevo Bien Mueble, ahora puede guardar una foto del mismo haciendo click en la pestaña -Imágenes-";
                $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                //$this->LTB->redir->Text = "nomina.nominas.admin_nominas";
                $params = array('mensaje');
                $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
        $this->tab_panel->ActiveViewID="View2";
        // Se incluye el rastro en el archivo de bitácora
        //$descripcion_log = "Incluido el usuario C.I.: ".$cedula." Nombre: ".$this->txt_nombre->Text." ".$this->txt_apellido->Text." Login: ".$login;
        //inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        }
 	}
}

?>