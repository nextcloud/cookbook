<?php

require('/var/www/html/config/config.php');

$CONFIG['debug'] = true;

$dump = '<?php' . "\n" . '$CONFIG = ' . var_export($CONFIG, true) . ";\n";

file_put_contents('/var/www/html/config/config.php', $dump);

