<?php

function isPhp5x() {
    if (version_compare(PHP_VERSION, '7.0', '<')) {
        echo "true";
    } else {
        echo "false";
    }
}

$function = $argv[1];
if (function_exists($function)) {
    $function();
}
