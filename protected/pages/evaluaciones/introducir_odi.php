<?php
     class introducir_odi extends TPage
     {
        public function onLoad($param)
        {           
                $sql="select * from evaluaciones.odi";
                $resultado=cargar_data($sql, $this);
                $this->datagrid1->datasource=$resultado;
                $this->datagrid1->databind();            
        }
        public function guardar_datos($sender, $param)
        {
            //guarda en variables el contenido de los controles del formulario y los guarda en la tabla odi
            $descripcion=$this->t_descripcion->text;
            $codigo=$this->t_codigo->text;
            $sql="insert into evaluaciones.odi (codigo, descripcion) values('$codigo', '$descripcion')";
            $resultado=modificar_data($sql, $sender);
            //lee la tabla odi y llena el datagrid
            $sql="select * from evaluaciones.odi";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid1->datasource=$resultado;
            $this->datagrid1->databind();
        }
        public function eliminar($sender, $param)
        {            
            //obtiene el id del registro seleccionado y lo borra de la tabla
            $id=$param->Item->Data[id];
            $sql="delete from evaluaciones.odi where(id='$id')";
            $resultado=modificar_data($sql, $sender);
            //lee de nuevo la tabla para cargar el datagrid
            $sql="select * from evaluaciones.odi";
            $resultado=cargar_data($sql, $sender);
            $this->datagrid1->datasource=$resultado;
            $this->datagrid1->databind();
        }
     }
?>
