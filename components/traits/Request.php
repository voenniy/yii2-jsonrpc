<?php
namespace common\modules\jsonrpc\components\traits;

trait Request {

    private $_requestMessage;
    private $_data;

    public function setRequestMessage($message)
    {
        \Yii::info("Message: ". $message, "RPC");
        $this->_requestMessage = $message;
        $this->_data = json_decode($message, true);
        \Yii::info("Decode message: ". print_r($this->_data, 1), "RPC");
    }

    public function getParams()
    {
        return $this->_data['params'];
    }

    public function setParams($params)
    {
        $this->_data['params'] = $params;
    }

    public function getMethod()
    {
        return isset($this->_data['method']) ? $this->_data['method'] : null;
    }

    public function setMethod($method){
        $this->_data['method'] = $method;
    }

    public function getObject(){
        return isset($this->_data['object']) ? $this->_data['object'] : $this->controller;
    }

    public function setObject($object){
        $this->_data['object'] = $object;
    }

    public function getRequestId()
    {
        return isset($this->_data['id']) ? $this->_data['id'] : $this->newId();
    }
}