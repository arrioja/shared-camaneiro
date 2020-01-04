<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Implementa la inclusión de descripción presupuestaria en las
 *              tablas correspondiente, se requiere un codigo presupuestario
 *              de acuerdo con la normativa de la onapre conformado por:
 *              Partida: tres (3) caracteres (p.e. 401 402 403).
 *              Generica: dos (02) caracteres (p.e. 01 02 03 04).
 *              Especifica: dos (02) caracteres (p.e. 01 02 03 04).
 *              SubEspecifica: dos (02) caracteres (p.e. 01 02 03 04).
 *****************************************************  FIN DE INFO
*/
class incluir_descripcion_presupuesto extends TPage
{
/* Esta función valida la existencia del código presupuestario en la tabla
 * descripcion_presupuesto, se usa un custom validator en el control para
 * responder al usuario si existe o no el mismo.
 */
    public function validar_codigo($sender, $param)
    {
       $ano = $this->drop_ano->Text;
       $partida = $this->txt_codigo->Text[0].$this->txt_codigo->Text[1].$this->txt_codigo->Text[2];
       $generica = $this->txt_codigo->Text[4].$this->txt_codigo->Text[5];
       $especifica = $this->txt_codigo->Text[7].$this->txt_codigo->Text[8];
       $subespecifica = $this->txt_codigo->Text[10].$this->txt_codigo->Text[11];

       $sql = "select * from administracion.descripcion_presupuesto
                    where ((ano = '$ano') and (partida = '$partida') and (generica = '$generica') and
                           (especifica = '$especifica') and (subespecifica = '$subespecifica'))";

       $existe = cargar_data($sql,$this);
       if (!($existe))// si el codigo no existe, se le puede dar play a la inclusion.
       {
           $param->IsValid = true;
       }
       else
       {
           $param->IsValid = false;
       }
    }


    function incluir_click($sender, $param)
    {
        $ano = $this->drop_ano->Text;
        $partida = $this->txt_codigo->Text[0].$this->txt_codigo->Text[1].$this->txt_codigo->Text[2];
        $generica = $this->txt_codigo->Text[4].$this->txt_codigo->Text[5];
        $especifica = $this->txt_codigo->Text[7].$this->txt_codigo->Text[8];
        $subespecifica = $this->txt_codigo->Text[10].$this->txt_codigo->Text[11];
        $descripcion = $this->txt_descripcion->Text;

        $sql = "insert into administracion.descripcion_presupuesto
                (ano, partida, generica, especifica, subespecifica, descripcion)
                values ('$ano','$partida','$generica','$especifica','$subespecifica','$descripcion')";
        $resultado=modificar_data($sql,$sender);
        $this->Response->redirect($this->Service->constructUrl('Home'));
    }
}
?>
