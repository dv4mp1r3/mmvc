<?php
namespace app\core;

use app\controllers\ErrorController;

class ExceptionHandler
{
    /**
     * функция обработки исключений
     * @param \Exception $ex
     */
    public static function doException($ex)
    {
        $err_ctrl = new ErrorController();
        if (DEBUG === false)
        {
            $err_ctrl->actionBase();
        }
        else
        {
            $err_ctrl->actionDetails($ex);
        }
        exit();
    }

    public static function doError($errLevel, $errMsg)
    {
        
    }
}