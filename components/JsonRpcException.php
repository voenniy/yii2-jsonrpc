<?php
namespace voenniy\jsonrpc\components;

use yii\base\UserException;
use yii\web\HttpException;

class JsonRpcException extends \yii\base\Exception
{

    const PARSE_ERROR = -32700;
    const INVALID_REQUEST = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL_ERROR = -32603;
    const UNAUTHORIZED   = -32001;

    protected $exception = null, $method;

    public function __construct($message, $code, $exception = null, $method=null)
    {
        $this->exception = $exception;
        $this->method = $method;
        parent::__construct($message, $code);
    }

    public function getName($code = null)
    {
        $names = [
            self::PARSE_ERROR => 'Parse error',
            self::INVALID_REQUEST => 'Invalid request',
            self::METHOD_NOT_FOUND => 'Method not found',
            self::INVALID_PARAMS => 'Invalid params',
            self::INTERNAL_ERROR => 'Internal error',
            self::UNAUTHORIZED => 'Unauthorized',
        ];
        return isset($names[$code]) ? $names[$code] : 'Error';
    }

    public function getErrorAsArray()
    {
        $result = [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ];
        if ($this->exception !== null) {
            $result['data'] = $this->convertExceptionToArray($this->exception);
        }
        return $result;
    }

    public static function convertExceptionToArray($exception)
    {
        $rpc = null;
        if (isset($exception->method) && $exception->method !== null) {
            $rpc = [
                'class' => $exception->method->class,
                'method' => $exception->method->name,
            ];
        }

        if (isset($exception->exception) && $exception->exception !== null) {
            $exception = $exception->exception;
        }
        if (!YII_DEBUG && !($exception instanceof UserException) && !($exception instanceof HttpException)) {
            $exception = new HttpException(500, \Yii::t('yii', 'An internal server error occurred.'));
        }

        $array = [
            'name' => $exception->getName(),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }
        if (YII_DEBUG) {
            $array['type'] = get_class($exception);
            $array['file'] = $exception->getFile();
            $array['line'] = $exception->getLine();
            $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
            if ($exception instanceof \yii\db\Exception) {
                $array['error-info'] = $exception->errorInfo;
            }

            if ($rpc !== null) {
                $array['rpc'] = $rpc;
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = self::convertExceptionToArray($prev);
        }

        return $array;
    }
}
