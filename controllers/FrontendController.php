<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 11/23/15
 * Time: 5:10 PM
 */

namespace voenniy\jsonrpc\controllers;


use voenniy\jsonrpc\components\UserAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class FrontendController extends Controller
{
    public $enableCsrfValidation = false;

    public function actions()
    {
        return array(
            'index' => array(
                'class' => 'voenniy\jsonrpc\components\Action',
            ),
        );
    }


    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),
            [
                'contentNegotiator' => [
                    'class' => ContentNegotiator::className(),
                    'formats' => [
                        'application/json' => 'jsonrpc',
                    ],
                ],
                'authenticator' => [
                    'class' => CompositeAuth::className(),
                    'optional' => ['index'],
                    'authMethods' => [
                        UserAuth::className(),
                        QueryParamAuth::className(),
                        HttpBearerAuth::className()
                    ],
                ],
                'corsFilter' => [
                    'class' => Cors::className(),
                ],

            ]);
    }


}