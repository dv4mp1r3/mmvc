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
        $reviews       = models\Review::findByCriteria("is_approved=0");
        global $view_variable;
        $view_variable = $reviews;
        $this->render('index');

        $review = new models\Review(1);

        echo $review;
    }
}