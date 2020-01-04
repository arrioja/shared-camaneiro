<?php

class reportes_historial extends TPage
{

    public function onLoad($param)
	{
       parent::onLoad($sender,$param);
       if (!$this->IsPostBack)
		{
        $this->DataGrid->DataSource=$this->cargar();
		$this->DataGrid->dataBind();
        }

   }

    public function changePage($sender,$param)
	{
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->DataGrid->DataSource=$this->cargar();
        $this->DataGrid->dataBind();
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

    public function cargar()
    {
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="SELECT distinct n.cod,n.tipo_nomina,na.titulo from nomina.nomina_historial n
            INNER JOIN nomina.nomina_actual na on n.cod=na.cod
            WHERE n.cod_organizacion='$cod_org'
            ORDER BY n.cod asc ";
        $resultado=cargar_data($sql,$this);
        return $resultado;
    }

    public function eliminar($sender,$param)
    {
    }
    public function reporte_general($sender,$param)
    {
   $parametros=$sender->CommandParameter;//recibe un array
   $datos=explode(",", $parametros);
   $cod=$datos[0];
   $tipo_nomina=$datos[1];
   $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

   $sql="select * from nomina.nomina_actual where cod='$cod' and cod_organizacion='$cod_org' order by cod asc";
            $datos_nomina=cargar_data($sql,$this);
            //$cod=$datos_nomina[0][cod];

            require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);

            $pdf->Cell(100,5,$datos_nomina[0]['titulo'],1,1);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Cod Nómina: ');
            $pdf->Cell(20,5,$datos_nomina[0]['cod']);
            $pdf->Cell(25,5,'Desde: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']));
            $pdf->Cell(25,5,'Hasta: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),0,1);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Nómina de: '.$tipo_nomina, 1, 1, 'C');


            if ($tipo_nomina=="PENSIONADOS"||$tipo_nomina=="JUBILADOS")
                $sql="select distinct p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula
                from nomina.nomina_historial n inner join nomina.integrantes i on i.cedula=n.cedula
                inner join organizacion.personas p on p.cedula=n.cedula
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina')
                order by cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina q son pensionados o jubilados
            else
                $sql="select distinct p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula,d.nombre_completo
                from nomina.nomina_historial n inner join nomina.integrantes i on i.cedula=n.cedula
                inner join organizacion.personas p on p.cedula=n.cedula
                INNER JOIN organizacion.personas_nivel_dir ond on ond.cedula=p.cedula
                INNER JOIN organizacion.direcciones d on ond.cod_direccion=d.codigo
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina')
                order by ond.cod_direccion asc,cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina

            $res_cedula=cargar_data($sql,$this);
             $dir="";//variable para verificar el cambio de direccion

            foreach ($res_cedula as $key=>$integrantes)
            {
            $pdf->SetFont('courier','B',7);
            $pdf->Cell(10,5,'COD',1);
            $pdf->Cell(60,5,'Nombres y Apellidos',1);
            $pdf->Cell(15,5,'Cédula',1);
            $pdf->Cell(50,5,'Descripción',1);
            $pdf->Cell(20,5,'Asignaciones',1);
            $pdf->Cell(20,5,'Deducciones',1);
            $pdf->Cell(21,5,'Totales',1,1);
            if (($tipo_nomina!="PENSIONADOS")&&($tipo_nomina!="JUBILADOS"))
            if ($dir!=$integrantes['nombre_completo'])//si cambia la direccion
                $pdf->Cell(85,5,$integrantes['nombre_completo'],1,1);
            $pdf->SetFont('courier','',7);
            $pdf->Cell(10,5,$integrantes['cod_nomina'],1);//cod nomina
            $pdf->Cell(60,5,$integrantes['nombres'].' '.$integrantes['apellidos'],1);
            $pdf->Cell(15,5,$integrantes['cedula'],1,1);


            $sql2="select n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo from nomina.nomina_historial n where n.cedula='".$integrantes['cedula']."' and n.cod='$cod' and n.tipo_nomina='$tipo_nomina' order by n.tipo asc";
            $result2=cargar_data($sql2,$this);
                foreach ($result2 as $key2=>$incidencias)
                {
                    if (($incidencias['tipo']=='CREDITO')||($incidencias['tipo']=='DEBITO'))
                    {//si es credito o debito
                        $pdf->Cell(85,5,'');
                        $pdf->Cell(50,5,$incidencias['descripcion'],1);

                       if ($incidencias['tipo']=='CREDITO')
                            $pdf->Cell(20,5,$incidencias['monto_incidencia'],1,1,'R');

                       if ($incidencias['tipo']=='DEBITO')
                            {
                             $pdf->Cell(20,5,"",1);
                             $pdf->Cell(20,5,$incidencias['monto_incidencia'],1,1,'R');
                            }
                    }//si es credito o debito

                    if ($incidencias['tipo']=='ASIGNACION')$asignacion=$incidencias['monto_incidencia'];
                    else
                        if ($incidencias['tipo']=='DEDUCCION')$deduccion=$incidencias['monto_incidencia'];
                }
                $pdf->Cell(135,5,'');
                $pdf->SetFont('courier','B',7);
                $pdf->Cell(20,5,$asignacion,1,0,'R');
                $pdf->Cell(20,5,$deduccion,1,0,'R');
                $pdf->Cell(21,5,$asignacion-$deduccion,1,1,'R');
 $pdf->Cell(225,5,"-----------------------------------------------------------------------------------------------------------------------------------",0,1);
            }
            $pdf->Output('reporte_general_historial.pdf','D');
    }

    public function reporte_revision_nomina($sender,$param)
    {
        $parametros=$sender->CommandParameter;//recibe un array
        $datos=explode(",", $parametros);
        $cod=$datos[0];
        $tipo_nomina=$datos[1];
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select * from nomina.nomina_actual where cod='$cod' and cod_organizacion='$cod_org' order by cod asc";
        $datos_nomina=cargar_data($sql,$this);
        $cod=$datos_nomina[0][cod];

        require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('P', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);

            $pdf->Cell(80,5,$datos_nomina[0]['titulo'],1,1);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Cod Nómina: ');
            $pdf->Cell(20,5,$datos_nomina[0]['cod']);
            $pdf->Cell(25,5,'Desde: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']));
            $pdf->Cell(25,5,'Hasta: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),0,1);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(0, 17, 'Reporte de Revisión: Nómina de '.$tipo_nomina, 1, 1, 'C');



            $sql="select p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula, n.descripcion,n.monto_incidencia,n.cod_incidencia, n.tipo,n.tipo_nomina
            from nomina.nomina_historial n inner join nomina.integrantes i on i.cedula=n.cedula inner join organizacion.personas p on p.cedula=n.cedula
            where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina') and(n.cod_incidencia='7001' or n.cod_incidencia='7002' or n.cod_incidencia='7003' ) order by cod_nomina asc, n.cod_incidencia asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina
                     $res_cedula=cargar_data($sql,$this);
            $pdf->SetFont('courier','B',9);
            $pdf->Cell(10,10,'COD',1,0,'C');
            $pdf->Cell(82,10,'Nombres y Apellidos',1,0,'C');
            $pdf->Cell(20,10,'Cédula',1,0,'C');
            $pdf->Cell(28,10,'Asignaciones',1,0,'C');
            $pdf->Cell(28,10,'Deducciones',1,0,'C');
            $pdf->Cell(28,10,'Total Neto',1,1,'C');
            $ced='';
            $pdf->SetFont('courier','',8);
            $asignaciones=0;
            $deducciones=0;
            foreach ($res_cedula as $key=>$integrantes)
            {
                if (($ced=='')||($ced!=$integrantes[cedula]))
                {
                $pdf->Cell(10,7,$integrantes['cod_nomina'],1,0,'C');//cod nomina
                $pdf->Cell(82,7,$integrantes['nombres'].' '.$integrantes['apellidos'],1);
                $pdf->Cell(20,7,$integrantes['cedula'],1,0,'C');
                $ced=$integrantes['cedula'];
                }

                if ($integrantes[cod_incidencia]=='7003')//neto
                    {$pdf->Cell(28,7,$integrantes['monto_incidencia'],1,1,'R');}
                else
                    {
                    $pdf->Cell(28,7,$integrantes['monto_incidencia'],1,0,'R');
                        if ($integrantes['tipo']=="ASIGNACION")
                        {
                        $asignaciones=$asignaciones+$integrantes['monto_incidencia'];
                        $asignaciones=round($asignaciones,2);
                        }
                        else
                            if ($integrantes['tipo']=="DEDUCCION")
                            {
                            $deducciones=$deducciones+$integrantes['monto_incidencia'];
                            $deducciones=round($deducciones,2);
                            }
                    }
            }//foreach integrantes
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(112,7,"TOTALES",1,0,'R');
            $pdf->Cell(28,7,$asignaciones,1,0,'R');
            $pdf->Cell(28,7,$deducciones,1,0,'R');
            $pdf->Cell(28,7,$asignaciones-$deducciones,1,0,'R');
            $pdf->Output('reporte_revision_nomina.pdf','D');

    }

       public function reporte_resumen_conceptos($sender,$param)
    {
        $parametros=$sender->CommandParameter;//recibe un array
        $datos=explode(",", $parametros);
        $cod=$datos[0];
        $tipo_nomina=$datos[1];
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
        $sql="select * from nomina.nomina_actual where cod='$cod' and cod_organizacion='$cod_org'";
        $datos_nomina=cargar_data($sql,$this);
        $cod=$datos_nomina[0][cod];

        require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('l', 'mm', 'letter', true, 'utf-8', false);
            $pdf->setPrintHeader(false);//oculta la cabecera
            $pdf->setPrintFooter(false);//oculta el pie de pagina
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris
            //pagina 1 *********************************************************
            $pdf->AddPage();
            //$pdf->Image('/var/www/cene/protected/pages/nomina/reportes/LogoCENE.gif', 11, 11, 16, 15);
            $pdf->SetFont('courier','B',16);

            $pdf->Cell(70,5,$datos_nomina[0]['titulo'],1,1);
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(40,5,'Cod Nómina: ');
            $pdf->Cell(20,5,$datos_nomina[0]['cod']);
            $pdf->Cell(25,5,'Desde: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_ini']));
            $pdf->Cell(25,5,'Hasta: ');
            $pdf->Cell(35,5,cambiaf_a_normal($datos_nomina[0]['f_fin']),0,1);
            $pdf->SetFont('courier','B',16);
            $pdf->Cell(230, 17, 'Reporte Resumen de Conceptos', 1, 1, 'C');


            $sql="select distinct cod_incidencia, descripcion, tipo, sum(monto_incidencia) suma,count(cod_incidencia) num
            from nomina.nomina_historial where tipo_nomina='$tipo_nomina' and (tipo='DEBITO' OR tipo='CREDITO') and cod='$cod'
            group by cod_incidencia order by tipo";

            $res_conceptos=cargar_data($sql,$this);
            $pdf->SetFont('courier','B',12);
            $pdf->Cell(15,10,'COD',1,0,'C');
            $pdf->Cell(110,10,'Descripción del Concepto',1,0,'C');
            $pdf->Cell(35,10,'Asignaciones',1,0,'C');
            $pdf->Cell(35,10,'Deducciones',1,0,'C');
            $pdf->Cell(35,10,'N° Personas',1,1,'C');
            $pdf->SetFont('courier','',10);
            $asignaciones=0;
            $deducciones=0;
            $num=0;
            foreach ($res_conceptos as $key=>$conceptos)
            {
                $pdf->Cell(15,7,$conceptos['cod_incidencia'],1,0,'C');//cod nomina
                $pdf->Cell(110,7,$conceptos['descripcion'],1);
                if ($conceptos['tipo']=='CREDITO')
                    {$pdf->Cell(35,7,$conceptos['suma'],1,0,'R');$pdf->Cell(35,7,'',1,0,'R');
                    $tot_asignaciones=$tot_asignaciones+$conceptos['suma'];

                    }
                else
                    {$pdf->Cell(35,7,'',1,0,'R');$pdf->Cell(35,7,$conceptos['suma'],1,0,'R');
                        $tot_deducciones=$tot_deducciones+$conceptos['suma'];
                    }
                $pdf->Cell(35,7,$conceptos['num'],1,1,'C');
            }//foreach integrantes
            $pdf->SetFont('courier','B',10);
            $pdf->Cell(15,10,'',0,0,'C');
            $pdf->Cell(110,10,'TOTALES',0,0,'C');
            $pdf->Cell(35,10,$tot_asignaciones,0,0,'C');
            $pdf->Cell(35,10,$tot_deducciones,0,0,'C');

            $pdf->Output('reporte_resumen_conceptos.pdf','D');

    }
   public function reporte_recibo_general($sender,$param)
    {

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
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina')
                order by cod_nomina asc";//lista de nombres apellidos cedula y cod nomina de los integrantes de la nomina q son pensionados o jubilados
            else
                $sql="select distinct p.apellidos, p.nombres,i.cod as cod_nomina, n.cedula,d.nombre_completo
                from nomina.nomina_historial n inner join nomina.integrantes i on i.cedula=n.cedula
                inner join organizacion.personas p on p.cedula=n.cedula
                INNER JOIN organizacion.personas_nivel_dir ond on ond.cedula=p.cedula
                INNER JOIN organizacion.direcciones d on ond.cod_direccion=d.codigo
                where (n.cod='$cod' and n.tipo_nomina='$tipo_nomina')
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
                $pdf->Cell(225,20,"                                                                                                                        ",0,1);
   $pdf->Cell(225,6,"                                            _______________________________                                                                 ",0,1);
   $pdf->Cell(225,3,"                                                    Recibí Conforme                                                                    ",0,1);
   $pdf->Cell(225,3,"                                                    C.I.$integrantes[cedula]                                                                    ",0,1);
    $pdf->Cell(225,4,"                                                                                                                        ",0,1);
    if ($par%2==0)
        {$pdf->Cell(225,15,"-------------------------------------------------------------------------------------------------------------------------------",0,1);
         $pdf->Cell(225,4,"                                                                                                                        ",0,1);
        }
   $par=$par+1;
            }//foreach integrantes

            $pdf->Output('recibo_general_historial.pdf','D');

            }





    function regresar($serder,$param)
    {
     $this->Response->redirect($this->Service->constructUrl('nomina.reportes.reportes'));
    }


}
?>
