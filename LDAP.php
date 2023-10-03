<?php

namespace HiIAmOscurlo;

use Exception;
use InvalidArgumentException;

/**
 * @see https://github.com/OscurloOriginal/
 * @version 1.1
 * @author Esteban serna palacios
 */

class LDAP
{

    public $uri, $dn, $base;
    public $port = 389;

    public function __construct()
    {
        $this->uri = getenv("USERDNSDOMAIN");
        $this->dn = getenv("USERDOMAIN") . "\\";
        $this->base = "DC=" . getenv("USERDOMAIN") . ",DC=COM";
    }

    /**
     * @param String $user Usuario
     * @param String $pass Contraseña
     * @param String $search Valor a filtrar una vez establecida la conexión
     * @param Array $attributes Campos del directorio activo por los cuales puede filtrar
     * @param Array $setOptions Configuración de ldap enviar nombre de la constante y el valor para ldap_set_option
     * @return Array Retorna los datos según las coincidecia del filtro enviado
     */
    public function connect(String $user, String $pass, String $search = "*", array $attributes = ["cn", "sn", "givenName", "mail"], array $setOptions = []): array
    {
        $this->dn = $this->dn . $user;
        // if (empty($user) || empty($pass)) throw new InvalidArgumentException("Usuario y contraseña son obligatorios");
        if (empty($pass)) throw new InvalidArgumentException("Usuario y contraseña son obligatorios");

        # format filter
        $search = ldap_escape($search, "*", LDAP_ESCAPE_FILTER);
        $filter = "(|" . implode("", array_map(function ($x) use ($search) {
            return "({$x}={$search})";
        }, $attributes)) . ")";

        # set_option
        $setOptions = array_merge([
            "LDAP_OPT_PROTOCOL_VERSION" => 3,
            "LDAP_OPT_REFERRALS" => 0,
            "LDAP_OPT_SIZELIMIT" => 100
        ], $setOptions);

        # connect ldap
        try {
            $ldap = ldap_connect($this->uri, $this->port);

            foreach ($setOptions as $key => $value) if (defined($key))
                ldap_set_option($ldap, constant($key), $value);

            $ldap_bind = ldap_bind($ldap, $this->dn, $pass);
            if (!$ldap_bind) throw new Exception("Error al autenticar el usuario: " . ldap_error($ldap));

            # search
            $ldap_search = @ldap_search($ldap, $this->base, $filter, $attributes);
            if (!$ldap_search) throw new Exception("Error al realizar la búsqueda LDAP: " . ldap_error($ldap));

            $ldap_get_entries = ldap_get_entries($ldap, $ldap_search);
        } catch (Exception $th) {
            throw new Exception("Error al conectar con LDAP: {$th->getMessage()}", $th->getCode());
        } finally {
            # close connect
            if (isset($ldap)) ldap_close($ldap);
        }

        # return result
        return $ldap_get_entries;
    }
}
