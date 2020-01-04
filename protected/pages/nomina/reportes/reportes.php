<?php
class reportes extends TPage
{
    		function createMultiple($link, $array) {
                        $item = $link->Parent->Data;
                        $return = array();
                        foreach($array as $key) {
                              $return[] = $item[$key];
                         }
                         return implode(",", $return);
                }


    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
    }

public function onLoad($param)
	{
 parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
            if (usuario_actual('tipo_nomina')=="")
                $this->Response->redirect($this->Service->constructUrl('nomina.set_nomina',array('redir'=>'nomina.reportes.reportes')));

        }


   }

    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->filtrar($serder, $param);
       /* $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();*/

    }

    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        //echo $item->ItemType;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
       /*     $item->cedula->TextBox->ReadOnly="True";
            $item->anos->TextBox->Columns=8;*/

        }

    }




    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        echo $id;
        /*$sql="DELETE FROM nomina.integrantes WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
*/
    }



    public function changePage($sender,$param)
	{
       		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid->DataSource=$this->cargar();
            $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}
}

/*
        */

?>
