<?php
    class calculo_evaluacion extends TPage
    {
        public function onLoad($param)
        {
            //escribe la cedula del usuario actual en el text cedula evaluador
            $cedula_evaluador = usuario_actual('cedula');
            $sql="select * from organizacion.personas_nivel_dir where(cedula='$cedula_evaluador') order by cedula";
            $resultado=cargar_data($sql, $this);
            $nivel_usuario=$resultado[0][nivel];
            $direccion=$resultado[0][cod_direccion];
            $this->text_ced_evaluador->Text=$cedula_evaluador;
            //evita que los controles pierdan los datos al hacer autopostback
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
                //llena el drop cedula del evaluado dependiendo del nivel del usuario
                if($nivel_usuario==90)
                {
                    $sql="select cedula from organizacion.personas_nivel_dir order by cedula";
                    $cedulas=cargar_data($sql,$this);
                    $this->drop_ced_evaluado->DataSource=$cedulas;
                    $this->drop_ced_evaluado->dataBind();
                }
                if($nivel_usuario==70)
                {
                    $sql="select cedula from organizacion.personas_nivel_dir where(nivel<90) order by cedula";
                    $cedulas=cargar_data($sql,$this);
                    $this->drop_ced_evaluado->DataSource=$cedulas;
                    $this->drop_ced_evaluado->dataBind();
                }
                if($nivel_usuario==50)
                {
                    $sql="select cedula from organizacion.personas_nivel_dir where(nivel<50) order by cedula";
                    $cedulas=cargar_data($sql,$this);
                    $this->drop_ced_evaluado->DataSource=$cedulas;
                    $this->drop_ced_evaluado->dataBind();
                }
                //llena el drop de superpervisor
                if($nivel_usuario==90)
                {
                    $sql="select  cedula from organizacion.personas_nivel_dir where(nivel>70) order by cedula";
                    $supervisor=cargar_data($sql, $this);
                    $this->drop_ced_supervisor->DataSource=$supervisor;
                    $this->drop_ced_supervisor->dataBind();
                }
                if($nivel_usuario==70)
                {
                    $sql="select  cedula from organizacion.personas_nivel_dir where(nivel>50) order by cedula";
                    $supervisor=cargar_data($sql, $this);
                    $this->drop_ced_supervisor->DataSource=$supervisor;
                    $this->drop_ced_supervisor->dataBind();
                }
                if($nivel_usuario==50)
                {
                    $sql="select  cedula from organizacion.personas_nivel_dir where(nivel>50) order by cedula";
                    $supervisor=cargar_data($sql, $this);
                    $this->drop_ced_supervisor->DataSource=$supervisor;
                    $this->drop_ced_supervisor->dataBind();
                }
            }
            $this->alerta->Visible = false; 
        }
        function cargar_datos_odi($sender, $param)
        {            
            //vacia la tabla temporal
            $sql="truncate table evaluaciones.datos_temp1";
            $resultado=modificar_data($sql, $sender);
            //guarda en variables la cedula de evaluado y del supervisor
            $ced_evaluado=$this->drop_ced_evaluado->Text;
            $ced_supervisor=$this->drop_ced_supervisor->Text;
            //busco el codigo de la evaluacion asociada dependiendo de la cedula del evaluado
            $sql="select codigo from evaluaciones.datos_evaluados where(cedula='$ced_evaluado')";
            $resultado=cargar_data($sql, $sender);
            $evaluacion_asociada=$resultado[0]['codigo'];
            //busco los campos desde/hasta en la tabla datos_evaludos
            $sql="select desde, hasta from evaluaciones.datos_evaluados where(codigo='$evaluacion_asociada') ";
            $resultado=cargar_data($sql, $sender);
            $desde=$resultado[0]['desde'];            
            $hasta=$resultado[0]['hasta'];                        
            //busco los detalle de la evaluacion dependiendo del codigo de la evaluacion asociada
            $sql="select * from evaluaciones.detalle_evaluacion where(codigo_evaluacion='$evaluacion_asociada')";
            $resultado=cargar_data($sql, $sender);
            //guarda en variables los codigos y los pesos de los odi
            $odi1=$resultado[0]['cod_odi1']; $peso_odi1=$resultado[0]['peso_odi1'];
            $odi2=$resultado[0]['cod_odi2']; $peso_odi2=$resultado[0]['peso_odi2'];
            $odi3=$resultado[0]['cod_odi3']; $peso_odi3=$resultado[0]['peso_odi3'];
            $odi4=$resultado[0]['cod_odi4']; $peso_odi4=$resultado[0]['peso_odi4'];
            $odi5=$resultado[0]['cod_odi5']; $peso_odi5=$resultado[0]['peso_odi5'];
            //busca las descripciones de los odi y las guarda en variables
            $sql="select descripcion from evaluaciones.odi where(codigo='$odi1')";
            $resultado=cargar_data($sql, $sender);
            $descripcion1=$resultado[0]['descripcion'];
            $sql="select descripcion from evaluaciones.odi where(codigo='$odi2')";
            $resultado=cargar_data($sql, $sender);
            $descripcion2=$resultado[0]['descripcion'];
            $sql="select descripcion from evaluaciones.odi where(codigo='$odi3')";
            $resultado=cargar_data($sql, $sender);
            $descripcion3=$resultado[0]['descripcion'];
            $sql="select descripcion from evaluaciones.odi where(codigo='$odi4')";
            $resultado=cargar_data($sql, $sender);
            $descripcion4=$resultado[0]['descripcion'];
            $sql="select descripcion from evaluaciones.odi where(codigo='$odi5')";
            $resultado=cargar_data($sql, $sender);
            $descripcion5=$resultado[0]['descripcion'];
            //calcula la suma y promedio del odi1
            //esta consulta busca los desempenos asociados a una cedula y una evaluacion dependiendo de en un rango de fechas dado
            $sql="select desempeno from evaluaciones.evaluacioncontinua
                  where((select str_to_date(evaluaciones.evaluacioncontinua.fechafindef, '%d-%m-%Y'))
                  between (select str_to_date('$desde', '%d-%m-%Y'))
                  and(select str_to_date('$hasta', '%d-%m-%Y')) and evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi1')";
            $resultado=cargar_data($sql, $sender);
            //determino la longitud del array que trae la consulta
            $largo1=count($resultado);
            //recorro el array y voy sumando
            $suma1=0;
            for ($i=0; $i<=$largo1; $i++)
            {
                $suma1=$suma1+$resultado[$i]['desempeno'];
            }            
            //obtengo el promedio redondeado
            $promedio1=round($suma1/$largo1);
            //calcula la suma y promedio del odi2
            //esta consulta busca los desempenos asociados a una cedula y una evaluacion dependiendo de en un rango de fechas dado
            $sql="select desempeno from evaluaciones.evaluacioncontinua
                  where((select str_to_date(evaluaciones.evaluacioncontinua.fechafindef, '%d-%m-%Y'))
                  between (select str_to_date('$desde', '%d-%m-%Y'))
                  and(select str_to_date('$hasta', '%d-%m-%Y')) and evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi2')";
            $resultado=cargar_data($sql, $sender);
            //determino la longitud del array que trae la consulta
            $largo2=count($resultado);
            //recorro el array y voy sumando
            $suma2=0;
            for ($i=0; $i<=$largo2; $i++)
            {
                $suma2=$suma2+$resultado[$i]['desempeno'];
            }
            //obtengo el promedio redondeado
            $promedio2=round($suma2/$largo2);
            //calcula la suma y promedio del odi3
            //esta consulta busca los desempenos asociados a una cedula y una evaluacion dependiendo de en un rango de fechas dado
            $sql="select desempeno from evaluaciones.evaluacioncontinua
                  where((select str_to_date(evaluaciones.evaluacioncontinua.fechafindef, '%d-%m-%Y'))
                  between (select str_to_date('$desde', '%d-%m-%Y'))
                  and(select str_to_date('$hasta', '%d-%m-%Y')) and evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi3')";
            $resultado=cargar_data($sql, $sender);
            //determino la longitud del array que trae la consulta
            $largo3=count($resultado);
            //recorro el array y voy sumando
            $suma3=0;
            for ($i=0; $i<=$largo3; $i++)
            {
                $suma3=$suma3+$resultado[$i]['desempeno'];
            }
            //obtengo el promedio redondeado
            $promedio3=round($suma3/$largo3);
            //calcula la suma y promedio del odi4
            //esta consulta busca los desempenos asociados a una cedula y una evaluacion dependiendo de en un rango de fechas dado
            $sql="select desempeno from evaluaciones.evaluacioncontinua
                  where((select str_to_date(evaluaciones.evaluacioncontinua.fechafindef, '%d-%m-%Y'))
                  between (select str_to_date('$desde', '%d-%m-%Y'))
                  and(select str_to_date('$hasta', '%d-%m-%Y')) and evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi4')";
            $resultado=cargar_data($sql, $sender);
            //determino la longitud del array que trae la consulta
            $largo4=count($resultado);
            //recorro el array y voy sumando
            $suma4=0;
            for ($i=0; $i<=$largo4; $i++)
            {
                $suma4=$suma4+$resultado[$i]['desempeno'];
            }
            //obtengo el promedio redondeado
            $promedio4=round($suma4/$largo4);
            //calcula la suma y promedio del odi5
            //esta consulta busca los desempenos asociados a una cedula y una evaluacion dependiendo de en un rango de fechas dado
            $sql="select desempeno from evaluaciones.evaluacioncontinua
                  where((select str_to_date(evaluaciones.evaluacioncontinua.fechafindef, '%d-%m-%Y'))
                  between (select str_to_date('$desde', '%d-%m-%Y'))
                  and(select str_to_date('$hasta', '%d-%m-%Y')) and evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi5')";
            $resultado=cargar_data($sql, $sender);
            //determino la longitud del array que trae la consulta
            $largo5=count($resultado);
            //recorro el array y voy sumando
            $suma5=0;
            for ($i=0; $i<=$largo5; $i++)
            {
                $suma5=$suma5+$resultado[$i]['desempeno'];
            }
            //obtengo el promedio redondeado
            $promedio5=round($suma5/$largo5);
            //busca los desempeños correspondientes
            $sql="select desempeno from evaluaciones.evaluacioncontinua where(evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi1')";
            $resultado=cargar_data($sql, $sender);
            print_r($desempeno1=$resultado[0]['desempeno']);
            $sql="select desempeno from evaluaciones.evaluacioncontinua where(evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi2')";
            $resultado=cargar_data($sql, $sender);
            print_r($desempeno2=$resultado[0]['desempeno']);
            $sql="select desempeno from evaluaciones.evaluacioncontinua where(evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi3')";
            $resultado=cargar_data($sql, $sender);
            print_r($desempeno3=$resultado[0]['desempeno']);
            $sql="select desempeno from evaluaciones.evaluacioncontinua where(evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi4')";
            $resultado=cargar_data($sql, $sender);
            print_r($desempeno4=$resultado[0]['desempeno']);
            $sql="select desempeno from evaluaciones.evaluacioncontinua where(evaluacion_asociada='$evaluacion_asociada' and odivinculado='$odi5')";
            $resultado=cargar_data($sql, $sender);
            print_r($desempeno5=$resultado[0]['desempeno']);           
            //calcula el total pesoxodi
            $total=($peso_odi1*$promedio1)+($peso_odi2*$promedio2)+($peso_odi3*$promedio3)+($peso_odi4*$promedio4)+($peso_odi5*$promedio5);
            //insertar los datos en la tabla datos_temp1 (se hacen 5 insersiones porque son 5 odis)
            $sql="insert into evaluaciones.datos_temp1 (cod_odi, descripcion, peso_odi, desempeno, pesoxrango, total, evaluado, supervisor, promedio) values('$odi1', '$descripcion1', '$peso_odi1', '$desempeno1', '$peso_odi1'*'$promedio1', '$total', '$ced_evaluado', '$ced_supervisor', '$promedio1')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp1 (cod_odi, descripcion, peso_odi, desempeno, pesoxrango, total, evaluado, supervisor, promedio) values('$odi2', '$descripcion2', '$peso_odi2', '$desempeno2', '$peso_odi2'*'$promedio2', '$total', '$ced_evaluado', '$ced_supervisor', '$promedio2')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp1 (cod_odi, descripcion, peso_odi, desempeno, pesoxrango, total, evaluado, supervisor, promedio) values('$odi3', '$descripcion3', '$peso_odi3', '$desempeno3', '$peso_odi3'*'$promedio3', '$total', '$ced_evaluado', '$ced_supervisor', '$promedio3')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp1 (cod_odi, descripcion, peso_odi, desempeno, pesoxrango, total, evaluado, supervisor, promedio) values('$odi4', '$descripcion4', '$peso_odi4', '$desempeno4', '$peso_odi4'*'$promedio4', '$total', '$ced_evaluado', '$ced_supervisor', '$promedio4')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp1 (cod_odi, descripcion, peso_odi, desempeno, pesoxrango, total, evaluado, supervisor, promedio) values('$odi5', '$descripcion5', '$peso_odi5', '$desempeno5', '$peso_odi5'*'$promedio5', '$total', '$ced_evaluado', '$ced_supervisor', '$promedio5')";
            $resultado=modificar_data($sql, $sender);
            //consultar la tabla temporal y cargar el datagrid
            $sql="select * from evaluaciones.datos_temp1";
            $resultado=cargar_data($sql, $sender);                                          
            $this->datagrid_odi->datasource=$resultado;
            $this->datagrid_odi->databind();
        }
        function editar($sender, $param)
        {
            //edita la tabla temporal de odis
            $sql="select * from evaluaciones.datos_temp1";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_odi->EditItemIndex=$param->Item->itemIndex;
            $this->datagrid_odi->DataSource=$resultado;
            $this->datagrid_odi->dataBind();
        }
        function editar2($sender, $param)
        {
            //edita la tabla temporal de competencias
            $sql="select * from evaluaciones.datos_temp2";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_competencia->EditItemIndex=$param->Item->itemIndex;
            $this->datagrid_competencia->DataSource=$resultado;
            $this->datagrid_competencia->dataBind();
        }
        function cancelar($sender, $param)
        {
            //cancela los cambios en la tabla temporal de odis
            $sql="select * from evaluaciones.datos_temp1";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_odi->EditItemIndex=-1;
            $this->datagrid_odi->DataSource=$resultado;
            $this->datagrid_odi->dataBind();
        }
         function cancelar2($sender, $param)
        {
            //cancela los cambios en la tabla temporal de competencias
            $sql="select * from evaluaciones.datos_temp2";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_competencia->EditItemIndex=-1;
            $this->datagrid_competencia->DataSource=$resultado;
            $this->datagrid_competencia->dataBind();
        }
        function guardar($sender, $param)
        {
            //guarda los cambios en la tabla temporal de odis
            //obtiene el id del registro seleccionado en la tabla
            $id=$param->Item->id->Text;
            //obtiene el desempeno del registro seleccionado
            $nuevodesempeno=$param->Item->desempeno->DropDownList->SelectedValue;
            //obtiene el peso del registro seleccionado
            $peso=$param->Item->peso->Text;
            //calcula el nuevo pesoxrango
            $nuevopesoxrango=$nuevodesempeno*$peso;
            //actualiza el campo desempeno de la tabla
            $sql="update evaluaciones.datos_temp1 set desempeno='$nuevodesempeno' where(id='$id')";
            $resultado=modificar_data($sql, $sender);
            //actualiza el campo pesoxrango de la tabla
            $sql="update evaluaciones.datos_temp1 set pesoxrango='$nuevopesoxrango' where(id='$id')";
            $resultado=modificar_data($sql, $sender);
            //calcula la suma del campo pesoxrango
            $sql="select sum(pesoxrango) from evaluaciones.datos_temp1";
            $resultado=cargar_data($sql, $sender);
            $nuevototal=$resultado[0]['sum(pesoxrango)'];
            //actualiza el campo total de la tabla
            $sql="update evaluaciones.datos_temp1 set total='$nuevototal'";
            $resultado=modificar_data($sql, $sender);
            $sql="select * from evaluaciones.datos_temp1";            
            $resultado=cargar_data($sql, $sender);            
            $this->datagrid_odi->DataSource=$resultado;
            $this->datagrid_odi->dataBind();            
        }
        function guardar2($sender, $param)
        {
            //$nuevopeso=0;
            //obtiene el id del registro seleccionado en la tabla
            $idtabla=$param->Item->id_comp->Text;
            //obtiene el id de la fila en el datagrid
            $idgrid=$param->Item->ItemIndex;
            //calcula la suma del campo peso
            $sql="select sum(peso_competencia) from evaluaciones.datos_temp2";
            $resultado=cargar_data($sql, $sender);
            $pesototal=$resultado[0]['sum(peso_competencia)'];
            if(($pesototal+$nuevopeso)<=400)
            {
                //obtiene el peso del registro seleccionado
                $nuevopeso=$param->Item->peso_comp->TextBox->Text;
                //obtiene el desempeno del registro seleccionado
                $nuevodesempeno=$param->Item->desem_comp->DropDownList->SelectedValue;
                //calcula el nuevo pesoxrango
                $nuevopesoxrango=$nuevodesempeno*$nuevopeso;
                if(($nuevopeso+$pesototal)<=80)
                {
                    //actualiza los campos peso, desempeño y pesoxrango de la tabla temporal
                    $sql="update evaluaciones.datos_temp2 set peso_competencia='$nuevopeso', desempeno='$nuevodesempeno', pesoxrango='$nuevopesoxrango' where(id='$idtabla')";
                    $resultado=modificar_data($sql, $sender);
                    //calcula la suma del campo pesoxrango
                    $sql="select sum(pesoxrango) from evaluaciones.datos_temp2";
                    $resultado=cargar_data($sql, $sender);
                    $nuevototal=$resultado[0]['sum(pesoxrango)'];
                    //actualiza el campo total de la tabla
                    $sql="update evaluaciones.datos_temp2 set total='$nuevototal'";
                    $resultado=modificar_data($sql, $sender);
                    //actualiza el datagrid despues de actualizar todos los campos
                    $sql="select * from evaluaciones.datos_temp2";
                    $resultado=cargar_data($sql, $sender);
                    $this->datagrid_competencia->DataSource=$resultado;
                    $this->datagrid_competencia->dataBind();
                }
                else
                {
                    $this->alerta->Visible = true;
                    $param->Item->peso_comp->TextBox->Text=0;
                }
            }                       
        }
        function vaciartemp($sender, $param)
        {
            $sql="select * from evaluaciones.datos_temp1";
            $resultado=cargar_data($sql, $sender);
            //saca los odis de la tabal temporal y los gurda en variables
            $odi1=$resultado[0]['descripcion'];
            $odi2=$resultado[1]['descripcion'];
            $odi3=$resultado[2]['descripcion'];
            $odi4=$resultado[3]['descripcion'];
            $odi5=$resultado[4]['descripcion'];
            //saca el peso de los odis de la tabla temporal y los guarda en variables
            $peso_odi1=$resultado[0]['peso_odi'];
            $peso_odi2=$resultado[1]['peso_odi'];
            $peso_odi3=$resultado[2]['peso_odi'];
            $peso_odi4=$resultado[3]['peso_odi'];
            $peso_odi5=$resultado[4]['peso_odi'];
            //saca el desempeño de la tabla temporal
            $desempeno1=$resultado[0]['desempeno'];
            $desempeno2=$resultado[1]['desempeno'];
            $desempeno3=$resultado[2]['desempeno'];
            $desempeno4=$resultado[3]['desempeno'];
            $desempeno5=$resultado[4]['desempeno'];
            //saca el pesoxrango
            $pesoxrango1=$resultado[0]['pesoxrango'];
            $pesoxrango2=$resultado[1]['pesoxrango'];
            $pesoxrango3=$resultado[2]['pesoxrango'];
            $pesoxrango4=$resultado[3]['pesoxrango'];
            $pesoxrango5=$resultado[4]['pesoxrango'];
            //saca el total, el evaluado, el evaluador, el supervisor y el codigo de la evaluacion
            $total=$resultado[0]['total'];
            $evaluado=$resultado[0]['evaluado'];
            $evaluador=$this->text_ced_evaluador->text;
            $supervisor=$resultado[0]['supervisor'];
            $codigo_evaluacion=$this->drop_codigo->text;            
            //inserta las variables en la tabla calculo_evaluacion
            $sql="insert into evaluaciones.calculo_evaluacion (desc_odi1, peso_odi1, desem_odi1, pesoxrango_odi1, desc_odi2, peso_odi2, desem_odi2, pesoxrango_odi2, desc_odi3, peso_odi3, desem_odi3, pesoxrango_odi3, desc_odi4, peso_odi4, desem_odi4, pesoxrango_odi4, desc_odi5, peso_odi5, desem_odi5, pesoxrango_odi5, total ,evaluado, supervisor, cod_evaluacion, evaluador) values('$odi1', '$peso_odi1', '$desempeno1', '$pesoxrango1', '$odi2', '$peso_odi2', '$desempeno2', '$pesoxrango2', '$odi3', '$peso_odi3', '$desempeno3', '$pesoxrango3', '$odi4', '$peso_odi4', '$desempeno4', '$pesoxrango4', '$odi5', '$peso_odi5', '$desempeno5', '$pesoxrango5', '$total', '$evaluado', '$supervisor', '$codigo_evaluacion', '$evaluador')";
            $resultado=modificar_data($sql, $sender);
            //vacia la tabla temporal
            $sql="truncate table evaluaciones.datos_temp1";
            $resultado=modificar_data($sql, $sender);
            $this->button2->Enabled="true";
            $this->button1->enabled="false";
        }
        public function evaluar_competencias($sender, $param)
        {                                
            //limpia la tabla temporal antes de usarla
            $sql="truncate table evaluaciones.datos_temp2";
            $resultado=modificar_data($sql, $sender);
            //obtiene la cedula del evaluado, del supervisor y la evaluacion asociada
            $ced_evaluado=$this->drop_ced_evaluado->Text;
            $ced_supervisor=$this->drop_ced_supervisor->Text;
            $evaluacion_asociada=$this->drop_codigo->text;
            //busca el tipo de empleado
            $sql="select tipo from evaluaciones.tipoempleado where(cedula='$ced_evaluado')";
            $resultado=cargar_data($sql, $sender);
            $tipo=$resultado[0]['tipo'];
            //busca las competencias asociadas al tipo del empleado
            $sql="select * from evaluaciones.competencias where(nivel='$tipo')";
            $resultado=cargar_data($sql, $sender);
            $competencia1=$resultado[0]['nombre_corto'];
            $competencia2=$resultado[1]['nombre_corto'];
            $competencia3=$resultado[2]['nombre_corto'];
            $competencia4=$resultado[3]['nombre_corto'];
            $competencia5=$resultado[4]['nombre_corto'];
            $competencia6=$resultado[5]['nombre_corto'];
            $competencia7=$resultado[6]['nombre_corto'];
            $competencia8=$resultado[7]['nombre_corto'];            
            //inserta las descripciones la cedula del evaluado y del supervisor en la tabla temporal
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia1', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia2', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia3', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia4', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia5', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia6', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia7', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            $sql="insert into evaluaciones.datos_temp2 (descripcion, peso_competencia, desempeno, pesoxrango, total, evaluado, supervisor, cod_evaluacion) values('$competencia8', 0, 0, 0, 0, '$ced_evaluado', '$ced_supervisor', '$evaluacion_asociada')";
            $resultado=modificar_data($sql, $sender);
            //lee la tabla temporal y llena el datagrid
            $sql="select * from evaluaciones.datos_temp2";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid_competencia->datasource=$resultado;
            $this->datagrid_competencia->databind();
            $this->button2->enabled="false";
            $this->button3->enabled="true";
        }
        public function vaciartemp2($sender, $param)
        {
            //copia los datos de la tabla temporal a la tabla calc_comp
            $codigo_evaluacion=$this->drop_codigo->text;
            $sql="insert into evaluaciones.calc_comp
                  (desc_comp, peso_comp, desem_comp, pesoxrango_comp, total, evaluado, supervisor, cod_evaluacion)
                  select evaluaciones.datos_temp2.descripcion, evaluaciones.datos_temp2.peso_competencia, evaluaciones.datos_temp2.desempeno, evaluaciones.datos_temp2.pesoxrango, evaluaciones.datos_temp2.total, evaluaciones.datos_temp2.evaluado, evaluaciones.datos_temp2.supervisor, evaluaciones.datos_temp2.cod_evaluacion
                  from evaluaciones.datos_temp2";
            $resultado=modificar_data($sql, $sender);
            //borra la tabla temporal
            $sql="truncate table evaluaciones.datos_temp2";
            $resultado=modificar_data($sql, $sender);
            $this->button3->enabled=false;
            $this->button4->enabled=true;
        }
        public function calificacion($sender, $param)
        {
            //lee los campos total de las tablas calc_comp y calculo_evaluacion para obtener la calificacion del evaluado
            $cedula_evaluado=$this->drop_ced_evaluado->text;
            $cod_evaluacion=$this->drop_codigo->text;
            $sql="select total from evaluaciones.calculo_evaluacion where(evaluado='$cedula_evaluado' and cod_evaluacion='$cod_evaluacion')";
            $resultado=cargar_data($sql, $sender);
            $this->ttotalodi->text=$resultado[0][total];
            $sql="select total from evaluaciones.calc_comp where(evaluado='$cedula_evaluado' and cod_evaluacion='$cod_evaluacion')";
            $resultado=cargar_data($sql, $sender);
            $this->ttotalcompetencia->text=$resultado[0][total];
            $sumatotal=$this->ttotalodi->text+$this->ttotalcompetencia->text;            
            $this->ttotal->text=$sumatotal;
            $sql="select * from evaluaciones.actuacion where('$sumatotal'<=escalamax and '$sumatotal'>=escalamin)";
            $resultado=cargar_data($sql, $sender);
            $this->rangoactuacion->text=$resultado[0][actuacion];
            $this->button4->enabled="false";
            $this->button5->enabled="true";
        }
        public function pasar_cedula($sender, $param)
        {
            //guarda la cedula seleccionda en una variable
            $cedula=$this->drop_ced_evaluado->text;
            //busca la cedula en la tabla tipoempleado para determinar su tipo
            $sql="select * from evaluaciones.tipoempleado where(cedula='$cedula')";
            $resultado=cargar_data($sql, $sender);
            $this->t_tipoempleado->text=$resultado[0]['tipo'];
            //busca la cedula en datos_evaluados para obtener los codigos de evaluaciones asociadas y que llenaran el drop de evaluacion
            $sql="select codigo from evaluaciones.datos_evaluados where(cedula='$cedula')";
            $resultado=cargar_data($sql, $sender);
            $this->drop_codigo->datasource=$resultado;
            $this->drop_codigo->databind();
        }
        public function guardarnotas($sender, $param)
        {
            //guarda las calificaciones en la base de datos
            $cod_evaluacion=$this->drop_codigo->text;
            $cedula=$this->drop_ced_evaluado->text;
            $totalodi=$this->ttotalodi->text;            
            $totalcompetencia=$this->ttotalcompetencia->text;
            $total=$this->ttotal->text;
            $actuacion=$this->rangoactuacion->text;
            $observaciones=$this->tobservacion->text;
            $sql="insert into evaluaciones.calificaciones (cedula, totalodis, totalcompetencia, total, evaluacion_asociada, actuacion, observaciones) values('$cedula', '$totalodi', '$totalcompetencia', '$total', '$cod_evaluacion', '$actuacion', '$observaciones')";
            $resultado=modificar_data($sql, $sender);
            $this->response->redirect("?page=evaluaciones.calculo_evaluacion");
        }
    }
?>