# LDAP
Pequeña clase para los que usan ldap para buscar información de un directorio activo. La clase la hice más pensando en que la van a usar con su servidor, así que use las variables de entorno "environment" para realizar la conexión ᕦ(ò_óˇ)ᕤ


## Ejemplo basico

```php
<?php

use HiIAmOscurlo\LDAP;

$result = json_encode(LDAP::connect("user", "pass", "example", ["name"]), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

print <<<HTML
    <pre>
        {$result}
    </pre>
HTML;
```
