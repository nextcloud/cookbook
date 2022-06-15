<?php

require('/nextcloud/config/config.php');

$CONFIG['debug'] = true;

$dump = '<?php' . "\n" . '$CONFIG = ' . var_export($CONFIG, true) . ";\n";

file_put_contents('/nextcloud/config/config.php', $dump);

