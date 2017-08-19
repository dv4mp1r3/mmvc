<?php 

require_once __DIR__.'/src/core/Loader.php';

spl_autoload_register('mmvc\\core\\Loader::load');
