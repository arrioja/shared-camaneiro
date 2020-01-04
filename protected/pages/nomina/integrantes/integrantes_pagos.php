<?php

class integrantes_pagos extends TPage
{
var $suma;//variable global para mostrar el total en el footer del grid
var $asignaciones=0;
var $deducciones=0;

public function cargar_posee($cedula)
{
 $tipo_nomina = usuario_actual('tipo_nomina');//conceptos asignados al integrante de acuerdo a su tipo de nomina
 $sql="select c.cod, c.descripcion, c.tipo, c.formula,ic.cedula,ic.id
        from nomina.conceptos c inner join nomina.integrantes_conceptos ic on ic.cod_concepto=c.cod
        where ic.cedula='$cedula' and ic.tipo_nomina='$tipo_nomina' order by c.tipo asc";
 return cargar_data($sql,$this);

}

public function cargar_no_posee($cedula)
{
    $tipo_nomina = usuario_actual('tipo_nomina');
        $sql_no="select * from nomina.conceptos where cod not in(select c.cod
        from nomina.conceptos c inner join nomina.integrantes_conceptos ic on ic.cod_concepto=c.cod
        where cedula='$cedula' and ic.tipo_nomina='$tipo_nomina')";

        return cargar_data($sql_no,$this);
}


public function agregar($sender,$param)//sin revisar
    {
        $tipo_nomina = usuario_actual('tipo_nomina');
        $cedula=$this->Request['ced'];
        $cod=$sender->CommandParameter;

        $sql="insert into  nomina.integrantes_conceptos (cedula,cod_concepto,tipo_nomina) values ('$cedula','$cod','$tipo_nomina')";
        $resultado=modificar_data($sql,$this);

        $this->DataGrid_asignaciones->DataSource=$this->cargar_posee($cedula);
		$this->DataGrid_asignaciones->dataBind();

        $this->DataGrid_no_asignados->DataSource=$this->cargar_no_posee($cedula);
		$this->DataGrid_no_asignados->dataBind();
    }

public function quitar($sender,$param)
    {$cedula=$this->Request['ced'];
        $id=$sender->CommandParameter;

        $sql="DELETE FROM nomina.integrantes_conceptos WHERE id = '$id'";
        $resultado=modificar_data($sql,$this);

        $this->DataGrid_asignaciones->DataSource=$this->cargar_posee($cedula);
		$this->DataGrid_asignaciones->dataBind();

        $this->DataGrid_no_asignados->DataSource=$this->cargar_no_posee($cedula);
		$this->DataGrid_no_asignados->dataBind();
    }

    public function go($sender,$param)
    {

   $id=$sender->CommandParameter;
   $this->Response->Redirect( $this->Service->constructUrl('nomina.integrantes.integrantes_banco',array('id'=>$id)));//
    }


public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $cedula=$this->Request['ced'];

        $sql="select * from organizacion.personas where cedula =$cedula";
        $persona=cargar_data($sql,$this);


        $this->txt_cedula->Text=$cedula;
        $this->txt_nombres->Text=$persona[0]['nombres'];
        $this->txt_apellidos->Text=$persona[0]['apellidos'];

        $this->DataGrid_asignaciones->DataSource=$this->cargar_posee($cedula);
		$this->DataGrid_asignaciones->dataBind();

        $this->DataGrid_no_asignados->DataSource=$this->cargar_no_posee($cedula);
		$this->DataGrid_no_asignados->dataBind();
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
        }

        if($item->getItemType() === 'Footer')
        {
            $total_asig = new TLabel();
            $total_deduc = new TLabel();

            $total_asig->Text=$this->asignaciones;
            $total_deduc->Text=$this->deducciones;

             $item->Cells[5]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
             $item->Cells[6]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[5]->Controls->insertAt(0,$total_asig);//$item->getControls()->add(0,"hola");
            $item->Cells[5]->HorizontalAlign="Right";
            $item->Cells[5]->ForeColor="Green";

            $item->Cells[6]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[6]->Controls->insertAt(0,$total_deduc);//$item->getControls()->add(0,"hola");
            $item->Cells[6]->HorizontalAlign="Right";
            $item->Cells[6]->ForeColor="Red";
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

    public function formatear($sender,$param)//sin revisar
    {
       $item=$param->Item;
       $item->formula->Visible="false";

       $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
       if (($item->monto_credito->Text!="Monto")||($item->monto_debito->Text!="Monto"))//si el valor es diferente a la cabecera lo cambia
        {
           $cedula=$item->cedula->Text;
           $formula=$item->formula->Text;
           $cod=$item->cod->Text;
           $sql="select anos_servicio from nomina.integrantes where cedula='$cedula'";
           $res=cargar_data($sql,$this);
           $anos=$res[0]['anos_servicio'];

            try
            {
            $db = $this->Application->Modules["db2"]->DbConnection;
            $db->Active=true;
            //

             if ($item->cod->Text=="0001")//bono de antiguedad
                  {$valor=evaluar_bono_antiguedad(array('cedula'=>$cedula,'anos_servicio'=>$anos),array('formula'=>$formula),$cod_org,$db);
                      $valor=(int)round($valor*100)/100;
                      $this->asignaciones=$this->asignaciones+$valor;
                  }
             else
               if  ($item->tipo->Text=="DEBITO")
                   // si el concepto es vivienda y habitat o RISLR se debe calcular con el total de las asignaciones
                    if (($cod=="0005")||($cod=="9001"))
                       {
                           $valor=evaluar_concepto_con_asignaciones(array('cedula'=>$cedula),array('formula'=>$formula,'cod'=>$cod),$this->asignaciones,$db);
                           $valor=(int)round($valor*100)/100;
                           $this->deducciones=$this->deducciones+$valor;
                       }
                    else
                        {
                            $valor=evaluar_concepto(array('cedula'=>$cedula),array('formula'=>$formula),$db);
                            $valor=(int)round($valor*100)/100;
                            $this->deducciones=$this->deducciones+$valor;
                        }
               else   //si son credito
               {
                   $valor=evaluar_concepto(array('cedula'=>$cedula),array('formula'=>$formula),$db);
                   $valor=(int)round($valor*100)/100;
                   $this->asignaciones=$this->asignaciones+$valor;

               }


                        if ($item->tipo->Text=="CREDITO")
                        {

                        $item->monto_credito->Text=$valor;
                        $item->monto_debito->Text="";
                        $item->monto_credito->ForeColor="green";
                        }
                        else
                        if ($item->tipo->Text=="DEBITO")
                        {
                        $item->monto_debito->Text=$valor;
                        $item->monto_credito->Text="";
                        $item->monto_debito->ForeColor="red";
                        }


            //
            $db->Active=false;
            }
            catch(Exception $e)
            {
            $mensaje=$e->getMessage();
            $db->Active=false;
            echo $mensaje;
            //$this->Response->redirect($this->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
            }


       }
    }

/*    public function saveItem($sender,$param)
    {
        $item=$param->Item;

        $id=$this->DataGrid->DataKeys[$item->ItemIndex];

        $anos=$item->anos->TextBox->Text;
        $tipo=$item->tipo_nomina->DropDownList->Text;
        $banco=$item->pago_banco->DropDownList->Text;
        $status=$item->status->DropDownList->Text;

		$sql="UPDATE nomina.integrantes set anos_servicio='$anos', status='$status', tipo_nomina='$tipo',pago_banco='$banco'  where id='$id'";

        $resultado=modificar_data($sql,$this);

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();

    }*/



	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'P&aacute;gina: ');
	}
    	public function footer($sender,$param)
	{
		$param->Footer->Controls->insertAt(0,'hola ');
	}
    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.integrantes.admin_integrantes'));
    }
}

/*
        */
?>
