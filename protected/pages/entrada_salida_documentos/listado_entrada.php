<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require('/var/www/tcpdf/tcpdf.php');
class listado_entrada extends TPage
{
     function onload($param)
     {
         parent::onLoad($param);
         if(!$this->IsPostBack)
         {
             //
             $sql1="select * from entrada_salida_documentos.entrada_documento";
             $resultado1=cargar_data($sql1, $this);
             $this->dg1->DataSource=$resultado1;
             $this->dg1->dataBind();
             //
             $sql2="select distinct numero from entrada_salida_documentos.entrada_documento";
             $resultado2=cargar_data($sql2, $this);
             $this->ddl1->DataSource=$resultado2;
             $this->ddl1->dataBind();
             //
             $sql3="select distinct a_quien from entrada_salida_documentos.entrada_documento";
             $resultado3=cargar_data($sql3, $this);
             $this->ddl2->DataSource=$resultado3;
             $this->ddl2->dataBind();
             //
             $sql4="select distinct quien_envia from entrada_salida_documentos.entrada_documento";
             $resultado4=cargar_data($sql4, $this);
             $this->ddl3->DataSource=$resultado4;
             $this->ddl3->dataBind();
             //
             $sql5="select distinct quien_recibe from entrada_salida_documentos.entrada_documento";
             $resultado5=cargar_data($sql5, $this);
             $this->ddl4->DataSource=$resultado5;
             $this->ddl4->dataBind();
         }
     }
     public function filtrar($sender, $param)
     {
         $fecha=cambiaf_a_mysql($this->dp_ini->text);
         $numero=$this->ddl1->SelectedValue;
         $a_quien=$this->ddl2->selectedValue;
         $quien_envia=$this->ddl3->SelectedValue;
         $quien_recibe=$this->ddl4->SelectedValue;
         if($fecha!='')
         {
             $sql="select * from entrada_salida_documentos.entrada_documento where(fecha_entrada='$fecha')";
             $resultado=cargar_data($sql, $this);
             $this->dg1->DataSource=$resultado;
             $this->dg1->dataBind();
         }
         if($numero!='')
         {
             $sql="select * from entrada_salida_documentos.entrada_documento where(numero='$numero')";
             $resultado=cargar_data($sql, $this);
             $this->dg1->DataSource=$resultado;
             $this->dg1->dataBind();
         }
         if($a_quien!='')
         {
             $sql="select * from entrada_salida_documentos.entrada_documento where(a_quien='$a_quien')";
             $resultado=cargar_data($sql, $this);
             $this->dg1->DataSource=$resultado;
             $this->dg1->dataBind();
         }
         if($quien_envia!='')
         {
             $sql="select * from entrada_salida_documentos.entrada_documento where(quien_envia='$quien_envia')";
             $resultado=cargar_data($sql, $this);
             $this->dg1->DataSource=$resultado;
             $this->dg1->dataBind();
         }
         if($quien_recibe!='')
         {
             $sql="select * from entrada_salida_documentos.entrada_documento where(quien_recibe='$quien_recibe')";
             $resultado=cargar_data($sql, $this);
             $this->dg1->DataSource=$resultado;
             $this->dg1->dataBind();
         }
         /*if($fecha!='' and $a_quien!='')
         {
             $sql="select * from entrada_salida_documentos.entrada_documento where(fecha_entrada='$fecha' and a_quien='$a_quien')";
             $resultado=cargar_data($sql, $this);
             $this->dg1->DataSource=$resultado;
             $this->dg1->dataBind();
         }
         if($fecha!='' and $quien_envia!='')
         {
             $sql="select * from entrada_salida_documentos.entrada_documento where(fecha_entrada='$fecha' and quien_envia='$quien_envia')";
             $resultado=cargar_data($sql, $this);
             $this->dg1->DataSource=$resultado;
             $this->dg1->dataBind();
         }*/
     }
     public function imprimir($sender, $param)
     {
        $ruta='/var/www/cene/imagenes/logos/';//ruta de la carpeta de imagenes
        $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);        
        $info_adicional= "Listado de Entrada de Documentos";
        $pdf->SetHeaderData("LogoCENE.gif", 15, "Sistema de Información Automatizado - ".usuario_actual('nombre_organizacion'), $info_adicional);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->AddPage();
        $pdf->Ln(10);
        $pdf->SetFont('helvetica','B',12);
        $pdf->Cell(31, 0, 'Fecha Entrada', 'RB', 0, 'L', $fill);
        $pdf->Cell(25, 0, 'Número', 'LB', 0, 'L', $fill);
        $pdf->Cell(50, 0, 'A Quien se  Envía', 'LB', 0, 'L', $fill);
        $pdf->Cell(50, 0, 'Quien Envia', 'LB', 0, 'L', $fill);
        $pdf->Cell(50, 0, 'Quien Recibe', 'LB', 0, 'L', $fill);                
        $pdf->Cell(53, 0, 'Descripcion', 'LB', 1, 'L', $fill);
        $pdf->SetFillColor(200, 220, 255);//color de relleno de las celdas
        $fill=0;        
        $pdf->SetFont('helvetica','',10);        
        $fecha=cambiaf_a_mysql($this->dp_ini->text);        
        $numero=$this->ddl1->text;
        $a_quien=$this->ddl2->text;
        $quien_envia=$this->ddl3->text;
        $quien_recibe=$this->ddl4->text;        
        //
        if($fecha!='')
        {
            $sql="select * from entrada_salida_documentos.entrada_documento where(fecha_entrada='$fecha')";
            $resultado=cargar_data($sql, $this);
            foreach($resultado as $fila)
            {
               $pdf->MultiCell(31, 0, $fila['fecha_entrada'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(25, 0, $fila['numero'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['a_quien'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_envia'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_recibe'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(53, 0, $fila['descripcion']."\n", 'LR', 'L', $fill, 1);
               if ($fill==0)
               {
                   $fill=1;
               }
               else
               {
                   $fill=0;
               }
            }
        }
        if($numero!='')
        {
            $sql="select * from entrada_salida_documentos.entrada_documento where(numero='$numero')";
            $resultado=cargar_data($sql, $this);
            foreach($resultado as $fila)
            {
               $pdf->MultiCell(31, 0, $fila['fecha_entrada'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(25, 0, $fila['numero'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['a_quien'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_envia'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_recibe'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(53, 0, $fila['descripcion']."\n", 'LR', 'L', $fill, 1);
               if ($fill==0)
               {
                   $fill=1;
               }
               else
               {
                   $fill=0;
               }
            }
        }
        if($a_quien!='')
        {
            $sql="select * from entrada_salida_documentos.entrada_documento where(a_quien='$a_quien')";
            $resultado=cargar_data($sql, $this);
            foreach($resultado as $fila)
            {
               $pdf->MultiCell(31, 0, $fila['fecha_entrada'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(25, 0, $fila['numero'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['a_quien'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_envia'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_recibe'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(53, 0, $fila['descripcion']."\n", 'LR', 'L', $fill, 1);
               if ($fill==0)
               {
                   $fill=1;
               }
               else
               {
                   $fill=0;
               }
            }
        }
        if($quien_envia!='')
        {
            $sql="select * from entrada_salida_documentos.entrada_documento where(quien_envia='$quien_envia')";
            $resultado=cargar_data($sql, $this);
            foreach($resultado as $fila)
            {
               $pdf->MultiCell(31, 0, $fila['fecha_entrada'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(25, 0, $fila['numero'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['a_quien'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_envia'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_recibe'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(53, 0, $fila['descripcion']."\n", 'LR', 'L', $fill, 1);
               if ($fill==0)
               {
                   $fill=1;
               }
               else
               {
                   $fill=0;
               }
            }
        }
        if($quien_recibe!='')
        {
            $sql="select * from entrada_salida_documentos.entrada_documento where(quien_recibe='$quien_recibe')";
            $resultado=cargar_data($sql, $this);
            foreach($resultado as $fila)
            {
               $pdf->MultiCell(31, 0, $fila['fecha_entrada'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(25, 0, $fila['numero'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['a_quien'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_envia'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_recibe'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(53, 0, $fila['descripcion']."\n", 'LR', 'L', $fill, 1);
               if ($fill==0)
               {
                   $fill=1;
               }
               else
               {
                   $fill=0;
               }
            }
        }
        if($fecha!='' and $a_quien!='')
        {
            $sql="select * from entrada_salida_documentos.entrada_documento where(fecha_entrada='$fecha' and a_quien='$a_quien')";
            $resultado=cargar_data($sql, $this);
            foreach($resultado as $fila)
            {
               $pdf->MultiCell(31, 0, $fila['fecha_entrada'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(25, 0, $fila['numero'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['a_quien'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_envia'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_recibe'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(53, 0, $fila['descripcion']."\n", 'LR', 'L', $fill, 1);
               if ($fill==0)
               {
                   $fill=1;
               }
               else
               {
                   $fill=0;
               }
            }
        }
        if($fecha!='' and $quien_envia!='')
        {
            $sql="select * from entrada_salida_documentos.entrada_documento where(fecha_entrada='$fecha' and quien_envia='$quien_envia')";
            $resultado=cargar_data($sql, $this);
            foreach($resultado as $fila)
            {
               $pdf->MultiCell(31, 0, $fila['fecha_entrada'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(25, 0, $fila['numero'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['a_quien'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_envia'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(50, 0, $fila['quien_recibe'], 'LR', 'L', $fill, 0);
               $pdf->MultiCell(53, 0, $fila['descripcion']."\n", 'LR', 'L', $fill, 1);
               if ($fill==0)
               {
                   $fill=1;
               }
               else
               {
                   $fill=0;
               }
            }
        }                
        $pdf->Output('entrada_documentos.pdf','D');
     }
}
?>
