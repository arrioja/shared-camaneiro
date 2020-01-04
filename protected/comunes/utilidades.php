<?php

/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M. / Carlos A. Ávila P
 * Descripción: Contiene funciones de uso común en los diversos sistemas,
 *              principalmente relacionadas con el tratamiento de cadenas de
 *              caracteres, fechas, etc.
 *****************************************************  FIN DE INFO
*/

function completar_ceros_izq($variable,$cant)
	{
while (strlen($variable)<$cant)//completar con #$cant ceros
            {
            $variable='0'.$variable;
            }
	return $variable;
	}



/*//genera la fila que se inserta en el archivo de texto a enviar al banco	*/
function generar_fila_archivo($cuenta,$monto,$cont, $nombre)
	{
/* Estas filas que se encuentran comentadas son del anterior formato exigido por
 * Bancaribe, las cuales fueron desactivadas por nuevo instructivo del banco.
 */
//        $monto=str_replace(".","",$monto);
//        while (strlen($monto)<20)//completar los 20 ceros
//            {
//            $monto='0'.$monto;
//            }
    //	$fila="\nC/$cuenta/$monto/$nombre";
        $monto = number_format($monto,2);
        $fila="\n$cuenta/$monto/$nombre";
//        Miles con comas y decimales con puntos.
        return ($fila);
	}


/*//genera la fila que se inserta en el archivo de texto a enviar al banco bicentenario	*/
function generar_fila_archivo2($empresa,$monto,$cuenta,$cedula,$tipo)
	{
/* Estas filas que se encuentran comentadas son del anterior formato exigido por
 * Bancaribe, las cuales fueron desactivadas por nuevo instructivo del banco.
 */
//        $monto=str_replace(".","",$monto);
//        while (strlen($monto)<20)//completar los 20 ceros
//            {
//            $monto='0'.$monto;
//            }
    //	$fila="\nC/$cuenta/$monto/$nombre";
        //$monto = number_format($monto,2);
$monto=str_replace(".","",$monto);
while (strlen($monto)<12)//completar los 20 ceros
            {
            $monto='0'.$monto;
            }
while (strlen($cedula)<10)//completar los 20 ceros
            {
            $cedula='0'.$cedula;
            }

        $fila="\n$empresa$monto$cuenta$cedula"."00000".$tipo."00";
//        Miles con comas y decimales con puntos.
        return ($fila);
	}
/* Esta función limpia la cadena de caracteres de entrada para que solo queden
 * números en la cadena de salida.
 */
function solo_numeros($cadena)
{$solonumeros='';
    $numeros = array ('0','1','2','3','4','5','6','7','8','9');
    for ($n = 0; $n < strlen($cadena)  ; $n++)
    {
        if (in_array($cadena[$n],$numeros))
        {
            $solonumeros = $solonumeros.$cadena[$n];
        }
    }
    return $solonumeros;
}

/* Esta función permite descomponer el código presupuestario del presupuesto de
 * ingresos de una institución de una manera mas manejable para el programador.
 * Se convierte en un arreglo cuyos subindices estan representados con cada parte
 * del codigo presupuestario descrito.
 * Pedro Arrioja
 */
function descomponer_codigo_ingreso($codigo)
{
    $codigo = solo_numeros($codigo);
    $codigo_ingreso['ram']=$codigo[0].$codigo[1].$codigo[2];
    $codigo_ingreso['gen']=$codigo[3].$codigo[4];
    $codigo_ingreso['esp']=$codigo[5].$codigo[6];
    $codigo_ingreso['sub']=$codigo[7].$codigo[8];
    return $codigo_ingreso;
}



/* Esta función permite descomponer el código presupuestario del presupuesto de
 * gastos de una institución de una manera mas manejable para el programador.
 * Se convierte en un arreglo cuyos subindices estan representados con cada parte
 * del codigo presupuestario descrito.
 * Pedro Arrioja
 */
function descomponer_codigo_gasto($codigo)
{
    $codigo = solo_numeros($codigo);
    $codigo=rellena_derecha($codigo,28,"0");
    $codigo_gasto['sector']=$codigo[0].$codigo[1];
    $codigo_gasto['programa']=$codigo[2].$codigo[3];
    $codigo_gasto['subprograma']=$codigo[4].$codigo[5];
    $codigo_gasto['proyecto']=$codigo[6].$codigo[7];
    $codigo_gasto['actividad']=$codigo[8].$codigo[9];
    $codigo_gasto['partida']=$codigo[10].$codigo[11].$codigo[12];
    $codigo_gasto['generica']=$codigo[13].$codigo[14];
    $codigo_gasto['especifica']=$codigo[15].$codigo[16];
    $codigo_gasto['subespecifica']=$codigo[17].$codigo[18];
    $codigo_gasto['ordinal']=$codigo[19].$codigo[20].$codigo[21].$codigo[22].$codigo[23];
    $codigo_gasto['fuente']=$codigo[24].$codigo[25].$codigo[26].$codigo[27];
    return $codigo_gasto;
}


/* Esta función permite llenar con un caracter a la izquierda la cadena o el numero
 * que le haya sido suministrado como parámetro, devuelve tantos caracteres como
 * se le indique concatenado al parámetro de entrada.
 * Pedro Arrioja.
 */
function rellena($entrada,$maximo,$caracter)
{
    $salida = $entrada;
    for ($num = strlen($entrada) ; $num < ($maximo) ; $num++)
    {
        $salida=$caracter.$salida;
    }
    return $salida;
}


/* Esta función permite llenar con un caracter a la derecha la cadena o el numero
 * que le haya sido suministrado como parámetro, devuelve tantos caracteres como
 * se le indique concatenado al parámetro de entrada.
 * Nota Adicional: Se ha realizado una nueva función en vez de modificar la anterior
 * para mantener compatibilidad con el codigo que se haya escrito antes de crear esta
 * nueva funcion.
 * Pedro Arrioja.
 */
function rellena_derecha($entrada,$maximo,$caracter)
{
    $salida = $entrada;
    for ($num = strlen($entrada) ; $num < ($maximo) ; $num++)
    {
        $salida=$salida.$caracter;
    }
    return $salida;
}

/* Esta función se encarga de construir un esquema de menú utilizando ul y li
 * para ser implementado con los Css de cssmenu.co.uk
 * Pedro Arrioja
 */
function elabora_menu($sender_local)
{
    /* Se saca los datos de la base de datos para comenzar la elaboración del
     * menú en base a los módulos que se encuentren disponibles.
     */
//	$sql="select codigo_modulo,nombre_corto,archivo_php from intranet.modulos order by codigo_modulo";
    $login = usuario_actual('login');
    $sql="SELECT distinct (b.codigo_modulo), m.nombre_corto, m.archivo_php
	  					   FROM intranet.usuarios_grupos as a, intranet.permisos_grupos as b,
                                intranet.modulos as m
						   WHERE ((a.login='$login') and (a.codigo=b.codigo_grupo) and (m.codigo_modulo = b.codigo_modulo))
						   ORDER BY b.codigo_modulo";
    $resul=cargar_data($sql,$sender_local);

    $submenu='<a class="fly" href="#url"><b>';
    $sender_local->Service->constructUrl('Home');
    $menu_ul_li='<ul id="flyout">';
    $maximo=false;
    $nivel = 0;
//    $num_rows = count($resul);
    for ($x = 0 ; $x < count($resul)-1; $x++)
    {
        // un par de valores que acompañan al registro actual sin importar cual sea su nivel
        $nombre_modulo=$resul[$x]['nombre_corto'];
        $link='<a href="'.$sender_local->Service->constructUrl($resul[$x]['archivo_php']).'"><b>';



        $codigo_actual=$resul[$x]['codigo_modulo'][0].$resul[$x]['codigo_modulo'][1];
//        if ($x != $num_rows)
//        {
          $codigo_siguiente=$resul[$x+1]['codigo_modulo'][0].$resul[$x+1]['codigo_modulo'][1];
//        }
        if ($codigo_actual != $codigo_siguiente)
        {
            $menu_ul_li=$menu_ul_li."<li>".$link.$nombre_modulo."</b></a></li>";
            /* Este for permite devolver el nivel al principio, desde donde este */
             for ($xnivel = $nivel ; $xnivel > 1 ; $xnivel--)
            {
                 $menu_ul_li= $menu_ul_li."</ul>";
            }
            $nivel=1;
            $maximo=false;
        }
        else
        {
            $codigo_actual=$resul[$x]['codigo_modulo'][2].$resul[$x]['codigo_modulo'][3];
            $codigo_siguiente=$resul[$x+1]['codigo_modulo'][2].$resul[$x+1]['codigo_modulo'][3];
            if ($codigo_actual != $codigo_siguiente)
            {
                if ($nivel >= 2)
                {
                    $menu_ul_li=$menu_ul_li."<li>".$link.$nombre_modulo."</a></li>";
                    /* Este for permite devolver el nivel 2, desde donde este */
                    for ($xnivel = $nivel ; $xnivel > 2 ; $xnivel--)
                    {
                         $menu_ul_li= $menu_ul_li."</ul>";
                    }
                }
                else
                {
                    $menu_ul_li=$menu_ul_li."<li>".$submenu.$nombre_modulo."</b></a>";
                    $menu_ul_li= $menu_ul_li."<ul>";
                }
                $nivel=2;
                $maximo=false;
            }
            else
            {
                $codigo_actual=$resul[$x]['codigo_modulo'][4].$resul[$x]['codigo_modulo'][5];
                $codigo_siguiente=$resul[$x+1]['codigo_modulo'][4].$resul[$x+1]['codigo_modulo'][5];
                if ($codigo_actual != $codigo_siguiente)
                {
                    if ($nivel >= 3)
                    {
                        $menu_ul_li=$menu_ul_li."<li>".$link.$nombre_modulo."</a></li>";
                        /* Este for permite devolver el nivel 2, desde donde este */
                        for ($xnivel = $nivel ; $xnivel > 3 ; $xnivel--)
                        {
                             $menu_ul_li= $menu_ul_li."</ul>";
                        }
                    }
                    else
                    {
                        $menu_ul_li=$menu_ul_li."<li>".$submenu.$nombre_modulo."</b></a>";
                        $menu_ul_li= $menu_ul_li."<ul>";
                    }
                    $nivel=3;
                    $maximo=false;
                }
                else
                {
                    $codigo_actual=$resul[$x]['codigo_modulo'][6].$resul[$x]['codigo_modulo'][7];
                    $codigo_siguiente=$resul[$x+1]['codigo_modulo'][6].$resul[$x+1]['codigo_modulo'][7];
                    if ($codigo_actual != $codigo_siguiente)
                    {
                        $nivel=4;
                        if ($maximo==false)
                        {
                            $menu_ul_li=$menu_ul_li."<li>".$submenu.$nombre_modulo."</b></a>";
                            $menu_ul_li= $menu_ul_li."<ul>";
                            $maximo=true;
                        }
                        else
                        {
                            $menu_ul_li=$menu_ul_li."<li>".$link.$nombre_modulo."</a></li>";
                        }
                    }
                }
            }
        }
    }
  $menu_ul_li= $menu_ul_li."</ul>";
  return $menu_ul_li;
}


/*!
  @function num2letras () 
  @abstract Dado un n?mero lo devuelve escrito.
  @param $num number - N?mero a convertir.
  @param $fem bool - Forma femenina (true) o no (false).
  @param $dec bool - Con decimales (true) o no (false).
  @result string - Devuelve el n?mero escrito en letra.

*/
function num_a_letras($num, $fem = false, $dec = true) {
//if (strlen($num) > 14) die("El n?mero introducido es demasiado grande");   
   $matuni[2]  = "dos";
   $matuni[3]  = "tres";
   $matuni[4]  = "cuatro";
   $matuni[5]  = "cinco";
   $matuni[6]  = "seis";
   $matuni[7]  = "siete";
   $matuni[8]  = "ocho";
   $matuni[9]  = "nueve";
   $matuni[10] = "diez";
   $matuni[11] = "once";
   $matuni[12] = "doce";
   $matuni[13] = "trece";
   $matuni[14] = "catorce";
   $matuni[15] = "quince";
   $matuni[16] = "dieciseis";
   $matuni[17] = "diecisiete";
   $matuni[18] = "dieciocho";
   $matuni[19] = "diecinueve";
   $matuni[20] = "veinte";
   $matunisub[2] = "dos";
   $matunisub[3] = "tres";
   $matunisub[4] = "cuatro";
   $matunisub[5] = "quin";
   $matunisub[6] = "seis";
   $matunisub[7] = "sete";
   $matunisub[8] = "ocho";
   $matunisub[9] = "nove";

   $matdec[2] = "veint";
   $matdec[3] = "treinta";
   $matdec[4] = "cuarenta";
   $matdec[5] = "cincuenta";
   $matdec[6] = "sesenta";
   $matdec[7] = "setenta";
   $matdec[8] = "ochenta";
   $matdec[9] = "noventa";
   $matsub[3]  = 'mill';
   $matsub[5]  = 'bill';
   $matsub[7]  = 'mill';
   $matsub[9]  = 'trill';
   $matsub[11] = 'mill';
   $matsub[13] = 'bill';
   $matsub[15] = 'mill';
   $matmil[4]  = 'millones';
   $matmil[6]  = 'billones';
   $matmil[7]  = 'de billones';
   $matmil[8]  = 'millones de billones';
   $matmil[10] = 'trillones';
   $matmil[11] = 'de trillones';
   $matmil[12] = 'millones de trillones';
   $matmil[13] = 'de trillones';
   $matmil[14] = 'billones de trillones';
   $matmil[15] = 'de billones de trillones';
   $matmil[16] = 'millones de billones de trillones';

   $num = trim((string)@$num);
   if ($num[0] == '-') {
      $neg = 'menos ';
      $num = substr($num, 1);
   }else
      $neg = '';
   while ($num[0] == '0') $num = substr($num, 1);
   if ($num[0] < '1' or $num[0] > 9) $num = '0' . $num;
   $zeros = true;
   $punt = false;
   $ent = '';
   $fra = '';
   for ($c = 0; $c < strlen($num); $c++) {
      $n = $num[$c];
      if (! (strpos(".,'''", $n) === false)) {
         if ($punt) break;
         else{
            $punt = true;
            continue;
         }

      }elseif (! (strpos('0123456789', $n) === false)) {
         if ($punt) {
            if ($n != '0') $zeros = false;
            $fra .= $n;
         }else

            $ent .= $n;
      }else

         break;

   }
   $ent = '     ' . $ent;
   if ($dec and $fra and ! $zeros) {
     //  $fin = ' con '.num_a_letras($fra)." céntimos";
//       $fra_en_letras =
//        $fin
/*      $fin = ' coma';
      for ($n = 0; $n < strlen($fra); $n++) {
         if (($s = $fra[$n]) == '0')
            $fin .= ' cero';
         elseif ($s == '1')
            $fin .= $fem ? ' una' : ' un';
         else
            $fin .= ' ' . $matuni[$s];
      }*/
   }else
      $fin = '';
   if ((int)$ent === 0) return 'Cero ' . $fin;
   $tex = '';
   $sub = 0;
   $mils = 0;
   $neutro = false;
   while ( ($num = substr($ent, -3)) != '   ') {
      $ent = substr($ent, 0, -3);
      if (++$sub < 3 and $fem) {
         $matuni[1] = 'una';
         $subcent = 'as';
      }else{
         $matuni[1] = $neutro ? 'un' : 'uno';
         $subcent = 'os';
      }
      $t = '';
      $n2 = substr($num, 1);
      if ($n2 == '00') {
      }elseif ($n2 < 21)
         $t = ' ' . $matuni[(int)$n2];
      elseif ($n2 < 30) {
         $n3 = $num[2];
         if ($n3 != 0) $t = 'i' . $matuni[$n3];
         $n2 = $num[1];
         $t = ' ' . $matdec[$n2] . $t;
      }else{
         $n3 = $num[2];
         if ($n3 != 0) $t = ' y ' . $matuni[$n3];
         $n2 = $num[1];
         $t = ' ' . $matdec[$n2] . $t;
      }
      $n = $num[0];
      if ($n == 1) {
         $t = ' ciento' . $t;
      }elseif ($n == 5){
         $t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
      }elseif ($n != 0){
         $t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
      }
      if ($sub == 1) {
      }elseif (! isset($matsub[$sub])) {
         if ($num == 1) {
            $t = ' Un mil';
         }elseif ($num > 1){
            $t .= ' mil';
         }
      }elseif ($num == 1) {
         $t .= ' ' . $matsub[$sub] . 'on';
      }elseif ($num > 1){
         $t .= ' ' . $matsub[$sub] . 'ones';
      }
      if ($num == '000') $mils ++;
      elseif ($mils != 0) {
         if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub];
         $mils = 0;
      }
      $neutro = true;
      $tex = $t . $tex;
   }
   $tex = $neg . substr($tex, 1) . $fin;
   return ucfirst($tex);
}


/* Esta función se encarga de generar un arreglo con los números desde el inicio
 * hasta el fin (muy útil para generar los años de un drop).
 * Pedro Arrioja
 */
function generar_rango($inicio, $fin, $maxfill)
{
    for ($x = $inicio ; $x <= $fin ; $x++)
    {
        $cant = rellena($x,$maxfill,'0');
        $cantidades[$cant] = $cant;
    }
    return $cantidades;
}

/*Reemplaza acentos de una cadena y la retorna sin estos*/
function reemplazar($String){

  $string = trim($string);

    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );

    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );

    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );

    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );

    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );

    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
        return $String;
}
?>
