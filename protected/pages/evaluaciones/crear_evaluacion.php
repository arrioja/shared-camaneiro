<?php
 class crear_evaluacion extends TPage
    {
        public function onLoad($param)
        {
            //lee el campo codigo de la tabla datos_evaluados, le suma 1 y lo escribe en su control correspondiente
            $sql2="select codigo from evaluaciones.datos_evaluados order by id desc limit 1";
            $resultado2=cargar_data($sql2, $this);
            $ult_codigo=$resultado2[0][codigo];
            $ult_codigo=$ult_codigo+1;
            $this->t_codigo->text=$ult_codigo;
            //llena el drod cedula
            $sql="select cedula from organizacion.personas_nivel_dir order by cedula";
            $cedulas=cargar_data($sql,$this);
            $this->drop_cedula->DataSource=$cedulas;
            $this->drop_cedula->dataBind();
            //calcula la fecha actual y la escribe en su control correspondiente
            $fecha_actual=date("d/m/Y");
            $this->t_fecha->text=$fecha_actual;
        }
        public function guardar_datos($sender, $param)
        {
            //lee los valores de los control y los guarda en variables
            $desde=$this->f_desde->text;;
            $hasta=$this->f_hasta->text;;
            $cedula=$this->drop_cedula->text;
            $codigo=$this->t_codigo->text;
            $fecha=$this->t_fecha->text;
            $eva1=$this->f_eva1->text;
            $eva2=$this->f_eva2->text;
            $eva3=$this->f_eva3->text;
            //guarda los valores de las variables en la tabla datos_evaluados
            $sql="insert into evaluaciones.datos_evaluados
                  (cedula, codigo, fecha, eva1, eva2, eva3, desde, hasta)
                  values
                  ('$cedula', '$codigo', '$fecha', '$eva1', '$eva2', '$eva3', '$desde', '$hasta')";
            $resultado=modificar_data($sql, $sender);
            $this->Response->redirect("?page=evaluaciones.crear_evaluacion");
        }
    }
?>
