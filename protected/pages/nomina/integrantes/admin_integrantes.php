<?php

class admin_integrantes extends TPage
{
var $suma;//variable global para mostrar el total en el footer del grid

    public function cargar()
    {
        $tipo_nomina = usuario_actual('tipo_nomina');
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select i.id,i.cedula,i.cod,p.nombres,p.apellidos,i.anos_servicio,itn.tipo_nomina,i.pago_banco,i.status from
        nomina.integrantes i inner join organizacion.personas p on i.cedula=p.cedula
        inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
        where i.cod_organizacion='$cod_org' and itn.tipo_nomina='$tipo_nomina'
        order by status desc, itn.tipo_nomina, i.cedula asc";
        $resultado=cargar_data($sql,$this);       
        $this->suma=count($resultado);
        return $resultado;
    }

    public function cargar_tipo_nomina()
    {
         $cod_organizacion=usuario_actual('cod_organizacion');
         $sql="select * from nomina.tipo_nomina where cod_organizacion='$cod_organizacion'";
         $resultado=cargar_data($sql,$this);
         return $resultado;
    }


    public function go($sender,$param)
    {
       /* $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select i.id,i.cedula,i.cod,p.nombres,p.apellidos,i.anos_servicio,i.tipo_nomina,i.pago_banco,i.status from
        nomina.integrantes i inner join organizacion.personas p on i.cedula=p.cedula  where i.cod_organizacion='$cod_org'
        order by status desc, i.tipo_nomina, i.cedula asc";
        $resultado=cargar_data($sql,$this);
        return $resultado;*/
            //echo "alert ('php echo $; ');"
   $id=$sender->CommandParameter;
   $this->Response->Redirect( $this->Service->constructUrl('nomina.integrantes.integrantes_banco',array('id'=>$id)));//
    }

    public function pagos($sender,$param)
    {
        $cedula=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('nomina.integrantes.integrantes_pagos',array('ced'=>$cedula)));//
    }

    public function reset()
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->txt_cadena->Text="";

        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }

    function filtrar($sender,$param)
    {$tipo_nomina = usuario_actual('tipo_nomina');
     $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
     $valor=$this->txt_cadena->Text;
        if($this->rad_cedula->Checked)
            $sql="select i.id,i.cedula,i.cod,p.nombres,p.apellidos,i.anos_servicio,itn.tipo_nomina,i.pago_banco,i.status from
        nomina.integrantes i inner join organizacion.personas p on i.cedula=p.cedula  
        inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
        where i.cod_organizacion='$cod_org' and itn.tipo_nomina='$tipo_nomina' and i.cedula like '$valor%'
        order by status desc, itn.tipo_nomina, i.cedula asc";
        if ($this->rad_nombres->Checked)
            $sql="select i.id,i.cedula,i.cod,p.nombres,p.apellidos,i.anos_servicio,itn.tipo_nomina,i.pago_banco,i.status from
        nomina.integrantes i inner join organizacion.personas p on i.cedula=p.cedula  
        inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
        where i.cod_organizacion='$cod_org' and itn.tipo_nomina='$tipo_nomina' and p.nombres like '$valor%'
        order by status desc, itn.tipo_nomina, i.cedula asc";
        if($this->rad_apellidos->Checked)
            $sql="select i.id,i.cedula,i.cod,p.nombres,p.apellidos,i.anos_servicio,itn.tipo_nomina,i.pago_banco,i.status from
        nomina.integrantes i inner join organizacion.personas p on i.cedula=p.cedula
        inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
        where i.cod_organizacion='$cod_org' and itn.tipo_nomina='$tipo_nomina' and p.apellidos like '$valor%'
        order by status desc, itn.tipo_nomina, i.cedula asc";
        if($this->rad_status->Checked)
            $sql="select i.id,i.cedula,i.cod,p.nombres,p.apellidos,i.anos_servicio,itn.tipo_nomina,i.pago_banco,i.status from
        nomina.integrantes i inner join organizacion.personas p on i.cedula=p.cedula  
        inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
        where i.cod_organizacion='$cod_org' and itn.tipo_nomina='$tipo_nomina' and i.status='$valor'
        order by status desc, itn.tipo_nomina, i.cedula asc";
        

        $resultado=cargar_data($sql,$this);
        $this->suma=count($resultado);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
    }

public function onLoad($param)
	{

       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
            if (usuario_actual('tipo_nomina')!="")
            {

                 $columnas = $this->DataGrid->Columns;
                 foreach ($columnas as $columna)
                     {
                        if($columna->ID=="tipo_nomina")
                        {

                        $sql="select tipo_nomina from nomina.tipo_nomina";
                        $resultado=cargar_data($sql,$this);
                        $columna->ListDataSource=cargar_tipo_nomina(usuario_actual('cod_organizacion'),$this);

                        }
                     }

                $this->DataGrid->DataSource=$this->cargar($param);
                $this->DataGrid->dataBind();
            }
            else
                $this->Response->redirect($this->Service->constructUrl('nomina.set_nomina',array('redir'=>'nomina.integrantes.admin_integrantes')));
                //$this->Response->redirect($this->Service->constructUrl('nomina.set_nomina'));


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


        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->cedula->TextBox->ReadOnly="True";
            $item->anos->TextBox->Columns=8;
            /*$sql="  SELECT tipo_nomina FROM nomina.tipo_nomina WHERE cod_organizacion='000001' order by tipo_nomina asc";
            $resultado=cargar_data($sql, $sender);
            $item->tipo_nomina->DropDownList->DataSource=$resultado;
            $item->tipo_nomina->DropDownList->dataBind();*/

        }

        if($item->getItemType() === 'Footer')
        {
            $total = new TLabel();
            $total->Text='total:'.$this->suma;
             $item->Cells[0]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[0]->Controls->insertAt(0,$total);//$item->getControls()->add(0,"hola");
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->Borrar->Button->Attributes->onclick='if(!confirm(\'esta Seguro?\')) return false;';
        }
  
    }

    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $this->filtrar($serder, $param);
        /*$this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();*/
    }


    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM nomina.integrantes WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));

    }

    public function saveItem($sender,$param)
    {
        $item=$param->Item;

        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        $cod=$item->cod->TextBox->Text;
        $anos=$item->anos->TextBox->Text;
        $tipo=$item->tipo_nomina2->DropDownList->Text;
        $banco=$item->pago_banco->DropDownList->Text;
        $status=$item->status->DropDownList->Text;

		$sql="UPDATE nomina.integrantes set cod='$cod', anos_servicio='$anos', status='$status', pago_banco='$banco'  where id='$id'";
        $resultado=modificar_data($sql,$this);

        $sql="UPDATE nomina.integrantes_tipo_nomina set tipo_nomina='$tipo' WHERE cedula='".$item->cedula->TextBox->Text."'";
        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
     
    }

    public function formatear($sender,$param)
    {
        $item=$param->Item;

        
        switch($item->status->Text)
        {
            case "0":
                $item->status->Text = "INACTIVO";
                break;
            case "1":
                //$item->Font->Bold="true";
                $item->status->Text = "ACTIVO";
                break;
        }
       switch($item->pago_banco->Text)
        {
            case "0":
                $item->pago_banco->Text = "No";
                break;
            case "1":
                //$item->Font->Bold="true";
                $item->pago_banco->Text = "S&iacute;";
                break;
        }
    }


    public function changePage($sender,$param)
	{
       		$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid->DataSource=$this->cargar();
            $this->DataGrid->dataBind();
	}

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'P&aacute;gina: ');
	}
    	public function footer($sender,$param)
	{
		$param->Footer->Controls->insertAt(0,'hola ');
	}
}

/*     
        */
?>
