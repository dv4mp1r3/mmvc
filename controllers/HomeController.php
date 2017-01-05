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
//        $review = new models\Review();
//        $review->loadFromPost();
//        $review->save();
        
        echo json_encode(['error' => 'ok']);
    }

    public function actionIndex()
    {
        $videos = models\Video::select(['user.name', 'video.url'])->
                join(models\Video::JOIN_TYPE_LEFT, 'user', 'user.id = video.user_id')->
                execute();  
        $data = array();
        foreach ($videos as &$video) 
        {
            $element = $video->asArray();
            $element['unique_id'] = $video->getVideoId();
            array_push($data, $element);
        }
        $this->appendVariable('videos', $data);
        $this->appendVariable('name', 'admin');
        $this->render('index'); 
    }

    public function actionInfo()
    {
        phpinfo();
    }
}