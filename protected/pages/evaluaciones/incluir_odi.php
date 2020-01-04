<?php
    class incluir_odi extends TPage
    {
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
                //llena el drop de cedula
                $sql="select cedula from organizacion.personas";
                $cedulas=cargar_data($sql,$this);
                $this->drop_cedula->DataSource=$cedulas;
                $this->drop_cedula->dataBind();
                //llena el drop odi1
                $sql="select * from evaluaciones.odi";
                $resultado=cargar_data($sql,$this);
                $this->drop_odi1->DataSource=$resultado;
                $this->drop_odi1->dataBind();
                //llena el drop odi2
                $sql="select * from evaluaciones.odi";
                $resultado=cargar_data($sql,$this);
                $this->drop_odi2->DataSource=$resultado;
                $this->drop_odi2->dataBind();
                //llena el drop odi3
                $sql="select * from evaluaciones.odi";
                $resultado=cargar_data($sql,$this);
                $this->drop_odi3->DataSource=$resultado;
                $this->drop_odi3->dataBind();
                //llena el drop odi4
                $sql="select * from evaluaciones.odi";
                $resultado=cargar_data($sql,$this);
                $this->drop_odi4->DataSource=$resultado;
                $this->drop_odi4->dataBind();
                //llena el drop odi5
                $sql="select * from evaluaciones.odi";
                $resultado=cargar_data($sql,$this);
                $this->drop_odi5->DataSource=$resultado;
                $this->drop_odi5->dataBind();
            }
            $this->alerta->Visible = false; 
        }
        public function buscar_datos($sender, $param)
        {
            //llena el drop de codigo
            $cedula=$this->drop_cedula->text;
            $sql="select codigo from evaluaciones.datos_evaluados where(cedula='$cedula')";
            $codigos=cargar_data($sql,$this);            
            $this->drop_codigo->DataSource=$codigos;
            $this->drop_codigo->dataBind();
            $cedula=$this->drop_cedula->Text;
            //con la cedula seleccionada hago un join de varias tablas para obtener todos los datos relacionados con ella
            $sql="select * from organizacion.personas join organizacion.personas_nivel_dir join organizacion.personas_cargos
                  on (organizacion.personas.cedula=organizacion.personas_nivel_dir.cedula)
                  and(organizacion.personas.cedula=organizacion.personas_cargos.cedula)
                  where(organizacion.personas.cedula='$cedula')";
            $resultado=cargar_data($sql, $sender);            
            $this->t_nombre->text=$resultado[0]['nombres'];
            $this->t_apellido->text=$resultado[0]['apellidos'];
            $this->t_denominacion->text=$resultado[0]['denominacion'];
            //busca el codigo de la direccion en la tabla direcciones para obtener el nombre corto asociado a ese codigo
            $cod_direccion=$resultado[0]['cod_direccion'];            
            $sql="select * from organizacion.direcciones where(codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $sender);            
            $this->t_nombre_corto->text=$resultado[0]['nombre_abreviado'];
        }
        public function buscar1($sender, $param)
        {
            $cod_odi=$this->drop_odi1->Text;
            $sql="select * from evaluaciones.odi where(codigo='$cod_odi')";
            $resultado=cargar_data($sql, $sender);
            $this->t_desc1->text=$resultado[0]['descripcion'];
        }
        public function buscar2($sender, $param)
        {
            $cod_odi=$this->drop_odi2->Text;
            $sql="select * from evaluaciones.odi where(codigo='$cod_odi')";
            $resultado=cargar_data($sql, $sender);
            $this->t_desc2->text=$resultado[0]['descripcion'];
        }
        public function buscar3($sender, $param)
        {
            $cod_odi=$this->drop_odi3->Text;
            $sql="select * from evaluaciones.odi where(codigo='$cod_odi')";
            $resultado=cargar_data($sql, $sender);
            $this->t_desc3->text=$resultado[0]['descripcion'];
        }
        public function buscar4($sender, $param)
        {
            $cod_odi=$this->drop_odi4->Text;
            $sql="select * from evaluaciones.odi where(codigo='$cod_odi')";
            $resultado=cargar_data($sql, $sender);
            $this->t_desc4->text=$resultado[0]['descripcion'];
        }
        public function buscar5($sender, $param)
        {
            $cod_odi=$this->drop_odi5->Text;
            $sql="select * from evaluaciones.odi where(codigo='$cod_odi')";
            $resultado=cargar_data($sql, $sender);
            $this->t_desc5->text=$resultado[0]['descripcion'];
        }
        public function sumar($sender, $param)
        {
            $acumulado=$this->t_peso1->text+$this->t_peso2->text+$this->t_peso3->text+$this->t_peso4->text+$this->t_peso5->text;
            $this->t_total->text=$acumulado;
            if($acumulado>50)
            {
                $this->alerta->visible=true;
                $this->t_total->text=0;
            }
            if($acumulado==50)
            {
                $this->button1->enabled=true;
            }
        }
        public function guardar_datos($sender, $param)
        {
            $codigo_evaluacion=$this->drop_codigo->text;
            $cedula=$this->drop_cedula->text;
            $cod_od1=$this->drop_odi1->text;
            $peso_odi1=$this->t_peso1->text;
            $cod_odi2=$this->drop_odi2->text;
            $peso_odi2=$this->t_peso2->text;
            $cod_odi3=$this->drop_odi3->text;
            $peso_odi3=$this->t_peso3->text;
            $cod_odi4=$this->drop_odi4->text;
            $peso_odi4=$this->t_peso4->text;
            $cod_odi5=$this->drop_odi5->text;
            $peso_odi5=$this->t_peso5->text;
            $sql="insert into evaluaciones.detalle_evaluacion 
                  (codigo_evaluacion, cod_odi1, peso_odi1, cod_odi2, peso_odi2, cod_odi3, peso_odi3, cod_odi4, peso_odi4, cod_odi5, peso_odi5)
                  values('$codigo_evaluacion', '$cod_od1', '$peso_odi1', '$cod_odi2', '$peso_odi2', '$cod_odi3', '$peso_odi3', '$cod_odi4', '$peso_odi4', '$cod_odi5', '$peso_odi5')";
            $resultado=modificar_data($sql, $sender);
            $this->Response->redirect("?page=evaluaciones.incluir_odi");
        }
    }
?>