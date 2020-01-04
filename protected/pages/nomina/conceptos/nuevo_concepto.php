<?php
class nuevo_concepto extends TPage
{
     /** CHECK si existe el código **/
 function check_codigo($sender,$param)
   {
     $param->IsValid=verificar_existencia("nomina.conceptos","cod",$this->txt_codigo->Text,null,$sender);
   }
    public function cargar_constantes()
    {
        $cod_organizacion=usuario_actual('cod_organizacion');
        $sql="select cod,descripcion,abreviatura from nomina.constantes where (cod_organizacion='$cod_organizacion') order by descripcion";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

public function agregar($sender,$param)//sin revisar
    {

        $abreviatura=$sender->CommandParameter;
        if (strlen($this->lbl_formula->Text)==4)//si no ha asignado ninguna variable aun
            {
                $this->lbl_formula->Text="y($abreviatura)=";
                $this->txt_formula->Text=$this->txt_formula->Text." $abreviatura";
            }

        else
            {
                $this->txt_formula->Text=$this->txt_formula->Text." $abreviatura";
                $abreviatura=",".$abreviatura;
                $this->lbl_formula->Text = substr($this->lbl_formula->Text, 0, strlen($this->lbl_formula->Text)-2) . $abreviatura . substr($this->lbl_formula->Text, strlen($this->lbl_formula->Text)-2);//insertar cadena dentro de otra

            }
    }

public function reset($sender,$param)//sin revisar
    {

            $this->lbl_formula->Text="y()=";
            $this->txt_formula->Text="";


    }



	public function onLoad($param)
	{
        parent::onLoad($param);
        if(!$this->IsPostBack)
          { 
            $this->DataGrid_constantes->DataSource=$this->cargar_constantes();
            $this->DataGrid_constantes->dataBind();

          }
	}

function guardar_concepto($sender, $param)
  {
    if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
     {
     // Se capturan los datos provenientes de los Controles
        $codigo = $this->txt_codigo->Text;
        $descripcion = $this->txt_descripcion->Text;
        $formula = $this->lbl_formula->Text.$this->txt_formula->Text;
        $tipo = $this->cmb_tipo->Text;
        $tipo_pago = $this->cmb_tipo_pago->Text;
        $general = $this->cmb_general->Text;
        $frecuencia = $this->cmb_frecuencia->Text;
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="insert into nomina.conceptos(cod,descripcion,
formula,tipo,tipo_pago,general,frecuencia,cod_organizacion)
values ('$codigo','$descripcion','$formula','$tipo','$tipo_pago','$general','$frecuencia','$cod_org' )";
       
       $resultado=modificar_data($sql,$sender);//ejecutar la consulta de insercion
        if ($resultado==1)
            {
            $this->Response->redirect($this->Service->constructUrl('nomina.conceptos.admin_conceptos'));
            }
        else
            {
                echo " Error gruardando los datos de conceptos - ";
                echo mysql_error();
                echo " ----- Antes de cerrar este mensaje de error, por favor contacte al soporte t&eacute;cnico de la Direcci&oacute;n de Sistemas para tomen las previsiones y lo corrijan oportunamente";
            //enviar a la pagina de error
            }
     }
  }
  function regresar()
  {
      $this->Response->redirect($this->Service->constructUrl('nomina.conceptos.admin_conceptos'));
  }
}
?>
