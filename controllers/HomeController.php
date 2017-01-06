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
        try
        {
            $user = models\User::select('id')->where('name = "'.$_POST['user_name'].'"')->execute();
            if ($user === null)
            {
                $user = new models\User();
                $user->name = $_POST['user_name'];
                $user->save();
            }

            $video = new models\Video();
            $video->url = $_POST['video_url'];
            $video->user_id = $user->id;
            $video->save();

            $result = ['url' => $video->url, 'id' => $video->id, 'username' => $user->name];
            
            echo json_encode(['error' => 0, 'data' => json_encode($result)]);
        } 
        catch (\Exception $ex) 
        {
            echo json_encode(['error' => 1, 
                'data' => $ex->getTraceAsString()]);
        }
        
    }

    public function actionIndex()
    {
        $videos = models\Video::select(['user.name username', 'video.url', 'video.id'])->
                join(models\Video::JOIN_TYPE_LEFT, 'user', 'user.id = video.user_id')->
                execute();  
        $data = array();
        $video_urls = array();
        foreach ($videos as &$video) 
        {
            $element = $video->asArray();
            $element['unique_id'] = $video->getVideoId();
            array_push($data, $element);
            array_push($video_urls, $video->url);
        }
        $this->appendVariable('videos', $data);
        $this->appendVariable('video_urls', json_encode($video_urls));
        $this->render('index'); 
    }

    public function actionInfo()
    {
        phpinfo();
    }
}