<?php
namespace app;

define('DEBUG', false);
define('ROOT_DIR', dirname(__FILE__));

require_once 'config.php';

function __autoload($classname)
{
    $filename = str_replace('app\\', '', $classname);
    $filename = ROOT_DIR.'/'.  str_replace('\\', '/', $filename).'.php';
    require_once $filename;
}

function vd($var)
{
    if (DEBUG)
        var_dump($var);
}

spl_autoload_register('app\\__autoload');

$url = null;
$ruri = $_SERVER['REQUEST_URI'];
$base_path = substr($ruri, 0, strpos($ruri, '/'));

$view_variable = [];

$url = $_GET['u'];
if (!$url)
{
    header('Refresh: 0; url=/?u=home-index');
}

$delemiter = strpos($url, '-');
$ctrl = substr($url, 0, $delemiter);
$action = substr($url, $delemiter+1);
$ctrl_name = ucfirst($ctrl).'Controller';

try
{
    $ctrl_name = 'app\\controllers\\'.$ctrl_name;
    $hc = new $ctrl_name();
    call_user_func(array($hc, 'action'.ucfirst($action)));
} 
catch (\Exception $ex) 
{
    if (DEBUG)
    {
        echo $ex->getMessage();
        echo '<br>';
        echo $ex->getTraceAsString();
    }
    else
    {
        echo 'something wrong';
    }
}


