<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo implementa la inclusión de integrantes a la nomina;
 *              es requisito que se encuentren inscritos como personas, ya que
 *              es ahi donde se almacenan los datos personales del usuario.
     ****************************************************  FIN DE INFO
*/

class integrantes_banco extends TPage
{
    public function cargar($cedula)
    {
        $cod_org=usuario_actual('cod_organizacion');
        $sql="select i.id, i.numero_cuenta,i.tipo,i.banco,i.cedula,i.uso,b.nombre from
        nomina.integrantes_banco i inner join presupuesto.bancos b on i.banco=b.cod_banco where i.cedula='$cedula' and b.cod_organizacion='$cod_org'";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

    public function datos_persona($id)
    {
    $sql="select i.cedula,p.nombres,p.apellidos from
        nomina.integrantes i inner join organizacion.personas p on i.cedula=p.cedula  where i.id='$id'";
        $datos=cargar_data($sql,$this);

        return $datos;
    }

    public function val_uso($sender, $param)
    {
        $param->IsValid=True;
        $cedula=$this->txt_cedula->Text;
        $resultado_drop = obtener_seleccion_drop($this->cmb_uso);
        $uso=$resultado_drop[1];
        if (verificar_existencia("nomina.integrantes_banco","uso",$uso,array('cedula'=>$cedula), $sender))
            $param->IsValid=True;
        else
            $param->IsValid=False;
    }

    public function onLoad($param)
    {      

        parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
       {
        $id=$this->Request['id'];
        $cod_org=usuario_actual('cod_organizacion');
        $sql="select * from presupuesto.bancos where cod_organizacion='$cod_org'";//bancos

        $bancos=cargar_data($sql,$this);
        $datos=$this->datos_persona($id);
        
        $this->txt_cedula->Text=$datos[0]['cedula'];
        $this->txt_nombre->Text=$datos[0]['nombres'];
        $this->txt_apellido->Text=$datos[0]['apellidos'];


       $this->cmb_bancos->DataSource=$bancos;
        $this->cmb_bancos->dataBind();



        /*$columnas = $this->DataGrid->Columns;
         foreach ($columnas as $columna)
             {
                if($columna->ID=="tipo_nomina")
                {

                $sql="select tipo_nomina from nomina.tipo_nomina";
                $resultado=cargar_data($sql,$this);
                $columna->ListDataSource=cargar_tipo_nomina(usuario_actual('cod_organizacion'),$this);

                }
             }*/

       $this->DataGrid->DataSource=$this->cargar($datos[0]['cedula']);
       $this->DataGrid->dataBind();
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
       // Se capturan los datos provenientes de los Controles
        $cedula = $this->txt_cedula->Text;
        $resultado_drop = obtener_seleccion_drop($this->cmb_bancos);
        $banco = $resultado_drop[1];

        $resultado_drop = obtener_seleccion_drop($this->cmb_tipo_cuenta);
        $tipo_cuenta=$resultado_drop[1];

        $resultado_drop = obtener_seleccion_drop($this->cmb_uso);
        $uso=$resultado_drop[1];

        $cod_org=usuario_actual('cod_organizacion');       
        $numero = $this->txt_numero->Text;
               /* Se guardan los datos de la cuenta del integrante. */

		$sql="insert into nomina.integrantes_banco (numero_cuenta,tipo,banco,cedula,uso
              )values('$numero','$tipo_cuenta','$banco','$cedula','$uso')";
        $resultado=modificar_data($sql,$sender);


       $this->DataGrid->DataSource=$this->cargar($cedula);
       $this->DataGrid->dataBind();

        /* Se incluye el rastro en el archivo de bitácora */
        //$descripcion_log = "Incluido el usuario C.I.: ".$cedula." Nombre: ".$this->txt_nombre->Text." ".$this->txt_apellido->Text." Login: ".$login;
        //inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        //$this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));/*
        }
 	}

     public function editItem($sender,$param)
    {   $cedula = $this->txt_cedula->Text;
        
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->DataGrid->DataSource=$this->cargar($cedula);
        $this->DataGrid->dataBind();

    }

    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;
        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
            $item->cedula->TextBox->ReadOnly="True";
            $item->banco->TextBox->ReadOnly="True";
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->Borrar->Button->Attributes->onclick='if(!confirm(\'esta Seguro?\')) return false;';
        }

    }

    public function cancelItem($sender,$param)
    {$cedula = $this->txt_cedula->Text;
        $this->DataGrid->EditItemIndex=-1;

        $this->DataGrid->DataSource=$this->cargar($cedula);
        $this->DataGrid->dataBind();
        
    }


    public function deleteItem($sender,$param)//sin revisar
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM nomina.integrantes_banco WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        //$this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
       $this->DataGrid->DataSource=$this->cargar($this->txt_cedula->Text);
       $this->DataGrid->dataBind();

    }

    public function saveItem($sender,$param)
    {
    $cedula = $this->txt_cedula->Text;//usada la cedula para recargar el grid

       $item=$param->Item;

        $id=$this->DataGrid->DataKeys[$item->ItemIndex];

        $tipo_cuenta=$item->tipo->DropDownList->Text;
        $uso=$item->uso->DropDownList->Text;
        $numero = $item->numero->TextBox->Text;
               /* Se guardan los datos de la cuenta del integrante. */

		$sql="update nomina.integrantes_banco set numero_cuenta='$numero', tipo='$tipo_cuenta', uso='$uso' where id='$id'";
        $resultado=modificar_data($sql,$sender);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar($cedula);
        $this->DataGrid->dataBind();
     
    }

    public function formatear($sender,$param)
    {
       /* $item=$param->Item;
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
        }*/
    }


    public function changePage($sender,$param)
	{
       		/*$this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
            $this->DataGrid->DataSource=$this->cargar();
            $this->DataGrid->dataBind();*/
	}

	public function pagerCreated($sender,$param)
	{
		//$param->Pager->Controls->insertAt(0,'Pagina: ');
	}
}


?>
