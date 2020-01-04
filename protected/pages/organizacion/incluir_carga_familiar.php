<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald Salazar
 * Descripción: Incluye la carga familiar de una persona registrada en la organizacion.
 *****************************************************  FIN DE INFO
*/
class incluir_carga_familiar extends TPage
{
    public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
           //llena el listbox con las cedulas
          
            $sql="  SELECT cedula,concat(nombres,', ', apellidos,' /V',cedula) as nombre
                    FROM organizacion.personas order by nombre asc";
            $resultado=cargar_data($sql, $this);
            $this->drop_persona->DataSource=$resultado;
            $this->drop_persona->dataBind();
           
        }
    }
  
    
    /*Carga los datos de los familiares cargados en la base de datos organizacion
     * tabla personas_carga_familiar*/
    function cargar_data($sender, $param)
    {
        $cedula=$this->drop_persona->SelectedValue;
        $sql="SELECT id,concat(nombre,',',apellido) as nombre,cedula,parentesco,sexo,fecha_nacimiento
               FROM organizacion.personas_carga_familiar WHERE(persona_cedula='$cedula') order by id desc ";
        $resultado=cargar_data($sql, $sender);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
    }

/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha_nacimiento->Text = cambiaf_a_normal($item->fecha_nacimiento->Text);

            if ($item->cedula->Text=="0") $item->cedula->Text= "-";
        }
    }


       public function itemCreated($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        $item->DeleteColumn->Button->Attributes->onclick='if(!confirm(\'¿Esta seguro?\')) return false;';
        
    }

        public function deleteItem($sender,$param)
    {
      $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
      $sql="DELETE FROM organizacion.personas_carga_familiar WHERE id = '$id' ";
      $resultado=modificar_data($sql,$sender);
      $this->cargar_data($sender, $param);
    }

     /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function btn_incluir_click($sender, $param)
	{
        if ($this->IsValid)
        {
            // Se capturan los datos provenientes de los Controles
            $cedula_persona = $this->drop_persona->Selectedvalue;
            $nombres = $this->txt_nombre->Text;
            $apellidos = $this->txt_apellido->Text;
            $parentesco = $this->drop_parentesco->Text;
            $sexo = $this->drop_sexo->Text;
            $cedula = $this->txt_cedula_cf->Text;
            $fecha_nac = cambiaf_a_mysql($this->txt_fecha_nac->Text);

            $sql="insert into organizacion.personas_carga_familiar
                    (persona_cedula,nombre,apellido,parentesco,sexo,cedula,fecha_nacimiento)
                  values ('$cedula_persona','$nombres','$apellidos','$parentesco','$sexo',
                          '$cedula','$fecha_nac')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Se ha incluido familiar de la persona C.I. ".$cedula_persona.": Parentesco ".$parentesco.", ".$nombres." ".$apellidos;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

            $this->txt_nombre->Text="";
            $apellidos = $this->txt_apellido->Text="";
            $this->drop_parentesco->SelectedValue="N/A";
            $this->drop_sexo->SelectedValue="N/A";
            $this->txt_cedula_cf->Text="";
            $this->txt_fecha_nac->Text="";

            $this->cargar_data($sender, $param);

        }
	}
}
?>
