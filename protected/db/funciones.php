<?php
/* 
****************************************************  INFO DEL ARCHIVO
  		   Creado por: 	Pedro E. Arrioja M.
  Descripción General:  Se encuentran funciones de aplicación genérica para la extracción
 *                      y listado de datos desde bases de datos.
****************************************************  FIN DE INFO
 */
/* Esta es una función genérica que devuelde un arreglo con el set
 * resultante de la consulta que se le pase como parámetro, se tomó la creada por
 * Axzel Marín y se modificó para que trabajase con el objeto contenedor (sender)
 */
function cargar_data($consulta,$sender)
{   try
    {
    $db = $sender->Application->Modules["db"]->DbConnection;
    $db->Active=true;
    $resultado = $db->createCommand($consulta)->query();
    $dataset=$resultado->readAll();
    $db->Active=false;
    return $dataset;
    }
    catch(Exception $e)
    {
       $mensaje=$e->getMessage();
        $sender->Response->redirect($sender->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
    }

}

/* Esta función sirve para los UPDATE, INSERTS Y DELETE, la decisión de llamarla
 * "modificar_data" obedece a que es la manera mas generica de denominarla, ya
 * que no importa el tipo de oprtacion que esta realice, al final es siempre
 * una modificacion de los datos contenidos en la base.
 */
function modificar_data($consulta,$sender)
{
    try
    {
    $db = $sender->Application->Modules["db"]->DbConnection;
    $db->Active=true;
    $ejecucion = $db->createCommand($consulta)->execute();
    $db->Active=false;
    return $ejecucion;
    }
    catch(Exception $e)
    {
        $mensaje=$e->getMessage();
        $sender->Response->redirect($sender->Service->constructUrl('intranet.mensaje',array('codigo'=>'00005','adic'=>$mensaje)));
    }
}

function modificar_data2($consulta,$sender)
{
    $db = $sender->Application->Modules["db2"]->DbConnection;
   // $db->Active=true;
    $ejecucion = $db->createCommand($consulta)->execute();
    //$db->Active=false;
    return $ejecucion;

}

/* Esta función se encarga de incluir en el sistema las marcas de rastreos
 * necesarias para dar seguimiento a las actividades de los usuarios en
 * el sistema.
 * Con respecto a esta función, el parámetro tipo puede tener uno de los siguientes valores:
 * L: Login y Logout de la Intranet.
 * C: Consulta de Datos, sea en forma de Listado o individualmente.
 * I: Inclusión de Datos en el Sistema.
 * M: Modificación de Datos en el Sistema.
 * E: Eliminación de Datos en el Sistema.
 * R: Error en la insersión de Datos.
 * A: Aprobaciones o Autorizaciones.
 * D: Desaprobaciones o Rechazos
 */
  function inserta_rastro($login,$cedula,$tipo,$descripcion,$ip,$sender)
    {
        $db = $sender->Application->Modules["db"]->DbConnection;
        $db->Active=true;
        $cod_organizacion = usuario_actual('cod_organizacion');
        $nom_organizacion = usuario_actual('nombre_organizacion');
        $descripcion = $descripcion." # Org: (".$cod_organizacion.") - ".$nom_organizacion;
        $consulta="insert into intranet.rastreo(login,cedula,tipo,descripcion,ip, cod_organizacion)
                   values ('$login','$cedula','$tipo','$descripcion','$ip','$cod_organizacion')";
        $ejecucion = $db->createCommand($consulta)->execute();
        $db->Active=false;
        return $ejecucion;
	}

/* Esta función se encarga de traer el siguiente número consecutivo del campo
 * de la tabla que se haya seleccionado, es útil para separar códigos únicos
 * de identificación de los índices (id) de la tabla, ya que si se llega a
 * reindexar la tabla por labores de mantenimiento y han existido eliminaciones,
 * la integridad de los datos se perdería.
 *
 * Se le añade a la función la habilidad
 * de localizar el proximo numero del campo en la tabla dependiendo de otros
 * valores condicionales que se debe cumplir tambien. Por ejemplo, cuando el
 * código depende de si es de una organización u otra. 000001 -> 001
 * 000002 -> 001, así tanto la organizacion 000001 como la 000002 tienen 001 y
 * el siguiente es 002 invididualmente por cada organizacion.
 *
 * Al momento de la llamada, el parámetro arreglo, se puede definir como nulo
 * si solo interesa que se busque en un solo campo, si no, es necesario que se
 * defina de la manera   indice => valor
 */
  function proximo_numero($tabla,$campo,$arreglo,$sender)
    {
        $db = $sender->Application->Modules["db"]->DbConnection;
        $db->Active=true;
        $consulta = "select max($campo) as maximo from $tabla";

        /* se añaden las extensiones de la consulta que hagan falta si en el
           arreglo de entrada vienen datos adicionales */
        if (empty($arreglo) == false)
        {
            $consulta = $consulta." where ";
            $solo_un_valor = true;
            foreach($arreglo as $indice => $valor)
              {
                  /* si la siguiente condicion se cumple, quiere decir que por lo
                   * menos es la segunda vez que se entra
                   */
                  if ($solo_un_valor == false)
                  {
                      $consulta=$consulta." and ";
                  }
                  $consulta = $consulta."(".$indice." = '".$valor."')";
                  $solo_un_valor = false;
              }            
        }

        $resultado = $db->createCommand($consulta)->query();
        $dataset=$resultado->readAll();
        $codigo_nuevo=$dataset[0]["maximo"]+1;
        $db->Active=false;

        return $codigo_nuevo;
	}

 /* Esta función se encarga de verificar la existencia de un valor dada una
  * tabla(bd.tabla) y el nombre del campo, además comprueba los valores adicionales
  * pasados como parámetros en el arreglo recibido. Si solo se desea buscar un valor individual,
  * el valor del arreglo pasado como parámetro debe ser null. Ver organizacion.incluir_persona
  */
    function verificar_existencia($tabla,$campo,$valor,$arreglo,$sender)
    {$encontrado='';
        $db = $sender->Application->Modules["db"]->DbConnection;
        $db->Active=true;
        $consulta = "select $campo from $tabla where ($campo='$valor')";
        /* se añaden las extensiones de la consulta que hagan falta si en el
           arreglo de entrada vienen datos adicionales */
        if (empty($arreglo) == false)
        {
            $consulta = $consulta." and ";
            $solo_un_valor = true;
            foreach($arreglo as $indice => $valor)
              {
                  /* si la siguiente condicion se cumple, quiere decir que por lo
                   * menos es la segunda vez que se entra
                   */
                  if ($solo_un_valor == false)
                  {
                      $consulta=$consulta." and ";
                  }
                  $consulta = $consulta."(".$indice." = '".$valor."')";
                  $solo_un_valor = false;
              }
        }
        $resultado = $db->createCommand($consulta)->query();
        $dataset=$resultado->readAll();
        if ($dataset!=null)
            $encontrado=$dataset[0][$campo];
        $db->Active=false;
           if ($encontrado=='')//si no encuentra el valor devuelve true
           return True;
           else
           return False;
    }


/*Esta función se encarga de verificar la existencia de un registro en la base
 * de datos dados dos valores a buscar en dos campos CARLOS: DOCUMENTA TUS FUNCIONES!!!.
 * Ésta función ha quedado obsoleta por la funcinción verificar_existencia, no obstante, se deja
 * en este archivo por compatibilidad.
 */
    function verificar_existencia_doble($tabla,$campo,$valor,$campo2,$valor2,$sender)//cuando verificas 2 campos de la bd
    {
        $db = $sender->Application->Modules["db"]->DbConnection;
        $db->Active=true;
        $consulta = "select $campo , $campo2 from $tabla where $campo='$valor' and $campo2='$valor2'";
        $resultado = $db->createCommand($consulta)->query();
        $dataset=$resultado->readAll();
        $encontrado=$dataset[0][$campo];
        $db->Active=false;
           if ($encontrado=='')//si no encuentra el valor devuelve true
           return true;
           else
           return false;
    }
/*Esta función se encarga de devolver el tipo de nomina dependiendo del codigo de
 * organizacion.  CARLOS: DOCUMENTA TUS FUNCIONES!!!.
 */
    function cargar_tipo_nomina($cod_org,$this)
        {
            $sql="select * from nomina.tipo_nomina where cod_organizacion='$cod_org'";
            $datos=cargar_data($sql,$this);
            return $datos;
        }
  /*Funcion que retorna los diferentes años registrados en la tabla entrada salida
   * esto sirve para tomar el año para reportes de asistencia.
   */
 function ano_asistencia($this)
        {
            $sql="SELECT YEAR(fecha) AS ano FROM asistencias.entrada_salida WHERE YEAR(fecha)!='0' GROUP BY ano ORDER BY ano DESC";
            $datos=cargar_data($sql,$this);
            return $datos;
        }
/* Esta función se encarga de devolver el año actual del presupuesto, esto es
 * el año presupuestario cuyo estatus sea PR = Presente.
 * Existen 3 Estatus: PA = Pasado, PR = Presente, FU = Futuro
 * Si $tipo es:
 *      0: se devuelve los años "pasados"
 *      1: se devuelve el año "Presente"
 *      2: se devuelve los años "futuros"
 *      3: se devuelve los años "Presente y Futuro"
 *      4: se devuelve los años "Pasado y Presente"
 *      5: se devuelven TODOS los años (Pasado, Presente y Futuro)
 */
    function ano_presupuesto($cod_org,$tipo,$this)
        {
            $sql="select ano from presupuesto.estatus_presupuesto ";
            switch ($tipo)
            {
                case 0:
                    $sql = $sql."where (estatus = 'PA') and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 1:
                    $sql = $sql."where (estatus = 'PR') and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 2:
                    $sql = $sql."where (estatus = 'FU') and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 3:
                    $sql = $sql."where ((estatus = 'PR') or (estatus = 'FU')) and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 4:
                    $sql = $sql."where ((estatus = 'PR') or (estatus = 'PA')) and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 5:
                    $sql = $sql."where (cod_organizacion = '$cod_org') order by ano";
                    break;
                default:
                    $sql = $sql."where (estatus = 'PR') and (cod_organizacion = '$cod_org') order by ano";
                    break;
            }              
            $dataset=cargar_data($sql,$this);
            return $dataset;
        }

/* Esta función se encarga de devolver el año actual del control de documentos, esto es
 * cuyo estatus sea PR = Presente.
 * Existen 3 Estatus: PA = Pasado, PR = Presente, FU = Futuro
 * Si $tipo es:
 *      0: se devuelve los años "pasados"
 *      1: se devuelve el año "Presente"
 *      2: se devuelve los años "futuros"
 *      3: se devuelve los años "Presente y Futuro"
 *      4: se devuelve los años "Pasado y Presente"
 *      5: se devuelven TODOS los años (Pasado, Presente y Futuro)
 */
    function ano_documentos($cod_org,$tipo,$this)
        {
            $sql="select ano from organizacion.estatus_ano_documentos ";
            switch ($tipo)
            {
                case 0:
                    $sql = $sql."where (estatus = 'PA') and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 1:
                    $sql = $sql."where (estatus = 'PR') and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 2:
                    $sql = $sql."where (estatus = 'FU') and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 3:
                    $sql = $sql."where ((estatus = 'PR') or (estatus = 'FU')) and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 4:
                    $sql = $sql."where ((estatus = 'PR') or (estatus = 'PA')) and (cod_organizacion = '$cod_org') order by ano";
                    break;
                case 5:
                    $sql = $sql."where (cod_organizacion = '$cod_org') order by ano";
                    break;
                default:
                    $sql = $sql."where (estatus = 'PR') and (cod_organizacion = '$cod_org') order by ano";
                    break;
            }
            $dataset=cargar_data($sql,$this);
            return $dataset;
        }


/* Esta funcion devuelve el string de inicio del codigo presupuestario de la Unidad
 * ejecutora del usuario actual para el año en curso.
 */
    function codigo_unidad_ejecutora($this)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
              // Para colocar el codigo de la unidad ejecutora (convertir esto en una funcion)
            $ano = ano_presupuesto($cod_organizacion,1,$this);
           
            $ano = $ano[0]['ano'];
            $sql = "select * from presupuesto.unidad_ejecutora
                    where ((cod_organizacion = '$cod_organizacion') and (ano = '$ano'))";
            $codigo = cargar_data($sql,$this);
            if (empty($codigo) == false)
              {
                $var_retorno = $codigo[0]['sector']."-".$codigo[0]['programa']."-".$codigo[0]['subprograma']."-".$codigo[0]['proyecto']."-".$codigo[0]['actividad'];
              }
              else
              {
                  $var_retorno = null;
              }
            return $var_retorno;
        }

/* Esta función genera un número aleatorio que sirve para identificar al registro
 * de los codigos presupuestarios de las diversas ordenes mientras aún no se han
 * registrado por completo en el sistema.
 */
    function aleatorio_compromisos($this)
        {
            $aleatorio = rand(1,999999);
            // me aseguro que tenga 6 caracteres
            while (strlen($aleatorio) < 6)
               {$aleatorio = rand(1,999999);}
            $existe = true;
            while ($existe == true)
            {
                $sql = "select numero from presupuesto.temporal_compromisos 
                        where (numero = '$aleatorio')";
                $resultado = cargar_data($sql,$this);
                $existe = !(empty($resultado));
            }
            return $aleatorio;
        }

/* Esta función genera un número aleatorio que sirve para identificar al registro
 * de los codigos presupuestarios de las diversas ordenes mientras aún no se han
 * registrado por completo en el sistema.
 */
    function aleatorio_causados($this)
        {
            $aleatorio = rand(1,999999);
            // me aseguro que tenga 6 caracteres
            while (strlen($aleatorio) < 6)
               {$aleatorio = rand(1,999999);}
            $existe = true;
            while ($existe == true)
            {
                $sql = "select numero from presupuesto.temporal_causado
                        where (numero = '$aleatorio')";
                $resultado = cargar_data($sql,$this);
                $existe = !(empty($resultado));
            }
            return $aleatorio;
        }

    function aleatorio_pagado($this)
    {
        $aleatorio = rand(1,999999);
        // me aseguro que tenga 6 caracteres
        while (strlen($aleatorio) < 6)
        {$aleatorio = rand(1,999999);}
        $existe = true;
        while ($existe == true)
        {
            $sql = "select numero_documento_pagado from presupuesto.temporal_causado_pagado
                    where (numero_documento_pagado = '$aleatorio')";
            $resultado = cargar_data($sql,$this);
            $existe = !(empty($resultado));
        }
        return $aleatorio;
    }

      function aleatorio_retenciones($this)
        {
            $aleatorio = rand(1,999999);
            // me aseguro que tenga 6 caracteres
            while (strlen($aleatorio) < 6)
               {$aleatorio = rand(1,999999);}
            $existe = true;
            while ($existe == true)
            {
                $sql = "select numero from presupuesto.temporal_retencion
                        where (numero = '$aleatorio')";
                $resultado = cargar_data($sql,$this);
                $existe = !(empty($resultado));
            }
            return $aleatorio;
        }
 /* Esta función genera un número aleatorio que sirve para identificar al registro
 * de los codigos presupuestarios de las diversas ordenes mientras aún no se han
 * registrado por completo en el sistema.
 */
    function aleatorio_traslados($this)
        {
            $existe = true;//mientras exista, genera otro aleatorio
            while ($existe)
                {
                $aleatorio = rand(1,999999);
                // me aseguro que tenga 6 caracteres
                 while (strlen($aleatorio) < 6)
                    {
                    $aleatorio = rand(1,999999);
                    }
                $sql = "select numero from presupuesto.temporal_traslados
                            where (numero = '$aleatorio')";

                $resultado = cargar_data($sql,$this);
                $existe = !(empty($resultado));
            }
            return $aleatorio;
        }

        /* Esta función genera un número aleatorio que sirve para identificar registros
 * mientras aún no se han registrado por completo en el sistema.
 */
    function aleatorio_numero($this,$tabla)
        {
            $existe = true;//mientras exista, genera otro aleatorio
            while ($existe)
                {
                $aleatorio = rand(1,999999);
                // me aseguro que tenga 6 caracteres
                 while (strlen($aleatorio) < 6)
                    {
                    $aleatorio = rand(1,999999);
                    }
                $sql = "select numero from $tabla
                            where (numero = '$aleatorio')";

                $resultado = cargar_data($sql,$this);
                $existe = !(empty($resultado));
            }
            return $aleatorio;
        }

 /* Esta función genera un número aleatorio que sirve para identificar al registro
 * de los codigos de cualquier tabla y cantidad de digitos
 */

    function codigo_aleatorio($largo,$tabla,$campo,$this)
        {
           $limite=rellena("9","10","9");
           $existe = true;//mientras exista, genera otro aleatorio
           while ($existe)
                {
                $aleatorio = rand(1, intval($limite));
                // me aseguro que tenga N caracteres
                while (strlen($aleatorio) < intval($largo))
                    {
                        $aleatorio = rand(1,intval($limite));
                    }
                $sql = "select $campo from $tabla
                            where ($campo = '$aleatorio')";
                $resultado = cargar_data($sql,$this);
                $existe = !(empty($resultado));
                }
            return $aleatorio;
        }

        
/* Esta función se encarga de realizar las sumas y restas en los montos de los
 * acumulados de los presupuestos de gastos.
 * $cod : codigo presupuestario
 * $tipo_operacion : suma: "+", resta: "-"
 */
    function modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, $campo, $tipo_operacion, $monto, $sender)
    {
        for ($xcod = 9 ; $xcod >= 1 ; $xcod--)
        {
            switch($xcod)
            {
                case 1:
                    $cod['programa']="00";
                    break;
                case 2:
                    $cod['subprograma']="00";
                    break;
                case 3:
                    $cod['proyecto']="00";
                    break;
                case 4:
                    $cod['actividad']="00";
                    break;
                case 5:
                    $cod['partida']="000";
                    break;
                case 6:
                    $cod['generica']="00";
                    break;
                case 7:
                    $cod['especifica']="00";
                    break;
                case 8:
                    $cod['subespecifica']="00";
                    break;
                case 9:
                    $cod['ordinal']="00000";
                    break;
            } // del switch

           $sql = "select * from presupuesto.presupuesto_gastos
                        where ((cod_organizacion = '$cod_organizacion') and
                                (ano = '$ano') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                               (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                               (ordinal = '$cod[ordinal]'))";
           $existe = cargar_data($sql,$sender);
           $id = $existe[0]['id'];

           if (empty($existe) == false)
           {
               if ($id_acumulado != $id)
               {
                    $id_acumulado = $id;
                    $sql2 = "update presupuesto.presupuesto_gastos set
                          $campo = $campo $tipo_operacion '$monto' where id = '$id' ";
                    $resultado=modificar_data($sql2,$sender);
               }
           }
        } // del for xcod
    }

/* Funcion que devuelve el codigo sin unidad ejecutora y sin ordinal*/
    
function codigo_resumido($codigo,$sender){
     $codigo = descomponer_codigo_gasto(rellena_derecha($codigo,'24','0'));
    return($codigo[partida]."-".$codigo[generica]."-".$codigo[especifica]."-".$codigo[subespecifica]);
     
}

/*Funcion que retorna saldo actual de una retencion dado un año
 * Saldo actual= saldo inicial+monto retenido en el año- monto pagado en el año
 */
function saldo_actual_retencion($ano,$cod_organizacion,$retencion,$sender){

       $codigo_sin_descomponer = $retencion;
       $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));

       //consulto saldo_inicial
        $sql="  SELECT r.saldo_inicial  from presupuesto.retencion as r
                WHERE ((r.ano = '$ano') AND (r.cod_organizacion='$cod_organizacion') AND (r.codigo='$codigo_sin_descomponer'))";

       $resultado_inicial=cargar_data($sql,$sender);

     //consulto retenciones realizadas a ordenes de pago segun retencion en el año
       $fecha_inicio="$ano/01/01";
       $fecha_fin="$ano/12/31";
       $sql = "select SUM(dp.monto) as monto FROM presupuesto.detalle_pagado as dp
            INNER JOIN presupuesto.maestro_pagado as m ON (m.numero=dp.numero_documento_pagado AND m.ano=dp.ano)
            where ((dp.es_retencion='1') and (m.cod_organizacion = '$cod_organizacion') and (dp.ano = '$ano') and
                  (dp.partida='$codigo[partida]' and dp.generica='$codigo[generica]' and especifica='$codigo[especifica]'
            and  dp.subespecifica='$codigo[subespecifica]' and dp.ordinal='$codigo[ordinal]')and (m.estatus_actual='NORMAL')
        and m.fecha_cheque  BETWEEN '$fecha_inicio' AND '$fecha_fin' ) ";
        $resultado_acum_ano = cargar_data($sql,$sender);


        //consulto retenciones realizadas a ordenes de pago segun retencion en el año
       $fecha_inicio="$ano/01/01";
       $fecha_fin="$ano/12/31";
       $sql = " SELECT SUM(rp.monto) as monto FROM presupuesto.retencion_pagado as rp
                INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=rp.cod_movimiento AND c.ano=rp.ano AND c.estatus_actual='NORMAL')
                INNER JOIN presupuesto.bancos_cuentas_movimientos as m ON (m.cod_movimiento=rp.cod_movimiento AND m.ano=rp.ano)
                WHERE (rp.codigo_retencion='$codigo_sin_descomponer' AND m.fecha  BETWEEN '$fecha_inicio' AND '$fecha_fin' ) ";
        $resultado_acum_ano_pagado = cargar_data($sql,$sender);


        return( $resultado_inicial[0]['saldo_inicial']+abs($resultado_acum_ano[0]['monto'])-$resultado_acum_ano_pagado[0]['monto']);

}

/*Funcion que retorna saldo de la retencion segun mes y año */
function saldo_mes_retencion($ano,$mes,$cod_organizacion,$retencion,$sender){

       $codigo_sin_descomponer = $retencion;
       $codigo = descomponer_codigo_gasto(rellena_derecha($codigo_sin_descomponer,'24','0'));

       //consulto saldo_inicial
        $sql="  SELECT r.saldo_inicial  from presupuesto.retencion as r
                WHERE ((r.ano = '$ano') AND (r.cod_organizacion='$cod_organizacion') AND (r.codigo='$codigo_sin_descomponer'))";

       $resultado_inicial=cargar_data($sql,$sender);

        $mes-=1;// porque el saldo se toma hasta el 31 del mes anterior
        
       if($mes!="0"){
         //consulto retenciones realizadas a ordenes de pago segun retencion en el año hasta el mes
       $fecha_inicio="$ano/01/01";
       $fecha_fin="$ano/$mes/31";
       $sql = "select SUM(dp.monto) as monto FROM presupuesto.detalle_pagado as dp
            INNER JOIN presupuesto.maestro_pagado as m ON (m.numero=dp.numero_documento_pagado AND m.ano=dp.ano)
            where ((dp.es_retencion='1') and (m.cod_organizacion = '$cod_organizacion') and (dp.ano = '$ano') and
                  (dp.partida='$codigo[partida]' and dp.generica='$codigo[generica]' and especifica='$codigo[especifica]'
            and  dp.subespecifica='$codigo[subespecifica]' and dp.ordinal='$codigo[ordinal]')and (m.estatus_actual='NORMAL')
        and m.fecha_cheque  BETWEEN '$fecha_inicio' AND '$fecha_fin' ) ";
        $resultado_acum_ano = cargar_data($sql,$sender);


        //consulto retenciones pagadas realizadas a ordenes de pago segun retencion en el año
       $fecha_inicio="$ano/01/01";
       $fecha_fin="$ano/$mes/31";
       $sql = " SELECT SUM(rp.monto) as monto FROM presupuesto.retencion_pagado as rp
                INNER JOIN presupuesto.cheques as c ON (c.cod_movimiento=rp.cod_movimiento AND c.ano=rp.ano AND c.estatus_actual='NORMAL')
                INNER JOIN presupuesto.bancos_cuentas_movimientos as m ON (m.cod_movimiento=rp.cod_movimiento AND m.ano=rp.ano)
                WHERE (rp.codigo_retencion='$codigo_sin_descomponer' AND m.fecha  BETWEEN '$fecha_inicio' AND '$fecha_fin' ) ";
        $resultado_acum_ano_pagado = cargar_data($sql,$sender);

        $acum=abs($resultado_acum_ano[0]['monto'])-$resultado_acum_ano_pagado[0]['monto'];
        }//fin si

        return(  $resultado_inicial[0]['saldo_inicial']+$acum);

}


/* Esta función se encarga de realizar las sumas y restas en los montos de los
 * acumulados de los presupuestos de gastos
 * PERO SÓLO PARA LAS CONSULTAS DE EJECUCIONES TEMPORALES POR FECHA, PARA LO CUAL
 * SE AFECTA SOLO LA TABLA PRESUPUESTO_GASTO_EJECUCION_TEMPORAL.
 * BAJO NINGUNA CIRCUNSTANCIA ESTE PROCEDIMIENTO DEBE INTERFERIR CON LA EJECUCIÓN
 * ACTUAL DEL PRESUPUESTO.
 * $cod : codigo presupuestario
 * $tipo_operacion : suma: "+", resta: "-"
 */
    function modificar_acumulados_presupuesto_gasto_temporal($cod, $cod_organizacion, $ano, $campo, $tipo_operacion, $monto, $sender)
    {
        for ($xcod = 9 ; $xcod >= 1 ; $xcod--)
        {
            switch($xcod)
            {
                case 1:
                    $cod['programa']="00";
                    break;
                case 2:
                    $cod['subprograma']="00";
                    break;
                case 3:
                    $cod['proyecto']="00";
                    break;
                case 4:
                    $cod['actividad']="00";
                    break;
                case 5:
                    $cod['partida']="000";
                    break;
                case 6:
                    $cod['generica']="00";
                    break;
                case 7:
                    $cod['especifica']="00";
                    break;
                case 8:
                    $cod['subespecifica']="00";
                    break;
                case 9:
                    $cod['ordinal']="00000";
                    break;
            } // del switch

           $sql = "select * from presupuesto.presupuesto_gastos_ejecucion_temporal
                        where ((cod_organizacion = '$cod_organizacion') and
                                (ano = '$ano') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                               (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                               (ordinal = '$cod[ordinal]'))";
           $existe = cargar_data($sql,$sender);
           $id = $existe[0]['id'];

           if (empty($existe) == false)
           {
               if ($id_acumulado != $id)
               {
                    $id_acumulado = $id;
                    $sql2 = "update presupuesto.presupuesto_gastos_ejecucion_temporal set
                          $campo = $campo $tipo_operacion '$monto' where id = '$id'";
                    $resultado=modificar_data($sql2,$sender);
               }
           }
        } // del for xcod
    }


/* Esta función modifica los datos de los montos pendientes en los compromisos
 * y causados registrados en el sistema.
 */
    function modificar_montos_pendientes($tipo, $cod, $cod_organizacion, $ano, $tipo_documento, $numero, $cod_proveedor, $monto, $sender)
    {
            switch($tipo)
            {
                case 'CO':
                    // se actualizan los datos del maestro_compromisos
                    $sql = "select id from presupuesto.maestro_compromisos
                                where ((cod_organizacion = '$cod_organizacion') and
                                       (ano = '$ano') and (tipo_documento = '$tipo_documento') and (numero = '$numero') and
                                       (cod_proveedor = '$cod_proveedor'))";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $sql2 = "update presupuesto.maestro_compromisos set
                              monto_pendiente = monto_pendiente - '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }

                    // se actualizan los datos del detalle_compromisos
                    $sql = "select * from presupuesto.detalle_compromisos
                                where ((cod_organizacion = '$cod_organizacion') and
                                        (ano = '$ano') and (tipo_documento = '$tipo_documento') and (numero = '$numero') and
                                        (cod_proveedor = '$cod_proveedor') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                        (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                        (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                                       (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                                       (ordinal = '$cod[ordinal]'))";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $id_acumulado = $id;
                        $sql2 = "update presupuesto.detalle_compromisos set
                              monto_pendiente = monto_pendiente - '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }
                break;
                case 'CA':
                    // se actualizan los datos del maestro_causado
                    $sql = "select id from presupuesto.maestro_causado
                                where ((cod_organizacion = '$cod_organizacion') and
                                       (ano = '$ano') and (tipo_documento = '$tipo_documento') and (numero = '$numero')
                                       )";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $sql2 = "update presupuesto.maestro_causado set
                              monto_pendiente = monto_pendiente - '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }
 // se actualizan los datos del compromiso_causado
                    $sql = "select * from presupuesto.compromiso_causado
                                where ((cod_organizacion = '$cod_organizacion') and
                                        (ano = '$ano') and (tipo_documento_causado = '$tipo_documento') and (numero_documento_causado = '$numero')
                                        and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                        (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                        (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                                       (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                                       (ordinal = '$cod[ordinal]'))";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $id_acumulado = $id;
                        $sql2 = "update presupuesto.compromiso_causado set
                              monto_pendiente = monto_pendiente - '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }
                    break;
                case 'PA':
                    break;
            } // del switch
    }



/* Esta función modifica los datos de los montos pendientes en los compromisos de forma genérica sumando (+) o restando (-)
 * y causados registrados en el sistema.
 */
    function modificar_montos_pendientes_2($tipo, $cod, $cod_organizacion, $ano, $tipo_documento, $numero, $cod_proveedor, $monto,$operacion, $sender)
    {
            switch($tipo)
            {
                case 'CO':
                    // se actualizan los datos del maestro_compromisos
                    $sql = "select id from presupuesto.maestro_compromisos
                                where ((cod_organizacion = '$cod_organizacion') and
                                       (ano = '$ano') and (tipo_documento = '$tipo_documento') and (numero = '$numero') and
                                       (cod_proveedor = '$cod_proveedor'))";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $sql2 = "update presupuesto.maestro_compromisos set
                              monto_pendiente = monto_pendiente $operacion '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }

                    // se actualizan los datos del detalle_compromisos
                    $sql = "select * from presupuesto.detalle_compromisos
                                where ((cod_organizacion = '$cod_organizacion') and
                                        (ano = '$ano') and (tipo_documento = '$tipo_documento') and (numero = '$numero') and
                                        (cod_proveedor = '$cod_proveedor') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                        (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                        (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                                       (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                                       (ordinal = '$cod[ordinal]'))";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $id_acumulado = $id;
                        $sql2 = "update presupuesto.detalle_compromisos set
                              monto_pendiente = monto_pendiente $operacion '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }
                break;

                case 'CH':
                     // se actualizan los datos del maestro_causado
                    $sql = "select id from presupuesto.maestro_causado
                                where ((cod_organizacion = '$cod_organizacion') and
                                       (ano = '$ano') and (numero = '$numero') and
                                       (cod_proveedor = '$cod_proveedor'))";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $sql2 = "update presupuesto.maestro_causado set
                              monto_pendiente = monto_pendiente $operacion '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }

                    $sql = "select * from presupuesto.compromiso_causado
                                where ((cod_organizacion = '$cod_organizacion') and
                                        (ano = '$ano') and (numero_documento_causado = '$numero')
                                        and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                        (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                        (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                                       (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                                       (ordinal = '$cod[ordinal]'))";
                    $existe = cargar_data($sql,$sender);
                    $id = $existe[0]['id'];

                    if (empty($existe) == false)
                    {
                        $id_acumulado = $id;
                        $sql2 = "update presupuesto.compromiso_causado set
                              monto_pendiente = monto_pendiente $operacion '$monto' where id = '$id'";
                        $resultado=modificar_data($sql2,$sender);
                    }
                break;

            } // del switch
    }



/* Esta función se encarga de generar todos los codigos presupuestarios acumulados
 * del codigo presupuestario pasado como parámetro. Si el codigo no existe, se crea
 * y si existe, se acumulan sus valores.
 */
    function generar_acumulados_presupuesto_gasto($ano,$cod_organizacion,$cod,$monto,$retencion)
    {
     //  $cod_organizacion = usuario_actual('cod_organizacion');
     //  $ano = $this->drop_ano->selectedValue;
     $existe = 'Valor de Inicio';
        for ($xcod = 9 ; $xcod >= 1 ; $xcod--)
        {
            switch($xcod)
            {
                case 1:
                    $cod['programa']="00";
                    break;
                case 2:
                    $cod['subprograma']="00";
                    break;
                case 3:
                    $cod['proyecto']="00";
                    break;
                case 4:
                    $cod['actividad']="00";
                    break;
                case 5:
                    $cod['partida']="000";
                    break;
                case 6:
                    $cod['generica']="00";
                    break;
                case 7:
                    $cod['especifica']="00";
                    break;
                case 8:
                    $cod['subespecifica']="00";
                    break;
                case 9:
                    $cod['ordinal']="00000";
                    break;
            }

           $sql = "select * from presupuesto.presupuesto_gastos
                        where ((cod_organizacion = '$cod_organizacion') and
                                (ano = '$ano') and (sector = '$cod[sector]') and (programa = '$cod[programa]') and
                                (subprograma = '$cod[subprograma]') and (proyecto = '$cod[proyecto]') and (actividad = '$cod[actividad]') and
                                (partida = '$cod[partida]') and (generica = '$cod[generica]') and
                               (especifica = '$cod[especifica]') and (subespecifica = '$cod[subespecifica]') and
                               (ordinal = '$cod[ordinal]'))";
           $existe = cargar_data($sql,$this);
           $id = $existe[0]['id'];

           if (empty($existe) == true)
           {
               // si el codigo no existe, se incluyen los acumulados de todos los codigos
                $sql2 = "insert into presupuesto.presupuesto_gastos
                        (cod_organizacion, ano, sector, programa, subprograma, proyecto, actividad, partida,
                         generica, especifica, subespecifica, ordinal, asignado, disponible, descripcion, acumulado, es_retencion)
                        values ('$cod_organizacion','$ano','$cod[sector]','$cod[programa]','$cod[subprograma]','$cod[proyecto]',
                                '$cod[actividad]','$cod[partida]','$cod[generica]','$cod[especifica]',
                                '$cod[subespecifica]','$cod[ordinal]','$monto','$monto','$descripcion','1','$retencion')";
                $resultado=modificar_data($sql2,$this);
                $sql3 = "select max(id) as id from presupuesto.presupuesto_gastos";
                $result_id = cargar_data($sql3,$this);
                $id_incluido = $result_id[0]['id'];
           }
           else
           {
               if (($existe[0]['acumulado'] == '1') && ($id_acumulado != $id) && ($id_incluido != $id))
               {
                   $id_acumulado = $id;
                   // si el codigo ya existe, se suman los acumulados en dicho código.
                   $sql2 = "update presupuesto.presupuesto_gastos set
                           asignado = asignado+'$monto', disponible = disponible+'$monto' where id = '$id'";
                   $resultado=modificar_data($sql2,$this);
               }
           }
        }
    }




/* Esta función devuelve tanto las organizaciones como las direcciones a las que
 * el usuario (cedula) tiene permiso de ver.
 * la estructura que devuelve es
 * codigo       nombre
 * ------       ------
 * 000001       Nombre de la Organizacion
 * 000002       Nombre de la Organizacion 2
 */
function vista_usuario($cedula,$cod_organizacion,$requerimiento,$sender)
{
    switch($requerimiento)
    {
        case 'D':
            $sql="Select distinct v.cod_direccion as codigo, d.nombre_completo as nombre
                  from intranet.usuarios_vistas v, organizacion.direcciones d
                  where ((v.cedula = '$cedula') and (d.codigo_organizacion = v.cod_organizacion)
                  and (v.cod_organizacion = '$cod_organizacion') and (d.codigo = v.cod_direccion)) ";
            $listado=cargar_data($sql,$sender);
            break;
        case 'O':
            $sql="SELECT DISTINCT v.cod_organizacion as codigo, o.nombre
                FROM intranet.usuarios_vistas v, organizacion.organizaciones o
                WHERE (v.cedula = '$cedula') AND (v.cod_organizacion = o.codigo)";
            $listado=cargar_data($sql,$sender);
            break;
    }
return $listado;
}


function cargar_direcciones($cod_organizacion,$requerimiento,$sender)
{
    switch($requerimiento)
    {
        case 'D':
            $sql="Select d.codigo as codigo, d.nombre_completo as nombre
                  from organizacion.direcciones d
                  where (d.codigo_organizacion = '$cod_organizacion')";
            $listado=cargar_data($sql,$sender);
            break;
    /*    case 'O':
            $sql="SELECT DISTINCT v.cod_organizacion as codigo, o.nombre
                FROM intranet.usuarios_vistas v, organizacion.organizaciones o
                WHERE (v.cedula = '$cedula') AND (v.cod_organizacion = o.codigo)";
            $listado=cargar_data($sql,$sender);
            break;*/
    }
return $listado;
}


//obtiene el nombre de la direccion dado un codigo de organizacion y un codigo de direccion
function obtener_direccion($sender,$cod_org,$cod_dir)
{
$sql="Select * from organizacion.direcciones
                  where (codigo_organizacion = '$cod_org' and codigo='$cod_dir')";
            $direccion=cargar_data($sql,$sender);
            return $direccion;
}



/* Esta función se encarga de realizar el descuento de los días que sean necesarios
 * si aplica un horario navideño cuando se aprueban solicitudes de vacaciones.
 * El procedimiento simplemente descuenta días de vacaciones pendientes de los
 * que tengan disponibles los períodos.
 */
 function descuento_especial_de_dias($restar,$cedula_restar,$sender)
{
 while ($restar > 0)
   {
     $sql2="select * from asistencias.vacaciones as v
            where((v.cedula='$cedula_restar') and (v.pendientes>'0'))
            order by v.pendientes";
     $result_descuento=cargar_data($sql2,$sender);

	 $periodo_a_restar=$result_descuento[0]['periodo']; // para saber el periodo que se esta tomando para restar
	 if ($result_descuento[0]['pendientes'] >= $restar)
	   { // si se puede descontar todos los dias se descuentan los dias del primer periodo que se haya seleccionado.
		 $valor_a_restar = $restar;
		 $restar = 0;
	   }
	 else
	   { // si no se pueden descontar todos los dias, se descuentan los que se puedan y se deja correr el ciclo.
		 $valor_a_restar = $result_descuento[0]['pendientes'];
		 $restar=$restar-$valor_a_restar;
	   }
	   // ejecuto la consulta para restar los dias que se haya decidido restar del periodo seleccionado.
        $sql="update asistencias.vacaciones set pendientes=pendientes-'$valor_a_restar',
                restados=restados+'$valor_a_restar'
                where cedula='$cedula_restar' and periodo='$periodo_a_restar'";
        $resultado=modificar_data($sql,$sender);
   } // del while
} // de la funci�n.


 /* Esta función actualiza el porcentaje de avance de los  */
    function actualizar_avance_planes($cod_actividad, $cod_obj_especifico, $cod_plan_operativo,$sender)
    {

        // Se actualizan los porcentajes de los objetivos específicos
        $sql="select COUNT(id) as numero from planificacion.actividades
              where (cod_objetivo_especifico = '$cod_obj_especifico')";
        $resultado=cargar_data($sql,$sender);
        $na = $resultado[0]['numero'];

        $sql="select COUNT(id) as numero from planificacion.actividades
              where (estatus = 'Finalizado') and (cod_objetivo_especifico = '$cod_obj_especifico')";
        $resultado=cargar_data($sql,$sender);
        $af = $resultado[0]['numero'];

        if (($af == 0) || ($na == 0)) {$avance = 0;} else {$avance = ($af/$na)*100;} // para evitar division entre cero
        $avance = round($avance,2);

        $sql="UPDATE planificacion.objetivos_especificos SET porcentaje_completo = '$avance'
              WHERE (cod_objetivo_especifico = '$cod_obj_especifico')";
        $resultado=modificar_data($sql,$sender);



        // se actualizan los porcentajes de los objetivos operativos
        $sql="select cod_objetivo_operativo from planificacion.objetivos_especificos
              where (cod_objetivo_especifico = '$cod_obj_especifico')";
        $resultado=cargar_data($sql,$sender);
        $cod_obj_operativo = $resultado[0]['cod_objetivo_operativo'];

        $sql="select COUNT(id) as numero from planificacion.objetivos_especificos
              where (cod_objetivo_operativo = '$cod_obj_operativo')";
        $resultado=cargar_data($sql,$sender);
        $noo = $resultado[0]['numero'];

        $sql="select SUM(porcentaje_completo) as porcentaje from planificacion.objetivos_especificos
              where (cod_objetivo_operativo = '$cod_obj_operativo')";
        $resultado=cargar_data($sql,$sender);
        $por_avance = $resultado[0]['porcentaje'];

        $por_total = $noo * 100;

        if (($por_total == 0) || ($por_avance == 0)) {$avance = 0;} else {$avance = ($por_avance/$por_total)*100;} // para evitar division entre cero
        $avance = round($avance,2);

        $sql="UPDATE planificacion.objetivos_operativos SET porcentaje_completo = '$avance'
              WHERE (cod_objetivo_operativo = '$cod_obj_operativo')";
        $resultado=modificar_data($sql,$sender);



        // se actualizan los porcentajes de los objetivos Estratégicos
        $sql="select cod_objetivo_estrategico, cod_plan_estrategico from planificacion.objetivos_operativos
              where (cod_objetivo_operativo = '$cod_obj_operativo')";
        $resultado=cargar_data($sql,$sender);
        $cod_obj_estrategico = $resultado[0]['cod_objetivo_estrategico'];
        $cod_plan_estrategico = $resultado[0]['cod_plan_estrategico'];

        $sql="select COUNT(id) as numero from planificacion.objetivos_operativos
              where (cod_objetivo_estrategico = '$cod_obj_estrategico')";
        $resultado=cargar_data($sql,$sender);
        $noo = $resultado[0]['numero'];

        $sql="select SUM(porcentaje_completo) as porcentaje from planificacion.objetivos_operativos
              where (cod_objetivo_estrategico = '$cod_obj_estrategico')";
        $resultado=cargar_data($sql,$sender);
        $por_avance = $resultado[0]['porcentaje'];

        $por_total = $noo * 100;

        if (($por_total == 0) || ($por_avance == 0)) {$avance = 0;} else {$avance = ($por_avance/$por_total)*100;} // para evitar division entre cero
        $avance = round($avance,2);

        $sql="UPDATE planificacion.objetivos_estrategicos SET porcentaje_completo = '$avance'
              WHERE (cod_objetivo_estrategico = '$cod_obj_estrategico')";
        $resultado=modificar_data($sql,$sender);


        // se actualizan los porcentajes de los Planes Operativos
        $sql="select COUNT(id) as numero from planificacion.objetivos_operativos
              where (cod_plan_operativo = '$cod_plan_operativo')";
        $resultado=cargar_data($sql,$sender);
        $noo = $resultado[0]['numero'];

        $sql="select SUM(porcentaje_completo) as porcentaje from planificacion.objetivos_operativos
              where (cod_plan_operativo = '$cod_plan_operativo')";
        $resultado=cargar_data($sql,$sender);
        $por_avance = $resultado[0]['porcentaje'];

        $por_total = $noo * 100;

        if (($por_total == 0) || ($por_avance == 0)) {$avance = 0;} else {$avance = ($por_avance/$por_total)*100;} // para evitar division entre cero
        $avance = round($avance,2);

        $sql="UPDATE planificacion.planes_operativos SET porcentaje_completo = '$avance'
              WHERE (cod_plan_operativo = '$cod_plan_operativo')";
        $resultado=modificar_data($sql,$sender);

        // se actualizan los porcentajes de los Planes Estratégicos
        $sql="select COUNT(id) as numero from planificacion.objetivos_estrategicos
              where (cod_plan_estrategico = '$cod_plan_estrategico')";
        $resultado=cargar_data($sql,$sender);
        $noo = $resultado[0]['numero'];

        $sql="select SUM(porcentaje_completo) as porcentaje from planificacion.objetivos_estrategicos
              where (cod_plan_estrategico = '$cod_plan_estrategico')";
        $resultado=cargar_data($sql,$sender);
        $por_avance = $resultado[0]['porcentaje'];

        $por_total = $noo * 100;

        if (($por_total == 0) || ($por_avance == 0)) {$avance = 0;} else {$avance = ($por_avance/$por_total)*100;} // para evitar division entre cero
        $avance = round($avance,2);

        $sql="UPDATE planificacion.planes_estrategicos SET porcentaje_completo = '$avance'
              WHERE (cod_plan_estrategico = '$cod_plan_estrategico')";
        $resultado=modificar_data($sql,$sender);

    }


   function registrar_movimiento($ano,$cod_organizacion,$codigo_banco,$numero_cuenta,$monto,$tipo,$descripcion,$referencia,$sender,$tipo_movimiento,$fecha,$numero)
    {
    $debe=0;$haber=0;
    $criterios_adicionales=array('cod_organizacion' => $cod_organizacion);
            $numero_movimiento=proximo_numero("presupuesto.bancos_cuentas_movimientos","cod_movimiento",$criterios_adicionales,$sender);
            if($numero!="")$cod_movimiento=$numero;
            else $cod_movimiento=rellena($numero_movimiento,10,"0");


            $sql="select * from presupuesto.bancos_cuentas_movimientos where cod_organizacion='$cod_organizacion' and cod_banco='$codigo_banco' and numero_cuenta='$numero_cuenta'  order by id Desc";
            $res_saldo=cargar_data($sql,$sender);
            $saldo_actual=$res_saldo[0][saldo];
            if ($tipo=='debe')
                {$debe=$monto;
                 $saldo_actual=$saldo_actual+$monto;
                }
            else
                {$haber=$monto;
                 $saldo_actual=$saldo_actual-$monto;
                }

            $sql="insert into presupuesto.bancos_cuentas_movimientos (ano,cod_movimiento, cod_organizacion, referencia,tipo, cod_banco, numero_cuenta, debe, haber, saldo, descripcion,fecha)
                  values ('$ano','$cod_movimiento','$cod_organizacion', '$referencia','$tipo_movimiento','$codigo_banco','$numero_cuenta','$debe','$haber', '$saldo_actual', '$descripcion','$fecha')";
            $resultado=modificar_data($sql,$sender);

            //se actualiza tabla bancos_cuentas
            $sql="update presupuesto.bancos_cuentas set saldo='$saldo_actual' where cod_banco='$codigo_banco' and numero_cuenta='$numero_cuenta'";
            modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluido movimiento de $tipo_movimiento en la Cuenta: ".$numero_cuenta." en el banco ".$codigo_banco;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
}

  function registrar_cheque($ano,$cod_movimiento,$tipo_pago,$cod_proveedor,$sender)
    {

            $sql="insert into presupuesto.cheques (ano,cod_movimiento,tipo_pago,estatus_actual,cod_proveedor)
                  values ('$ano','$cod_movimiento', '$tipo_pago','NORMAL','$cod_proveedor')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluido Cheque ".$tipò_pago.": Numero de Movimiento #".$cod_movimiento." ,Año $año.";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
}

function registrar_retencion($ano,$cod_movimiento,$codigo_retencion,$monto,$sender)
    {

            // se inserta en la tabla retencion_pagado
            $sql="INSERT INTO presupuesto.retencion_pagado (ano,cod_movimiento,codigo_retencion,monto)
                  VALUES ('$ano','$cod_movimiento','$codigo_retencion','$monto')";
            $resultado=modificar_data($sql,$sender);

            /* Se incluye el rastro en el archivo de bitácora */
            $descripcion_log = "Incluida Retencion ".$codigo_retencion.": Numero de Movimiento #".$cod_movimiento." ,Año $año.";
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
}

/*Funcion que retorna la disponibilidad de una cuenta bancaria */
function disponibilidad_bancaria($ano,$cod_organizacion,$codigo_banco,$numero_cuenta,$sender)
{
$db = $sender->Application->Modules["db"]->DbConnection;
$db->Active=true;
$fecha_fin=date('Y-m-d');

 $sql="  SELECT SUM( debe - haber )as saldo FROM presupuesto.bancos_cuentas_movimientos
         WHERE fecha<='$fecha_fin'
         AND cod_banco='$codigo_banco' AND numero_cuenta='$numero_cuenta' AND cod_organizacion='$cod_organizacion' ";

//$sql="select saldo from presupuesto.bancos_cuentas_movimientos where cod_organizacion='$cod_organizacion' and cod_banco='$codigo_banco' and numero_cuenta='$numero_cuenta' and ano='$ano'  order by id Desc";
$res_saldo=cargar_data($sql,$sender);
$saldo_actual=$res_saldo[0][saldo];

return $saldo_actual;

}//fin

/*Funcion que retorna saldo inicial de un mes*/
function saldo_inicial($ano,$mes,$cod_organizacion,$codigo_banco,$numero_cuenta,$sender)
{
$db = $sender->Application->Modules["db"]->DbConnection;
$db->Active=true;

          $mes-=1;
          if($mes=="0"){$ano-=1;$mes=12;}

          $fecha_fin="$ano/$mes/31";

           $sql="  SELECT SUM( debe - haber )as saldo
                FROM presupuesto.bancos_cuentas_movimientos
                WHERE fecha<='$fecha_fin'
                AND cod_banco='$codigo_banco' AND numero_cuenta='$numero_cuenta' AND cod_organizacion='$cod_organizacion' ";

//$sql="select saldo from presupuesto.bancos_cuentas_movimientos where cod_organizacion='$cod_organizacion' and cod_banco='$codigo_banco' and numero_cuenta='$numero_cuenta' and ano='$ano'  order by id Desc";
$res_saldo=cargar_data($sql,$sender);
$saldo_actual=$res_saldo[0][saldo];

return $saldo_actual;

}//fin

/*Funcion que permite coger una cadena dada y cortarla al tamaño que le indiquemos
 * sin dejar palabras a medias. Donde $string representa la cadena de texto que queremos “resumir”
 * sin cortarla indebidamente (sin dejar palabras a medias), $limit es un entero que indica la
 * longitud mínima que debe tener la nueva cadena, $break es la cadena que indica el corte
 * (lo típico es un espacio en blanco), y $pad es el texto que se colocará al final de la cadena
 * devuelta.
 */
function myTruncate($string, $limit, $break, $pad) {
// return with no change if string is shorter than $limit
if(strlen($string) <= $limit)
return $string;
// is $break present between $limit and the end of the string?
if(false !== ($breakpoint == strpos($string, $break, $limit))) {
if($breakpoint < strlen($string) - 1) {
$string = substr($string, 0, $breakpoint) . $pad;
}
}
return $string;
}

/*funcion que elimina una justificacion de las tablas relacionadas dado el codigo*/
function eliminar_justificacion($codigo_just,$sender){
//se elimina la justificacion
            $sql="DELETE FROM asistencias.justificaciones WHERE codigo='$codigo_just'";
            $resultado=modificar_data($sql,$sender);
            $sql="DELETE FROM asistencias.justificaciones_dias WHERE codigo_just='$codigo_just'";
            $resultado=modificar_data($sql,$sender);
            $sql="DELETE FROM asistencias.justificaciones_personas WHERE codigo_just='$codigo_just'";
            $resultado=modificar_data($sql,$sender);
}
/*Suma dos horas de formato hh:mm:ss, ya sea de formato 12 o 24.
 * La hora resultante es rerornado en el mismo formato*/
function sumahoras ($hora1,$hora2){
$hora1=explode(":",$hora1);
$hora2=explode(":",$hora2);
$horas=(int)$hora1[0]+(int)$hora2[0];
$minutos=(int)$hora1[1]+(int)$hora2[1];
$segundos=(int)$hora1[2]+(int)$hora2[2];
$horas+=(int)($minutos/60);
$minutos=(int)($minutos%60)+(int)($segundos/60);
$segundos=(int)($segundos%60);

return (str_pad($horas, 2, '0', STR_PAD_LEFT).":".str_pad($minutos, 2, '0', STR_PAD_LEFT).":".str_pad($segundos, 2, '0', STR_PAD_LEFT));
}

 /* Resta dos horas de formato hh:mm:ss, ya sea de formato 12 o 24.
 * La hora resultante es rerornado en el mismo formato
  * la variable $mer(0,1) si es 1 retorna meridiano AM o PM
  */
# tiene que recibir la hora inicial y la hora final
function RestarHoras($hora1,$hora2,$mer)
{
$hora1=explode(":",$hora1);
$hora2=explode(":",$hora2);
if ($mer=="1") if($hora1[0] < 12)$mer="AM"; else $mer="PM";
$horas=(int)$hora1[0]-(int)$hora2[0];
$minutos=(int)$hora1[1]-(int)$hora2[1];
$segundos=(int)$hora1[2]-(int)$hora2[2];
$horas+=(int)($minutos/60);
$minutos=(int)($minutos%60)+(int)($segundos/60);
$segundos=(int)($segundos%60);

return (str_pad($horas, 2, '0', STR_PAD_LEFT).":".str_pad($minutos, 2, '0', STR_PAD_LEFT).":".str_pad($segundos, 2, '0', STR_PAD_LEFT)." ".$mer);
}
?>