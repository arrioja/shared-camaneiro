<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Ronald Salazar
 * Descripción: Incluye los cargos de las personas etc.
 *****************************************************  FIN DE INFO
*/
class incluir_cargo extends TPage
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
            $cedula=$this->Request['cedula'];
            $this->drop_persona->Selectedvalue=$cedula;
            $this->cargar_data($this, $param);
        }
    }
    
   public function pagos($sender,$param)
    {
        $id=$sender->CommandParameter;
        $this->Response->Redirect( $this->Service->constructUrl('nomina.integrantes.incluir_cargo_conceptos',array('id'=>$id)));//
    }
    /*Carga los datos de los familiares cargados en la base de datos organizacion
     * tabla personas_carga_familiar*/
    function cargar_data($sender, $param)
    {
        $cedula=$this->drop_persona->SelectedValue;
        $sql="SELECT id,denominacion,condicion,decreto_contrato,fecha_ini,fecha_fin,lugar_trabajo
               FROM organizacion.personas_cargos WHERE(cedula='$cedula') order by fecha_ini ASC ";
        $resultado=cargar_data($sql, $sender);
        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
    }

    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->cargar_data($sender, $param);
    }



public function cargar_dir($sender,$param)
    {
        $fecha=cambiaf_a_mysql($this->txt_fecha_in->Text);
        
        //se obtiene la fecha comparando la vigencia  para consultar direcciones de esa como vigencia_desde
        /*$sql="SELECT id,nombre,periodo
            FROM organizacion.direcciones_historial
            WHERE periodo = (
            SELECT id
            FROM organizacion.direcciones_historial_periodo
            WHERE (vigencia_desde <='$fecha' AND vigencia_hasta >='$fecha')
            ORDER BY vigencia_desde ASC
            LIMIT 1 ) ";*/
    
        $sql="SELECT dh.id,dh.nombre,dh.periodo,dhp.vigencia_desde,dhp.vigencia_hasta
            FROM organizacion.direcciones_historial as dh
            INNER JOIN organizacion.direcciones_historial_periodo dhp ON(dhp.vigencia_desde <='$fecha' AND dhp.vigencia_hasta >='$fecha')
            WHERE (dh.periodo=dhp.id)
             ";
        $resultado=cargar_data($sql, $sender);

        //si es vacio
        if (empty($resultado)){
            //se obtiene la primera fecha de orden Asc segun fecha de inicio del cargo para consultar direcciones de esa como vigencia_desde
        $sql="SELECT id,nombre FROM organizacion.direcciones_historial
            WHERE periodo = (SELECT id FROM organizacion.direcciones_historial_periodo
                             ORDER BY vigencia_desde DESC LIMIT 1 ) ";
        $resultado=cargar_data($sql, $sender);

        $arreglo = array();
        foreach ($resultado as $datos)
         {
               //por acentos se codifica en utf8
            $id=$datos[id];
            $nombre=utf8_encode($datos[nombre]);
            $nombre="$nombre (Vigente Actualmente)";
            $direccion = array('id'=>$id, 'nombre'=>$nombre );
            array_unshift($arreglo, $direccion);
         }
            $this->drop_ubicacion->DataSource=$arreglo;
            $this->drop_ubicacion->dataBind();

        }else{
            //por acentos se codifica en utf8
            $arreglo = array();
            foreach ($resultado as $datos)
             {
                $id=$datos[id];
                $nombre=utf8_encode($datos[nombre]);
                $nombre="$nombre (Vigente: ".cambiaf_a_normal($datos[vigencia_desde])."-".cambiaf_a_normal($datos[vigencia_hasta]).")";
                $direccion = array('id'=>$id, 'nombre'=>$nombre );
                array_unshift($arreglo, $direccion);
             }
                $this->drop_ubicacion->DataSource=$arreglo;
                $this->drop_ubicacion->dataBind();

        }//fin si
      
    }

 public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

       $item=$param->Item;

         if($item->ItemType==='EditItem')
        {
           // $item->fecha_ini->TextBox->Text=cambiaf_a_mysql($item->fecha_ini->Text);
           // $item->fecha_fin->TextBox->Text=cambiaf_a_mysql($item->fecha_fin->Text);

            $item->decreto_contrato->TextBox->Columns=4;
            $item->fecha_ini->TextBox->Columns=10;
            $item->fecha_fin->TextBox->Columns=10; 

            /*$sql="SELECT nombre
            FROM organizacion.direcciones_historial
            WHERE id='".$item->lugar_trabajo->Text."' ";
            $resultado=cargar_data($sql, $sender);

            $item->lugar_trabajo->TextBox->Text=utf8_encode($resultado[0]['nombre']);*/
            

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
        $condicion = $item->condicion->DropDownList->Text;
        $decreto_contrato=$item->decreto_contrato->TextBox->Text;
        $fecha_ini = ($item->fecha_ini->TextBox->Text);
        $fecha_fin = ($item->fecha_fin->TextBox->Text);
       

		$sql="UPDATE organizacion.personas_cargos set denominacion='$denominacion', condicion='$condicion',
              fecha_ini='$fecha_ini', fecha_fin='$fecha_fin',decreto_contrato='$decreto_contrato'  WHERE id='$id' ";

        $resultado=modificar_data($sql,$sender);

        $this->DataGrid->EditItemIndex=-1;
        $this->cargar_data($sender, $param);
      

    }

/* este procedimiento cambia la fecha a formato normal antes de mostrarla en el grid */
    public function nuevo_item($sender,$param)
    {
        $item=$param->Item;

        
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
        {
            $item->fecha_ini->Text = cambiaf_a_normal($item->fecha_ini->Text);

            if ($item->fecha_fin->Text=="0000-00-00") $item->fecha_fin->Text= "-";
            else $item->fecha_fin->Text = cambiaf_a_normal($item->fecha_fin->Text);


            $sql="SELECT nombre
            FROM organizacion.direcciones_historial
            WHERE id='".$item->lugar_trabajo->Text."' ";
            $resultado=cargar_data($sql, $sender);

            $item->lugar_trabajo->Text=utf8_encode($resultado[0]['nombre']);
            
        }

             
    }

       public function deleteItem($sender,$param)
    {
      
       $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM organizacion.personas_cargos WHERE id = '$id'";
        $this->DataGrid->EditItemIndex=-1;
        $resultado=modificar_data($sql,$this);
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
            
            $condicion = $this->drop_condicion->Selectedvalue;
            $decreto_contrato=$this->txt_res_con->text;
            $fecha_ini = cambiaf_a_mysql($this->txt_fecha_in->Text);
            $fecha_fin = cambiaf_a_mysql($this->txt_fecha_fin->Text);
            //$lugar_trabajo=strtoupper($this->txt_ubicacion->text);
             $lugar_trabajo=$this->drop_ubicacion->Selectedvalue;
            //$this->mensaje->setSuccessMessage($sender, "denominacion= $denominacion - id= ".$this->lbl_denominacion->Text, 'grow');

            // se verifica si existe el cargo para verificar exactamente su nombre
            if($this->lbl_denominacion->Text!="")
           {// si no existe
            $sql = "SELECT nombre FROM nomina.cargos WHERE id='".$this->lbl_denominacion->Text."'";
            $datos = cargar_data($sql,$sender);
            $denominacion = strtoupper(utf8_encode($datos[0]['nombre']));
           }else{
               $denominacion = strtoupper($this->txt_denominacion->text);
           }
          
          $sql_ulcar = " (SELECT id FROM organizacion.personas_cargos WHERE cedula='$cedula_persona' ORDER BY id DESC LIMIT 1  )";
          $ultimo_cargo = cargar_data($sql_ulcar,$sender);
           if (!empty($ultimo_cargo)){
            //-------se asigna conceptos del cargo anterior a tabla organizacion.personas_cargos_asignaciones------
             $tipo_nomina = usuario_actual('tipo_nomina');//conceptos asignados al integrante de acuerdo a su tipo de nomina
             $sql=" SELECT c.cod, c.descripcion, c.tipo, c.formula
                    FROM nomina.conceptos c INNER JOIN nomina.integrantes_conceptos ic ON (ic.cod_concepto=c.cod)
                    WHERE ic.cedula='$cedula_persona' AND ( ic.cod_concepto!='0002')
                    ORDER BY c.tipo asc";
             $resultado=cargar_data($sql,$sender);
             $cedula=$cedula_persona;
           //se recorre para evaluar las formulas e insertar el monto por concepto
            foreach ($resultado as $datos)
            {
               $formula=$datos['formula'];
               $cod=$datos['cod'];
               $tipo=$datos['tipo'];
               $sql2="select anos_servicio,cod_organizacion from nomina.integrantes where cedula='$cedula'";
               $res=cargar_data($sql2,$sender);
               $anos=$res[0]['anos_servicio'];
               $cod_org=$res[0]['cod_organizacion'];

                try{
                $db = $this->Application->Modules["db2"]->DbConnection;
                $db->Active=true;
                //

                 if ($cod=="0001")//bono de antiguedad
                 {
                     $valor=evaluar_bono_antiguedad(array('cedula'=>$cedula,'anos_servicio'=>$anos),array('formula'=>$formula),$cod_org,$db);
                     $valor=(int)round($valor*100)/100;
                     $asignaciones= $asignaciones+$valor;
                 }
                 else

                   if($tipo=="DEBITO"){
                       // si el concepto es vivienda y habitat o RISLR se debe calcular con el total de las asignaciones
                        if (($cod=="0005")||($cod=="9001"))
                           {
                               $valor=evaluar_concepto_con_asignaciones(array('cedula'=>$cedula),array('formula'=>$formula,'cod'=>$cod),$asignaciones,$db);
                               $valor=(int)round($valor*100)/100;
                           }else{
                                $valor=evaluar_concepto(array('cedula'=>$cedula),array('formula'=>$formula),$db);
                                $valor=(int)round($valor*100)/100;
                            }
                   }else {//si son credito
                       $valor=evaluar_concepto(array('cedula'=>$cedula),array('formula'=>$formula),$db);
                       $valor=(int)round($valor*100)/100;
                       $asignaciones= $asignaciones+$valor;
                   }

                // Se capturan los datos provenientes de los Controles

                $descripcion = $datos['descripcion'];
                
                $sql="insert into organizacion.personas_cargos_asignaciones
                      (cargo,cod,tipo,denominacion,monto)
                      values ((SELECT id FROM organizacion.personas_cargos WHERE cedula='$cedula' ORDER BY id DESC LIMIT 1  ),'$cod','$tipo','$descripcion','$valor')";
                $resultado=modificar_data($sql,$sender);

                $db->Active=false;
                }catch(Exception $e){
                $mensaje=$e->getMessage();
                $db->Active=false;
                echo $mensaje;
                //$this->Response->redirect($this->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
                }

            }//fin para

            //-------FIN se asigna conceptos del cargo anterior------
           }//fin si tenia un cargo
          


            //*se inserta el nuevo cargo*//
             $sql="insert into organizacion.personas_cargos
                  (cedula,denominacion,condicion,decreto_contrato,fecha_ini,fecha_fin,lugar_trabajo)
                  values ('$cedula_persona','$denominacion','$condicion','$decreto_contrato','$fecha_ini','$fecha_fin','$lugar_trabajo') ";
           $resultado=modificar_data($sql,$sender);


            //Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha incluido Cargo de la persona C.I. ".$cedula_persona.": Fecha de Inicio ".$fecha_ini." Denominacion ".$denominacion.", Condicion de ".$condicion." por Resolucion o Contrato Nº ".$decreto_contrato ;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

           $this->txt_denominacion->text="";
           $this->lbl_denominacion->text="";
           $this->drop_condicion->Selectedvalue="N/A";
           $this->txt_res_con->text="";
           $this->txt_fecha_in->text="";
           $this->txt_fecha_fin->text="";
           $this->drop_ubicacion->DataSource=array();
           $this->drop_ubicacion->dataBind();

           $this->cargar_data($sender, $param);

        }
	}


    /* Esta muestra una consulta filtrada por la cadena ingresada en el text de nombre del cargo*/
    public function cargo_sugeridos($sender,$param) {
        $cod_organizacion = usuario_actual('cod_organizacion');
         // para llenar el listado de articulos
        $articulo=$this->txt_denominacion->Text;
        $sql = "select id,nombre from nomina.cargos
                    where (cod_organizacion = '$cod_organizacion') and (nombre LIKE  '$articulo%')order by codigo";
        $resultado = cargar_data($sql,$this);

        //por acentos se codifica en utf8
        $arreglo = array();
        foreach ($resultado as $datos)
         {
            //por acentos se codifica en utf8
            $id=$datos[id];
            $nombre=utf8_encode($datos[nombre]);
            $cargo = array('id'=>$id, 'nombre'=>$nombre );
            array_unshift($arreglo, $cargo);
         }

        $sender->DataSource=$arreglo;// se carga en el data
        $sender->dataBind();
    }
/* Esta función obtiene el id del cargo de la tabla cargos */
     public function cargo_selecionado($sender,$param) {
        $id=$sender->Suggestions->DataKeys[ $param->selectedIndex ];
        $descripcion= $this->txt_denominacion->Text;
        $this->lbl_denominacion->Text=$id;// se asigna en una lbl el id
    }

     
}
?>
