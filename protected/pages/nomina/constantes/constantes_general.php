<?php

class constantes_general extends TPage
{

 public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select * from nomina.constantes where cod_organizacion='$cod_org' and global='1'";//solo las constantes que son generales
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

public function onLoad($param)
	{ 

       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
            if (!usuario_actual('tipo_nomina')!="")
                $this->Response->redirect($this->Service->constructUrl('nomina.set_nomina',array('redir'=>'nomina.constantes.constantes_general')));

            $this->DataGrid->DataSource=$this->cargar();
			$this->DataGrid->dataBind();
        }




	}



    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
       /* $item=$param->Item;
        $item->monto->Visible="True";*/

    }

    public function itemCreated($sender,$param)
    {
        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->monto->TextBox->Columns=10;
            $item->cod->TextBox->ReadOnly="True";
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
        $cod=$item->cod->TextBox->Text;
        $monto=$item->monto->TextBox->Text;
            $monto=TPropertyValue::ensureFloat(ltrim($monto,'BsF'));
       
		$sql="UPDATE nomina.constantes set monto='$monto' where id='$id'";
            $resultado=modificar_data($sql,$this);//modifica la tabla constantes
        $sql="Update nomina.integrantes_constantes set monto='$monto' where cod_constantes='$cod'";
            $resultado=modificar_data($sql,$this);
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
     
    }


    public function changePage($sender,$param)
	{
       /* $sql="select id,codigo_modulo,nombre_largo,archivo_php from intranet.modulos order by codigo_modulo";
			$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $resultado=cargar_data($sql,$this);
            $this->DataGrid->DataSource=$resultado;
            $this->DataGrid->dataBind();*/
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}


}

/*     
        */
?>
