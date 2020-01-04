<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo genera codigos de barra para bienes;
     ****************************************************  FIN DE INFO
*/

class generar_codigos extends TPage
{

function check_grupo($sender,$param)
   { $cod_org=usuario_actual('cod_organizacion');
     $param->IsValid=verificar_existencia("bienes.grupo","grupo",$this->txt_grupo->Text,array('cod_organizacion' => $cod_org),$sender);
   }

    public function onLoad($param)
    {
        parent::onLoad($param);



      /*  $sql="select * from organizacion.direcciones where codigo_organizacion='".usuario_actual('cod_organizacion')."'";
        $direcciones=cargar_data($sql,$this);
        $this->cmb_direcciones->DataSource=$direcciones;
        $this->cmb_direcciones->dataBind();
    */}

    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function incluir($sender, $param)
	{
        if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
        {
       // Se capturan los datos provenientes de los Controles
        $grupo = $this->txt_grupo->Text;
        $descripcion=$this->txt_descripcion->Text;
        $cod_org=usuario_actual('cod_organizacion');
  
               /* Se guardan los datos. */
		$sql="insert into bienes.grupo (grupo,descripcion,cod_organizacion)values
              ('$grupo','$descripcion','$cod_org')";

        $resultado=modificar_data($sql,$sender);
            $this->Response->redirect($this->Service->constructUrl('bienes.admin_grupo'));
       
        }
 	}


   public function generar($sender, $param)
	{
        $numeros=array();
        for ($i=1;$i<=10;$i+=1)
            $numeros[$i]["indice"]=codigo_aleatorio("10","bienes.bienes_muebles","codigo",$this);

	$cod_org=usuario_actual('cod_organizacion');
	$this->DataGrid->DataSource=$numeros;
      	$this->DataGrid->DataBind();
	require('/var/www/tcpdf/tcpdf.php');

	$pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

// set document information

            $pdf->SetTitle('Nuevos Códigos de Barra de Bienes Muebles');
            $pdf->SetSubject('Codigos de Barra');

           // $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            $style = array(
                'position' => 'R',
                'border' => true,
                'padding' => 2,
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, 
                'text' => true,
                'font' => 'helvetica',
                'fontsize' => 8,
                'stretchtext' => 1

            );     
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);

            $pdf->SetFont('courier','B',14);

            $pdf->Cell(0, 5, 'Camara de Maneiro', 0, 1, 'C', 0, '', 0);
            $pdf->Cell(0, 5, 'Dirección de Administración', 0, 1, 'C', 0, '', 0);
            $pdf->Cell(0, 5, 'Inventario Interno de Bienes Muebles', 0, 1, 'C', 0, '', 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 5, 'Impresión de Código de barras para Bienes registrados', 0, 1, 'C', 0, '', 0);
            $ini=40;
            $inc=0;
           for ($i=1;$i<=10;$i+=1)
                {
                $sql="insert into bienes.bienes_muebles (codigo,cod_organizacion)values('".$numeros[$i]["indice"]."','".$cod_org."')";
                $res=modificar_data($sql,$this);//inserto los los 10 codigos
                $pdf->write1DBarcode($numeros[$i]["indice"], 'C128B', 50, $i*$ini-$inc, 40, 15, 0.3, $style, 'N');
                $pdf->write1DBarcode($numeros[$i]["indice"], 'C128B', 140, $i*$ini-$inc, 40, 15, 0.3, $style, 'N');
                $pdf->Ln();
                $inc=$inc+20;
                }
            $pdf->Output('codigos.pdf','D');
           }

   
  /* public function generar($sender, $param)
	{
        $numeros=array();
        for ($i=1;$i<=10;$i+=1)
            $numeros[$i]["indice"]=codigo_aleatorio("10","bienes.bienes_muebles","codigo",$this);


        $sesion=new THttpSession;
        $sesion->open();
        $sesion['nuevos_codigos_bienes']=$numeros;

        $sesion->close();


      $this->DataGrid->DataSource=$numeros;
      $this->DataGrid->DataBind();
 	}

    public function imprimir($sender, $param)
        {
            $sesion=new THttpSession;
            $sesion->open();
            $numeros=$sesion['nuevos_codigos_bienes'];
            $sesion->close();
            require('/var/www/tcpdf/tcpdf.php');

            $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

// set document information

            $pdf->SetTitle('Nuevos Códigos de Barra de Bienes Muebles');
            $pdf->SetSubject('Codigos de Barra');

           // $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            $style = array(
                'position' => 'R',
                'border' => true,
                'padding' => 2,
                'fgcolor' => array(0,0,0),
                'bgcolor' => false, 
                'text' => true,
                'font' => 'helvetica',
                'fontsize' => 8,
                'stretchtext' => 1

            );     
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);

            $pdf->SetFont('courier','B',14);

            $pdf->Cell(0, 5, 'Camara de Man', 0, 1, 'C', 0, '', 0);
            $pdf->Cell(0, 5, 'Dirección de Administración', 0, 1, 'C', 0, '', 0);
            $pdf->Cell(0, 5, 'Inventario Interno de Bienes Muebles', 0, 1, 'C', 0, '', 0);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(0, 5, 'Impresión de Código de barras para Bienes registrados', 0, 1, 'C', 0, '', 0);
            $ini=40;
            $inc=0;
           for ($i=1;$i<=10;$i+=1)
                {
                $sql="insert into bienes.bienes_muebles (codigo,cod_organizacion)values('".$numeros[$i]["indice"]."','".$cod_org."')";
                $res=modificar_data($sql,$this);//inserto los los 10 codigos
                $pdf->write1DBarcode($numeros[$i]["indice"], 'C128B', 50, $i*$ini-$inc, 40, 15, 0.3, $style, 'N');
                $pdf->write1DBarcode($numeros[$i]["indice"], 'C128B', 140, $i*$ini-$inc, 40, 15, 0.3, $style, 'N');
                $pdf->Ln();
                $inc=$inc+20;
                }
            $pdf->Output('codigos.pdf','D');
           //}
        }*/
}

?>
