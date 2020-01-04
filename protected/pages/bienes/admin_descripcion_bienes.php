<?php

class admin_descripcion_bienes extends TPage
{
    		function createMultiple($link, $array) {
                        $item = $link->Parent->Data;
                        $return = array();
                        foreach($array as $key) {
                              $return[] = $item[$key];
                         }
                         return implode(",", $return);
                }

    public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select id, grupo,subgrupo,secciones,descripcion from bienes.descripcion_bienes_muebles where cod_organizacion='$cod_org'";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

    public function eliminar($sender,$param)
    {

   $id=$sender->CommandParameter;
   $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

   $sql="delete from bienes.descripcion_bienes_muebles where id='$id'";
   $resultado=modificar_data($sql,$this);

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
    }
 
    public function reset()
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->txt_cadena->Text="";

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }


public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
        $this->DataGrid->DataSource=$this->cargar();
		$this->DataGrid->dataBind();
        }

   }

    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
       $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }

    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->descripcion->TextBox->Columns=50;
            $item->secciones->TextBox->Columns=5;
            $item->grupo->TextBox->ReadOnly='True';
            $item->subgrupo->TextBox->ReadOnly='True';
        }

    }
    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
    }
        public function saveItem($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid->DataKeys[$item->ItemIndex];
        $descripcion=$item->descripcion->TextBox->Text;
        $secciones=$item->secciones->TextBox->Text;
		$sql="UPDATE bienes.descripcion_bienes_muebles set descripcion='$descripcion', secciones='$secciones' where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }


}

?>
