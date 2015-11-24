<?php

namespace common\modules\jsonrpc;

use common\modules\jsonrpc\assets\JsonrpcAsset;

class JsonRPCModule extends \yii\base\Module
{
    public  $apiNamespace = 'frontend\APIv1';

    public function init()
    {
        parent::init();

        \Yii::setAlias('@jsonrpc', __DIR__);

        $view = \Yii::$app->getView();
        JsonrpcAsset::register($view);
    }

    public function getApiPath()
    {
        return rtrim(\Yii::getAlias('@' . str_replace('\\', '/', $this->apiNamespace)), '/') . '/';
    }
}
