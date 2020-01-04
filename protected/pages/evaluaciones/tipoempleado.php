<?php
class tipoempleado extends TPage
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
            }
        }
        public function buscar_datos($sender, $param)
        {
            $cedula=$this->drop_cedula->text;            
            $sql="select nombres, apellidos, denominacion, cod_direccion, nombre_abreviado
            from organizacion.personas join organizacion.personas_cargo
            on organizacion.personas.cedula=organizacion.personas_cargo.cedula
            join organizacion.personas_nivel_dir
            on organizacion.personas.cedula=organizacion.personas_nivel_dir.cedula
            join organizacion.direcciones
            on organizacion.personas_nivel_dir.cod_direccion=organizacion.direcciones.codigo
            where(personas.cedula='$cedula')";
            $resultado=cargar_data($sql, $sender);
            //print_r($resultado);
            $this->t_nombre->text=$resultado[0][nombres];
            $this->t_apellido->text=$resultado[0][apellidos];
            $this->t_denominacion->text=$resultado[0][denominacion];
            $this->t_cod_dir->text=$resultado[0][cod_direccion];
            $this->t_nom_abre->text=$resultado[0][nombre_abreviado];
        }
        public function guardar_datos($sender, $param)
        {
            $cedula=$this->drop_cedula->text;
            $tipo=$this->drop_tipo->text;
            $sql="insert into evaluaciones.tipoempleado (cedula, tipo) values('$cedula', '$tipo')";
            $resultado=modificar_data($sql, $sender);
        }
    }
?>
