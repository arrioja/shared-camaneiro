<?php

class integrantes_pagos extends TPage
{
var $suma;//variable global para mostrar el total en el footer del grid
var $asignaciones=0;
var $deducciones=0;

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


        }

   }
public function cargar_posee($cedula)
{
 $cod=$this->Request['cod'];
  $cedula=$this->Request['ced'];
    $sql="select '$cod' as cod,nh.id,nh.cod_incidencia, nh.descripcion, nh.tipo, nh.monto_incidencia ,nh.cedula
        from nomina.nomina_historial nh
        where nh.cedula='$cedula' and nh.cod='$cod' AND (nh.tipo =  'CREDITO' OR nh.tipo =  'DEBITO')
         order by nh.tipo asc ";


 return cargar_data($sql,$this);

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

    }



    public function itemCreated($sender,$param)
    {          // Sender is the datagrid object
  // Param is a TDataGridEventParameter  which contains the item newly created

        $item=$param->Item;

        if($item->getItemType() === 'Footer')
        {
            $total_asig = new TLabel();
            $total_deduc = new TLabel();
            $total = new TLabel();


            $total_asig->Text=$this->asignaciones;
            $total_deduc->Text=$this->deducciones;
            $total->Text=($this->asignaciones-$this->deducciones);


            $item->Cells[5]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            $item->Cells[6]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            $item->Cells[7]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[5]->Controls->insertAt(0,$total_asig);//$item->getControls()->add(0,"hola");
            $item->Cells[5]->HorizontalAlign="Right";
            $item->Cells[5]->ForeColor="Green";

            $item->Cells[6]->Text = ''; // Empty the Text property (if the ItemType is 'Header' or 'Footer' or 'Separator'(?))
            //$item->Cells[0]->Controls->clear(); // Clear the children controls (if the ItemType is any Item)
            $item->Cells[6]->Controls->insertAt(0,$total_deduc);//$item->getControls()->add(0,"hola");
            $item->Cells[6]->HorizontalAlign="Right";
            $item->Cells[6]->ForeColor="Red";
            
            $item->Cells[7]->Controls->insertAt(0,$total);//$item->getControls()->add(0,"hola");
            $item->Cells[7]->HorizontalAlign="Right";
            $item->Cells[7]->ForeColor="Black";
            $item->Cells[7]->Font->Bold = "true";
           


        }


    }



    public function formatear($sender,$param)//sin revisar
    {
       $item=$param->Item;

       $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
       if (($item->monto_credito->Text!="Monto")||($item->monto_debito->Text!="Monto"))//si el valor es diferente a la cabecera lo cambia
        {

             if ($item->cod->Text=="0001")//bono de antiguedad
                  {   $valor=$item->monto->Text;
                      $valor=(int)round($valor*100)/100;
                      $this->asignaciones=$this->asignaciones+$valor;
                  }
             else
               if  ($item->tipo->Text=="DEBITO")
                   // si el concepto es vivienda y habitat o RISLR se debe calcular con el total de las asignaciones
                    if (($cod=="0005")||($cod=="9001"))
                       {
                           $valor=$item->monto->Text;
                           $valor=(int)round($valor*100)/100;
                           $this->deducciones=$this->deducciones+$valor;
                       }
                    else
                        {
                             $valor=$item->monto->Text;
                            $valor=(int)round($valor*100)/100;
                            $this->deducciones=$this->deducciones+$valor;
                        }
               else   //si son credito
               {
                   $valor=$item->monto->Text;
                   $valor=(int)round($valor*100)/100;
                   $this->asignaciones=$this->asignaciones+$valor;

               }

    $item->monto_total->Text="";

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


       }
    }


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

     $this->Response->redirect($this->Service->constructUrl('nomina.reportes.nomina_historial',array('ano'=>$this->Request['ano'],'ced'=>$this->Request['ced'])));
    }
}

?>
