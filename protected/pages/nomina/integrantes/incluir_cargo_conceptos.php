<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald Salazar
 * Descripción: Incluye los conceptos del cargo de las persona(DEBITO - CREDITO).
 *****************************************************  FIN DE INFO
*/
class incluir_cargo_conceptos extends TPage
{
    public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
           //llena el DropDownList
            $this->drop_tipo->DataSource=array('N/A'=>'Seleccione','DEBITO'=>'DEBITO', 'CREDITO'=>'CREDITO');
            $this->drop_tipo->dataBind();
            
                       
            $id=$this->Request['id'];
            $sql="SELECT denominacion FROM personas_cargos WHERE id='$id'";
            $resultado=cargar_data($sql, $this);
            $this->lbl_cargo->Text=$resultado[0]['denominacion'];

            $this->cargar_data($this, $param);
           
        }
    }

  public function cargar_descripcion($sender, $param)
	{
       
        $sql="select id,descripcion from nomina.conceptos WHERE tipo='".$this->drop_tipo->Text."'";
        $resultado=cargar_data($sql, $sender);
        $this->drop_descripcion->DataSource=$resultado;
        $this->drop_descripcion->dataBind();
       /* $tipo_nomina = usuario_actual('tipo_nomina');
        $sql_no="select * from nomina.conceptos where cod not in(select c.cod
        from nomina.conceptos c inner join nomina.integrantes_conceptos ic on ic.cod_concepto=c.cod
        where cedula='$cedula' and ic.tipo_nomina='$tipo_nomina')";*/
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
            $id=$this->Request['id'];
            $denominacion = strtoupper($this->drop_descripcion->Selectedvalue);
            $tipo = $this->drop_tipo->Selectedvalue;
            $monto=($this->txt_monto->text);
           
            
            $sql="insert into organizacion.personas_cargos_asignaciones
                  (cargo,tipo,denominacion,monto)
                  values ('$id','$tipo','$denominacion','$monto')";
            $resultado=modificar_data($sql,$sender);
            //obtiene cedula persona
            $sql="SELECT cedula FROM organizacion.personas_cargos WHERE id='$id'";
            $resultado=modificar_data($sql,$sender);

            //Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha incluido asignacion del Cargo de la persona C.I. ".$resultado[0]['cedula']." : Denominacion ".$denominacion.", Tipo ".$tipo." por Monto Bs. ".$monto ;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);


            $this->txt_monto->text="";
            $this->drop_tipo->Selectedvalue="N/A";
            
            $this->cargar_data($sender, $param);

        }
	}

     /*Carga los datos de las asignaciones cargados en la base de datos organizacion
     * tabla personas_cargos_asignaciones*/
    function cargar_data($sender, $param)
    {
        $id=$this->Request['id'];
        $sql="SELECT id,tipo,denominacion,monto
               FROM organizacion.personas_cargos_asignaciones WHERE(cargo='$id') order by tipo ASC ";
        $resultado=cargar_data($sql, $sender);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
    }

public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->cargar_data($sender, $param);

    }

 public function itemCreated($sender,$param)
    {        
       $item=$param->Item;

         if($item->ItemType==='EditItem')
        {
            $item->denominacion->TextBox->Columns=40;
            $item->monto->TextBox->Columns=5;
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
        $this->cargar_data($sender, $param);
    }


  public function saveItem($sender,$param)
    {
        $item=$param->Item;
        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        // Se capturan los datos provenientes de los Controles
        $denominacion = strtoupper($item->denominacion->TextBox->Text);
        $tipo = $item->tipo->DropDownList->Text;
        $monto=$item->monto->TextBox->Text;
       
        $sql="UPDATE organizacion.personas_cargos_asignaciones set denominacion='$denominacion', tipo='$tipo',
              monto='$monto' WHERE id='$id' ";
        $resultado=modificar_data($sql,$sender);

        $this->DataGrid->EditItemIndex=-1;
        $this->cargar_data($sender, $param);


    }

/* este procedimiento cambia el formato de los campos del grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;


        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            /*$item->fecha_ini->Text = cambiaf_a_normal($item->fecha_ini->Text);

            if ($item->fecha_fin->Text=="0000-00-00") $item->fecha_fin->Text= "-";
            else $item->fecha_fin->Text = cambiaf_a_normal($item->fecha_fin->Text);*/
        }

      /*  if ($item->tipo->Text=="CREDITO"){
            $item->monto->ForeColor="green";
        }elseif ($item->tipo->Text=="DEBITO"){
            $item->monto->ForeColor="red";
        }*/


    }


       public function deleteItem($sender,$param)
    {
        $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM organizacion.personas_cargos_asignaciones WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
        $this->cargar_data($sender, $param);
    }

     public function btn_cancelar_click($sender, $param){
          $id=$this->Request['id'];
            $sql="SELECT cedula FROM personas_cargos WHERE id='$id'";
            $resultado=cargar_data($sql, $sender);
            $cedula=$resultado[0]['cedula'];
        $this->Response->Redirect( $this->Service->constructUrl('nomina.integrantes.incluir_cargo',array('cedula'=>$cedula)));//
    }



}
?>
