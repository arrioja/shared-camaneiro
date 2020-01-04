<?php
// Se incluye TDbUserManager.php que define a TDbUser
Prado::using('System.Security.TDbUserManager');

/**
 * Clase usuario
 * la clase "usuario" representa los datos del usuario activo que es necesario
 * mantener en la sesión.
 */

class usuario extends TDbUser
{
    public $nombre; // nombre de usuario visible al público.
    public $cedula; // cédula de identidad del usuario activo.
    public $email; // correo electrónico del usuario activo.


    public function createUser($username)
    {
        // usa el active record para localizar al usuario por su login
        $userRecord=usuarios::finder()->findBy_login($username);
        if($userRecord instanceof usuarios) // if found
        {
            $user=new usuario($this->Manager);
            $user->nombre = $userRecord->login;  // se coloca el login
            $user->cedula = $userRecord->cedula;
            $user->email = $userRecord->email;
            $user->IsGuest=false;   // the user is not a guest
            return $user;
        }
        else
            return null;
    }

    /**
     * Este método es requerido por TDbUser.
     * y autentica al usuario con su login y su clave.
     */
    public function validateUser($username,$password)
    {
        // Usa el método finder del active record para localizar los valores.
        return usuarios::finder()->findBy_login_AND_clave($username,$password)!==null;
    }



    /**
     * @return boolean whether this user is an administrator.
     */
    public function getIsAdmin()
    {
        return $this->isInRole('admin');
    }
}

?>
