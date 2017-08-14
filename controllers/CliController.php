<?php namespace mmvc\controllers;

use mmvc\controllers\BaseController;

class CliController extends BaseController
{    
    protected function getInput($name, $filterType = null, $inputType = null)
    {
        $value = $_SERVER['argv'][$name];
        return filter_var($value, $filterType);
    }
    
    protected function printLine($message)
    {
        echo "$message\n";
    }
    
    /**
     * Вывод в stdout информации о выброшенном исключении
     * @see mmvc\core\ExceptionHandler::doCliAppException()
     * @param \Exception $ex
     */
    public function printExceptionData($ex)
    {
        $this->printLine("Exception: ".get_class($ex));
        $this->printLine("Message: ".$ex->getMessage());
        $this->printLine("File: ".$ex->getFile());
        $this->printLine("Line: ".$ex->getLine());
        $this->printLine("(stacktrace): ".$ex->getTraceAsString());
    }
}
