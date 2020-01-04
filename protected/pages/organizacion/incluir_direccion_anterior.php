<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald Salazar
 * Descripción: Incluye las direcciones segun los periodos definidos de la ORGANIZACIÓN etc.
 *****************************************************  FIN DE INFO
*/
class incluir_direccion_anterior extends TPage
{
    public function onload($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
           //llena el listbox 
            $sql="  SELECT * FROM organizacion.direcciones_historial_periodo order by vigencia_desde desc";
            $resultado=cargar_data($sql, $this);
            //por acentos se codifica en utf8
            $arreglo = array();
            foreach ($resultado as $datos)
             {
                $id=$datos[id];
                $nombre=utf8_encode($datos[nombre]);
                $nombre=" Desde: ".cambiaf_a_normal($datos[vigencia_desde])." Hasta:".cambiaf_a_normal($datos[vigencia_hasta])."";
                $direccion = array('id'=>$id, 'nombre'=>$nombre );
                array_unshift($arreglo, $direccion);
             }
                $this->drop_periodo->DataSource=$arreglo;
                $this->drop_periodo->dataBind();

  
            //$this->cargar_data($this, $param);
        }
    }
    
   /*Carga los datos de las direcciones en la base de datos organizacion
     * segun el periodo seleccionado*/
    function cargar_data($sender, $param)
    {
        $id=$this->drop_periodo->SelectedValue;
        $sql="SELECT dh.id,dh.nombre,dh.periodo,dh.nivel
               FROM organizacion.direcciones_historial_periodo as dhp
               INNER JOIN organizacion.direcciones_historial dh ON(dh.periodo=dhp.id)
               WHERE(dhp.id='$id') order by dhp.vigencia_desde ASC";
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
              $item->denominacion->Text=utf8_encode($item->denominacion->Text);
        }


    }
     public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

       $item=$param->Item;

         if($item->ItemType==='EditItem')
        {
            $item->denominacion->TextBox->Columns=50;
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
   public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->cargar_data($sender, $param);
    }


     public function saveItem($sender,$param)
    {
        $item=$param->Item;

        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        // Se capturan los datos provenientes de los Controles
        $nombre = ($item->denominacion->TextBox->Text);
        $nivel = $item->nivel->DropDownList->Text;

		$sql="UPDATE organizacion.direcciones_historial set nombre='$nombre', nivel='$nivel' WHERE id='$id'";

        $resultado=modificar_data($sql,$sender);

        $this->DataGrid->EditItemIndex=-1;
        $this->cargar_data($sender, $param);


    }

     public function deleteItem($sender,$param)
    {

       $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];

       $sql="SELECT * FROM organizacion.personas_cargos WHERE lugar_trabajo='$id' ";
       $resultado=cargar_data($sql, $sender);
       
       if(empty($resultado)){
        $sql="DELETE FROM organizacion.direcciones_historial WHERE id='$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
       }else{
        $this->mensaje->setErrorMessage($sender, "Direccion ya asignada a personas, No se puede Eliminar", 'grow');
       }//fin si
       
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
           $nombre=$this->txt_nombre->text;
           $nivel=$this->drop_nivel->Selectedvalue;
           $periodo=$this->drop_periodo->Selectedvalue;
           $codigo=proximo_numero("organizacion.direcciones_historial","codigo",null,$sender);
           $repetido=false;
          //se verifica que no exista
           $otro=reemplazar($nombre);
           $sql="SELECT * FROM organizacion.direcciones_historial WHERE nombre='$otro' AND periodo='$periodo'";
           $resultado=cargar_data($sql, $sender);

           if(!empty($resultado))$repetido=true;


           if($repetido==false){
               //*se inserta la nueva direccion*//
               $sql="insert into organizacion.direcciones_historial
                      (codigo,nombre,nivel,periodo)
                      values ('$codigo','$nombre','$nivel','$periodo')";
               $resultado=modificar_data($sql,$sender);

                //Se incluye el rastro en el archivo de bitácora
                $descripcion_log = "Se ha incluido Direccion Periodo: ".$periodo." Denominacion ".$nombre.", Nivel de ".$nivel;
                inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

               $this->txt_nombre->text="";
               $this->drop_nivel->Selectedvalue="N/A";
               $this->drop_nivel->Selectedvalue="Seleccione";

              $this->cargar_data($sender, $param);
          }else{
             $this->mensaje->setErrorMessage($sender, "Ya Existe, No se puede Incluir", 'grow');

          }

    }
}


}
?>
