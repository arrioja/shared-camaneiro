<?php

class nomina_historial extends TPage
{

    public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{

            $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            // funcionarios activos de asistencia
            $sql="select p.cedula,  CONCAT(p.nombres,' ',p.apellidos) as nombre
 						FROM asistencias.personas_status_asistencias as u, organizacion.personas as p, organizacion.personas_nivel_dir nd
						WHERE ((u.status_asistencia = '1') and (u.cedula=p.cedula) and (nd.cedula = p.cedula) and (nd.cod_organizacion = '$cod_org'))
                        ORDER BY p.nombres, p.apellidos";

            $resultado=cargar_data($sql,$this);
            $this->drop_funcionario->DataSource=$resultado;
            $this->drop_funcionario->dataBind();

           
         // se toma año de los registros de entrada_salida
             $this->drop_ano->Datasource = ano_asistencia($this);
             $this->drop_ano->dataBind();
             $cod_organizacion = usuario_actual('cod_organizacion');
             
             if((!empty($this->Request['ano']))&&((!empty($this->Request['ced'])))){
             $this->drop_ano->SelectedValue=$this->Request['ano'];
             $this->drop_funcionario->SelectedValue=$this->Request['ced'];
             $this->cargar($this, $param);
             }//fin si
        }

   }

    public function changePage($sender,$param)
	{
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
	}

    public function pagos($sender,$param)
    {
        $cedula=$sender->CommandParameter[0];
        $cod=$sender->CommandParameter[1];
        $ano=$this->drop_ano->SelectedValue;
        $this->Response->Redirect( $this->Service->constructUrl('nomina.nominas.integrantes_pagos',array('ced'=>$cedula,'cod'=>$cod,'ano'=>$ano)));//
    }

	public function pagerCreated($sender,$param)
	{
		$param->Pager->Controls->insertAt(0,'Pagina: ');
	}

  function createMultiple($link, $array) {
        $item = $link->Parent->Data;
        $return = array();
        foreach($array as $key)$return[] = $item[$key];

        return implode(",", $return);
  }

    public function cargar($sender,$param)
    {
        $cedula= $this->drop_funcionario->SelectedValue;
        $ano=$this->drop_ano->SelectedValue;
        if(!empty($ano)){
        
        
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="SELECT distinct n.cod,n.tipo_nomina,na.titulo,na.f_ini,n.cedula from nomina.nomina_historial n
            INNER JOIN nomina.nomina_actual na on n.cod=na.cod
            WHERE (na.f_ini Between '$ano-01-01' AND '$ano-12-31') AND n.cod_organizacion='$cod_org' AND n.cedula='$cedula'
            ORDER BY n.cod DESC";
        $resultado=cargar_data($sql,$this);
        $this->DataGrid->DataSource=$resultado;
		$this->DataGrid->dataBind();

        }//fin si
    }


   public function reporte_recibo_general($sender,$param)
    {
 $cedula= $this->drop_funcionario->SelectedValue;
    $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $cod=$datos[0];
   $tipo_nomina=$datos[1];
   $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado


            $sql="select nombre from organizacion.organizaciones where codigo='$cod_org'";
            $datos_organizacion=cargar_data($sql,$this);//datos organizacion
            $sql="select * from nomina.nomina_actual where cod='$cod' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);//datos nomina historial
            $cod=$datos_nomina[0][cod];
            require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $par=2;
            if ($tipo_nomina=="PENSIONADOS"||$tipo_nomina=="JUBILADOS")
                $sql="select distinct p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula
                from nomina.nomina_historial n inner join nomina.integrantes i on i.cedula=n.cedula
                inner join organizacion.personas p on p.cedula=n.cedula
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina' AND n.cedula='$cedula'  )
                order by cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina q son pensionados o jubilados
            else
                $sql="select distinct p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula,d.nombre_completo
                from nomina.nomina_historial n inner join nomina.integrantes i on i.cedula=n.cedula
                inner join organizacion.personas p on p.cedula=n.cedula
                INNER JOIN organizacion.personas_nivel_dir ond on ond.cedula=p.cedula
                INNER JOIN organizacion.direcciones d on ond.cod_direccion=d.codigo
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina' AND n.cedula='$cedula')
                order by ond.cod_direccion asc,cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina

            $res_cedula=cargar_data($sql,$this);

            foreach ($res_cedula as $key=>$integrantes)
            {

                if ($par%2==0)
                    $pdf->AddPage();

            $pdf->SetFont('courier','B',12);
            $pdf->Cell(0,5,$datos_organizacion[0]['nombre'],1, 1, 'C');
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0,5,$datos_nomina[0]['titulo'],1, 1, 'C');
            $pdf->Cell(0, 5, 'Nómina de: '.$tipo_nomina, 1, 1, 'C');
            $pdf->SetFont('courier','B',9);
            $pdf->Cell(40,5,'Cod Nómina: ',1);
            $pdf->Cell(20,5,$datos_nomina[0]['cod'],1);
            $pdf->Cell(25,5,'Desde: ',1);
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']),1);
            $pdf->Cell(25,5,'Hasta: ',1);
            $pdf->Cell(51,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),1,1);
            if (($tipo_nomina!="PENSIONADOS")&&($tipo_nomina!="JUBILADOS"))//muestra la direccion si no es jubilado o pensionado
                $pdf->Cell(0,5,"Dirección: ".$integrantes['nombre_completo'],1,1);
            $pdf->Cell(0,5,$integrantes['nombres'].' '.$integrantes['apellidos'].' C.I.'.$integrantes['cedula'],1,1,'C');
            $pdf->SetFont('courier','B',7);
            $pdf->Cell(25,5,'Codigo',1,0,'C');
            $pdf->Cell(80,5,'Descripción',1);
            $pdf->Cell(25,5,'Asignaciones',1);
            $pdf->Cell(25,5,'Deducciones',1,1);


            $sql2="select n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo from nomina.nomina_historial n where n.cedula='".$integrantes['cedula']."' and n.cod='$cod' and n.tipo_nomina='$tipo_nomina' order by n.tipo asc";
            $result2=cargar_data($sql2,$this);
                foreach ($result2 as $key2=>$incidencias)
                {
                    if (($incidencias['tipo']=='CREDITO')||($incidencias['tipo']=='DEBITO'))
                    {//si es credito o debito
                        $pdf->Cell(25,3,$incidencias['cod_incidencia'],1,0,'C');
                        $pdf->Cell(80,3,$incidencias['descripcion'],1);

                       if ($incidencias['tipo']=='CREDITO')
                            $pdf->Cell(25,3,$incidencias['monto_incidencia'],1,1,'R');

                       if ($incidencias['tipo']=='DEBITO')
                            {
                             $pdf->Cell(25,3,"",1);
                             $pdf->Cell(25,3,$incidencias['monto_incidencia'],1,1,'R');
                            }
                    }//si es credito o debito

                    if ($incidencias['tipo']=='ASIGNACION')$asignacion=$incidencias['monto_incidencia'];
                    else
                        if ($incidencias['tipo']=='DEDUCCION')$deduccion=$incidencias['monto_incidencia'];
                }//foreach asignaciones y deducciones
                $pdf->Cell(105,5,'   Totales  ',1,0,'R');
                $pdf->Cell(25,5,$asignacion,1,0,'R');
                $pdf->Cell(25,5,$deduccion,1,1,'R');
                $pdf->SetFont('courier','B',10);
                $pdf->Cell(155,5,'   Total General  ',1,0,'R');

                $pdf->Cell(25,5,$asignacion-$deduccion,1,0,'R');
                $pdf->SetFont('courier','B',7);
               /* $pdf->Cell(225,20,"                                                                                                                        ",0,1);
   $pdf->Cell(225,6,"                                            _______________________________                                                                 ",0,1);
   $pdf->Cell(225,3,"                                                    Recibí Conforme                                                                    ",0,1);
   $pdf->Cell(225,3,"                                                    C.I.$integrantes[cedula]                                                                    ",0,1);
    $pdf->Cell(225,4,"                                                                                                                        ",0,1);
    if ($par%2==0)
        {$pdf->Cell(225,15,"-------------------------------------------------------------------------------------------------------------------------------",0,1);
         $pdf->Cell(225,4,"                                                                                                                        ",0,1);
        }
   $par=$par+1;*/
            }//foreach integrantes

            $pdf->Output('recibo_general_historial.pdf','D');

            }


    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.reportes.reportes'));
    }

/* Esta Función permite colocarle cierto formato al grid antes de que se muestre, por ejemplo, las fechas,
 * el mostrar o no el botón de detalles, etc.
 */
    public function formatear($sender, $param)
    {
        $color1 = array("#E6ECFF","#BFCFFF");

        $item=$param->Item;

        if ($item->ano->Text != "Año")
        {
            $arreglo=split('-',$item->ano->Text);
            $item->ano->Text = $arreglo[0];
            $arreglomes=array('01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre');

            $item->mes->Text = $arreglomes[$arreglo[1]];
        }
    }


}
?>
