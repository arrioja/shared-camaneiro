<?php
/*   ****************************************************  INFO DEL ARCHIVO
  Creado por:   Pedro E. Arrioja M.
  Descripción:  Este archivo implementa la inclusión de integrantes a la nomina;
 *              es requisito que se encuentren inscritos como personas, ya que
 *              es ahi donde se almacenan los datos personales del usuario.
     ****************************************************  FIN DE INFO
*/

class incluir_integrante extends TPage
{
     /** CHECK si existe el código **/
 function check_codigo($sender,$param)
   { $cod_org=usuario_actual('cod_organizacion');
     $param->IsValid=verificar_existencia('nomina.integrantes','cod',$this->txt_codigo->Text,array('cod_organizacion'=>$cod_org),$sender);
   }
    public function onLoad($param)
    {
       parent::onLoad($param);
       if (!$this->IsPostBack)
        {
            $cod_organizacion = usuario_actual('cod_organizacion');
            $sql="select * from nomina.tipo_nomina where cod_organizacion='$cod_organizacion'";
            $tipo_nomina=cargar_data($sql,$this);
            $this->cmb_nomina->DataSource=$tipo_nomina;
            $this->cmb_nomina->dataBind();

            //llena el listbox con las cedulas

            $sql="  SELECT cedula,concat(nombres,', ', apellidos,' /V',cedula) as nombre
                    FROM organizacion.personas order by nombre asc";
            $resultado=cargar_data($sql, $this);
            $this->drop_persona->DataSource=$resultado;
            $this->drop_persona->dataBind();


           
        }
    }

   //***funcion para verificar que la organizacion del usuario logueado
   //**es la misma que  la de la cedula introducida
    public function verificar_organizacion($sender,$param)
    {
        $param->IsValid=true;
        $cedula=$this->txt_cedula->Text;
        $sql="select cod_organizacion from organizacion.personas_nivel_dir where cedula='$cedula'";
        $datos_persona=cargar_data($sql,$sender);
        if($datos_persona[0]['cod_organizacion']==usuario_actual('cod_organizacion'))
            {
            $param->IsValid= true;
            }
        else
            {
            $param->IsValid=false;
            $this->txt_nombre->Text = "La persona no tiene asignada una organizacion";
            $this->txt_apellido->Text = "";
            }
    }



    /*verifica la existencia en la tabla nomina. integrantes*/
    public function verif_existencia_integrantes($sender,$param)
    {$param->IsValid=True;
        $cedula=$this->txt_cedula->Text;
        $tipo_nomina=$this->cmb_nomina->SelectedValue;
///revisar
            $param->IsValid=verificar_existencia('nomina.integrantes_tipo_nomina','cedula',$cedula,array('tipo_nomina'=>$tipo_nomina),$this);
    }

    /* Esta función implementa la comprobación de que la cédula que se esta
     * introduciendo exista en nomina y sino existe coloca el numero siguiente del codigo nomina
     */
    public function validar_cedula($sender, $param)
    {
        
        $cedula = $this->drop_persona->Selectedvalue;
         if (verificar_existencia('nomina.integrantes','cedula',$cedula,null,$this))//si no está en integrantes lo inserta
        {
            $param->IsValid=true;
            $this->btn_incluir->Enabled="True";
            $resultado=cargar_data("SELECT (cod+1) as cod FROM nomina.integrantes ORDER BY cod DESC LIMIT 1",$sender);
            $this->txt_codigo->Text=$resultado[0][cod];
        }else{
            $param->IsValid=false;
            $this->btn_incluir->Enabled="False";
            $this->mensaje->setErrorMessage($sender, "¡Integrante ya existe en Nómina!", 'grow');
           
        }
        /*$cedula=$this->txt_cedula->Text;
        $sql="select p.cedula,p.nombres,p.apellidos from organizacion.personas p inner join organizacion.personas_nivel_dir pnd  on p.cedula=pnd.cedula where p.cedula='$cedula'";
        $datos_persona=cargar_data($sql,$sender);
        if (empty($datos_persona ))
        { // si no existe, se vacian los controles para forzar validación

            $this->txt_nombre->Text = "no se encontraron datos";
            $this->txt_apellido->Text = "";
           
            
        }
        else
        { // si existe, se colocan los datos de la persona en los campos correspondientes
            $this->txt_nombre->Text = $datos_persona[0]['nombres'];
            $this->txt_apellido->Text = $datos_persona[0]['apellidos'];
        }*/
    }

    /* En esta funcion, se recogen los datos de los campos en variables, se
     * construye una consulta sql y se envia a la función generica de
     * inclusion de datos.
     * */
	public function incluir($sender, $param)
	{
        //if($this->IsValid)//SI PASA LAS VAlidaciones de la pagina
       // {
       // Se capturan los datos provenientes de los Controles
        $this->btn_incluir->Enabled="False";
        $cedula = $this->drop_persona->Selectedvalue;
        $cod=$this->txt_codigo->Text;
        $anos=$this->txt_anos->Text;
        $cod_org=usuario_actual('cod_organizacion');
        $resultado_drop = obtener_seleccion_drop($this->cmb_pago);
        $pago_banco = $resultado_drop[1];
        $resultado_drop = obtener_seleccion_drop($this->cmb_nomina);
        $tipo_nomina =$resultado_drop[1];
        // se verifica si exste en tipo de nomina seleccionado
        if(verificar_existencia('nomina.integrantes_tipo_nomina','cedula',$cedula,array('tipo_nomina'=>$tipo_nomina),$this))//si no está con cédula y tipo_nomina en int_tipo_nomina lo inserta
        {
            //  Se guardan los datos del integrante
            $sql="insert into nomina.integrantes (cedula,cod,anos_servicio,status,pago_banco,cod_organizacion)
            values('$cedula','$cod','$anos','1','$pago_banco','$cod_org')";
            $resultado=modificar_data($sql,$sender);
            //se guarda en tipo de nomina
            $sql="insert into nomina.integrantes_tipo_nomina (cedula,tipo_nomina) values('$cedula','$tipo_nomina')";
            $resultado=modificar_data($sql,$sender);
            //se guardan datos del cargo
            $cedula_persona = $this->drop_persona->Selectedvalue;
            $denominacion = strtoupper($this->txt_denominacion->text);
            $condicion = $this->drop_condicion->Selectedvalue;
            $decreto_contrato=$this->txt_res_con->text;
            $fecha_ini = cambiaf_a_mysql($this->txt_fecha_in->Text);
            $lugar_trabajo=$this->drop_ubicacion->Selectedvalue;
            // se verifica si existe el cargo para verificar exactamente su nombre
                if($this->lbl_denominacion->Text!="")
               {// si no existe
                $sql = "SELECT nombre FROM nomina.cargos WHERE id='".$this->lbl_denominacion->Text."'";
                $datos = cargar_data($sql,$sender);
                $denominacion = strtoupper(utf8_encode($datos[0]['nombre']));
               }
          
            $sql="insert into organizacion.personas_cargos
                  (cedula,denominacion,condicion,decreto_contrato,fecha_ini,lugar_trabajo)
                  values ('$cedula_persona','$denominacion','$condicion','$decreto_contrato','$fecha_ini','$lugar_trabajo')";
            $resultado=modificar_data($sql,$sender);
            //se guardan datos del cargo
            $this->mensaje->setSuccessMessage($sender, "¡Integrante Guardado Exitosamente!", 'grow');
        } else {
            $this->mensaje->setErrorMessage($sender, "¡Integrante ya existe en Nómina de ".$tipo_nomina."!", 'grow');
        }

        //Se incluye el rastro en el archivo de bitácora
        $descripcion_log = "Se ha incluido en la Nomina la persona C.I. ".$cedula_persona.", con el Codigo ".$cod.": con el cargo de Denominacion ".$denominacion.",Fecha de Ingreso ".$fecha_ini." Condicion de ".$condicion." por Resolucion o Contrato Nº ".$decreto_contrato ;
        inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);

        }
 	//}

       /* Esta muestra una consulta filtrada por la cadena ingresada en el text de nombre del cargo*/
    public function cargo_sugeridos($sender,$param) {
        $cod_organizacion = usuario_actual('cod_organizacion');
         // para llenar el listado de articulos
        $articulo=$this->txt_denominacion->Text;
        $sql = "select id,nombre from nomina.cargos
                    where (cod_organizacion = '$cod_organizacion') and (nombre LIKE  '$articulo%')order by codigo";
        $resultado = cargar_data($sql,$this);

        //por acentos se codifica en utf8
        $arreglo = array();
        foreach ($resultado as $datos)
         {
            //por acentos se codifica en utf8
            $id=$datos[id];
            $nombre=utf8_encode($datos[nombre]);
            $cargo = array('id'=>$id, 'nombre'=>$nombre );
            array_unshift($arreglo, $cargo);
         }

        $sender->DataSource=$arreglo;// se carga en el data
        $sender->dataBind();
    }
/* Esta función obtiene el id del cargo de la tabla cargos */
     public function cargo_selecionado($sender,$param) {
        $id=$sender->Suggestions->DataKeys[ $param->selectedIndex ];
        $descripcion= $this->txt_denominacion->Text;
        $this->lbl_denominacion->Text=$id;// se asigna en una lbl el id
    }

public function cargar_dir($sender,$param)
    {
        $fecha=cambiaf_a_mysql($this->txt_fecha_in->Text);

        //se obtiene la fecha comparando la vigencia  para consultar direcciones de esa como vigencia_desde
        /*$sql="SELECT id,nombre,periodo
            FROM organizacion.direcciones_historial
            WHERE periodo = (
            SELECT id
            FROM organizacion.direcciones_historial_periodo
            WHERE (vigencia_desde <='$fecha' AND vigencia_hasta >='$fecha')
            ORDER BY vigencia_desde ASC
            LIMIT 1 ) ";*/

        $sql="SELECT dh.id,dh.nombre,dh.periodo,dhp.vigencia_desde,dhp.vigencia_hasta
            FROM organizacion.direcciones_historial as dh
            INNER JOIN organizacion.direcciones_historial_periodo dhp ON(dhp.vigencia_desde <='$fecha' AND dhp.vigencia_hasta >='$fecha')
            WHERE (dh.periodo=dhp.id)
             ";
        $resultado=cargar_data($sql, $sender);

        //si es vacio
        if (empty($resultado)){
            //se obtiene la primera fecha de orden Asc segun fecha de inicio del cargo para consultar direcciones de esa como vigencia_desde
        $sql="SELECT id,nombre FROM organizacion.direcciones_historial
            WHERE periodo = (SELECT id FROM organizacion.direcciones_historial_periodo
                             ORDER BY vigencia_desde DESC LIMIT 1 ) ";
        $resultado=cargar_data($sql, $sender);

        $arreglo = array();
        foreach ($resultado as $datos)
         {
               //por acentos se codifica en utf8
            $id=$datos[id];
            $nombre=utf8_encode($datos[nombre]);
            $nombre="$nombre (Vigente Actualmente)";
            $direccion = array('id'=>$id, 'nombre'=>$nombre );
            array_unshift($arreglo, $direccion);
         }
            $this->drop_ubicacion->DataSource=$arreglo;
            $this->drop_ubicacion->dataBind();

        }else{
            //por acentos se codifica en utf8
            $arreglo = array();
            foreach ($resultado as $datos)
             {
                $id=$datos[id];
                $nombre=utf8_encode($datos[nombre]);
                $nombre="$nombre (Vigente: ".cambiaf_a_normal($datos[vigencia_desde])."-".cambiaf_a_normal($datos[vigencia_hasta]).")";
                $direccion = array('id'=>$id, 'nombre'=>$nombre );
                array_unshift($arreglo, $direccion);
             }
                $this->drop_ubicacion->DataSource=$arreglo;
                $this->drop_ubicacion->dataBind();

        }//fin si

    }

}

?>
