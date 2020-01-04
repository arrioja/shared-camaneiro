<?php
/*****************************************************  INFO DEL ARCHIVO
 * Creado por: 	Pedro E. Arrioja M.
 * Descripción: Esta pagina brinda las funciones comunes de autenticacion y de
 *              permisos relacionados con los usuarios del sistema, la
 *              información relevante de los mismos, se almacena en variables
 *              de sesión.
 *****************************************************  FIN DE INFO
*/

/* Función que permite la validación del nombre de usuario y clave
 * proporcionada al momento de la autenticación.
 */
function validar($username,$password)
{
    $password=substr(MD5($password), 0, 200);    
    // Usa el método finder del active record para localizar los valores.
    if ($usr_val = usuarios::finder()->findBy_login_AND_clave($username,$password))
        { return $usr_val; }
    else
        { return null; }
}

/* Esta funcion permite realizar el llenado de la variable usuario en forma de
 * arreglo en la session una vez que los datos de usuario y clave han sido
 * debidamente validados.
 */
function login_usuario($username,$password,$sender)
{
    $usuario_validado = validar($username,$password);
    if (!($usuario_validado == null))
    {
        $sesion=new THttpSession;
        $sesion->open();
        $sesion['tipo_nomina']="EMPLEADOS";
        $sesion['login']=$usuario_validado->login;
        $sesion['cedula']=$usuario_validado->cedula;
        $sesion['email']=$usuario_validado->email;
        /* Con el número de cédula del usuario, se localizan los datos de la
         * organizacion a la que pertenece.
         */
        $sql="SELECT n.cod_organizacion, o.nombre, o.rif, d.codigo, d.nombre_completo, d.siglas
                FROM organizacion.personas_nivel_dir n, organizacion.organizaciones o, organizacion.direcciones d
                WHERE ((d.codigo = n.cod_direccion) and (o.codigo = n.cod_organizacion) and (n.cedula = '$usuario_validado->cedula'))";
        $resultado=cargar_data($sql,$sender);
        $sesion['cod_organizacion'] = $resultado[0]['cod_organizacion'];
        $sesion['rif_organizacion'] = $resultado[0]['rif'];
        $sesion['nombre_organizacion'] = $resultado[0]['nombre'];
        $sesion['cod_direccion'] = $resultado[0]['codigo'];
        $sesion['siglas_direccion'] = $resultado[0]['siglas'];
        $sesion['nombre_direccion'] = $resultado[0]['nombre_completo'];
        $sesion->close();
        return true;
    }                                   
    else
    { return false; }
}

/* Esta función se encarga de realizar el deslogueo del usuario del sistema
 * limpia las variables que la sesion se encuentre usando y al final la
 * destruye para mayor seguridad.
 */
function logout_usuario($objeto_local)
{
    /* Se incluye el rastro en el archivo de bitácora */
    $descripcion_log = "Salida del sistema.";
    inserta_rastro(usuario_actual('login'),usuario_actual('cedula'),'L',$descripcion_log,"",$objeto_local);
    $sesion = new THttpSession;
    $sesion->open();
    $sesion->clear();
    $sesion->close();
    $sesion->destroy();
    $objeto_local->Response->redirect($objeto_local->Service->constructUrl('intranet.login'));
    return true;
}

/* Esta función se encarga de brindar una comprobación genérica de que el usuario
 * haya realizado un logueo en el sistema, si no lo ha hecho, simplemente se
 * redirecciona a la pagina de login.
 */
function comprueba_sesion($this2)
{
    $sesion = new THttpSession;
    $sesion->open();
    if (!isset($sesion['login']))
    {
        $this2->Response->redirect($this2->Service->constructUrl('intranet.login'));
    }
    $this2->lbl_usuario_top->Text=$sesion['login'];
    $sesion->close();
}

/* Esta función se encarga de retornar la información del usuario actualmente
 * logeado en el sistema para no tener que abrir extraer y cerrar sesiones cada
 * vez que se necesita en las páginas, sino que se hace siempre desde aqui.
 * Ejemplo: $login = usuario_actual('login');
 * Los valores que acepta la variable "request" están definidos en la función
 * login_usuario
 */
function usuario_actual($request)
{
    $sesion=new THttpSession;
    $sesion->open();    
    if (!isset($sesion['login']))
    {
        return null;
    }
    else
    {
        $valor_a_devolver = $sesion[$request];
    }
    $sesion->close();
    return $valor_a_devolver;
}



?>
