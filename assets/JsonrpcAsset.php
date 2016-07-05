<?php

namespace voenniy\jsonrpc\assets;

use yii\web\AssetBundle;

class JsonrpcAsset extends AssetBundle {
    public $sourcePath = '@voenniy/jsonrpc/assets/js';

    public $js = [
        'rpc_callback.js',
        'jquery.jsonrpc.js',
        'script.js'
    ];
    public $depends = [
        'voenniy\jsonrpc\assets\JqueryAutocompleteAsset',
        'yii\web\JqueryAsset',
    ];
} 