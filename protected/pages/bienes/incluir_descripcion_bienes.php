<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo implementa la inclusión de integrantes a la nomina;
 *              es requisito que se encuentren inscritos como personas, ya que
 *              es ahi donde se almacenan los datos personales del usuario.
     ****************************************************  FIN DE INFO
*/

class incluir_descripcion_bienes extends TPage
{



function check_secciones($sender,$param)
   {
     $cod_org=usuario_actual('cod_organizacion');
     $param->IsValid=verificar_existencia("bienes.descripcion_bienes_muebles","grupo",$this->cmb_grupo->Text,array('subgrupo'=>$this->cmb_subgrupo->Text,'secciones'=>$this->txt_secciones->Text,'cod_organizacion' => $cod_org),$sender);
   }

    public function onLoad($param)
    { if (!$this->IsPostBack)
        {
        $sql="select * from bienes.grupo where cod_organizacion='".usuario_actual('cod_organizacion')."'";
        $grupo=cargar_data($sql,$this);
        $this->cmb_grupo->DataSource=$grupo;
        $this->cmb_grupo->dataBind();
        }
    }


    public function cargar_subgrupo($param)
    {
        //$this->cmb_subgrupo->DataSource="";
        $resultado_drop = obtener_seleccion_drop($this->cmb_grupo);
        $grupo = $resultado_drop[1]; // el segundo valor
       
        $sql="select * from bienes.subgrupo where grupo='".$grupo."' and cod_organizacion='".usuario_actual('cod_organizacion')."'";
        $subgrupo=cargar_data($sql,$this);
        $this->cmb_subgrupo->DataSource=$subgrupo;
        $this->cmb_subgrupo->dataBind();
    }
    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function incluir($sender, $param)
	{
        if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
        {
       // Se capturan los datos provenientes de los Controles
        $resultado_drop = obtener_seleccion_drop($this->cmb_grupo);
        $grupo = $resultado_drop[1]; //

        $resultado_drop = obtener_seleccion_drop($this->cmb_subgrupo);
        $subgrupo = $resultado_drop[1]; //
        $secciones=$this->txt_secciones->Text;
        $descripcion=$this->txt_descripcion->Text;
        $cod_org=usuario_actual('cod_organizacion');
  
               /* Se guardan los datos. */
		$sql="insert into bienes.descripcion_bienes_muebles (grupo,subgrupo,secciones,descripcion,cod_organizacion)values
              ('$grupo','$subgrupo','$secciones','$descripcion','$cod_org')";

        $resultado=modificar_data($sql,$sender);
        
          /*  $this->LTB->titulo->Text = "Incluir Grupo";
            $this->LTB->texto->Text = "Se ha registrado exitosamente el nuevo Grupo";
            $this->LTB->imagen->Imageurl = "imagenes/botones/memoranda.png";
            $this->LTB->redir->Text = "Home";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);*/

            $this->Response->redirect($this->Service->constructUrl('bienes.admin_descripcion_bienes'));


        /*    $this->LTB->titulo->Text = "Incluir Grupo";
            $this->LTB->texto->Text = "Se ha registrado exitosamente el nuevo Grupo".$resultado;
            $this->LTB->imagen->Imageurl = "imagenes/botones/memoranda.png";
            $this->LTB->redir->Text = "Home";
            $params = array('mensaje');
            $this->getPage()->getCallbackClient()->callClientFunction('muestra_mensaje', $params);*/

        /* Se incluye el rastro en el archivo de bitácora */
        //$descripcion_log = "Incluido el usuario C.I.: ".$cedula." Nombre: ".$this->txt_nombre->Text." ".$this->txt_apellido->Text." Login: ".$login;
        //inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
        
        }
 	}
}

?>