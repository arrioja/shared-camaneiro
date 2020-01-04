<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo implementa la inclusión de integrantes a la nomina;
 *              es requisito que se encuentren inscritos como personas, ya que
 *              es ahi donde se almacenan los datos personales del usuario.
     ****************************************************  FIN DE INFO
*/

class movimientos extends TPage
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

        $this->img_1->ImageUrl=$nombre_n;

                $resultado_drop = obtener_seleccion_drop($this->cmb_codigos);
                $cod_bien = $resultado_drop[1]; //
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
        $secciones = $resultado_drop[1]; // el segundo valor

        $sql="select descripcion from bienes.descripcion_bienes_muebles where grupo='".$grupo."' and subgrupo='".$subgrupo."' and secciones='".$secciones."' and cod_organizacion='".usuario_actual('cod_organizacion')."'";
        $secciones=cargar_data($sql,$this);

        $sql="select codigo from bienes.bienes_muebles where grupo=''";
        $codigos=cargar_data($sql,$this);
            $todos = array('codigo'=>'-1', 'codigo'=>'Seleccione un Código');
                array_unshift($codigos, $todos);
        $this->cmb_codigos->DataSource=$codigos;
        $this->cmb_codigos->dataBind();

        $sql="select * from organizacion.direcciones where codigo_organizacion='".usuario_actual('cod_organizacion')."'";
        $direcciones=cargar_data($sql,$this);
                    $todos = array('codigo'=>'-1', 'nombre_completo'=>'Seleccione una Dirección');
                array_unshift($direcciones, $todos);
        $this->cmb_direcciones->DataSource=$direcciones;
        $this->cmb_direcciones->dataBind();
        
        $this->txt_desc->text=$secciones[0]["descripcion"];
    }


function check_codigo($sender,$param)
   { $cod_org=usuario_actual('cod_organizacion');
     $param->IsValid=verificar_existencia_doble("nomina.integrantes","cod",$this->txt_codigo->Text,"cod_organizacion",$cod_org,$sender);
   }

    public function onLoad($param)
    {
         if (!$this->IsPostBack)
        {
        $id=$this->Request['id'];
        $sql="select * from bienes.bienes_muebles where id='$id'";
        $bien=cargar_data($sql,$this);
        $this->txt_grupo->Text=$bien[0]['grupo'];
        $this->txt_subgrupo->Text=$bien[0]['subgrupo'];
        $this->txt_secciones->Text=$bien[0]['secciones'];
        $this->txt_desc->Text=$bien[0]['descripcion'];
        $this->txt_codigo->Text=$bien[0]['codigo'];
        $cod_direccion=$bien[0]['cod_direccion'];

        $sql_dir="select * from organizacion.direcciones where codigo='$cod_direccion' and codigo_organizacion='".usuario_actual('cod_organizacion')."'";
        $dir=cargar_data($sql_dir,$this);
        $this->txt_direccion->Text=$dir[0]['nombre_completo'];
        $this->txt_direccion_oculto->Value=$cod_direccion;//codigo de la direccion

        $sql="select * from organizacion.direcciones where codigo_organizacion='".usuario_actual('cod_organizacion')."'";
        $direcciones=cargar_data($sql,$this);
        $this->drop_direcciones->DataSource=$direcciones;
        $this->drop_direcciones->dataBind();

        $sql="select * from bienes.conceptos where tipo='1'";
        $conceptos=cargar_data($sql,$this);
        $this->drop_tipo_movimiento->DataSource=$conceptos;
        $this->drop_tipo_movimiento->dataBind();

        $sql="select * from bienes.conceptos where tipo='0'";
        $conceptos2=cargar_data($sql,$this);
        $this->drop_tipo_incorporacion->DataSource=$conceptos2;
        $this->drop_tipo_incorporacion->dataBind();

        $this->txt_fecha_movimiento->Text=date('d/m/Y');
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

public function val_direccion($sender,$param)
{
    $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
    $direccion_sel=$resultado_drop[1];
    $direccion=$this->txt_direccion_oculto->Value;
    if($direccion==$direccion_sel)
    {
     $param->IsValid =false;
    }

}

public function prueba($sender,$param)
{

    if ('5'=='5'and'7'=='7')
    $this->txt_direccion->Text='holaaaaaaggggg';

}

    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function registrar_movimiento($sender, $param)
	{
        if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
        {
        $cod_org=usuario_actual('cod_organizacion');
       // Se capturan los datos provenientes de los Controles
             
        $codigo = $this->txt_codigo->Text;
        $f_movimiento = cambiaf_a_mysql($this->txt_fecha_movimiento->Text);
        $resultado_drop = obtener_seleccion_drop($this->drop_direcciones);
        $direccion=$resultado_drop[1];
               /* Se actualizan los datos del bien. */
		$sql="update bienes.bienes_muebles set cod_direccion='$direccion'
            where codigo='$codigo' and cod_organizacion='$cod_org'";//actualiza la tabla bienes muebles
        $sql2="insert into bienes.movimientos_bienes (cod_organizacion,cod_bien,cod_direccion,fecha) values('$cod_org','$codigo','$direccion','$f_movimiento')";

        try
        {
        $db = $this->Application->Modules["db2"]->DbConnection;
        $db->Active=true;
        $ejecucion = $db->createCommand('begin')->execute();//inicia transaccion
            if ($ejecucion = $db->createCommand($sql)->execute())//ejecuta consulta
                {
                $ejecucion = $db->createCommand($sql2)->execute();//ejecuta consulta
                $ejecucion = $db->createCommand("commit")->execute();//ejecuta consulta
                $this->LTB->titulo->Text = "Movimientos Bienes";
                $this->LTB->texto->Text = "Se ha cambiado exitosamente la dirección del bien";
                $this->LTB->imagen->Imageurl = "imagenes/botones/bien.png";
                $this->LTB->redir->Text = "bienes.admin_bienes";
                $params = array('mensaje');
                $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);

                }
            else
                {
                $ejecucion = $db->createCommand('rollback')->execute();//devuelve transaccion
                $this->LTB->titulo->Text = "Movimientos Bienes";
                $this->LTB->texto->Text = "No Se ha cambiado la dirección del bien";
                $this->LTB->imagen->Imageurl = "imagenes/botones/mal.png";
                $params = array('mensaje');
                $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);
                }

        $db->Active=false;
	    //$serder->Response->redirect($serder->Service->constructUrl('nomina.nominas.crear_nomina'));
        }
        catch(Exception $e)
        {
        $mensaje=$e->getMessage();
        $ejecucion = $db->createCommand('rollback')->execute();//devuelve transaccion
        $db->Active=false;
        $this->Response->redirect($this->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
        }
        }
 	}
    public function imprimir_movimientos($sender, $param)
    {

        $cod_organizacion = usuario_actual('cod_organizacion');
        $cod_bien=$this->txt_codigo->Text;

        $sql="select * from bienes.movimientos_bienes m
            inner join bienes.bienes_muebles bm on bm.codigo=m.cod_bien
            inner join organizacion.direcciones d on bm.cod_direccion=d.codigo
             where m.cod_organizacion = '$cod_organizacion' and m.cod_bien='$cod_bien' order by fecha asc";
        $resultado_rpt=cargar_data($sql,$sender);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
            require('/var/www/tcpdf/tcpdf.php');

            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            $info_adicional= "Listado de Movimientos de Bienes Muebles\n".
                             "para la fecha: ".date("d/m/Y");
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Listado de Movimientos de Bienes Muebles');
            $pdf->SetSubject('Listado de Movimientos de Bienes Muebles');

            $pdf->AddPage();

            $listado_header = array('Descripcion', 'Dirección', 'Fecha');

            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 6, "Listado de Movimientos del código: ".$cod_bien, 0, 1, 'C', 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header
            $w = array(90, 80, 20);
            for($i = 0; $i < count($listado_header); $i++)
            $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',7);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
            foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['descripcion'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['nombre_completo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, cambiaf_a_normal($row['fecha']), $borde, 0, 'C', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro
                $pdf->Ln();
                $fill=!$fill;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Output("listado_movimientos.pdf",'D');
        }
    }
}

?>