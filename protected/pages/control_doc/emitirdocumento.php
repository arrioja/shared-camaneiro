<?php
require('/var/www/odtphp/library/odf.php');
class emitirdocumento extends TPage
{
    public function onload($param)
    {
         parent::onLoad($param);
         if(!$this->IsPostBack)
         {
             //llena el drop de cedulas
             $sql1="select * from organizacion.personas_cargo order by(cedula)";
             $resultado1=cargar_data($sql1, $this);
             $this->ddl1->datasource=$resultado1;
             $this->ddl1->databind();
             //llena el drop de plantillas
             $sql2="select * from plantillas.docu";
             $resultado2=cargar_data($sql2, $this);
             $this->ddl2->datasource=$resultado2;
             $this->ddl2->databind();
         }
    }
    public function llenarrtf($sender, $param)
    {
        if($this->ddl2->text=="BONOS VACACIONALES 2009.rtf")
        {
            //determino las variables
            $fecha1=date("d F Y"); //fecha actual con mes en letras
            $fecha2=date("d/n/Y"); //fecha actual pero con mes en 2 digitos
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select * from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select monto_incidencia from nomina.nomina where (cedula='$cedula' and cod_incidencia='7001')";
            $resultado=cargar_data($sql, $this);
            $sueldointegral=$resultado[0]['monto_incidencia']*2;
            $diario=($sueldointegral/30);
            $montodiario=number_format($diario, 2, ",", ".");
            $montobono=(($sueldointegral/30)*40);
            $bono=number_format($montobono, 2, ",", ".");
            $bonoletras=num_a_letras($montobono);
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select fecha_ingreso from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $fechaingreso=cambiaf_a_normal($resultado[0]['fecha_ingreso']);
            $dias_servicio=intval(num_dias_entre_fechas($ingreso,$fecha2));
            $servicio=intval($dias_servicio/365);
            $sql="select * from asistencias.vacaciones_disfrute where(cedula='6122659') order by id desc";
            $resultado=cargar_data($sql, $this);
            $periodo=$resultado[0]['periodo'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/BONOS VACACIONALES 2009.odt");
            $odf->setVars("fecha", $fecha1);
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("bono", $bono);
            $odf->setVars("bonoletras", $bonoletras);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("fechaingreso", $fechaingreso);
            $odf->setVars("servicio", $servicio);
            $odf->setVars("periodo", $periodo);
            $odf->setVars("montodiario", $montodiario);
            $odf->setVars("sueldointegral", $sueldointegral);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="Constancias de Trabajo 2009.rtf")
        {
            //determino las variables
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select * from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select monto_incidencia from nomina.nomina where (cedula='$cedula' and cod_incidencia='7001')";
            $resultado=cargar_data($sql, $this);
            $sueldointegral=$resultado[0]['monto_incidencia']*2;
            $sql="select fecha_ingreso from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $fecha=date("d/n/Y");//fecha actual
            list($diaactual, $mescorriente, $anoactual)=explode("/", $fecha);
            $diaactualletras=num_a_letras($diaactual);
            $anoactualletras=num_a_letras($anoactual);
            if($mescorriente=='01')
            {$mesactual='Enero';}
            if($mescorriente=='02')
            {$mesactual='Febrero';}
            if($mescorriente=='03')
            {$mesactual='Marzo';}
            if($mescorriente=='04')
            {$mesactual='Abril';}
            if($mescorriente=='05')
            {$mesactual='Mayo';}
            if($mescorriente=='06')
            {$mesactual='Junio';}
            if($mescorriente=='07')
            {$mesactual='Julio';}
            if($mescorriente=='08')
            {$mesactual='Agosto';}
            if($mescorriente=='09')
            {$mesactual='Septiembre';}
            if($mescorriente=='10')
            {$mesactual='Octubre';}
            if($mescorriente=='11')
            {$mesactual='Noviembre';}
            if($mescorriente=='12')
            {$mesactual='Diciembre';}
            $fechaingreso=cambiaf_a_normal($resultado[0]['fecha_ingreso']);
            list($diaingreso, $mes, $anoingreso)=explode("/", $fechaingreso);
            if($mes=='01')
            {$mesingreso='Enero';}
            if($mes=='02')
            {$mesingreso='Febrero';}
            if($mes=='03')
            {$mesingreso='Marzo';}
            if($mes=='04')
            {$mesingreso='Abril';}
            if($mes=='05')
            {$mesingreso='Mayo';}
            if($mes=='06')
            {$mesingreso='Junio';}
            if($mes=='07')
            {$mesingreso='Julio';}
            if($mes=='08')
            {$mesingreso='Agosto';}
            if($mes=='09')
            {$mesingreso='Septiembre';}
            if($mes=='10')
            {$mesingreso='Octubre';}
            if($mes=='11')
            {$mesingreso='Noviembre';}
            if($mes=='12')
            {$mesingreso='Diciembre';}
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/Constancias de Trabajo 2009.odt");
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("sueldointegral", $sueldointegral);
            $odf->setVars("diaingreso", $diaingreso);
            $odf->setVars("mesingreso", $mesingreso);
            $odf->setVars("anoingreso", $anoingreso);
            $odf->setVars("diaactualletras", $diaactualletras);
            $odf->setVars("diaactual", $diaactual);
            $odf->setVars("mesactual", $mesactual);
            $odf->setVars("anoactualletras", $anoactualletras);
            $odf->exportAsAttachedFile("generado.odt");            
        }
        if($this->ddl2->text=="Constancias de Vacaciones 2009 .rtf")
        {
            //determino las variables
            $fecha=date("d F Y");
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;
            $sql="select * from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select fecha_ingreso from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $ingreso=cambiaf_a_normal($resultado[0]['fecha_ingreso']);
            $dias_servicio=intval(num_dias_entre_fechas($ingreso,$fecha2));
            $servicio=intval($dias_servicio/365);
            $sql="select * from asistencias.vacaciones_disfrute where(cedula='$cedula') order by id desc";
            $resultado=cargar_data($sql, $this);
            $desde=$resultado[0]['fecha_desde'];
            $hasta=$resultado[0]['fecha_hasta'];
            $periodo=$resultado[0]['periodo'];
            $disfrute=$resultado[0]['dias_disfrute'];
            $disletras=num_a_letras($disfrute);
            $sql="select * from asistencias.vacaciones where(cedula='5477905') order by id desc";
            $resultado=cargar_data($sql, $this);
            $pendiente=$resultado[0]['pendientes'];
            $penletras=num_a_letras($pendiente);
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/Constancias de Vacaciones 2009 .odt");
            $odf->setVars("fecha", $fecha);
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres, false, utf-8);
            $odf->setVars("apellidos", $apellidos, false, utf-8);
            $odf->setVars("fechaingreso", $fechaingreso);
            $odf->setVars("servicio", $servicio);
            $odf->setVars("periodo", $periodo);
            $odf->setVars("desde", $desde);
            $odf->setVars("hasta", $hasta);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="Formato Punto de Cuenta2007.rtf")
        {
            //determino las variables
            $fecha=date("d F Y");
            $cedula=$this->ddl1->text;
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            $sql="select monto_incidencia from nomina.nomina where (cedula='$cedula' and cod_incidencia='1000')";
            $resultado=cargar_data($sql, $this);
            $sueldobasicomensual=$resultado[0]['monto_incidencia']*2;
            $sueldobasicomensualletras=num_a_letras($sueldobasicomensual);
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/Formato Punto de Cuenta2007.odt");
            $odf->setVars("fecha", $fecha);
            $odf->setVars("cedula", $cedula);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("direccion", $direccion);
            $odf->setVars("sueldobasicomensual", $sueldobasicomensual);
            $odf->setVars("sueldobasicomensualletras", $sueldobasicomensualletras);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="FORMATOS DE SOLICITUDES DE PERMISOS.rtf")
        {
            //determino las variables
            $fecha=date("d F Y");
            list($dia, $mes, $ano)=explode(" ", $fecha);
            $cedula=$this->ddl1->text;
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/FORMATOS DE SOLICITUDES DE PERMISOS.odt");
            $odf->setVars("fecha", $fecha);
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("direccion", $direccion);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="solicitud de vacaciones.rtf")
        {
            //determino las variables
            $fecha=date("d F Y");            
            $cedula=$this->ddl1->text;            
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];            
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];            
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/solicitud de vacaciones.odt");
            $odf->setVars("fecha", $fecha);
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("direccion", $direccion);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="solicitud permiso obligatorio.rtf")
        {
            //determino las variables
            $cedula=$this->ddl1->text;
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];            
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/solicitud permiso obligatorio.odt");
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("direccion", $direccion);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="formato punto de cuenta-movimiento de personal.odt")
        {
            ////determino las variables
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            $sql="select monto_incidencia from nomina.nomina where (cedula='$cedula' and cod_incidencia='7001')";
            $resultado=cargar_data($sql, $this);
            $sueldointegral=$resultado[0]['monto_incidencia']*2;
            $sueldointegralletras=num_a_letras($sueldointegral);
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/formato punto de cuenta-movimiento de personal.odt");
            $odf->setVars("cedula", $cedula);
            $odf->setVars("fecha", $fecha2);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("direccion", $direccion);
            $odf->setVars("sueldointegral", $sueldointegral);
            $odf->setVars("sueldointegralletras", $sueldointegralletras);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="formato punto de cuenta-prima profesionalizacion.odt")
        {
            //determino las variables
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/formato punto de cuenta-prima profesionalizacion.odt");
            $odf->setVars("fecha", $fecha2);
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("direccion", $direccion);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="formato punto de cuenta-nombramiento.odt")
        {
            //determino las variables
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            $sql="select monto_incidencia from nomina.nomina where (cedula='$cedula' and cod_incidencia='1000')";
            $resultado=cargar_data($sql, $this);
            $sueldobasicomensual=$resultado[0]['monto_incidencia']*2;
            $sueldobasicomensualletras=num_a_letras($sueldobasicomensual);
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/formato punto de cuenta-nombramiento.odt");
            $odf->setVars("cedula", $cedula);
            $odf->setVars("fecha", $fecha2);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("direccion", $direccion);
            $odf->setVars("sueldobasicomensual", $sueldobasicomensual);
            $odf->setVars("sueldobasicomensualletras", $sueldobasicomensualletras);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="formato punto de cuenta-homologacion.odt")
        {
            //determino las variables
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/formato punto de cuenta-homologacion.odt");
            $odf->setVars("cedula", $cedula);
            $odf->setVars("fecha", $fecha2);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="formato punto de cuenta-designacion.odt")
        {
            //determino las variables
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            $sql="select denominacion  from organizacion.personas_cargo where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cargo=$resultado[0]['denominacion'];
            $sql="select cod_direccion from organizacion.personas_nivel_dir where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $cod_direccion=$resultado[0]['cod_direccion'];
            $sql="select nombre_completo from organizacion.direcciones where (codigo='$cod_direccion')";
            $resultado=cargar_data($sql, $this);
            $direccion=$resultado[0]['nombre_completo'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/formato punto de cuenta-designacion.odt");
            $odf->setVars("cedula", $cedula);
            $odf->setVars("fecha", $fecha2);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->setVars("cargo", $cargo);
            $odf->setVars("direccion", $direccion);
            $odf->exportAsAttachedFile("generado.odt");
        }        
        if($this->ddl2->text=="formato punto de cuenta-aumento de jubilacion.odt")
        {
            //determino las variables
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];            
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/formato punto de cuenta-aumento de jubilacion.odt");
            $odf->setVars("fecha", $fecha2);
            $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->exportAsAttachedFile("generado.odt");
        }
        if($this->ddl2->text=="formato punto de cuenta-aumento de pensionado.odt")
        {
            //determino las variables
            $fecha2=date("d/n/Y");
            $cedula=$this->ddl1->text;// cedula del solicitante del documento
            $sql="select nombres, apellidos from organizacion.personas where (cedula='$cedula')";
            $resultado=cargar_data($sql, $this);
            $nombres=$resultado[0]['nombres'];
            $apellidos=$resultado[0]['apellidos'];
            //reemplazo las variables
            $odf = new odf("/var/www/cene/plantillas/formato punto de cuenta-aumento de pensionado.odt");
            $odf->setVars("fecha", $fecha2);
             $odf->setVars("cedula", $cedula);
            $odf->setVars("nombres", $nombres);
            $odf->setVars("apellidos", $apellidos);
            $odf->exportAsAttachedFile("generado.odt");
        }
    }    
}
?>