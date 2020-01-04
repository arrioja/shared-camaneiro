<?php
function anos_servicio($f_ingreso,$f_actual)
{
//fecha final de la nomina actual
    $fecha_fin=explode("-",$f_actual);
    $dia=$fecha_fin[2];
    $mes=$fecha_fin[1];
    $ano=$fecha_fin[0];
    //fecha de nacimiento
    $fecha=explode("/", $f_ingreso);
    $dia_ingreso=$fecha[0];
    $mes_ingreso=$fecha[1];
    $ano_ingreso=$fecha[2];
    //si el mes es el mismo pero el dia inferior aun no ha cumplido años, le quitaremos un año al actual
    if (($mes_ingreso == $mes) && ($dia_ingreso > $dia)) {
        $ano=($ano-1); }
    //si el mes es superior al actual tampoco habra cumplido años, por eso le quitamos un año al actual
    if ($mes_ingreso > $mes) {
        $ano=($ano-1);}
    //ya no habria mas condiciones, ahora simplemente restamos los años y mostramos el resultado como su edad
    $anos_servicio=($ano-$ano_ingreso);
    if ($anos_servicio > 30) { // Se limita a no más de 30 años que es el máximo que establece la ley
        $anos_servicio = 30;
    }

    return($anos_servicio);
}


function insertar_incidencia_nomina($cod_nomina,$cedula,$cod_incidencia,$monto,$tipo,$descripcion,$tipo_nomina,$cod_org,$db)
	 {
	 //redondear a dos decimales los montos a incluir
	 //$monto=round($monto*100)/100;
     $monto=round($monto,2);//a 2 decimales
	 $excluidas=array("4444","5555");//incidencias excluidas para la creacion de la nomina ej: sueldo integral
	 if (in_array($cod_incidencia,$excluidas)==false)//si no esta en las excluidas
	 	{
	 	if ($db->createCommand("insert into nomina.nomina(cod,cedula,cod_incidencia, monto_incidencia,tipo,descripcion, tipo_nomina, cod_organizacion) values ('$cod_nomina','$cedula','$cod_incidencia','$monto','$tipo','$descripcion','$tipo_nomina','$cod_org')")->execute())
	      {
	      return true;
	      }
	   else
	      {
	   	return false;
	  	   }
	  }
	  return true;//si esta en excluidas igual devuelve true
	 }



function evaluar_concepto_con_asignaciones($funcionarios, $conceptos,$asignaciones,$db)
{
$ta=$asignaciones;//ta=total de asignaciones
$cod=$conceptos['cod'];
$cedula=$funcionarios['cedula'];
if (($conceptos['cod']=='0005'))	// 	ley de vivienda y habitat
	{
	$m = new EvalMath;
	$m->suppress_errors = true;
	if ($m->evaluate($conceptos['formula']))
		{
		preg_match('/^\s*([a-z]\w*)\s*\(\s*([a-z]\w*(?:\s*,\s*[a-z]\w*)*)\s*\)\s*=\s*(.+)$/', $conceptos['formula'], $matches);//divide la ecuacion
		$def="y(".$matches[2].")";
		$def= str_replace ("ta", $ta, $def);//sustituye donde salen las variables por el valor reemplazo
		$monto=$m->e($def);
		//$monto=round($monto*100)/100;//redondear
		return $monto;
		}
		else {
			 $error="No pudo evaluar la funcion: ". $m->last_error;
			 $mensaje="No pudo evaluar la funcion: ". $m->last_error.' '.$e->getMessage();
             $sender->Response->redirect($sender->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
			 }
	}
else
	{
	if ($conceptos['cod']=='9001')	//evaluar R.I.S.L.R.
		{
			//porcentaje de impuesto sobre la renta del funcionario
            $res_monto=$db->createCommand("select monto from integrantes_constantes where cedula='$cedula' and cod_constantes='9000'")->query();
		
		$const=$res_monto->readAll();
		$pct=$const[0]['monto'];
		$m = new EvalMath;
		$m->suppress_errors = true;
		if ($m->evaluate($conceptos['formula']))
			  {
			  preg_match('/^\s*([a-z]\w*)\s*\(\s*([a-z]\w*(?:\s*,\s*[a-z]\w*)*)\s*\)\s*=\s*(.+)$/', $conceptos['formula'], $matches);//divide la ecuacion
			  $def="y(".$matches[2].")";
			  $def= str_replace ("ta", $ta, $def);//sustituye donde salen las variables por el valor reemplazo
		      $def= str_replace ("pct", $pct, $def);//sustituye donde salen las variables por el valor reemplazo
			  $monto=$m->e($def);
				//$monto=round($monto*100)/100;//redondear
				return $monto;
			  }
		else {
			 $error="No pudo evaluar la funcion: ". $m->last_error;
			 $mensaje="No pudo evaluar la funcion: ". $m->last_error.' '.$e->getMessage();
             $sender->Response->redirect($sender->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
			 }
		}
	}
}

function evaluar_bono_antiguedad($funcionarios, $conceptos,$cod_org,$db)
	 {

      $res_variables= $db->createCommand("select * from nomina.variables where cod_organizacion='$cod_org'")->query();//variables del sistema
	  $res_nomina=$db->createCommand("select * from nomina.nomina_actual where status='1'")->query();//nomina activa
	  //$nomina_actual=mysql_fetch_array($res_nomina);
	 foreach ($res_variables as $key=>$variables)
	  {
	   if ($variables["cod"]=="1000")// si es el monto antiguedad previa
	   $map=$variables["valor"];
	   else
	   	if ($variables["cod"]=="1001")// si es el monto antiguedad actual
	   	$mas=$variables["valor"];
	  }
	  $cedula=$funcionarios['cedula'];
	  //$res_f_ingreso=$db->createCommand("select fecha_ingreso from organizacion.personas p where p.cedula='$cedula'")->query();//fecha de ingreso del funcionario y se encuentra en la bd organizacion
	//fecha ingreso a la institucion  
	$res_f_ingreso=$db->createCommand("SELECT fecha_ini as fecha_ingreso FROM organizacion.personas_cargos WHERE cedula='$cedula' order by fecha_ini asc LIMIT 1")->query();//fecha de ingreso del funcionario y se encuentra en la bd organizacion
	  $f_ingreso=$res_f_ingreso->readAll();
//hasta la fecha de la nomina actual 
      $nomina=$res_nomina->readAll();
	  $as=anos_servicio(cambiaf_a_normal($f_ingreso[0]['fecha_ingreso']),$nomina[0]['f_fin']);//años de servicio del funcionario
      $aap=$funcionarios['anos_servicio'];//años anteriores en la administracion publica
      //
      //
      //
      //if($cedula=="16931555")  $res_f_ingreso=$db->createCommand("select fecha_ingreso ".$f_ingreso[0]['fecha_ingreso']." fecha fin".$nomina[0]['f_fin']." años de servicio $as años anteriores $aap")->query();//fecha de ingreso del funcionario y se encuentra en la bd organizacion

///condicion segun el estatuto de personal contraloria de nueva esparta
//se paga el bono de antiguedad previo luego de 3 año en la institucion (contraloria ne)
     /*if ($as<3)
     	$aap=0;*/
	  $m = new EvalMath;
	  $m->suppress_errors = true;
	  if ($m->evaluate($conceptos['formula']))
	  {
	  preg_match('/^\s*([a-z]\w*)\s*\(\s*([a-z]\w*(?:\s*,\s*[a-z]\w*)*)\s*\)\s*=\s*(.+)$/', $conceptos['formula'], $matches);//divide la ecuacion
	 	$def="y(".$matches[2].")";
  	   $def= str_replace ("aap", $aap, $def);//sustituye donde salen las variables por el valor reemplazo
  	   /*$def= str_replace ("map", $map, $def);//sustituye donde salen las variables por el valor reemplazo
  	   $def= str_replace ("mas", $mas, $def);//sustituye donde salen las variables por el valor reemplazo*/
  	   $def= str_replace ("as", $as, $def);//sustituye donde salen las variables por el valor reemplazo
	   $monto=$m->e($def);
	   //$monto=round($monto*100)/100;//redondear
	   return $monto;

	  }
		else {
			 $error="No pudo evaluar la funcion: ". $m->last_error;
			 $mensaje="No pudo evaluar la funcion: ". $m->last_error.' '.$e->getMessage();
             $sender->Response->redirect($sender->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
			 }
	 }

function evaluar_concepto($funcionarios, $conceptos,$db)
{
$cedula=$funcionarios['cedula'];
//$anos_servicio=anos_servicio($funcionarios['fecha_ingreso']);//años de servicio del funcionario
$m = new EvalMath;
$m->suppress_errors = true;
//constantes del funcionario
$res_constantes=$db->createCommand("select c.cod,ic.monto,c.abreviatura from integrantes_constantes as ic inner join constantes as c on ic.cod_constantes=c.cod where ic.cedula='$cedula'")->query();
if ($m->evaluate($conceptos['formula']))
	{
	preg_match('/^\s*([a-z]\w*)\s*\(\s*([a-z]\w*(?:\s*,\s*[a-z]\w*)*)\s*\)\s*=\s*(.+)$/', 		$conceptos['formula'], $matches);//divide la ecuacion
	$args = explode(",", preg_replace("/\s+/", "", $matches[2]));//aqui tengo los argumentos (variables)
	natsort($args);//ordena el arreglo por tamaño de la variable
	$def=$matches[3];
	foreach ($args as $arg)//recorre el arreglo previamente ordenado no por el indice sino por el tamaño de la variable
	 	{
	 	foreach ($res_constantes as $key=>$constantes)
			{
				if ($constantes['abreviatura']==$arg)
					{
					$reemplazo=$constantes['monto'];//monto de la constante
					$patron="($arg)";
					$def= preg_replace ($patron, $reemplazo, $def, 1);//sustituye donde salen las variables por el valor reemplazo
					}
			 }
	   // Reset Our Pointer.
		//mysql_data_seek($result,0);
	 	}
	$monto=$m->e($def);
	//$monto=(round($monto*100))/100;//redondear
	return $monto;
	}
/*else {
     $error="No pudo evaluar la funcion: ". $m->last_error;
     $mensaje="No pudo evaluar la funcion: ". $m->last_error;
     $sender->Response->redirect($sender->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
     }*/
}

function verificar_nomina($db)//verificar si la nomina a crear no fue ya creada
{
$tipo_nomina=usuario_actual('tipo_nomina');
$cod_org=usuario_actual('cod_organizacion');//codigo de la organizacion del usuario logueado
$res_datos_nomina_actual= $db->createCommand("select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'")->query();//datos de la nomina y organizacion actual
$nomina=$res_datos_nomina_actual->readAll();
$cod=$nomina[0]["cod"];//cod_nomina

$res_datos_nomina= $db->createCommand("select * from nomina.nomina where cod='$cod' and tipo_nomina='$tipo_nomina'")->query();//datos de la nomina y organizacion actual
$res=$res_datos_nomina->readAll();

if (empty($res))
	{
        return true;
	}
else
	{
	return false;
	}
}



function crear_nomina($tipo_nomina,$cod_org,$db)
{
    $sql="select * from nomina.integrantes i
    inner join nomina.integrantes_tipo_nomina itn on itn.cedula=i.cedula
    where i.status>'0' and itn.tipo_nomina='$tipo_nomina' and i.cod_organizacion='$cod_org'";

    $resultado=$db->createCommand($sql)->query();//todos los funcionarios activos pertenecientes a la nomina y organizacion seleccionada
    $res_integrantes=$resultado->readAll();
        $sql="select * from nomina.nomina_actual where status='1' and cod_organizacion='$cod_org'";
    $res_datos_nomina_actual= $db->createCommand($sql)->query();//datos de la nomina y organizacion actual
    $nomina=$res_datos_nomina_actual->readAll();
    $contador=0;
    
 foreach($res_integrantes as $key=>$funcionarios   )

		{
		$contador++; //asignar constantes
		$ced=$funcionarios["cedula"];
		   //solo se van a asignar los conceptos como incidencias en la nomina
		$asignaciones=0;
		$deducciones=0;
			 //buscar todos los conceptos del funcionario Q SEAN TIPO CREDITO
		$sql_conceptos="select c.cod,c.formula,c.tipo,c.descripcion from integrantes_conceptos as ic inner join conceptos as c on ic.cod_concepto=c.cod where ic.cedula='$ced' and c.tipo='CREDITO' and ic.tipo_nomina='$tipo_nomina'";
		$res_conceptos=$db->createCommand($sql_conceptos)->query();
        //echo 'ci.'.$ced.'-';

        foreach ($res_conceptos as $key=>$conceptos)//insertar conceptos DE TIPO CREDITO a la nomina
			{             
			//evaluar concepto//si el concepto es especifico (ej:bono de antiguedad 0001)
			if ($conceptos["cod"]=="0001")
				$valor=evaluar_bono_antiguedad($funcionarios,$conceptos,$cod_org,$db);
			else//si el concepto es general lo evalua
				$valor=evaluar_concepto($funcionarios,$conceptos,$db);
					//insertar la incidencia
            $valor=round($valor,2);
			$asignaciones=$asignaciones+$valor;
            //$asignaciones=round($asignaciones,2);
			if (!insertar_incidencia_nomina($nomina[0]["cod"],$funcionarios["cedula"],$conceptos["cod"],$valor,$conceptos["tipo"],$conceptos["descripcion"],$tipo_nomina,$cod_org,$db))
				{
				return false;
				}

			}//echo $valor;
            ////while credito
		insertar_incidencia_nomina($nomina[0]["cod"],$funcionarios["cedula"],'7001',$asignaciones,'ASIGNACION','Total Asignaciones',$tipo_nomina,$cod_org,$db);//insertar el total asignacion
///	 TIPO DEBITO
        $sql_conceptos="select c.cod,c.formula,c.tipo,c.descripcion from integrantes_conceptos as ic inner join conceptos as c on ic.cod_concepto=c.cod where ic.cedula='$ced' and c.tipo='DEBITO' and ic.tipo_nomina='$tipo_nomina'";
		$res_conceptos_debito=$db->createCommand($sql_conceptos)->query();

		foreach ($res_conceptos_debito as $key=>$conceptos)//insertar conceptos DE TIPO DEBITO a la nomina
			{
// si el concepto es vivienda y habitat o RISLR se debe calcular con el total de las asignaciones
				if (($conceptos["cod"]=="0005")||($conceptos["cod"]=="9001"))
					$valor=evaluar_concepto_con_asignaciones($funcionarios,$conceptos,$asignaciones,$db);
				else
					$valor=evaluar_concepto($funcionarios,$conceptos,$db);
            $valor=round($valor,2);
			$deducciones=$deducciones+$valor;
            //$deducciones=round($deducciones,2);
					//insertar la incidencia
			if (!insertar_incidencia_nomina($nomina[0]["cod"],$funcionarios["cedula"],$conceptos["cod"],$valor,$conceptos["tipo"],$conceptos["descripcion"],$tipo_nomina,$cod_org,$db))
				{
                return false;
				}
			}//foreach tipo debito
		insertar_incidencia_nomina($nomina[0]["cod"],$funcionarios["cedula"],'7002',$deducciones,'DEDUCCION','Total Deducciones',$tipo_nomina,$cod_org,$db);//total deduccion
        if ($asignaciones-$deducciones>0)//si el total a pagar es mayor a 0
		insertar_incidencia_nomina($nomina[0]["cod"],$funcionarios["cedula"],'7003',$asignaciones-$deducciones,'NETO','Total Neto',$tipo_nomina,$cod_org,$db);//total general neto
        else// no crea nominas que algun total asignado sea menor a 0Bs
		return false;
		}//foreach funcionarios

		return true;

}
?>
