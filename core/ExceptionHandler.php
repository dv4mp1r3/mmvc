<?php
namespace app\core;

class ExceptionHandler extends \Exception
{
    /**
     * функция обработки исключений
     * @param \Exception $ex
     */
    public function doException($ex)
    {
        echo $ex->message.PHP_EOL;
        echo '<br>';
        echo $ex->getTraceAsString();

        

        exit();
    }
}