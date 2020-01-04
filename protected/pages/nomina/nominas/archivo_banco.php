<?php
class archivo_banco extends TPage
{
    var $texto; // info de las justificaciones
    public function onLoad($param)
    {
if (!$this->IsPostBack)
        {
        $cod_nomina=$this->Request['cod_nomina'];
        $tipo_nomina=$this->Request['tipo_nomina'];
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $this->txt_tipo_nomina->Text=$tipo_nomina;
        $this->txt_codigo->Text=$cod_nomina;
       /* $this->txt_tipo_nomina->Text=;
        $this->txt_tipo_nomina->Text=;*/
        $sql="select * from nomina.nomina_actual where cod_organizacion='".usuario_actual('cod_organizacion')."' and cod='$cod_nomina'";
        $datos_nomina=cargar_data($sql,$this);

        
        $this->txt_titulo->Text=$datos_nomina[0]['titulo'];
        $this->txt_fecha_i->Text=cambiaf_a_normal($datos_nomina[0]['f_ini']);
        $this->txt_fecha_f->Text=cambiaf_a_normal($datos_nomina[0]['f_fin']);

        $sql="select n.cedula, p.nombres, p.apellidos, n.monto_incidencia,ib.numero_cuenta from nomina.nomina n inner join  nomina.integrantes_banco ib on (ib.cedula=n.cedula)
              inner join  organizacion.personas p on (p.cedula=n.cedula)
        where n.cod='$cod_nomina' and n.cod_incidencia='7003' and n.tipo_nomina='$tipo_nomina' and n.cod_organizacion='$cod_org'";
        $result=cargar_data($sql,$this);//montos de totales nomina actual y tipo nomina
            $cont=1;
            $num=count($result);//numero de registros
            $fecha=date("d/m/Y");$fecha=str_replace("/","",$fecha);
            $sql="select sum(monto_incidencia) as total from
            nomina.nomina n inner join  nomina.integrantes_banco ib on (ib.cedula=n.cedula)
            where n.cod='$cod_nomina' and n.cod_incidencia='7003' and n.tipo_nomina='$tipo_nomina' and cod_organizacion='$cod_org'";
            $result_total=cargar_data($sql,$this);
            $total=$result_total[0]['total'];//suma total de todos los integrantes seleccionados
            $total=str_replace(".","",$total);
            while (strlen($total)<20)//completar los 20 ceros
                    {
                    $total='0'.$total;
                    }
            //$data="\n$fecha/$num/$total";
            //$data="$fecha/$num/$total";
            //trim($data);
	
		
	$monto_total=0;
$cont=0;
            foreach($result as $key=>$montos)
                {
                $ced=$montos['cedula'];
                $monto=$montos['monto_incidencia'];
                $cuenta=$montos['numero_cuenta'];

                $data=$data.generar_fila_archivo2("2777",$monto,$cuenta,$ced,0);
                $cont++;
		$monto_total=$monto_total+$monto;
                }
	//encabezado archivo:numero de cuenta(20), fecha(8), monto general (17), total registros en archivo

	$monto_total=str_replace(".","",$monto_total);
	$monto_total=completar_ceros_izq($monto_total,17);
	$cont=completar_ceros_izq($cont,4);

	$data=$data."\n01750421540231025806".date("Ymd").$monto_total.$cont;	
                //$this->generar($sender,$param);
            $this->txt_archivo->Text=$data;

        }
    }
    function regresar()
      {
          $this->Response->redirect($this->Service->constructUrl('nomina.nominas.admin_nominas'));
      }

          function generar($sender,$param)
      {

//datos nomina
        $cod_nomina=$this->Request['cod_nomina'];
        $tipo_nomina=$this->Request['tipo_nomina'];
        $cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado

        $this->txt_tipo_nomina->Text=$tipo_nomina;
        $this->txt_codigo->Text=$cod_nomina;
       /* $this->txt_tipo_nomina->Text=;
        $this->txt_tipo_nomina->Text=;*/
        $sql="select * from nomina.nomina_actual where cod_organizacion='".usuario_actual('cod_organizacion')."' and cod='$cod_nomina'";
        $datos_nomina=cargar_data($sql,$this);


        $this->txt_titulo->Text=$datos_nomina[0]['titulo'];
        $this->txt_fecha_i->Text=cambiaf_a_normal($datos_nomina[0]['f_ini']);
        $this->txt_fecha_f->Text=cambiaf_a_normal($datos_nomina[0]['f_fin']);
//selecciono y cargo datos de la nomina
        $sql="select n.cedula, p.nombres, p.apellidos, n.monto_incidencia,ib.numero_cuenta from nomina.nomina n inner join  nomina.integrantes_banco ib on (ib.cedula=n.cedula)
              inner join  organizacion.personas p on (p.cedula=n.cedula)
        where n.cod='$cod_nomina' and n.cod_incidencia='7003' and n.tipo_nomina='$tipo_nomina' and n.cod_organizacion='$cod_org'";
        $result=cargar_data($sql,$this);//montos de totales nomina actual y tipo nomina
            
	$cont=1;
            $num=count($result);//numero de registros
            $fecha=date("d/m/Y");$fecha=str_replace("/","",$fecha);
            $sql="select sum(monto_incidencia) as total from
            nomina.nomina n inner join  nomina.integrantes_banco ib on (ib.cedula=n.cedula)
            where n.cod='$cod_nomina' and n.cod_incidencia='7003' and n.tipo_nomina='$tipo_nomina' and cod_organizacion='$cod_org'";
            $result_total=cargar_data($sql,$this);
            $total=$result_total[0]['total'];//suma total de todos los integrantes seleccionados
            $total=str_replace(".","",$total);
            while (strlen($total)<20)//completar los 20 ceros
                    {
                    $total='0'.$total;
                    }
            //$data="\n$fecha/$num/$total";
            //$data="$fecha/$num/$total";
            $data="";
            // creando y abriendo archivo

	$monto_total=0;
$cont=0;
            $archivo=fopen("/var/www/cmm/protected/pages/nomina/nominas/nomina.txt","w+") or die("no puedo abrir archivo");
            foreach($result as $key=>$montos)
                {
                $ced=$montos['cedula'];
                $monto=$montos['monto_incidencia'];
                $cuenta=$montos['numero_cuenta'];



                $data=$data.generar_fila_archivo2("2777",$monto,$cuenta,$ced,0);
                //$texto.="$cuenta/$monto/$nombre\n";
                // grabando los campos
                //fputs($archivo, "$cuenta/$monto/$nombre"."\n");
                $cont++;
		$monto_total=$monto_total+$monto;
                }
	//encabezado archivo:numero de cuenta(20), fecha(8), monto general (17), total registros en archivo

	$monto_total=str_replace(".","",$monto_total);
	$monto_total=completar_ceros_izq($monto_total,17);
	$cont=completar_ceros_izq($cont,4);

	$data=$data."\n01750421540231025806".date("Ymd").$monto_total.$cont;	

fputs($archivo, $data);
//cerrando archivo
fclose($archivo);

       // $this->txt_archivo->Text=$data;

        //  $fp=fopen("/var/www/cene/protected/pages/nomina/nominas/nomina.txt","w");
        //  fwrite($fp,($texto).PHP_EOL);
         // fclose($fp);

      }
}
?>
