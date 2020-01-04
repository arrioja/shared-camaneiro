<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M. / Carlos A. Ávila P
 * Descripción: Este archivo contiene funciones creadas para interactuar de
 * manera genérica con algunos controles, de uso común en el sistema, esto se
 * hace con la finalidad de facilitar la interacción entre
 * programador - controles.
 *****************************************************  FIN DE INFO
*/

/* Esta función se encarga de obtener el set seleccionado de un dropdownlist,
 * devuelve un arreglo con tres valores:
 * 0: el index de la selección.
 * 1: el Value del item seleccionado.
 * 2: el Text del ítem seleccionado.
 */
    function obtener_seleccion_drop($control)
    {
        $indices=$control->SelectedIndices;
        $result=array();
        foreach($indices as $index)
        {
            $item=$control->Items[$index];
            $result[0]=$index; // el primer valor del arreglo es el indice
            $result[1]=$item->Value;// el segundo valor es el value de la seleccion
            $result[2]=$item->Text; // el tercer valor es el Text de la seleccion
        }
        return $result;
    }


?>
