<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class despachar extends TPage
{
    public function onload($param)
    {
        $cadena=$this->Request['idvinculo'];
        $this->t7->text=$cadena;        
        $sql="select *, sum(actual) from bienes.consumibles where(idvinculo='$cadena' and actual>0)";
        $resultado=cargar_data($sql, $this);
        //$this->t1a->text=$resultado[0]['id'];
        $this->t1->text=$resultado[0]['descripcion'];
        $this->t2->text=$resultado[0]['minimo'];
        $this->t3->text=$resultado[0]['maximo'];
        $this->t4->text=$resultado[0]['sum(actual)'];        
        $this->t8->text=$resultado[0]['ano'];
        $this->t9->text=$resultado[0]['partida'];
        $this->t10->text=$resultado[0]['generica'];
        $this->t11->text=$resultado[0]['especifica'];
        $this->t12->text=$resultado[0]['subespecifica'];
        $this->t13->text=$resultado[0]['costo'];
        $cod_organizacion = usuario_actual('cod_organizacion');
        //llena el listbox con los valores desde 1 hasta el maximo disponible
        $valores=array();
        $maxi=$resultado[0]['sum(actual)'];
        for($i = 1; $i <= $maxi; $i++)
        {
            $valores[$i]=$i;
        }
        $this->ddl_a_entregar->DataSource=$valores;
        $this->ddl_a_entregar->DataBind();        
        $sql2="select * from organizacion.direcciones where(codigo_organizacion = '$cod_organizacion')";
        $resultado2=cargar_data($sql2, $this);
        $this->ddl1->DataSource=$resultado2;
        $this->ddl1->DataBind();                
        parent::onLoad($param);
        if (!$this->IsPostBack)
        {
            $this->fecha1->text=date("d/m/Y");
        }        
        if ($this->t4->text==0)
        {
            $this->b1->Enabled=false;
        }
    }
    public function validar($sender, $param)
    {
        $actual=$this->t4->text;        
        if(($param->Value>$actual)or($param->Value<=0))
            $param->IsValid=false;
    }

    public function actualizar_consumible($sender, $param)
    {
        if ($this->IsValid)
        {
            $id=$this->t7->text;
            $direccion=$this->ddl1->SelectedValue;
            $sql1="select nombre_completo from organizacion.direcciones where(codigo='$direccion')";
            $resultado1=cargar_data($sql1, $this);
            $nomb_dir=$resultado1[0]['nombre_completo'];
            $cantidad_entregar=$this->ddl_a_entregar->SelectedValue;
            $desc=$this->t1->text;
            $ano=$this->t8->text;
            $partida=$this->t9->text;
            $generica=$this->t10->text;
            $especifica=$this->t11->text;
            $subespecifica=$this->t12->text;
            $costo=$this->t13->text;
            $fecha=cambiaf_a_mysql($this->fecha1->text);            
            //calculo cuantos articulos hay para hacer un bucle
            $sql2="select count(*) from bienes.consumibles where(idvinculo='$id')";
            $resultado2=cargar_data($sql2, $sender);
            $contador=$resultado2[0]['count(*)'];
            //entro en el ciclo
            for ($i = 1; $i <= $contador; $i++)
            {
                //busco el articulo de menor costo
                $sql3="select * from bienes.consumibles where(idvinculo='$id' and actual<>0) order by costo asc limit 1";
                $resultado3=cargar_data($sql3, $sender);
                //si la entrega <= a la existencia del articulo de menor costo
                if($cantidad_entregar<=$resultado3[0]['actual'] and $cantidad_entregar>0)
                {
                    $nuevo_actual=$resultado3[0]['actual']-$cantidad_entregar;
                    //obtengo costo del registro seleccionado
                    $nuevo_costo=$resultado3[0]['costo'];
                    //inserto datos en consumibles_entregados
                    $sql4="insert into bienes.consumibles_entregados (f_entrega, direccion_entrega, cantidad, descripcion, idvinculo, ano, partida, generica, especifica, subespecifica, costo) values('$fecha', '$direccion', '$cantidad_entregar', '$desc', '$id', '$ano', '$partida', '$generica', '$especifica', '$subespecifica', '$nuevo_costo')";
                    $resultado4=modificar_data($sql4, $this);
                    $cantidad_entregar=0;
                }
                if($cantidad_entregar>$resultado3[0]['actual'])
                {
                    //obtengo costo del registro seleccionado
                    $nuevo_costo=$resultado3[0]['costo'];
                    $nueva_cantidad=$cantidad_entregar-$resultado3[0]['actual'];
                    $nuevo_actual=$cantidad_entregar-$resultado3[0]['actual']-$nueva_cantidad;
                    if($nueva_cantidad > 0)
                    {
                        $cantidad_temporal=$nueva_cantidad;
                        $cantidad_entregar=$resultado3[0]['actual'];
                        //inserto datos en consumibles_entregados
                        $sql4="insert into bienes.consumibles_entregados (f_entrega, direccion_entrega, cantidad, descripcion, idvinculo, ano, partida, generica, especifica, subespecifica, costo) values('$fecha', '$direccion', '$cantidad_entregar', '$desc', '$id', '$ano', '$partida', '$generica', '$especifica', '$subespecifica', '$nuevo_costo')";
                        $resultado4=modificar_data($sql4, $this);
                        $cantidad_entregar=$cantidad_temporal;
                    }                   
                }
                $sql5="update bienes.consumibles set actual='$nuevo_actual' where(idvinculo='$id' and actual<>0) order by costo asc limit 1";
                $respuesta5=modificar_data($sql5, $this);
                //vuelvo a sumar la existencia del producto
                $sql6="select sum(actual) from bienes.consumibles where(idvinculo='$id')";
                $resultado6=cargar_data($sql6, $sender);
                $nuevo_total=$resultado6[0]['sum(actual)'];
                //actualizo la tabla maestro
                $sql7="update bienes.consumibles_corto set total='$nuevo_total' where(idvinculo='$id')";
                $resultado7=modificar_data($sql7, $this);
            }//salgo del ciclo
            // Se incluye el rastro en el archivo de bitácora
            $descripcion_log = "El usuario ".$login." ha entregado ".$cant." ".$desc." a la direccion ".$nomb_dir;
            inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'I',$descripcion_log,"",$sender);
            //reenvio a la pagina de entrega de consumibles
            $this->Response->Redirect($this->Service->constructUrl('bienes.consumibles.entregar_consumible'));
        }
    }
}
?>