<?php
class excel extends TPage
{
        public function onLoad($param)
        {
            parent::onLoad($param);
            if(!$this->IsPostBack)
            {
header('Content-type: application/vnd.ms-excel');
            header("Content-Disposition: attachment; filename=archivo.xls");
            header("Pragma: no-cache");
            header("Expires: 0");
$sql="SELECT p.rif, p.nombres, p.apellidos, ic.monto, sum(n.monto_incidencia)as monto_incidencia,n.cod, n.cod_incidencia,n.tipo
FROM organizacion.personas p
JOIN nomina.nomina n ON n.cedula = p.cedula
left JOIN nomina.integrantes_constantes ic ON ic.cedula = p.cedula and ic.cod_constantes = '9000'
where (n.cod = '0023' or n.cod = '0024')AND (n.cod_incidencia = '7001' or n.cod_incidencia = '9001')
group by p.rif,p.nombres,p.apellidos,n.tipo
order by p.rif,ic.monto,n.cod_incidencia";
            /*$sql="SELECT p.rif, p.nombres, p.apellidos, ic.monto, n.monto_incidencia,n.cod, n.cod_incidencia
FROM organizacion.personas p
JOIN nomina.nomina n ON n.cedula = p.cedula
left JOIN nomina.integrantes_constantes ic ON ic.cedula = p.cedula and ic.cod_constantes = '9000'
where (n.cod = '0023' or n.cod = '0024')AND (n.cod_incidencia = '7001' or n.cod_incidencia = '9001')
order by p.rif,ic.monto";*/
            $resultado=cargar_data($sql,$this);
            echo "<table border=1>\n";
            echo "<tr>\n";
            echo "<th>RIF</th>\n";
            echo "<th>NOMBRES</th>\n";
            echo "<th>APELLIDOS</th>\n";
            echo "<th>Asignaciones</th>\n";
            echo "<th>% retencion</th>\n";
            echo "<th>Monto retenido</th>\n";
            echo "</tr>\n";
    $nombre="nada";
        foreach($resultado as $key=>$personas)
        {


        if (empty($personas['monto']))
            {

            echo "<tr>\n";
            //echo "<td><font color=green>Manuel Gomez</font></td>\n";
            echo "<td>".$personas['rif']."</td>\n";
            echo "<td>".$personas['nombres']."</td>\n";
            echo "<td>".$personas['apellidos']."</td>\n";
            //$monto_asignaciones=$personas['monto_incidencia']*2;
            echo "<td>".$personas['monto_incidencia']."</td>\n";
            echo "<td></td>\n";
            echo "<td></td>\n";
            echo "</tr>\n";
            }
        else
            {

            if ($nombre!=$personas['rif'].$personas['nombres'].$personas['apellidos'])
                {
                $nombre=$personas['rif'].$personas['nombres'].$personas['apellidos'];
                echo "<tr>\n";
                  //echo "<td><font color=green>Manuel Gomez</font></td>\n";
                echo "<td>".$personas['rif']."</td>\n";
                echo "<td>".$personas['nombres']."</td>\n";
                echo "<td>".$personas['apellidos']."</td>\n";
                echo "<td>".$personas['monto_incidencia']."</td>\n";
                }
           else
                {
                echo "<td>".$personas['monto']."</td>\n";
                echo "<td>".$personas['monto_incidencia']."</td>\n";
                echo "</tr>\n";
                }


            }

        }
echo "</table>\n";   
            }
        }


    public function export($serder,$param)
    {


    }
}


?>
