<?php

namespace app\controllers;

use app\models;
use app\core\AccessChecker;

class HomeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->rules = [
            'index' => [
            AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
        ];
    }

    public function actionUpload()
    {
        $review = new models\Review();
        $review->loadFromPost();
        $review->save();
        
        echo json_encode(['error' => 'ok']);
    }

    public function actionIndex()
    {
        $reviews       = models\Review::select()->where('is_approved=0')->execute();
        global $view_variable;
        $view_variable = $reviews;

        var_dump($reviews);
        $this->render('index'); 
    }

    public function actionInfo()
    {
        phpinfo();
    }
}