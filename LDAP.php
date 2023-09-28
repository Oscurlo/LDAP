<?php

namespace HiIAmOscurlo;

use Exception;
use InvalidArgumentException;

/**
 * @see https://github.com/OscurloOriginal/
 * @version 1
 * @author Esteban serna palacios
 */

class LDAP
{
    /**
     * @param String $user
     * @param String $pass
     * @param String $search
     * @param Array $attributes
     * @param Array $setOptions
     * @return Array
     */
    static function connect(String $user, String $pass, String $search = "", array $attributes = [], array $setOptions = []): array
    {
        if (empty($user) || empty($pass))
            throw new InvalidArgumentException("Usuario y contraseña son obligatorios");

        # format filter
        $base = "dc=" . getenv("USERDOMAIN") . ",dc=COM";
        $attributes = $attributes;
        define("FILTER", $search);

        $filter = "(|" . implode("", array_map(function ($x) {
            return "({$x}=" . FILTER . ")";
        }, $attributes)) . ")";

        # set_option
        $setOptions = array_merge([
            "LDAP_OPT_PROTOCOL_VERSION" => 3,
            "LDAP_OPT_REFERRALS" => 0,
            "LDAP_OPT_SIZELIMIT" => 100
        ], $setOptions);

        # connect ldap
        try {
            $ldap = ldap_connect(getenv("USERDNSDOMAIN"));
            foreach ($setOptions as $key => $value) if (defined($key))
                ldap_set_option($ldap, constant($key), $value);

            $ldap_bind = @ldap_bind($ldap, getenv("USERDOMAIN") . "\\{$user}", $pass);
            if (!$ldap_bind) throw new Exception("Error al autenticar el usuario", ldap_error($ldap));

            # filter
            $ldap_search = @ldap_search($ldap, $base, $filter, $attributes);
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
