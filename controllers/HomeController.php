<?php

namespace app\controllers;

use app\models;
use app\core\AccessChecker;

class HomeController extends BaseController {

    public function __construct() {
        $this->rules = [
            'index' => [
                AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
            'info' => [
                AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
        ];
        parent::__construct();
    }

    public function actionIndex() {
        return 'it works';
    }

    public function actionInfo() {
        phpinfo();
    }

}
