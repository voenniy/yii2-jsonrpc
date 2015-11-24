<?php
namespace common\modules\jsonrpc\components\traits;

use common\modules\jsonrpc\components\Exception;

trait Serializable
{
    use Request;
    public $exception;
    public $result = false;

    public function toJson()
    {

        return json_encode($this->toArray());
    }

    public function toArray(){
        $request = json_decode($this->_requestMessage, true);
        $answer  = [
            'jsonrpc' => '2.0',
            'id'      => isset($request['id']) ? $request['id'] : $this->newId(),
        ];
        if ($this->exception) {
            if ($this->exception instanceof Exception) {
                $answer['error'] = $this->exception->getErrorAsArray();
            } else {
                $answer['error'] = [
                    'code'    => Exception::INTERNAL_ERROR,
                    'message' => $this->exception
                ];
            }
        }
        $answer['result'] = $this->result;
        if (self::isValidJsonRpc($answer)) {
            $answer['error'] = [
                'code'    => Exception::INTERNAL_ERROR,
                'message' => 'Internal error'
            ];
        }
        return $answer;
    }

    public function isSuccessResponse()
    {
        return !$this->exception;
    }

    public function newId()
    {
        return md5(microtime());
    }

    public static function isValidJsonRpc($response)
    {
        $version    = isset($response['jsonrpc']) && $response['jsonrpc'] == '2.0';
        $method     = isset($response['method']);
        $data       = isset($response['result']) || isset($response['error']);
        $additional = true;
        if (isset($response['error'])) {
            $additional = isset($response['error']['code'], $response['error']['message']);
        }

        return $version && $method && $data && $additional;
    }

    public static function checkContentType()
    {
        return empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/json-rpc';
    }
}