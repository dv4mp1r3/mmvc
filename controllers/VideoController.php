<?php

namespace app\controllers;

use app\models;
use app\core\AccessChecker;

class VideoController extends BaseController
{
    public function __construct()
    {       
        $this->rules = [
            'upload' => [
            AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
            'remove' => [
            AccessChecker::RULE_GRANTED => AccessChecker::USER_ALL,
            ],
        ];
        parent::__construct();
    }
    
    public function actionUpload()
    {
        try
        {
            $name = mysql_escape_string($_POST['user_name']);
            $user = models\User::select('id')->where('name = "'.$name.'"')->execute();
            if ($user === null)
            {
                $user = new models\User();
                $user->name = $name;
                $user->save();
            }

            $video = new models\Video();
            $video->url = mysql_escape_string($_POST['video_url']);
            $video->user_id = $user->id;
            $video->save();
            
            $params = ['isAdmin' => true,
                'video' => [
                    'url' => htmlspecialchars($video->url), 
                    'id' => intval($video->id), 
                    'username' => htmlspecialchars($user->name),
                ],
            ];

            $result = [               
                'html' => $this->getHtmlContent('views/home/webm_block.tpl', $params),
                'url' => htmlspecialchars($video->url), 
                ];
            
            echo json_encode(['error' => 0, 'data' => $result]);
        } 
        catch (\Exception $ex) 
        {
            echo json_encode(['error' => 1, 
                'data' => $ex->getMessage()]);
        }
        
    }
    
    public function actionRemove()
    {
        if (isset($_POST['video_id']))
            return $this->actionHide($_POST['video_id']);    
        
        echo json_encode(['error' => 1, 'data' => 'Wrong API parameters']);
    }
    
    public function actionUpdate()
    {
        if (isset($_POST['video_id']))
            return $this->actionHide($_POST['video_id']);
        
        echo json_encode(['error' => 1, 'data' => 'Wrong API parameters']);
    }
    
    private function actionHide($id)
    {
        if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true)
        {
            echo json_encode(['error' => 1, 'data' => 'Access level error']);
            return;
        }
        
        try
        {
            $result = models\Video::update(['is_viewed' => 1])->where('id ='.  intval($id))->execute();      
            echo json_encode(['error' => intval(!$result)]);
        } 
        catch (\Exception $ex) 
        {
            echo json_encode(['error' => 1, 'data' => $ex->getMessage()]);
        } 
    }
}

