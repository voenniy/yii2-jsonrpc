<?php
/**
 * Created by PhpStorm.
 * User: oleg
 * Date: 13.11.14
 * Time: 18:22
 */

namespace voenniy\jsonrpc\components;


class JsonRPC  {
    protected $method = [];

    public function __call($method, $params){
        // Если передан ассоциативный массив, то берем только первый элемент, в нём будут содержаться именнованые параметры
        if(($namedMethod = str_replace("__named", "", $method)) != $method){
            $method = $namedMethod;
            $params = current($params);
        }
        $this->method = ['method'=>$method, 'params' => $params];
    }

    public function __toString(){
        $s = '';
        if($this->method){
            $request = [
                'jsonrpc' => '2.0',
                'method' => $this->method['method'],
                'params' => $this->method['params'],
                'id' => uniqid()
            ];
            $s .= json_encode($request);
        }
        $this->method = null;
        return $s;
    }
} 