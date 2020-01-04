<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: En esta página incluyen compromisos al presupuesto de gastos
 *              de la institución para el año en curso.
 *****************************************************  FIN DE INFO
*/
class incluir_compromiso extends TPage
{
     

    public function onLoad($param)
    {
        
        parent::onLoad($param);
       
       
        if(!$this->IsPostBack)
          {
             
              $this->lbl_organizacion->Text = usuario_actual('nombre_organizacion');
              $this->lbl_codigo_temporal->Text = aleatorio_compromisos($this);
              $cod_organizacion = usuario_actual('cod_organizacion');
              // para llenar el listado del Tipo de Documento
              $sql = "select nombre, siglas from presupuesto.tipo_documento
                    where ((cod_organizacion = '$cod_organizacion') and (operacion = 'CO'))";
              $datos = cargar_data($sql,$this);
              $this->drop_tipo->Datasource = $datos;
              $this->drop_tipo->dataBind();

              // para llenar el listado de Beneficiarios
              $sql2 = "select cod_proveedor, CONCAT(nombre,' / ',rif) as nomb from presupuesto.proveedores
                    where (cod_organizacion = '$cod_organizacion') order by nombre";
              $datos2 = cargar_data($sql2,$this);
              $this->drop_proveedor->Datasource = $datos2;
              $this->drop_proveedor->dataBind();

              $codigo = codigo_unidad_ejecutora($this);
              if (empty($codigo) == false)
              { $this->txt_codigo->Mask = $codigo."-###-##-##-##-#####"; }

              // coloca el año presupuestario "Presente"
              $ano = ano_presupuesto($cod_organizacion,1,$this);
              //if ($ano!=null
              $this->lbl_ano->Text = $ano[0]['ano'];

              // coloca la fecha
              $this->lbl_fecha->Text = date("d/m/Y");
              //$this->txt_fecha->Text=date('d/m/Y');
              $this->lbl_total->Text="0.00";

              $this->actualiza_listado();
          }
    }
 
 /*Esta funcion actualiza la pagina si se cambia el select tipo*/
 public function refrescar($sender,$param) {
     $cod_organizacion = usuario_actual('cod_organizacion');
     $ano=$this->lbl_ano->Text;
     $aleatorio = $this->lbl_codigo_temporal->Text;

        $sql="DELETE FROM presupuesto.temporal_compromisos WHERE numero='$aleatorio' AND ano='$ano'
            AND cod_organizacion='$cod_organizacion' ";
        $resultado=modificar_data($sql,$this);
        $sql="DELETE FROM presupuesto.temporal_articulos_compromisos WHERE numero='$aleatorio' AND ano='$ano'
            AND cod_organizacion='$cod_organizacion' ";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();

//$this->Response->redirect($this->Service->constructUrl('presupuesto.compromisos.incluir_compromiso'));

    }
/* Esta muestra una consulta filtrada por la cadena ingresada en el text de articulos
 *
 */
    public function articulos_sugeridos($sender,$param) {
        $cod_organizacion = usuario_actual('cod_organizacion');
         // para llenar el listado de articulos
        $articulo=$this->AutoComplete_articulos->Text;
        $sql = "select cod as id,descripcion as name from presupuesto.articulos
                    where (cod_organizacion = '$cod_organizacion') and (descripcion LIKE  '$articulo%')order by Descripcion";
        $datos = cargar_data($sql,$this);

        $sender->DataSource=$datos;// se carga en el data
        $sender->dataBind();
    }
/* Esta función verifica la existencia del código del articulo en la tabla
 * de articulos, de no existir, se genera uno nuevo.
 */
     public function sugerido_selecionado($sender,$param) {
        $id=$sender->Suggestions->DataKeys[ $param->selectedIndex ];
        $descripcion= $this->AutoComplete_articulos->Text;
        $cod_organizacion = usuario_actual('cod_organizacion');
        $this->lbl_articulo->Text=$id;// se asigna en una lbl el codigo
    }

  
/* Esta función valida la existencia del código presupuestario en la tabla
 * de presupuestos, de no existir, se muestra un mensaje de error.
 */
    public function validar_existencia($sender, $param)
    {
       $ano = $this->lbl_ano->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $sql = "select * from presupuesto.presupuesto_gastos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                            (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
                            (ordinal = '$codigo[ordinal]') and (acumulado = '0'))";
       $existe = cargar_data($sql,$this);
       $param->IsValid = !(empty($existe));
       //$param->IsValid;
    }

/* Esta función valida la existencia del código presupuestario en la tabla
 * de temporal_compromiso, .
 */
    public function verifica_existencia_temporal_compromiso($cod)
    {
       $ano = $this->lbl_ano->Text;
       $numero = $this->lbl_codigo_temporal->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_gasto(rellena_derecha($cod,'24','0'));
       $sql = "select * from presupuesto.temporal_compromisos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                            (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
                            (ordinal = '$codigo[ordinal]')and numero='$numero')";
       $existe = cargar_data($sql,$this);
       return(empty($existe));

    }

    public function actualiza_fecha($sender, $param)
    {
    $this->lbl_fecha->Text=$this->txt_fecha->Text;
    }

/* Esta función valida que el monto a comprometer tenga disponibilidad */
    public function validar_monto($sender, $param)
    {
       $param->IsValid = true;
       $ano = $this->lbl_ano->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $monto = $this->txt_monto->Text;
       $cantidad=$this->txt_cantidad->Text;
       //$monto=$monto*$cantidad; se debe multiplicar el pero sigue dando error 'insuficiente' cuando el error es de la cantidad
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $sql = "select * from presupuesto.presupuesto_gastos
                    where ((cod_organizacion = '$cod_organizacion') and
                            (ano = '$ano') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                            (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                            (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                            (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
                            (ordinal = '$codigo[ordinal]') and (acumulado = '0')) ";
       $registro = cargar_data($sql,$this);
       
       if ($registro[0]['disponible']>= ($monto*$cantidad) )   
       {
           $param->IsValid = true;
       }
       else
       {
           $param->IsValid = false;
       }
    }

/* Esta función valida que codigo introducido sea diferente a los existentes en la table temporal_compromisos */
    /* revisar esta funcion pq puede tener el mismo codigo presupuestario 2 veces en laa misma orden*/
    public function validar_codigo($sender, $param)
    {

       $ano = $this->lbl_ano->Text;
       $cod_organizacion = usuario_actual('cod_organizacion');
       $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
       $arreglo=array('sector'=>$codigo[sector],'programa'=>$codigo[programa],'subprograma'=>$codigo[subprograma],'proyecto'=>$codigo[proyecto],'actividad'=>$codigo[actividad],'partida'=>$codigo[partida],'generica'=>$codigo[generica],'especifica'=>$codigo[especifica],'subespecifica'=>$codigo[subespecifica],'ordinal'=>$codigo[ordinal]);//arreglo para los datos a verificar

      $param->IsValid=verificar_existencia('presupuesto.temporal_compromisos','ano',$ano,$arreglo,$sender);
      //Si el usuario registro un codigo y despues se salio de la pantalla queda registrado
      //en el temporal_compromisos por lo que no funciona con esta funcion
      $param->IsValid=True;
      //revisar xq no funciona
     //$numero=$this->lbl_codigo_temporal->Text;
     // $param->IsValid=verificar_existencia_doble('presupuesto.temporal_compromisos','ano',$ano,'numero',$numero,$arreglo,$sender);

    
    }

/* Esta funcion actualiza el listado de los codigos presupuestarios que se
 * muestran en el ActiveDataGrid.
 */
    function actualiza_listado()
    {
        // se actualiza el listado
        $cod_organizacion = usuario_actual('cod_organizacion');
        $numero = $this->lbl_codigo_temporal->Text;

        $sql = "select ac.id, CONCAT(ac.sector,'-',ac.programa,'-',ac.subprograma,'-',ac.proyecto,'-',ac.actividad,'-',ac.partida,'-',
                            ac.generica,'-',ac.especifica,'-',ac.subespecifica,'-',ac.ordinal) as codigo,
              ac.monto_parcial,a.descripcion,ac.cantidad,ac.unidad,ac.precio_unitario from presupuesto.temporal_articulos_compromisos ac
              inner join presupuesto.articulos a on (a.cod=ac.articulo)
              where ((ac.cod_organizacion = '$cod_organizacion') and (ac.numero = '$numero'))
              order by codigo";
        $datos = cargar_data($sql,$this);
        if (empty($datos))
            $this->btn_incluir->Enabled=False;
        $this->DataGridArticulos->Datasource = $datos;
        $this->DataGridArticulos->dataBind();
        // si no hay nada que listar, se deshabilita el boton incluir
        //$this->btn_incluir->Enabled = !(empty($datos));
        $sql = "select id, CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo,
              monto_parcial from presupuesto.temporal_compromisos
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))
              order by codigo";
        $datos2 = cargar_data($sql,$this);
        $this->DataGrid->Datasource = $datos2;
        $this->DataGrid->dataBind();
        // se actualiza la sumatoria total
        $sql = "select SUM(monto_parcial) as total from presupuesto.temporal_compromisos
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$numero'))";
        $datos = cargar_data($sql,$this);
        // para evitar que se muestre null cuando viene vacio.
        if (empty($datos[0]['total']) == false)
        { $this->lbl_total->Text=$datos[0]['total'];}
        else
        { $this->lbl_total->Text="0.00";}
    }

/* esa funcion añade el codigo presupuestario en la tabla temporal con el fin
 * de conservarlo ahí hasta que se incluya definitivamente en la orden.
 */
    function anadir_click($sender, $param)
    {
        if ($this->IsValid)
        {
            $codigo = descomponer_codigo_gasto(rellena_derecha($this->txt_codigo->Text,'24','0'));
            $numero = $this->lbl_codigo_temporal->Text;
            $ano=$this->lbl_ano->Text;
            $tipo_documento = $this->drop_tipo->SelectedValue;
            $cod_proveedor=$this->drop_proveedor->SelectedValue;
            $cod_organizacion = usuario_actual('cod_organizacion');
            $precio_unitario=$this->txt_monto->Text;
            $cantidad=$this->txt_cantidad->Text;
            $unidad=$this->txt_unidad->Text;
            $monto = $this->txt_monto->Text*$this->txt_cantidad->Text;

           //$articulo=$this->drop_articulo->SelectedValue;

            // se verifica si existe articulo sino se crea
            $descripcion= $this->AutoComplete_articulos->Text;

            $arreglo=array('cod_organizacion'=>$cod_organizacion);//arreglo para los datos a verificar
            if(verificar_existencia('presupuesto.articulos','descripcion',$descripcion,$arreglo,$sender)==True)
           {// si no existe   
             $cod_articulo=codigo_aleatorio("10","presupuesto.articulos","cod",$this);
            // se inserta en la base de datos
            $sql = "insert into presupuesto.articulos
                    (cod,ano,cod_organizacion,descripcion, tipo,precio)
                    values ('$cod_articulo','$ano','$cod_organizacion','".strtoupper($descripcion)."','1','$precio_unitario')";
            $resultado=modificar_data($sql,$sender);

            $articulo=$cod_articulo;
           // $this->mensaje->setSuccessMessage($sender, "descripcion= $descripcion - id= ".$this->lbl_articulo->Text, 'grow');

           }else{// si existe se asigna el codigo oculto en label
            $sql = "select cod as id from presupuesto.articulos
                    where (cod_organizacion = '$cod_organizacion') and (descripcion LIKE '$descripcion%')order by Descripcion";
            $datos = cargar_data($sql,$this);
            $articulo=$datos[0]['id'];
           
          //  $this->mensaje->setSuccessMessage($sender, "descripcion= $descripcion - id= ".$this->lbl_articulo->Text, 'grow');

           }
            // fin se verifica si existe articulo sino se crea
           
$sql1 = "insert into presupuesto.temporal_articulos_compromisos
                        (ano, cod_organizacion, cod_proveedor, tipo_documento, numero, sector, programa, subprograma, proyecto, actividad, partida,
                         generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto_parcial, monto_pendiente,cantidad,unidad,precio_unitario,articulo)
                        values ('$ano','$cod_organizacion','$cod_proveedor','$tipo_documento','$numero','$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                                '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]',
                                '$codigo[subespecifica]','$codigo[ordinal]','00','$monto','$monto','$cantidad','$unidad','$precio_unitario','$articulo')";
$resultado1=modificar_data($sql1,$sender);//inserta en temporal_articulos_compromisos


        if (!$this->verifica_existencia_temporal_compromiso($this->txt_codigo->Text))
            $sql="Update presupuesto.temporal_compromisos set monto_parcial=monto_parcial+'$monto',monto_pendiente=monto_pendiente+'$monto'
                where ((cod_organizacion = '$cod_organizacion') and
                (ano = '$ano')and(numero=$numero) and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
                (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
                (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
                (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
                (ordinal = '$codigo[ordinal]'))";
        else
            $sql = "insert into presupuesto.temporal_compromisos
                (ano, cod_organizacion,cod_proveedor, tipo_documento, numero, sector, programa, subprograma, proyecto, actividad, partida,
                 generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto_parcial, monto_pendiente,monto_reversos)
                values ('$ano','$cod_organizacion','$cod_proveedor','$tipo_documento','$numero','$codigo[sector]','$codigo[programa]','$codigo[subprograma]','$codigo[proyecto]',
                        '$codigo[actividad]','$codigo[partida]','$codigo[generica]','$codigo[especifica]','$codigo[subespecifica]','$codigo[ordinal]','00','$monto','$monto',0)";

$resultado=modificar_data($sql,$sender);
$this->actualiza_listado();
            $this->btn_incluir->Enabled=true;

            // esto borra los datos de los controles de codigo y monto, no es muy
            // elegante pero funciona.  Lo que no funciona es el envio del foco al control de codigo.
            $this->txt_monto->Text="0.00";
            $this->txt_unidad->Text="";
            $this->txt_cantidad->Text="1";
            $this->lbl_articulo->Text="";
            $this->AutoComplete_articulos->Text="";
            //$this->txt_articulo->Text='';
            $cod = codigo_unidad_ejecutora($this);
            if (empty($cod) == false)
            {
                //$this->txt_codigo->Mask = $cod."-###-##-##-##-#####";
                $this->txt_codigo->Text=$cod."-___-__-__-__-_____";
                //$this->txt_codigo->Text="";
            }
            else
            {
               /* $this->txt_codigo->Text="";
                //$this->txt_codigo->Mask = $cod."##-##-##-##-##-###-##-##-##-#####";
                //$this->txt_codigo->Text=$codigo."__-__-__-__-__-___-__-__-__-_____";*/
            }

            $this->setFocus($this->txt_codigo);

        }
    }
/* esta funcion elimina un codigo presupuestario del listado de articulos*/
	public function delete_articulo($sender,$param)
	{

        $item=$param->Item;
		$id=$this->DataGridArticulos->DataKeys[$param->Item->ItemIndex];

        $cod=$item->codigo_articulo->Text;
        $monto=$item->monto_articulo->Text;
        $codigo=descomponer_codigo_gasto(rellena_derecha($cod,'24','0'));
        $numero=$this->lbl_codigo_temporal->Text;
        $cod_organizacion=usuario_actual('cod_organizacion');
        $ano=$this->lbl_ano->Text;
        //$this->txt_motivo->Text=$codigo;
        $sql="DELETE FROM presupuesto.temporal_articulos_compromisos WHERE id='$id'";
        $resultado=modificar_data($sql,$this);

        $sql="select monto_parcial from presupuesto.temporal_compromisos
        where ((cod_organizacion = '$cod_organizacion') and
        (ano = '$ano')and(numero=$numero) and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
        (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
        (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
        (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
        (ordinal = '$codigo[ordinal]'))";
        $resul_monto=cargar_data($sql,$sender);
        
    if ($resul_monto[0][monto_parcial]>$monto)

    $sql="Update presupuesto.temporal_compromisos set monto_parcial=monto_parcial-'$monto',monto_pendiente=monto_pendiente-'$monto'
        where ((cod_organizacion = '$cod_organizacion') and
        (ano = '$ano')and(numero=$numero) and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
        (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
        (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
        (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
        (ordinal = '$codigo[ordinal]'))";
else
    $sql = "delete from presupuesto.temporal_compromisos
        where ((cod_organizacion = '$cod_organizacion') and
        (ano = '$ano')and(numero='$numero') and (sector = '$codigo[sector]') and (programa = '$codigo[programa]') and
        (subprograma = '$codigo[subprograma]') and (proyecto = '$codigo[proyecto]') and (actividad = '$codigo[actividad]') and
        (partida = '$codigo[partida]') and (generica = '$codigo[generica]') and
        (especifica = '$codigo[especifica]') and (subespecifica = '$codigo[subespecifica]') and
        (ordinal = '$codigo[ordinal]'))";
$resultado=modificar_data($sql,$this);
$this->actualiza_listado();
	}


/* esta funcion elimina un codigo presupuestario del listado */
	public function eliminar($sender,$param)
	{
		$id=$this->DataGrid->DataKeys[$param->Item->ItemIndex];
        $sql="DELETE FROM presupuesto.temporal_compromisos WHERE id='$id'";
        $resultado=modificar_data($sql,$this);
        $this->actualiza_listado();
	}



    function incluir_click($sender, $param)
    {

        if (($this->IsValid)&&( $this->btn_incluir->Enabled))
        {
           
            $this->btn_incluir->Enabled=False;//Se deshabilita boton incluir
            $this->anadir->Enabled=False;//Se deshabilita boton añadir
            // captura de datos desde los controles
            $cod_organizacion = usuario_actual('cod_organizacion');
            $ano=$this->lbl_ano->Text;
            $fecha=cambiaf_a_mysql($this->lbl_fecha->Text);
            $aleatorio = $this->lbl_codigo_temporal->Text;
            $tipo_documento = $this->drop_tipo->SelectedValue;
            
            $criterios_adicionales=array('cod_organizacion' => $cod_organizacion,
                                          'tipo_documento' => $tipo_documento,
                                          'ano' => $ano);

       //validar disponibilidad codigos
  
               $sql = "select pg.disponible,tc.monto_parcial, CONCAT(tc.sector,'-',tc.programa,'-',tc.subprograma
                        ,'-',tc.proyecto,'-',tc.actividad,'-',tc.partida,'-',tc.generica,'-',tc.especifica,'-',tc.subespecifica,'-',tc.ordinal) as codigo
                from presupuesto.presupuesto_gastos pg inner join presupuesto.temporal_compromisos as tc on( (pg.sector = tc.sector) and (pg.programa = tc.programa) and
                                    (pg.subprograma = tc.subprograma) and (pg.proyecto = tc.proyecto) and (pg.actividad = tc.actividad) and
                                    (pg.partida = tc.partida) and (pg.generica = tc.generica) and
                                    (pg.especifica = tc.especifica) and (pg.subespecifica = tc.subespecifica) and
                                    (pg.ordinal = tc.ordinal) and (acumulado = '0'))
                            where ((pg.cod_organizacion = '$cod_organizacion') and
                                    (pg.ano = '$ano') and tc.numero='$aleatorio') ";
               $registro = cargar_data($sql,$this);
                // analizamos codigo por codigo para cargar en var mensaje si noa hay disponibilidad en algun codigo
                $error=false;
                foreach ($registro as $undato)
                {
                        if ($undato['disponible'] < $undato['monto_parcial'])
                      {
                           $mensaje.=" Codigo $undato[codigo] Disponibilidad Actual Bs.$undato[disponible] </br> ";
                           $error=true;
                       }

                }

                if ($error){//si no hay disponibilidad en una partida
                $this->mensaje->setErrorMessage($sender, "¡Disponibilidad Insuficiente!</br>".$mensaje, 'grow');
                 }else{//si existe disponibilidad
        
      //     var_dump($criterios_adicionales);
            $numero=proximo_numero("presupuesto.maestro_compromisos","numero",$criterios_adicionales,$this);
            $numero=rellena($numero,6,"0");
            $cod_proveedor=$this->drop_proveedor->SelectedValue;
            $motivo=$this->txt_motivo->Text;
            $monto_total=$this->lbl_total->Text;

            // inserción en la base de datos de la información del maestro.
            $sql = "insert into presupuesto.maestro_compromisos
                    (cod_organizacion, ano, fecha, tipo_documento, numero, cod_proveedor, motivo, monto_total, monto_pendiente, estatus_actual)
                    values ('$cod_organizacion','$ano','$fecha','$tipo_documento','$numero','$cod_proveedor','$motivo','$monto_total','$monto_total','NORMAL')";
            $resultado=modificar_data($sql,$sender);

            // inclusión en la base de datos articulos_compromisos
            $sql = "insert into presupuesto.articulos_compromisos
                    (ano, cod_organizacion, cod_proveedor, tipo_documento, numero, sector, programa, subprograma, proyecto, actividad, partida,
                     generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto_parcial, monto_pendiente,articulo,cantidad,unidad,precio_unitario)
	   					select t.ano, t.cod_organizacion, '$cod_proveedor', t.tipo_documento, t.numero, t.sector, t.programa, t.subprograma, t.proyecto, t.actividad, t.partida,
                     t.generica, t.especifica, t.subespecifica, t.ordinal, t.cod_fuente_financiamiento, t.monto_parcial, t.monto_pendiente,t.articulo,t.cantidad,t.unidad,t.precio_unitario from presupuesto.temporal_articulos_compromisos as t where (t.numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            // inclusión en la base de datos del detalle compromisos
            $sql = "insert into presupuesto.detalle_compromisos
                    (ano, cod_organizacion, cod_proveedor, tipo_documento, numero, sector, programa, subprograma, proyecto, actividad, partida,
                     generica, especifica, subespecifica, ordinal, cod_fuente_financiamiento, monto_parcial, monto_pendiente)
	   					select t.ano, t.cod_organizacion, '$cod_proveedor', t.tipo_documento, t.numero, t.sector, t.programa, t.subprograma, t.proyecto, t.actividad, t.partida,
                     t.generica, t.especifica, t.subespecifica, t.ordinal, t.cod_fuente_financiamiento, t.monto_parcial, t.monto_pendiente from presupuesto.temporal_compromisos as t where (t.numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            // antes de eliminar los de la tabla temporal, modifico los acumulados correspondientes
            $sql = "select CONCAT(sector,'-',programa,'-',subprograma,'-',proyecto,'-',actividad,'-',partida,'-',
                            generica,'-',especifica,'-',subespecifica,'-',ordinal) as codigo, monto_parcial
              from presupuesto.temporal_compromisos
              where ((cod_organizacion = '$cod_organizacion') and (numero = '$aleatorio'))
              order by codigo";
            $datos = cargar_data($sql,$this);
            if (empty($datos[0]['codigo']) == false)
            {
                foreach ($datos as $undato)
                {
                    $codi= solo_numeros($undato['codigo']);
                    $cod = descomponer_codigo_gasto($codi);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'comprometido', '+', $undato['monto_parcial'], $this);
                    modificar_acumulados_presupuesto_gasto($cod, $cod_organizacion, $ano, 'disponible', '-', $undato['monto_parcial'], $this);
                }
            }
   // Se cambia el numero aleatorio por el numero que realmente que debe tener en articulos_compromisos.
            $sql = "update presupuesto.articulos_compromisos set numero='$numero' where (numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);
            // Se cambia el numero aleatorio por el numero que realmente que debe tener.
            $sql = "update presupuesto.detalle_compromisos set numero='$numero' where (numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

              // Se eliminan los registros aleatorios que habia en las tablas temporales.
            $sql = "delete from presupuesto.temporal_compromisos where (numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);
            $sql = "delete from presupuesto.temporal_articulos_compromisos where (numero='$aleatorio')";
            $resultado=modificar_data($sql,$sender);

            //Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "Se ha incluido la orden: ".$tipo_documento." # ".$numero. " / año: ".$ano.
                               " a favor del proveedor Nro: ".$cod_proveedor.
                               " por un monto total de Bs. ".$monto_total." Motivo: ".$motivo;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            
            $this->mensaje->setSuccessMessage($sender, $this->drop_tipo->Text."-$numero guardada exitosamente!!", 'grow');
          
            $this->lbl_numero->Text=$numero;
           
            
           // $this->btn_imprimir->Enabled="True";
            //$this->Response->redirect($this->Service->constructUrl('Home'));
      
        }//fin si error 
      }
    }
    
    
    public function imprimir_item($sender,$param)
    {
       if(!$this->lbl_numero->Text==""){
        $tipo_documento = $this->drop_tipo->SelectedValue;
        $numero=$this->lbl_numero->Text;
        $ano=$this->lbl_ano->Text;
        $sql="select mc.*, td.nombre as nombre_documento, p.rif, p.nombre
              from presupuesto.maestro_compromisos mc, presupuesto.proveedores p,
                   presupuesto.tipo_documento td
              where (p.cod_proveedor = mc.cod_proveedor) and
                    (p.cod_organizacion = mc.cod_organizacion) and
                    (mc.tipo_documento = td.siglas) and
                    (mc.cod_organizacion = td.cod_organizacion) and
                    (mc.numero = '$numero')and  (mc.tipo_documento='$tipo_documento') and (ano='$ano')";

        $resultado=cargar_data($sql,$this);

        $cod_organizacion = $resultado[0]['cod_organizacion'];
        $ano = $resultado[0]['ano'];
        $tipo = $resultado[0]['tipo_documento'];
        $tipo_nombre = $resultado[0]['nombre_documento'];
        $motivo = $resultado[0]['motivo'];
        $monto_total = $resultado[0]['monto_total'];
        $fecha = cambiaf_a_normal($resultado[0]['fecha']);
        $rif =  $resultado[0]['rif'];
        $nom_beneficiario =  $resultado[0]['nombre'];

        $sql="select CONCAT(ac.partida,'-',ac.generica,'-',ac.especifica,'-',ac.subespecifica,'-',ac.ordinal) as codigo,
              ac.monto_parcial, ac.monto_pendiente, ac.monto_reversos,a.descripcion,ac.cantidad,ac.unidad,ac.precio_unitario from presupuesto.articulos_compromisos ac
              inner join presupuesto.articulos a on (a.cod=ac.articulo)
              where ((ac.cod_organizacion = '$cod_organizacion') and (ac.ano = '$ano') and
                     (ac.numero ='$numero') and (ac.tipo_documento = '$tipo')) ";
        $resultado_rpt=cargar_data($sql,$this);

        if (!empty ($resultado_rpt))
        { // si la consulta trae resultados, entonces si se imprime
           // require('../tcpdf/tcpdf.php');
             require('/var/www/tcpdf/tcpdf.php');
            $pdf=new TCPDF('p', 'mm', 'letter', true, 'utf-8', false);
            $pdf->SetFillColor(205, 205, 205);//color de relleno gris

            $info_adicional= "Detalle de ".$tipo_nombre.". \n".
                             "Número: ".$numero.", Año: ".$ano;
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
            $pdf->SetAutoPageBreak(TRUE, 12);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            $pdf->SetAuthor('Proyecto SIMON');
            $pdf->SetTitle('Detalle de '.$tipo_nombre);
            $pdf->SetSubject('Detalle de '.$tipo_nombre);

            $pdf->AddPage();

            $lineas=1;//contador de lineas para el footer

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(50, 5, "Número del Documento:", 1, 0, 'L', 1);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(75, 5, $numero."-".$ano, 1, 0, 'L', 1);
            $pdf->Cell(25, 5, "Fecha:", 1, 0, 'L', 1);
            $pdf->Cell(36, 5, $fecha, 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFillColor(210,210,210);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(186, 4, 'PROVEEDOR', 1, 0, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Ln();
            $pdf->SetFont('', 'B');
            $pdf->Cell(25, 6, "C.I/RIF:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',10);
            $pdf->Cell(30, 6, $rif, 1, 0, 'L', 1);
            $pdf->SetFont('', 'B',12);
            $pdf->Cell(30, 6, "Beneficiario:", 1, 0, 'L', 1);
            $pdf->SetFont('', '',10);
            $pdf->Cell(101, 6, strtoupper($nom_beneficiario), 1, 0, 'L', 1);
            $pdf->Ln();
            $pdf->SetFont('', 'B');
            $pdf->MultiCell(40, 12, "Destino o Motivo:", 1, 'JL', 0, 0, '', '', true, 0);
            $pdf->SetFont('', '',7);
            $pdf->MultiCell(146, 12, $motivo, 1, 'JL', 0, 1, '', '', true, 0);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(186, 2, "", 1, 1, 'C', 1);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetFillColor(210,210,210);

            $pdf->Cell(186, 6, "Detalle de la ".$tipo_nombre, 1, 1, 'C', 1);
            $pdf->SetFillColor(255, 255, 255);

            $pdf->SetFont('helvetica', '', 10);
            // se realiza el listado de asistentes en el PDF
            $pdf->SetFillColor(210,210,210);
            $pdf->SetTextColor(0);
            $pdf->SetDrawColor(41, 22, 11);
            $pdf->SetLineWidth(0.3);
            $pdf->SetFont('', 'B');
            // Header del listado
            $listado_header = array('Código Presupuestario','Cantidad','Unidad','Denominación','Monto','Monto Total');
            $w = array(40,16,20,60,25,25);
            for($i = 0; $i < count($listado_header); $i++)
            {
                $pdf->Cell($w[$i], 7, $listado_header[$i], 1, 0, 'C', 1);

            }
            $pdf->Ln();
            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255);
            $pdf->SetTextColor(0);
            $pdf->SetFont('','',10);
            // Data
            $fill = 0;
            $borde="LR";
            $ultimo_elemento = end($resultado_rpt);
             foreach($resultado_rpt as $row) {
                if ($row == $ultimo_elemento) {$borde="LRB";}
                $pdf->Cell($w[0], 6, $row['codigo'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[1], 6, $row['cantidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[2], 6, $row['unidad'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[3], 6, $row['descripcion'], $borde, 0, 'C', $fill);
                $pdf->Cell($w[4], 6, "Bs. ".number_format($row['precio_unitario'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->Cell($w[5], 6, "Bs. ".number_format($row['monto_parcial'], 2, ',', '.'), $borde, 0, 'R', $fill);
                $pdf->SetTextColor(0); // iniciamos con el color negro

                $pdf->Ln();
                $fill=!$fill;
                $lineas++;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('', 'B');
            for($i=1;$i<22-$lineas;$i++)//PARA AJUSTAR LAS LINEAS DEL FOOTER
                {
                $pdf->Cell($w[0], 6,'',1,0, 'R', 0);
                $pdf->Cell($w[1], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[2], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[3], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[4], 6,'',1, 0, 'R', 0);
                $pdf->Cell($w[5], 6,'',1, 0, 'R', 0);
                $pdf->Ln();
                }
            $pdf->Cell($w[0], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[1], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[2], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[3], 6, "", '', 0, 'R', 0);
            $pdf->Cell($w[4], 6, "TOTAL: Bs.", '', 0, 'R', 0);
            $pdf->Cell($w[5], 6, number_format($monto_total, 2, ',', '.'), 1, 0, 'R', 0);


            $pdf->Ln();
            $pdf->Ln();
            //firmas
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(186, 3, 'FIRMAS', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetFont('','',8);
            $pdf->MultiCell(62, 3, 'Control Presupuestario', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Administrador', 1, 'C', 1, 0, '', '', true);
            $pdf->MultiCell(62, 3, 'Presidente', 1, 'C', 1, 0, '', '', true);
            $pdf->Ln();
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Cell(62, 15, '', 1, 0, 'C', 1);
            $pdf->Ln();

            //pROVEEDOR
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(186, 3, 'PROVEEDOR', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFont('','',4);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->MultiCell(124,15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 'C', 1, 0, '', '', true);
            //$pdf->Cell(124, 15, 'EL PROVEEDOR SE COMPROMETE A ENTREGAR LOS MATERIALES Y BIENES INDICADOS EN LA PRESENTE ORDEN EN UN LAPSO DE DÍAS HÁBILES CONTADOS A PARTIR DE LA FECHA DE RECEPCIÓN. eL INCUMPLIMIENTO ORIGINARÁ UNA MULTA EQUIVALENTE AL 0.05% DEL MONTO TOTAL DE LA ORDEN, POR CADA DÍA DE ATRASO O LA ANULACIÓN DE LA ORDEN', 1, 0, 'J', 1);
            $pdf->SetFont('','',8);
            $pdf->Cell(31, 15, 'Firma', 1, 0, 'l', 1);
            $pdf->Cell(31, 15, 'Fecha', 1, 0, 'l', 1);
            $pdf->Ln();
//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false) {
            //RECIBO CONFORME UNIDAD SOLICITANTE
            $pdf->SetFillColor(210,210,210);
            $pdf->Cell(186, 3, 'RECIBO CONFORME UNIDAD SOLICITANTE', 1, 0, 'C', 1);
            $pdf->Ln();
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(46, 10, 'Nombre y Apellido', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Firma', 1, 0, 'L', 1);
            $pdf->Cell(46, 10, 'Cédula de Identidad', 1, 0, 'L', 1,'',0,true);
            $pdf->Cell(48, 10, 'Fecha', 1, 1, 'L', 1);

            $pdf->Output("detalle_".$tipo_nombre."_".$numero."_".$ano.".pdf",'D');
        }
      }
    }


}
?>