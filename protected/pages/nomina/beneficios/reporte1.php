<?php
 
class reporte1 extends TPage
{

   
    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack && !$this->IsCallBack)
        {
            $this->txt_bs->Text=0;
            $this->txt_dias->Text=0;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select tipo_nomina from nomina.tipo_nomina
                  where (cod_organizacion='$cod_organizacion' ) order by tipo_nomina";
            $resultado=cargar_data($sql,$this);
            // las siguientes dos líneas añaden el elemento "TODAS" al listado de Dir.
            $todos1 = array('tipo_nomina'=>'TODAS');
            array_unshift($resultado, $todos1);
            //$todos = array('tipo_nomina'=>'EMPLEADOS - DIRECTORES');
            //array_unshift($resultado, $todos);
            $todos2 = array('tipo_nomina'=>'Seleccione');
            array_unshift($resultado, $todos2);
            // Se enlaza el nuevo arreglo con el listado
            $this->drop_nomina->DataSource=$resultado;
            $this->drop_nomina->dataBind();

            $this->drop_monto->DataSource=array('Seleccione'=>'Seleccione','Sueldo Integral'=>'Sueldo Integral','Monto Unico'=>'Monto Unico');
            $this->drop_monto->dataBind();
            $arreglo= array();
            for($i=1;$i<=25;$i++)array_push($arreglo,$i);

            array_unshift($arreglo,'Hijos de Edad Hasta:');
            $this->drop_hijo->DataSource=$arreglo;
            $this->drop_hijo->dataBind();

            $this->txt_dias->Enabled=False;
            $this->txt_bs->Enabled=False;
            $this->lbl_codigo_temporal->Text = aleatorio_numero($this,'nomina.bonificacion_personas_temporal');

        }
    }
 
    public function itemCreated($sender,$param)
    {
        $item=$param->Item;
       /* if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {

            if($this->drop_monto->SelectedValue=="Sueldo Integral" )
            $item->editable->Text="Dias";
            else
            $item->editable->Text="Monto";
       }*/

        if($item->ItemType==='EditItem')
        {
            // set column width of textboxes
           // $item->BookTitleColumn->TextBox->Columns=40;
           $item->nombre->TextBox->Enabled=false;
            $item->cedula->TextBox->Enabled=false;
            $item->editable->TextBox->Columns=10;
        }
        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' || $item->ItemType==='EditItem')
        {
            // add an aleart dialog to delete buttons
            $item->DeleteColumn->Button->Attributes->onclick='if(!confirm(\'¿Esta seguro?\')) return false;';
        }
    }
 
    public function editItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        /*$this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();*/
         $sql="select t.id, p.cedula, CONCAT(p.nombres,' ',p.apellidos) as nombre,editable
                  FROM nomina.bonificacion_personas_temporal as t, organizacion.personas as p
                  WHERE ((t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula) )
                  ORDER BY id ASC ";
      $resultado=cargar_data($sql,$sender);
      $this->DataGrid->DataSource=$resultado;
      $this->DataGrid->dataBind();
    }
 
    public function saveItem($sender,$param)
    {
        $item=$param->Item;
        $id=$this->DataGrid->DataKeys[$item->ItemIndex];
        $editable=$item->editable->TextBox->Text;

        $sql="UPDATE nomina.bonificacion_personas_temporal set editable='$editable' WHERE id='$id' ";
        $resultado=modificar_data($sql,$sender);

        $sql2="select t.id, p.cedula, CONCAT(p.apellidos,' ',p.nombres) as nombre,editable
                  FROM nomina.bonificacion_personas_temporal as t, organizacion.personas as p
                  WHERE ((t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula) )
                  ORDER BY id ASC ";
        $resultado=cargar_data($sql2,$sender);

        $this->DataGrid->EditItemIndex=-1;

        $this->DataGrid->DataSource=$resultado;
        $this->DataGrid->dataBind();
       
    }
 
    public function cancelItem($sender,$param)
    {
        $this->DataGrid->EditItemIndex=-1;
        $sql="select t.id, p.cedula, CONCAT(p.apellidos,' ',p.nombres) as nombre,editable
                  FROM nomina.bonificacion_personas_temporal as t, organizacion.personas as p
                  WHERE ((t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula) )
                  ORDER BY id ASC ";
        $resultado=cargar_data($sql,$sender);
        $this->DataGrid->DataSource=$resultado;
         $this->DataGrid->dataBind();
       
    }
 
    public function deleteItem($sender,$param)
    {
      $id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
      $sql="DELETE FROM nomina.bonificacion_personas_temporal WHERE id = '$id' ";
      $resultado=modificar_data($sql,$sender);
      $this->DataGrid->EditItemIndex=-1;

     /* $sql="select t.id, p.cedula, CONCAT(p.apellidos,' ',p.nombres) as nombre,editable
                  FROM nomina.bonificacion_personas_temporal as t, organizacion.personas as p
                  WHERE ((t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula) )
                  ORDER BY id ASC ";
      $resultado=cargar_data($sql,$sender);
      $this->DataGrid->DataSource=$resultado;
      $this->DataGrid->dataBind();*/

        $this->actualiza_listados($sender);
    }


    public function actualiza_listados($sender)
    {
        $fecha_reporte=$this->txt_fecha->Text;




        /*$sql="DELETE FROM nomina.bonificacion_personas_temporal
              where (numero = '".$this->lbl_codigo_temporal->Text."') ";
        $resultado=modificar_data($sql,$this);*/

          if($this->drop_nomina->SelectedValue!="Seleccione"){
                if($this->drop_nomina->SelectedValue=="TODAS"){
                $AND=" (n.tipo_nomina!='') and (itn.tipo_nomina!='') ";
                $tipo_nomina="TODAS";
                }elseif($this->drop_nomina->SelectedValue=="EMPLEADOS - DIRECTORES"){
                $AND=" (n.tipo_nomina='EMPLEADOS' or n.tipo_nomina='DIRECTORES') and (itn.tipo_nomina='EMPLEADOS' OR itn.tipo_nomina='DIRECTORES') ";
                $tipo_nomina="EMPLEADOS";
                }else{
                $tipo_nomina=$this->drop_nomina->SelectedValue;
                $AND=" (n.tipo_nomina='$tipo_nomina') and (itn.tipo_nomina='$tipo_nomina') ";
                }//fin si

          if($this->drop_sexo->SelectedValue!="N/A")
          $AND_sexo=" AND (p.sexo='".$this->drop_sexo->SelectedValue."' OR p.sexo='".strtoupper($this->drop_sexo->SelectedValue)."')  ";
          if($this->drop_hijo->SelectedValue!="0"){
            $arre=split('/',$this->txt_fecha->Text);
            $fecha_restada=($arre[2]-$this->drop_hijo->SelectedValue)."-$arre[1]-$arre[0]";
          $AND_hijo="inner join organizacion.personas_carga_familiar pcf ON (pcf.persona_cedula=p.cedula AND pcf.parentesco='Hijo(a)' AND ( (TO_DAYS('".cambiaf_a_mysql($fecha_reporte)."') - TO_DAYS(pcf.fecha_nacimiento))>= (TO_DAYS('".cambiaf_a_mysql($fecha_reporte)."') - TO_DAYS('$fecha_restada') ))) ";
          }
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            // se consulta codigo de nomina actual
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            //tiene bonificacion
           /* $sql="INSERT INTO nomina.bonificacion_personas_temporal
            (numero,cedula)
            (SELECT '".$this->lbl_codigo_temporal->Text."',n.cedula
            FROM nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
            inner join organizacion.personas p on (p.cedula=n.cedula $AND_sexo )
            $AND_hijo
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            WHERE (n.cod='$cod'  and $AND ) GROUP BY n.cedula ORDER BY p.apellidos, p.nombres ASC)";
            $resultado=modificar_data($sql,$this);*/
            //no tiene bonificacion
                $sql="SELECT p.cedula, CONCAT(p.apellidos,' ',p.nombres) as nombre,p.cedula as id
                    FROM nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
                    inner join organizacion.personas p on (p.cedula=n.cedula $AND_sexo )
                    $AND_hijo
                    inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
                    WHERE n.cod='$cod'  and $AND
                    AND NOT EXISTS (SELECT * FROM nomina.bonificacion_personas_temporal as t WHERE (t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula))
                    GROUP BY n.cedula ORDER BY p.apellidos, p.nombres ASC";
            $resultado=cargar_data($sql,$sender);
            $this->DataGrid_no_tiene->DataSource=$resultado;
            $this->DataGrid_no_tiene->dataBind();

            $sql="select t.id, p.cedula, CONCAT(p.apellidos,' ',p.nombres) as nombre
                  FROM nomina.bonificacion_personas_temporal as t, organizacion.personas as p
                  WHERE ((t.numero= '".$this->lbl_codigo_temporal->Text."') and (t.cedula=p.cedula) )
                  ORDER BY id ASC ";
            $resultado=cargar_data($sql,$sender);
            $this->DataGrid->DataSource=$resultado;
            $this->DataGrid->dataBind();
        }//FIN SI
    }

/* agrega a la tabla temporal las cedulas de los sujetos seleccionados para justificacion*/
	public function agregar($sender,$param)
	{
        $id=$this->DataGrid_no_tiene->DataKeys[$param->Item->ItemIndex];

        $sql="INSERT INTO nomina.bonificacion_personas_temporal
                (numero,cedula)VALUES ('".$this->lbl_codigo_temporal->Text."','$id')";
        $resultado=modificar_data($sql,$this);

        $this->actualiza_listados($sender);
	}

    public function actualiza($sender,$param){

   if($this->drop_monto->SelectedValue=="Monto Unico"){
        $this->txt_dias->Enabled=False;
        $this->txt_bs->Enabled=True;
    }elseif($this->drop_monto->SelectedValue=="Sueldo Integral"){
        $this->txt_dias->Enabled=true;
        $this->txt_bs->Enabled=False;
    }
}
public function imprimir($sender,$param){

 if(($this->drop_monto->SelectedValue=="Sueldo Integral")&&($this->txt_dias->text!="")&&($this->txt_dias->text!="0"))
 $this->imprimir_sueldo_integral($sender,$param);
 elseif($this->drop_monto->SelectedValue=="Monto Unico")
 $this->imprimir_monto_unico($sender,$param);

}

public function imprimir_sueldo_integral($sender,$param){
            //$this->mensaje->setSuccessMessage($sender, "Orden Anulada Exitosamente!!", 'grow');

             if($this->drop_nomina->SelectedValue=="TODAS"){
                $AND=" (n.tipo_nomina!='') and (itn.tipo_nomina!='') ";
                $tipo_nomina="TODAS";
                }elseif($this->drop_nomina->SelectedValue=="EMPLEADOS - DIRECTORES"){
                $AND=" (n.tipo_nomina='EMPLEADOS' or n.tipo_nomina='DIRECTORES') and (itn.tipo_nomina='EMPLEADOS' OR itn.tipo_nomina='DIRECTORES') ";
                $tipo_nomina="EMPLEADOS";
                }else{
                $tipo_nomina=$this->drop_nomina->SelectedValue;
                $AND=" (n.tipo_nomina='$tipo_nomina') and (itn.tipo_nomina='$tipo_nomina') ";
                }//fin si

            $ano=date(Y);
            $dias=$this->txt_dias->Text;
            $descripcion=strtolower($this->txt_descripcion->Text);

            require('/var/www/tcpdf/tcpdf.php');
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            // se consulta codigo de nomina actual
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            $pdf=new TCPDF('L', 'mm', 'legal', true, 'utf-8', false);
            /*$pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            */

            $sqlpers="SELECT CONCAT(p.nombres,' ',p.apellidos) as nombre_persona FROM organizacion.personas p
                      WHERE (p.cedula = '".usuario_actual('cedula')."')";
            $persona=cargar_data($sqlpers,$sender);

            $info_adicional= "Reporte de Nomina de ".ucwords($descripcion)."\nDirección: Recursos Humanos\nRealizado por: ".ucwords(strtolower($persona[0]['nombre_persona']));
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Reporte de Nomina');
            $pdf->SetSubject('Reporte de Nomina');


            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('helvetica','B',13);
            $pdf->Cell(0, 17, strtoupper($descripcion), 0, 0, 'C');
            $pdf->Ln(5);
            $pdf->Cell(0, 17, "DEL PERSONAL $tipo_nomina AÑO ".$ano." RESOLUCION Nº ".$this->txt_resolucion->Text, 0, 0, 'C');
            $pdf->Ln(5);
            $pdf->Cell(0, 17, "CAMARA DEL MUNICIPIO MANEIRO", 0, 0, 'C');

            $pdf->Ln();
            $sql="select itn.tipo_nomina,p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula, n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo,itn.tipo_nomina
            from nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
            inner join organizacion.personas p on p.cedula=n.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            where (n.cod='$cod' and $AND ) and(n.cod_incidencia='7001' ) order by p.apellidos, p.nombres,cod_nomina asc, n.cod_incidencia asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
            $res_cedula=cargar_data($sql,$sender);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            $pdf->SetFont('helvetica','B',8);
            $pdf->Cell(5,10,'Nº',1,0,'C',1);
            $pdf->Cell(82,10,'Apellidos y Nombres',1,0,'C',1);
            $pdf->Cell(17,10,'Cédula',1,0,'C',1);
            $pdf->Cell(22,10,"Fecha Ingreso",1,0,'C',1);
            $pdf->Cell(42,10,'Sueldo Integral',1,0,'C',1);
            $pdf->Cell(22,10,'Dias a Bonificar',1,0,'C',1);
            $pdf->Cell(28,10,'Total a Bonificar',1,0,'C',1);
            $pdf->Cell(33,10,'Menos Retencion ISLR',1,0,'C',1);
            $pdf->Cell(30,10,'Total Neto a Cobrar',1,0,'C',1);
            $pdf->Cell(46,10,'Firma',1,1,'C',1);
            $ced='';
            $pdf->SetFont('helvetica','',8);
            $asignaciones=0;
            $deducciones=0;
            $i=1;

            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',8);
            $j=0;
            foreach ($res_cedula as $key=>$integrantes)
            {
                //verificamos si esta incluido para el reporte
                $sql="select cedula,editable FROM nomina.bonificacion_personas_temporal
                  WHERE numero= '".$this->lbl_codigo_temporal->Text."' AND cedula='".$integrantes['cedula']."'";
                $funcionario=cargar_data($sql,$sender);
                if ((($ced=='')||($ced!=$integrantes[cedula]))&&($funcionario[0]['cedula']!=""))
                {

                    $pdf->Cell(5,7,$i,1,0,'C');//numero
                    $pdf->Cell(82,7,$integrantes['apellidos'].', '.$integrantes['nombres'],1);
                    $pdf->Cell(17,7,$integrantes['cedula'],1,0,'C');
                    // se obtiene la fecha de inicio del primer cargo
                    $sql2="SELECT fecha_ini FROM organizacion.personas_cargos WHERE cedula='$integrantes[cedula]' order by fecha_ini asc LIMIT 1 ";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
                    $res_fecha=cargar_data($sql2,$this);

                    $pdf->Cell(22,7,cambiaf_a_normal($res_fecha[0]['fecha_ini']),1,0,'C');
                    $ced=$integrantes['cedula'];
                    $nomina=$integrantes['tipo_nomina'];

                    $pdf->Cell(42,7,number_format(($integrantes['monto_incidencia']*2), 2, ',', '.'),1,0,'R');
                    if($funcionario[0]['editable']!="")
                    {$pdf->Cell(22,7,$funcionario[0]['editable'],1,0,'R');
                    $monto_a_pagar=((($integrantes['monto_incidencia']*2))/30)*$funcionario[0]['editable'];
                    }else{
                    $pdf->Cell(22,7,$dias,1,0,'R');
                    $monto_a_pagar=((($integrantes['monto_incidencia']*2))/30)*$dias;
                    }//fin si
                    
                    $pdf->Cell(28,7,number_format($monto_a_pagar, 2, ',', '.'),1,0,'R');
                    $formula_islr=NULL;
                    
                    // Se obtiene si posee R.I.S.L.R segun codigo 9001 en tabla integrantes
                    $db = $this->Application->Modules["db2"]->DbConnection;$db->Active=true;
                    $sql2="select c.formula
                          from nomina.conceptos c inner join nomina.integrantes_conceptos ic on ic.cod_concepto=c.cod
                          where ic.cedula='$ced' and ic.tipo_nomina='$nomina' AND ic.cod_concepto='9001' ";
                    $formula_islr=cargar_data($sql2,$this);

                        $valor=0;
                       if($formula_islr[0]['formula']!=""){
                       $valor=evaluar_concepto_con_asignaciones(array('cedula'=>$ced),array('formula'=>$formula_islr[0]['formula'],'cod'=>'9001'),$monto_a_pagar,$db);
                       $valor=(int)round($valor*100)/100;
                            if($valor!=0)$pdf->Cell(33,7,number_format($valor, 2, ',', '.'),1,0,'R');//islr
                            else $pdf->Cell(33,7,"0,00",1,0,'R');//islr
                        }else{
                         $pdf->Cell(33,7,"0,00",1,0,'R');//islr
                        }//fin si

                   // $db->Active=false;


                    $pdf->Cell(30,7,number_format(($monto_a_pagar-$valor), 2, ',', '.'),1,0,'R');//total neto
                    $acum=$acum+($monto_a_pagar-$valor);
                    $pdf->Cell(46,7,"",1,0,'R');//firma

                    $pdf->Ln();
                    $i++;//enumerador de filas
                    $j++;
                    //encabezado
                    if($j==19){
                        $j=-4;
                        // se realiza el listado de asistentes en el PDF
                        $pdf->SetFillColor(205, 205, 205);//color de relleno gris
                        $pdf->SetFont('helvetica','B',8);
                        $pdf->Cell(5,10,'Nº',1,0,'C',1);
                        $pdf->Cell(82,10,'Apellidos y Nombres',1,0,'C',1);
                        $pdf->Cell(17,10,'Cédula',1,0,'C',1);
                        $pdf->Cell(22,10,"Fecha Ingreso",1,0,'C',1);
                        $pdf->Cell(42,10,'Sueldo Integral',1,0,'C',1);
                        $pdf->Cell(22,10,'Dias a Bonificar',1,0,'C',1);
                        $pdf->Cell(28,10,'Total a Bonificar',1,0,'C',1);
                        $pdf->Cell(33,10,'Menos Retencion ISLR',1,0,'C',1);
                        $pdf->Cell(30,10,'Total Neto a Cobrar',1,0,'C',1);
                        $pdf->Cell(46,10,'Firma',1,1,'C',1);
                        $pdf->SetFont('helvetica','',8);
                    }//fin si
                }

            }//foreach integrantes

            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            $pdf->SetFont('helvetica','B',10);
            $pdf->Cell(251,7,"TOTAL Bs.",1,0,'R',1);
            $pdf->Cell(30,7,number_format($acum, 2, ',', '.'),1,0,'R');


                //observaciones
            if ($this->txt_observacion->text!=""){
            $pdf->Ln();$pdf->Ln();
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(0, 0, "Observaciones", 0, 1, '', 1);
            $pdf->SetFont('helvetica', '', 12);

            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $pdf->MultiCell($w[0], 0, $this->txt_observacion->text, 1, 'L',0,'1','','',true,0,false);
            }

            $pdf->Output("reporte.pdf","D");
}

public function imprimir_monto_unico($sender,$param){
                //  $this->mensaje->setSuccessMessage($sender, "Orden Anulada Exitosamente!!", 'grow');
                 $fecha_reporte=$this->txt_fecha->Text;

            if($this->drop_nomina->SelectedValue=="TODAS"){
                $AND=" (n.tipo_nomina!='') and (itn.tipo_nomina!='') ";
                $tipo_nomina="TODAS";
                }elseif($this->drop_nomina->SelectedValue=="EMPLEADOS - DIRECTORES"){
                $AND=" (n.tipo_nomina='EMPLEADOS' or n.tipo_nomina='DIRECTORES') and (itn.tipo_nomina='EMPLEADOS' OR itn.tipo_nomina='DIRECTORES') ";
                $tipo_nomina="EMPLEADOS";
                }else{
                $tipo_nomina=$this->drop_nomina->SelectedValue;
                $AND=" (n.tipo_nomina='$tipo_nomina') and (itn.tipo_nomina='$tipo_nomina') ";
                }//fin si

            $ano=date(Y);
            $descripcion=$this->txt_descripcion->Text;

            require('/var/www/tcpdf/tcpdf.php');
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            // se consulta codigo de nomina actual
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
           $sqlpers="SELECT CONCAT(p.nombres,' ',p.apellidos) as nombre_persona FROM organizacion.personas p
                      WHERE (p.cedula = '".usuario_actual('cedula')."')";
            $persona=cargar_data($sqlpers,$sender);

            $info_adicional= "Reporte de Nomina de ".ucwords($descripcion)."\nDirección: Recursos Humanos\nRealizado por: ".ucwords(strtolower($persona[0]['nombre_persona']));
            $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(10, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT, 2);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 15);//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Reporte de Nomina');
            $pdf->SetSubject('Reporte de Nomina');


            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('helvetica','B',13);
            $pdf->Cell(0, 17, strtoupper($descripcion), 0, 0, 'C');
            $pdf->Ln(5);
            $pdf->Cell(0, 17, "DEL PERSONAL $tipo_nomina AÑO ".$ano." RESOLUCION Nº ".$this->txt_resolucion->Text, 0, 0, 'C');
            $pdf->Ln(5);
            $pdf->Cell(0, 17, "CAMARA DEL MUNICIPIO MANEIRO ", 0, 0, 'C');

            $pdf->Ln();
            $sql="select itn.tipo_nomina,p.fecha_ingreso,p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula, n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo,itn.tipo_nomina
            from nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
            inner join organizacion.personas p on p.cedula=n.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            where (n.cod='$cod' and $AND ) and(n.cod_incidencia='7001' ) order by p.apellidos, p.nombres,cod_nomina asc, n.cod_incidencia asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
            $res_cedula=cargar_data($sql,$this);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            $pdf->SetFont('helvetica','B',8);
            $pdf->Cell(5,10,'Nº',1,0,'C',1);
            $pdf->Cell(80,10,'Apellidos y Nombres',1,0,'C',1);
            $pdf->Cell(17,10,'Cédula',1,0,'C',1);
            $pdf->Cell(22,10,"Fecha Ingreso",1,0,'C',1);
            $pdf->Cell(25,10,'Monto a Cancelar',1,0,'C',1);
            $pdf->Cell(46,10,'Firma',1,1,'C',1);
            $ced='';
            $pdf->SetFont('helvetica','',8);
            $asignaciones=0;
            $deducciones=0;
            $i=1;


            $pdf->SetFont('','',8);
            $acum=0;$j=0;
            foreach ($res_cedula as $key=>$integrantes)
            {   //verificamos si esta incluido para el reporte
                $sql="select cedula,editable FROM nomina.bonificacion_personas_temporal
                  WHERE numero= '".$this->lbl_codigo_temporal->Text."' AND cedula='".$integrantes['cedula']."' ";
                $funcionario=cargar_data($sql,$sender);
                if ((($ced=='')||($ced!=$integrantes[cedula]))&&($funcionario[0]['cedula']!=""))
                {

                    $pdf->Cell(5,7,$i,1,0,'C');//numero
                    $pdf->Cell(80,7,$integrantes['apellidos'].', '.$integrantes['nombres'],1);
                    $pdf->Cell(17,7,$integrantes['cedula'],1,0,'C');
                    // se obtiene la fecha de inicio del primer cargo
                    $sql2="SELECT fecha_ini FROM organizacion.personas_cargos WHERE cedula='$integrantes[cedula]' order by fecha_ini asc LIMIT 1";
                    $res_fecha=cargar_data($sql2,$this);

                    $pdf->Cell(22,7,cambiaf_a_normal($res_fecha[0]['fecha_ini']),1,0,'C');

                    //si es modificado
                    if($funcionario[0]['editable']!=""){
                    $monto=$funcionario[0]['editable'];
                    }else{
                    $monto=$this->txt_bs->text;
                    // si se filtra por hijos se busca la cantidad para multiplicarle el monto
                        if($this->drop_hijo->SelectedValue!="0"){
                        $arre=split('/',$this->txt_fecha->Text);
                        $fecha_restada=($arre[2]-$this->drop_hijo->SelectedValue)."-$arre[1]-$arre[0]";

                        //verificamos si esta incluido para el reporte
                        $sql2="select COUNT(*) as n FROM nomina.bonificacion_personas_temporal t
                        INNER JOIN organizacion.personas_carga_familiar as pcf ON (pcf.persona_cedula=t.cedula AND pcf.parentesco='Hijo(a)' AND ( (TO_DAYS('".cambiaf_a_mysql($fecha_reporte)."') - TO_DAYS(pcf.fecha_nacimiento))>= (TO_DAYS('".cambiaf_a_mysql($fecha_reporte)."') - TO_DAYS('$fecha_restada') )))
                          WHERE t.numero= '".$this->lbl_codigo_temporal->Text."' AND t.cedula='".$integrantes['cedula']."'";
                        $hijos=cargar_data($sql2,$sender);
                        $monto=$monto*$hijos[0][n];
                        }//fin is
    
                    }

                    $pdf->Cell(25,7,number_format($monto, 2, ',', '.'),1,0,'R');
                    
                    
                    
                    $pdf->Cell(46,7,"",1,0,'R');//firma
                    $acum=$acum+$monto;
                    $pdf->Ln();
                    $i++;//enumerador de filas
                    $j++;
                    //encabezado
                    if($j==28){
                        $j=-4;
                        // se realiza el listado de asistentes en el PDF
                        $pdf->SetFillColor(205, 205, 205);//color de relleno gris
                        $pdf->SetFont('helvetica','B',8);
                        $pdf->Cell(5,10,'Nº',1,0,'C',1);
                        $pdf->Cell(80,10,'Apellidos y Nombres',1,0,'C',1);
                        $pdf->Cell(17,10,'Cédula',1,0,'C',1);
                        $pdf->Cell(22,10,"Fecha Ingreso",1,0,'C',1);
                        $pdf->Cell(25,10,'Monto a Cancelar',1,0,'C',1);
                        $pdf->Cell(46,10,'Firma',1,1,'C',1);
                        $pdf->SetFont('helvetica','',8);
                    }//fin si
                }

            }//foreach integrantes
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            $pdf->SetFont('helvetica','B',10);
            $pdf->Cell(124,7,"TOTAL Bs.",1,0,'R',1);
            $pdf->Cell(25,7,number_format($acum, 2, ',', '.'),1,0,'R');

            //observaciones
        if ($this->txt_observacion->text!=""){
        $pdf->Ln();$pdf->Ln();
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(0, 0, "Observaciones", 0, 1, '', 1);
        $pdf->SetFont('helvetica', '', 12);
        
        // Color and font restoration
        $pdf->SetFillColor(224, 235, 255);
        $pdf->SetTextColor(0);
        $pdf->SetFont('','',10);
        // Data
        $pdf->MultiCell($w[0], 0, $this->txt_observacion->text, 1, 'L',0,'1','','',true,0,false);
        }



            $pdf->Output("reporte.pdf","D");
}

public function limpiar($sender,$param)
    {
        $this->Response->Redirect( $this->Service->constructUrl('nomina.beneficios.reporte1'));
    }

public function imprimir_recibos($sender,$param){


            require('/var/www/tcpdf/tcpdf.php');
            $cod_org=$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
            $sql="select nombre from organizacion.organizaciones where codigo='$cod_org'";
            $datos_organizacion=cargar_data($sql,$this);
            $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
            $datos_nomina=cargar_data($sql,$this);
            $cod=$datos_nomina[0][cod];
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->SetMargins(15, 25, 15, 15);
              $pdf->SetAutoPageBreak(TRUE, 15);
  
            if($this->drop_nomina->SelectedValue=="TODAS"){
                $AND=" (n.tipo_nomina!='') and (itn.tipo_nomina!='') ";
                $tipo_nomina="TODAS";
                }elseif($this->drop_nomina->SelectedValue=="EMPLEADOS - DIRECTORES"){
                $AND=" (n.tipo_nomina='EMPLEADOS' or n.tipo_nomina='DIRECTORES') and (itn.tipo_nomina='EMPLEADOS' OR itn.tipo_nomina='DIRECTORES') ";
                $tipo_nomina="EMPLEADOS";
                }else{
                $tipo_nomina=$this->drop_nomina->SelectedValue;
                $AND=" (n.tipo_nomina='$tipo_nomina') and (itn.tipo_nomina='$tipo_nomina') ";
                }//fin si

            $ano=date(Y);
            $dias=$this->txt_dias->Text;
            $descripcion=strtolower($this->txt_descripcion->Text);
            $sql="select itn.tipo_nomina,p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula, n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo,itn.tipo_nomina
            from nomina.nomina n inner join nomina.integrantes i on i.cedula=n.cedula
            inner join organizacion.personas p on p.cedula=n.cedula
            inner join nomina.integrantes_tipo_nomina itn on itn.cedula=p.cedula
            where (n.cod='$cod' and $AND ) and(n.cod_incidencia='7001' ) order by p.apellidos, p.nombres,cod_nomina asc, n.cod_incidencia asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
            $res_cedula=cargar_data($sql,$sender);
           
            $pdf->AddPage();
            $conta=1;
            //se recorre los funcionarios
            foreach ($res_cedula as $key=>$integrantes)
            {

                //verificamos si esta incluido para el reporte
                $sql="select cedula,editable FROM nomina.bonificacion_personas_temporal
                  WHERE numero= '".$this->lbl_codigo_temporal->Text."' AND cedula='".$integrantes['cedula']."'";
                $funcionario=cargar_data($sql,$sender);
               
                if ((($ced=='')||($ced!=$integrantes[cedula]))&&($funcionario[0]['cedula']!=""))
                {
                   
                    $pdf->SetFont('courier','B',12);
                    $pdf->Cell(0,5,$datos_organizacion[0]['nombre'],1, 1, 'C');
                    $pdf->SetFont('courier','B',10);
                    $pdf->Cell(0,5,strtoupper($descripcion)." AÑO ".$ano." RESOLUCION Nº ".$this->txt_resolucion->Text,1, 1, 'C');
                    $pdf->Cell(0, 5, 'Nómina de: '.$tipo_nomina, 1, 1, 'C');
                    $pdf->SetFont('courier','B',9);

                    //if (($tipo_nomina!="PENSIONADOS")&&($tipo_nomina!="JUBILADOS"))//muestra la direccion si no es jubilado o pensionado
                    //$pdf->Cell(0,5,"Dirección: ".$integrantes['nombre_completo'],1,1);
                    $pdf->Cell(0,5,$integrantes['nombres'].' '.$integrantes['apellidos'].' C.I.'.$integrantes['cedula'],1,1,'C');
                    $pdf->SetFont('courier','B',7);
                    //$pdf->Cell(25,5,'Codigo',1,0,'C');
                    $pdf->Cell(105,5,'Descripción',1);
                    $pdf->Cell(25,5,'Asignaciones',1);
                 if($this->drop_monto->SelectedValue=="Sueldo Integral")$pdf->Cell(25,5,'Deducciones',1,1);else$pdf->Cell(25,5,'',0,1);

                   // $pdf->Cell(5,7,$i,1,0,'C');//numero
                    //$pdf->Cell(82,7,$integrantes['apellidos'].', '.$integrantes['nombres'],1);
                    //$pdf->Cell(17,7,$integrantes['cedula'],1,0,'C');
                
                    $ced=$integrantes['cedula'];
                    $nomina=$integrantes['tipo_nomina'];
                   
                    //$descripcion
                    $pdf->Cell(105,3,strtolower($descripcion),1);
                    //$pdf->Cell(25,3,"2000",1,1,'R');

                   //CREDITO
                   //si es con sueldo integral

                  if($this->drop_monto->SelectedValue=="Sueldo Integral"){
                        if($funcionario[0]['editable']!="")
                        {
                        //$pdf->Cell(22,7,$funcionario[0]['editable'],1,0,'R');
                        $monto_a_pagar=((($integrantes['monto_incidencia']*2))/30)*$funcionario[0]['editable'];
                        }else{
                        //$pdf->Cell(22,3,$dias,1,0,'R');
                        $monto_a_pagar=((($integrantes['monto_incidencia']*2))/30)*$dias;
                        }//fin si

                   }else{
                       //si es modificado
                        if($funcionario[0]['editable']!=""){
                        $monto_a_pagar=$funcionario[0]['editable'];
                        }else{
                        $monto_a_pagar=$this->txt_bs->text;
                        // si se filtra por hijos se busca la cantidad para multiplicarle el monto
                            if($this->drop_hijo->SelectedValue!="0"){
                            $arre=split('/',$this->txt_fecha->Text);
                            $fecha_restada=($arre[2]-$this->drop_hijo->SelectedValue)."-$arre[1]-$arre[0]";

                            //verificamos si esta incluido para el reporte
                            $sql2="select COUNT(*) as n FROM nomina.bonificacion_personas_temporal t
                            INNER JOIN organizacion.personas_carga_familiar as pcf ON (pcf.persona_cedula=t.cedula AND pcf.parentesco='Hijo(a)' AND ( (TO_DAYS('".cambiaf_a_mysql($fecha_reporte)."') - TO_DAYS(pcf.fecha_nacimiento))>= (TO_DAYS('".cambiaf_a_mysql($fecha_reporte)."') - TO_DAYS('$fecha_restada') )))
                              WHERE t.numero= '".$this->lbl_codigo_temporal->Text."' AND t.cedula='".$integrantes['cedula']."'";
                            $hijos=cargar_data($sql2,$sender);
                            $monto_a_pagar=$monto_a_pagar*$hijos[0][n];
                            }//fin is

                        }//fin si
                   }//fin si
                     $pdf->Cell(25,3,number_format($monto_a_pagar, 2, ',', '.'),1,1,'R');
                    //CREDITO
if($this->drop_monto->SelectedValue=="Sueldo Integral"){
                   //DEBITO
                   //$descripcion
                    $pdf->Cell(105,3,"I.S.L.R.",1);
                    $pdf->Cell(25,3,"",1);
                    $formula_islr=NULL;
                    // Se obtiene si posee R.I.S.L.R segun codigo 9001 en tabla integrantes
                    $db = $this->Application->Modules["db2"]->DbConnection;$db->Active=true;
                    $sql2="select c.formula
                          from nomina.conceptos c inner join nomina.integrantes_conceptos ic on ic.cod_concepto=c.cod
                          where ic.cedula='$ced' and ic.tipo_nomina='$nomina' AND ic.cod_concepto='9001' ";
                    $formula_islr=cargar_data($sql2,$this);

                    $valor=0;
                   if($formula_islr[0]['formula']!=""){
                   $valor=evaluar_concepto_con_asignaciones(array('cedula'=>$ced),array('formula'=>$formula_islr[0]['formula'],'cod'=>'9001'),$monto_a_pagar,$db);
                   $valor=(int)round($valor*100)/100;
                        if($valor!=0)$pdf->Cell(25,3,number_format($valor, 2, ',', '.'),1,1,'R');//islr
                        else $pdf->Cell(25,3,"0,00",1,1,'R');//islr
                    }else{
                     $pdf->Cell(25,3,"0,00",1,1,'R');//islr
                    }//fin si
                    //DEBITO            
}
                   $pdf->Cell(105,5,'   Totales  ',1,0,'R');
                   //CREDITO
                   $pdf->Cell(25,5,number_format($monto_a_pagar, 2, ',', '.'),1,0,'R');
                   //DEBITO
                   $pdf->Cell(25,5,number_format($valor, 2, ',', '.'),1,1,'R');
                   $pdf->SetFont('courier','B',10);
                   $pdf->Cell(155,5,'   Total General  ',1,0,'R');
                   $pdf->Cell(25,5,number_format(($monto_a_pagar-$valor), 2, ',', '.'),1,0,'R');
                   $pdf->SetFont('courier','B',7);
                   $pdf->Cell(225,20,"                                                                                                                        ",0,1);
                   $pdf->Cell(225,6,"                                            _______________________________                                                                 ",0,1);
                   $pdf->Cell(225,3,"                                                    Recibí Conforme                                                                    ",0,1);
                   $pdf->Cell(225,3,"                                                    C.I.$integrantes[cedula]                                                                    ",0,1);
                  // $pdf->Cell(225,4,"                                                                                                                        ",0,1);


                 
                    

 $pdf->Cell(225,10, "-------------------------------------------------------------------------------------------------------------------------------",0,1);
                   //$pdf->Cell(225,4,"                                                                                                                        ",0,1);
                 
                }//fin si verificar tabla bonificacion

               
            }//foreach integrantes

            $pdf->Output('recibo_general.pdf','D');

}
}
?>
