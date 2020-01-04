<?php

class listar_no_laborables extends TPage
{

    public function cargar($pager,$param)
    {
        $cod_organizacion = usuario_actual('cod_organizacion');
        $sql="select * from organizacion.dias_no_laborables where (cod_organizacion = '$cod_organizacion')
              order by  mes, dia, ano";
        if ($pager == true) // si la llamada viene del paginador
        {$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;}
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
    }

	public function onLoad($param)
	{
        if (!$this->IsPostBack)
        {$this->cargar(false,null);}
	}

	public function changePage($sender,$param)
	{
        $this->cargar(true,$param);
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

    public function nuevo_item($sender,$param)
    {
        $meses = array('1'=>'Enero','2'=>'Febrero','3'=>'Marzo','4'=>'Abril',
                       '5'=>'Mayo','6'=>'Junio','7'=>'Julio','8'=>'Agosto','9'=>'Septiembre',
                       '10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');
        $item=$param->Item;

        if ($item->mes->Text <> "Mes")
        {
            $item->mes->Text = $meses[$item->mes->Text];
        }
        if ($item->ano->Text == "XXXX")
        {
            $item->ano->Text = "Todos";
        }
    }

	 public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->cargar(false,null);
    }

    public function itemCreated($sender,$param)
    {
        $item=$param->Item;

        if($item->ItemType==='EditItem')
        {
            $item->dia->TextBox->Visible=false;
            $item->mes->TextBox->Visible=false;
            $item->ano->TextBox->Visible=false;
            /* como no quiero que se me muestren los textboxs si no los voy a
             * editar, los pongo invisibles y coloco de nuevo en la celda el valor
             * que tiene el registro en la base de datos en el campo correspondiente
             */

            $val = $param->item->getData();
            $item->dia->Text = $val['dia'];
            $item->mes->Text = $val['mes'];
            $item->ano->Text = $val['ano'];

            $item->descripcion->TextBox->Columns=40;

        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->Borrar->Button->Attributes->onclick='if(!confirm(\'Esta Seguro de Borrar el Registro?\')) return false;';
        }
    }

    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->cargar(false,null);
    }

    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM organizacion.dias_no_laborables WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->cargar(false,null);
    }

    public function saveItem($sender,$param)
    {
        $item=$param->Item;
		$id=$this->DataGrid->DataKeys[$item->ItemIndex];
        $descripcion=$item->descripcion->TextBox->Text;
		$sql="UPDATE organizacion.dias_no_laborables set descripcion = '$descripcion' where id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->DataGrid->EditItemIndex=-1;
        $this->cargar(false,null);
    }

}

?>
