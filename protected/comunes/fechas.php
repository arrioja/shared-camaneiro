<?php
/*
****************************************************  INFO DEL ARCHIVO
  		   Creado por: 	Pedro E. Arrioja M.
 *     Modificado por:  Ronald A. Salazar C.
  Descripción General:  Funciones principalmente relacionadas con el manejo de
 *                      fechas y horas, tengan o no que ver con base de datos,
 *                      son funciones de uso comun, pero generalmente usadas por
 *                      los modulos del sistema de asistencias.
****************************************************  FIN DE INFO
 */

/* Esta función cambia una fecha en formato normal dd/mm/aaaa a un formato que
 * sea aceptado por MySql aaaa-mm-dd.
 * Carlos Avila
 */
function cambiaf_a_mysql($fecha)
{
    $mifecha=explode("/",$fecha);
    $lafecha=$mifecha[2]."-".$mifecha[1]."-".$mifecha[0];
    return $lafecha;
}

/* Esta función cambia una fecha en formato aceptado por MySql aaaa-mm-dd a un
 * formato normal dd/mm/aaaa.
 * Carlos Avila
 */
function cambiaf_a_normal($fecha)
{
    $mifecha=explode("-",$fecha);
    $lafecha=$mifecha[2]."/".$mifecha[1]."/".$mifecha[0];
    return $lafecha;
}

/* Ésta función comprueba que la fecha corresponda con alguno de los días de la 
 * semana marcados para comparar, es útil en la comprobación de los permisos
 * recurrentes de los reportes de asistencias; p.e. 05/05/2008,1,0,1,0,1 da como 
 * resultado true porque se consulta la fecha (Lunes) y si cae lunes, miercoles
 * o viernes, la función se evalua cierta si la fecha cae en alguno de los dias
 * de la semana marcados con 1*/
function es_dia($fecha,$lu,$ma,$mi,$ju,$vi)
{
 $resultado=false;
 list($dia, $mes, $anio) = split("/", $fecha);
 $mes=intval($mes);
 $dia=intval($dia);
 $anio=intval($anio);
 if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Mon') && ($lu==1))
   {$resultado=true;}
 else
   {
     if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Tue') && ($ma==1))
       {$resultado=true;}
     else
       {
         if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Wed') && ($mi==1))
           {$resultado=true;}
         else
           {
             if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Thu') && ($ju==1))
               {$resultado=true;}
             else
               {
                 if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Fri') && ($vi==1))
                   {$resultado=true;}
               } // del else del jueves
           }// del else del miércoles
       } // del else del martes
   }// del else del lunes
return $resultado;
}

/* Esta función se encarga de devolver el horario de trabajo que se encuentre
 * vigente para la fecha pasada como parámetro.  Se da prioridad al status 2 que
 * significa horario especial, luego al 1 que significa horario normal y luego
 * al 0 que significa horario anterior o no activo.
 * la fecha parámetro debe ser en formato AAAA-MM-DD
 */
    function obtener_horario_vigente($cod_organizacion, $fecha, $sender)
	{
        $sql="select * from asistencias.opciones
              where ((cod_organizacion = '$cod_organizacion') and (vigencia_desde <= '$fecha') and
                     (vigencia_hasta >= '$fecha'))
              order by vigencia_desde desc Limit 1 ";
        //  order by status desc Limit 1"; asi estaba antes
        $resultado=cargar_data($sql,$sender);
		return $resultado;
	}

/* Esta función se encarga de verificar si la falta a la asistencia tiene un
 * justificativo registrado. Si la hora no esta justificado, se devuelve false, pero
 * si esta justificado, se devuelve la información de la justificación.
 * Parámetros:  $justif es un arreglo con las justificaciones.
 *              $cedula = cedula del funcionari@
 *              $fecha = dd/mm/aaaa
 *              $hora = hora a justificar en formato 24H
 */
function esta_justificado($justif,$cedula,$fecha,$hora,$sender)
{
    $cuenta_just=0;
    $just_encontrada=false;
    $fecha = cambiaf_a_mysql($fecha);
    while (($cuenta_just < count($justif)) && ($just_encontrada == false))
    {
       // se consulta sino es una justificacion que abarca el horario completo de la jornada laboral
       // se justifica como verdadero
      if ($cedula!=""){$sql="SELECT jd.hora_completa FROM asistencias.justificaciones_dias as jd, asistencias.justificaciones_personas jp
            WHERE (jp.codigo_just=jd.codigo_just AND jp.cedula='".$cedula."' AND jd.codigo_just = '".$justif[$cuenta_just][codigo]."'
            AND (jd.fecha_desde>='$fecha' AND jd.fecha_desde<='$fecha') ) ";
       $resultado=cargar_data($sql,$sender);

        if ($resultado[0]['hora_completa']=="1"){$just_encontrada = $justif[$cuenta_just];break;}
       }


      if (($cedula == $justif[$cuenta_just]['cedula_just']) &&
          ($hora >= $justif[$cuenta_just]['hora_desde']) &&
          ($hora <= $justif[$cuenta_just]['hora_hasta']) &&
          ($fecha >= $justif[$cuenta_just]['fecha_desde']) &&
          ($fecha <= $justif[$cuenta_just]['fecha_hasta']) &&
          (es_dia(cambiaf_a_normal($fecha),$justif[$cuenta_just]['lun'],$justif[$cuenta_just]['mar'],
           $justif[$cuenta_just]['mie'],$justif[$cuenta_just]['jue'],
           $justif[$cuenta_just]['vie'])==true))
            {
              $just_encontrada = $justif[$cuenta_just];
            }
     $cuenta_just++;
    }
    return $just_encontrada;
}

/* Esta Función devuelve el número de días de diferencia que existe entre dos fechas dadas*/
	function num_dias_entre_fechas($fecha1,$fecha2)
	{	// Separo los datos de fechamayor y fechamenor
	    list($dia1,$mes1,$ano1) = explode("/",$fecha1);
	    list($dia2,$mes2,$ano2) = explode("/",$fecha2);
		//calculo timestam de las dos fechas
		$mes1=intval($mes1);
		$dia1=intval($dia1);
		$ano1=intval($ano1);
		$mes2=intval($mes2);
		$dia2=intval($dia2);
		$ano2=intval($ano2);
		$timestamp1 = mktime(0,0,0,$mes1,$dia1,$ano1);
		$timestamp2 = mktime(0,0,0,$mes2,$dia2,$ano2);

		//resto a una fecha la otra
		$segundos_diferencia = $timestamp1 - $timestamp2;
		//convierto segundos en d�as
		$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
		//obtengo el valor absoulto de los d�as (quito el posible signo negativo si me mandan las fechas menor y mayor invertidas)
		$dias_diferencia = abs($dias_diferencia);
		//quito los decimales a los d�as de diferencia
		$dias_diferencia = intval($dias_diferencia);
		return $dias_diferencia;
	}

/* Esta función le suma $ndias a una $fecha dada y devuelve la nueva fecha resultante*/
	function suma_dias($fecha, $ndias)
	{
		list($dia, $mes, $ano) = split("/", $fecha);
		$mes=intval($mes);
		$dia=intval($dia);
		$ano=intval($ano);
		$nueva = mktime(0, 0, 0, $mes, $dia, $ano) + $ndias * 24 * 60 * 60;
		$nuevafecha = date("d/m/Y", $nueva);
		return $nuevafecha;
	}

    /* Esta función le resta $ndias a una $fecha dada y devuelve la nueva fecha resultante*/
	function resta_dias($fecha, $ndias)
	{
		list($dia, $mes, $ano) = split("/", $fecha);
		$mes=intval($mes);
		$dia=intval($dia);
		$ano=intval($ano);
		$nueva = mktime(0, 0, 0, $mes, $dia, $ano) - $ndias * 24 * 60 * 60;
		$nuevafecha = date("d/m/Y", $nueva);
		return $nuevafecha;
	}



/* Esta función devuelve una nueva fecha como resultado de haberle sumado n
 * días hábiles a la fecha pasada como parámetro. (dd/mm/aaaa)
 */
 	function suma_dias_habiles($fecha, $numdias, $dias_feriados, $sender)
	{   $primera_corrida=true;
		while ($numdias!=0)
		{
		  if ($primera_corrida == false)
              {
                  $fecha = suma_dias($fecha, 1);
              }
		  $primera_corrida = false;
          if (es_feriado($fecha, $dias_feriados, $sender) == 0)
              { 
                  $numdias--; 
              }
	    }
	return $fecha;
	}

    /* Esta función devuelve una nueva fecha como resultado de haberle restado n
 * días hábiles a la fecha pasada como parámetro. (dd/mm/aaaa)
 */
 	function resta_dias_habiles($fecha, $numdias, $dias_feriados, $sender)
	{   $primera_corrida=true;
		while ($numdias!=0)
		{
		  if ($primera_corrida == false)
              {
                  $fecha = resta_dias($fecha, 1);
              }
		  $primera_corrida = false;
          if (es_feriado($fecha, $dias_feriados, $sender) == 0)
              {
                  $numdias--;
              }
	    }
	return $fecha;
	}


/* Esta función devuelve el número de días feriados que hay entre una fecha y otra
 * El parámetro numdias esta representado en días hábiles.
 */
 	function cuenta_feriados($fecha, $numdias, $dias_feriados, $sender)
	{   $primera_corrida=true; $cuentaferiados=0;
		while ($numdias!=0)
		{
		  if ($primera_corrida == false)
              { $fecha = suma_dias($fecha, 1); }

		  $primera_corrida = false;
          $resultado_feriado = es_feriado($fecha, $dias_feriados, $sender);
		  if (is_array($resultado_feriado) && ($resultado_feriado[0]==1))
              { $cuentaferiados++; }
          else
          {
              if (es_feriado($fecha, $dias_feriados, $sender) == 0)
                {
                  $numdias--;
                }
          }
	    }
	return $cuentaferiados;
	}



/* Esta función devuelve un arreglo que contiene las fechas que se encuentran entre dos fechas dadas,
 * ambas inclusive; adicionalmente, el parámetro $tipo_dias, acepta los siguientes valores:
 * 0: Sólo días hábiles entre las fechas.
 * 1: Todos los días excluyendo sábados y domingos.
 * 2: Todos los días, incluyendo fin de semana y feriados, etc.
 * Nota: si $arreglo_feriados es pasado como parámetro, se trabaja con él en vez de trabajar
 *       con la base de datos, se asume que arreglo_feriados es un set de datos con los
 *       que el programador desea trabajar.
 */
	function dias_entre_fechas($fecha1,$fecha2, $tipo_dias, $arreglo_feriado, $sender)
    {
        $nro_dias = num_dias_entre_fechas($fecha1,$fecha2)+1;
        $fecha = $fecha1;
        $cuenta = 0;
        $sub = 0;
        while ($cuenta < $nro_dias)
            {
                $es_fer = es_feriado($fecha, $arreglo_feriado ,$sender); // para evitar varias llamadas a la función y asi menos consultas mysql
                switch ($tipo_dias)
                  {
                      // sólo los días hábiles, ni feriados ni sábados ni domingos
                     case 0:
                             if ($es_fer == 0)
                             {
                                 $fechas_resultantes[$sub]['fecha'] = $fecha;
                                 $sub++;
                             }
                             break;
                     // Todos, menos sabados y domingos, se incluyen feriados en la semana.
                     case 1:
                         if (($es_fer <> 2) and ($es_fer <> 3))
                           {
                               $fechas_resultantes[$sub]['fecha'] = $fecha;
                               if (is_array($es_fer))
                               {
                                   $fechas_resultantes[$sub]['observacion'] = $es_fer[1];
                               }
                               $sub++;
                           }
                           break;
                     // todos los dias
                     case 2:
                         $fechas_resultantes[$sub]['fecha'] = $fecha;
                         $sub++;
                         break;
                  }
                $fecha = suma_dias($fecha, 1);
                $cuenta++;
                
            }
        return $fechas_resultantes;
    }

/* Esta función se encarga de comprobar que la fecha sea un dia habil, un
 * feriado, o un fin de semana, la fecha suministrada debe ser en formato (dd/mm/aaaa)
 *      0: si es laborable (ni feriado ni fin de semana)
 * 	    1: si es feriado en dia de semana (no cae en fin de semana)
 * 		2: si es fin de semana no feriado
 * 		3: si es fin de semana feriado
 * Nota: si $arreglo_feriados es pasado como arreglo, s trabaja con el en vez de trabajar
 *       con la base de datos, se asume que arreglo_feriados es un set de datos con los
 *       que el programador desea trabajar.
 *      $fecha es del tipo dd/mm/aaaa
 */
	function es_feriado($fecha, $arreglo_feriados, $sender)
	{
        $tipo_feriado=0;
	    // antes de calcular las fechas se carga en un arreglo los datos de dias festivos de la base de datos
	    list($dia, $mes, $anio) = split("/", $fecha);	$mes=intval($mes); $dia=intval($dia); $anio=intval($anio);

        if (!empty($arreglo_feriados))
            { // si arreglo_feriados tiene datos, prioritariamente se trabaja con el.
                $esta = false; $cuentadia=0;
                while (($cuentadia < count ($arreglo_feriados)) and ($esta == false)) //($arreglo_feriados as $un_feriado)
                {
                    if ((($arreglo_feriados[$cuentadia]['dia'] == $dia) && ($arreglo_feriados[$cuentadia]['mes'] == $mes)) &&
                        (($arreglo_feriados[$cuentadia]['ano'] == $anio) || ($arreglo_feriados[$cuentadia]['ano'] == 'XXXX')))
                        {
                            $feriado[0]['descripcion']=$arreglo_feriados[$cuentadia]['descripcion'];
                        }
                    $cuentadia++;
                }
            }
        else
            { // si arreglo_feriados es null, se buscan los datos necesarios en la base de datos.
                $sql = "SELECT * FROM organizacion.dias_no_laborables
                        where ((dia = '$dia') and (mes = '$mes') and ((ano = 'XXXX') or (ano='$anio')))";
                $feriado=cargar_data($sql,$sender);
            }
        if (!empty($feriado))
        {
            $descrip=$feriado[0]['descripcion'];
            $tipo_feriado=1;
            if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sat' || date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sun'))
		      {$tipo_feriado=3;}
        }
        else
	      { // si no es feriado, se comprueba si es fin de semana o no
            if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sat' || date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sun'))
		      {$tipo_feriado=2;}
	      }

        switch ($tipo_feriado)
          {  // si es laborable, retorno solo diciendo que es laborable "0"
             case 1: return array($tipo_feriado,$descrip);
                     break;
             // si es cualquier otro caso, se devuelve la variable simple, no el arreglo
             default: return $tipo_feriado;
                     break;
          }
	}

/* Esta función se encarga de devolver un arreglo con los días feriados que se encuentren
 * registrados en la base de datos.
 */
	function dias_feriados($sender)
	{
        $sql = "select * from organizacion.dias_no_laborables order by ano,mes,dia";
        $arreglo_feriados=cargar_data($sql,$sender);
		return $arreglo_feriados;
	}


/* Esta función devuelve el número de días que serán restados al disfrute de
 * vacaciones si aplica horario especial, y dependiendo de la fecha en que el
 * funcionario tome sus vacaciones y el tiempo que estas duren.
 */
  function calcula_dias_restados($fechamy,$diasX,$horariosX,$feriados,$sender)
  {
    $cuenta_dias_recorridos=1;
	$cuenta_dias_descuento=0;
	$chx = count($horariosX) - 1;
    $ya='no';
	while ($diasX!=0)
	{ 
	  $xcont=0;
	  $ya='no';
	  while (($xcont <= $chx) and ($ya == 'no'))
	   {
	    if (($horariosX[$xcont]['vigencia_desde'] <= $fechamy) and ($horariosX[$xcont]['vigencia_hasta'] >= $fechamy) and
	        ($horariosX[$xcont]['status'] == '2'))
		  { $ya=si; }
	    else
		  { $xcont++; }
	   } // del while xcount
        // si la fecha no se encuentra en horario especial, se busca en horario normal
  	  if ($ya == 'no')
	    { $xcont=0;
		  while (($xcont<= $chx) and ($ya == 'no'))
	        {
	           if (($horariosX[$xcont]['vigencia_desde'] <= $fechamy) and ($horariosX[$xcont]['vigencia_hasta'] >= $fechamy)
		           and ($horariosX[$xcont]['status'] == '1'))
		         { $ya=si; }
	           else
		         { $xcont++; }
			}
		} // del ya = no

      // si se encontró, ya sea en horario especial o normal, se cuentan los dias
      // y se calculan los descuentos respectivos.
	  if ($ya == 'si')
	    {
		  if ($horariosX[$xcont]['dias_a_contar'] > 0)
			{
			  if ($cuenta_dias_recorridos == $horariosX[$xcont]['dias_a_contar'])
				{
				  $cuenta_dias_recorridos=1;
				  $cuenta_dias_descuento++;
				}
			  else
				{ $cuenta_dias_recorridos++; }
			}
		  else // del if dias a contar = 0
			{ // devuelvo los dias recorridos a cero porque si cae aqui estamos leyendo un horario normal, por lo tanto no hay nada especial
			  $cuenta_dias_recorridos=1;
			}
		  $fechamy=cambiaf_a_mysql(suma_dias_habiles(cambiaf_a_normal($fechamy), 2, $feriados, $sender));
		  $diasX--;
	    } // del if ya == si

	} // del while dias
    return $cuenta_dias_descuento;
  }




    function comprobar_fechas_periodos($fecha, $dias, $cedula, $feriados, $sender)
    {
        $sql="select * from asistencias.vacaciones as v
              where((v.cedula='$cedula') and (v.pendientes > '0'))
              order by v.disponible_desde";
        $fechamy = cambiaf_a_mysql($fecha);
        $datos_pendientes=cargar_data($sql,$sender);
        $cuenta_periodo = count ($datos_pendientes) -1;
        $bien = true; $xcount=0;
        while (($dias > 0) && ($xcount <= $cuenta_periodo) && ($bien == true))
        {// $f2 = $f2." ".$fechamy;
            if ($fechamy >= $datos_pendientes[$xcount]['disponible_desde'])
            {
                if ($dias > $datos_pendientes[$xcount]['pendientes'])
                {
                    $dias = $dias - $datos_pendientes[$xcount]['pendientes'];
                    $fechamy=cambiaf_a_mysql(suma_dias_habiles(cambiaf_a_normal($fechamy), $datos_pendientes[$xcount]['pendientes']+1, $feriados, $sender));
                    $xcount++;
                }
                else
                {
                    $dias = 0;
                    $fechamy=cambiaf_a_mysql(suma_dias_habiles(cambiaf_a_normal($fechamy), $dias+1, $feriados, $sender));
                    $xcount++;
                }
            }
            else
            {$bien=false; $dias = 0;}
        }
        return $bien;
    }

/*Funcion que retorna numero del ultimo dia del mes y año pasado por parametro*/
function ultimo_dia_mes($mes,$ano)
{
    return strftime("%d", mktime(0, 0, 0, $mes+1, 0, $ano));
} //fin funcion


/* Esta función se encarga de comprobar que la fecha sea un dia habil, un
 * feriado, o un fin de semana, la fecha suministrada debe ser en formato (dd/mm/aaaa)
 *      0: si es laborable (ni feriado ni fin de semana)
 * 	    1: si es feriado en dia de semana (no cae en fin de semana)
 * 		2: si es fin de semana no feriado
 * 		3: si es fin de semana feriado
 * Nota: si $arreglo_feriados es pasado como arreglo, s trabaja con el en vez de trabajar
 *       con la base de datos, se asume que arreglo_feriados es un set de datos con los
 *       que el programador desea trabajar.
 *      $fecha es del tipo dd/mm/aaaa
 * La diferencia entre es_feriado y es_feriado2 es que esta retorna solo el tipo de dia, es decir el numero
 */
	function es_feriado2($fecha, $arreglo_feriados, $sender)
	{
        $tipo_feriado=0;
	    // antes de calcular las fechas se carga en un arreglo los datos de dias festivos de la base de datos
	    list($dia, $mes, $anio) = split("/", $fecha);	$mes=intval($mes); $dia=intval($dia); $anio=intval($anio);

        if (!empty($arreglo_feriados))
            { // si arreglo_feriados tiene datos, prioritariamente se trabaja con el.
                $esta = false; $cuentadia=0;
                while (($cuentadia < count ($arreglo_feriados)) and ($esta == false)) //($arreglo_feriados as $un_feriado)
                {
                    if ((($arreglo_feriados[$cuentadia]['dia'] == $dia) && ($arreglo_feriados[$cuentadia]['mes'] == $mes)) &&
                        (($arreglo_feriados[$cuentadia]['ano'] == $anio) || ($arreglo_feriados[$cuentadia]['ano'] == 'XXXX')))
                        {
                            $feriado[0]['descripcion']=$arreglo_feriados[$cuentadia]['descripcion'];
                        }
                    $cuentadia++;
                }
            }
        else
            { // si arreglo_feriados es null, se buscan los datos necesarios en la base de datos.
                $sql = "SELECT * FROM organizacion.dias_no_laborables
                        where ((dia = '$dia') and (mes = '$mes') and ((ano = 'XXXX') or (ano='$anio')))";
                $feriado=cargar_data($sql,$sender);
            }
        if (!empty($feriado))
        {
            $descrip=$feriado[0]['descripcion'];
            $tipo_feriado=1;
            if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sat' || date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sun'))
		      {$tipo_feriado=3;}
        }
        else
	      { // si no es feriado, se comprueba si es fin de semana o no
            if ((date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sat' || date("D", mktime(0, 0, 0, $mes, $dia, $anio)) == 'Sun'))
		      {$tipo_feriado=2;}
	      }

      return $tipo_feriado;


	}

 
?>