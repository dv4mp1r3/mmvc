<?php

namespace app\controllers;
use app\models;

require_once dirname(__FILE__).'/../models/Review.php';

class HomeController extends BaseController 
{
    public function __construct() {
        parent::__construct();
        $this->rules = [
            'index' => [
                'access' => '*',    
            ],
        ];
    }
    
    /**
     * 
     * @param array $data
     */
    public function actionUpload()
    {       
        $review = new models\Review();
        $review->loadFromPost();
        $review->save();
        
        echo json_encode(['error' => 'ok']);
    }
    
    public function actionIndex()
    {   
        $reviews = models\Review::findByCriteria("is_approved=1");
        global $view_variable;
        $view_variable = $reviews;
        $this->render('index');    
        
        $review = new models\Review(1);
    }
}
