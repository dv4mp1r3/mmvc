<?php

namespace app\core;

class ViewTemplate
{
    private $name;
    private $view;

    public function __construct($ctrl, $view)
    {
        $this->name = $ctrl;
        $this->view = $view;
    }

    protected function doctype()
    {
        echo '<!DOCTYPE html>';
    }

    protected function header()
    {
        echo '<head>
        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="css/my.css">
        <script src="js/jquery.js"></script>       
        <script src="js/bootstrap.js"></script>
        <script src="js/my.js"></script>
        </head>';
    }

    protected function footer()
    {
        
    }

    public function content($params = null)
    {
        if (isset($params)) {
            global $view_variable;
            $view_variable = $params;
        }
        require_once ROOT_DIR.'/views/'.$this->name.'/'.$this->view.'.php';
    }

    public function doHtml()
    {
        $this->doctype();
        echo '<html><body>';
        $this->header();
        $this->content();
        $this->footer();
        echo '</body></hmtl>';
    }
}