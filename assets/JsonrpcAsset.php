<?php

namespace voenniy\jsonrpc\assets;

use yii\web\AssetBundle;

class JsonrpcAsset extends AssetBundle {
    public $sourcePath = '@voenniy/jsonrpc/assets/js';

    public $js = [
        'jquery.textcomplete.min.js',
        'rpc_callback.js',
        'jquery.jsonrpc.js',
        'script.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
} 