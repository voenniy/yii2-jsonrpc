<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 13.11.14
 * Time: 14:14
 */

namespace common\modules\jsonrpc\controllers;

use yii\web\Controller;

class BackendController extends Controller{

    public function actionIndex(){
        return $this->render("index");
    }

    public function actions()
    {
        return array(
            'view' => array(
                'class' => 'common\modules\jsonrpc\components\Action',
                'debug' => true
            ),
        );
    }

    public function actionView2($command){
        preg_match("/([^.]*\.)?([^\(]*)\((.*)\)/", $command, $parsed);
        $object = str_replace(".", "", $parsed[1]);

        $method = $parsed[2];
        $params = explode(",", $parsed[3]);


        $object = \Yii::createObject('frontend\APIv1\\' . $object );

        if(!$object){
            $output = 'Не найден объект ' . $command;
            return $this->render('view', ['output'=>$output]);
        }

        $output = call_user_func_array([$object, $method], $params);


        return $this->render('view', ['output'=>$output]);
    }

    public function behaviors()
    {
        return [

        ];
    }
}