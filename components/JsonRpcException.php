<?php
namespace voenniy\jsonrpc\components;
use yii\base\ErrorException;
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

    private $exception = null;

    public function __construct($message, $code, $exception = null)
    {
        $this->exception = $exception;
        parent::__construct($message, $code);
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
        if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, \Yii::t('yii', 'An internal server error occurred.'));
        }

        $array = [
            'name' => ($exception instanceof \Exception || $exception instanceof ErrorException) ? $exception->getName() : 'Exception',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }
        if (YII_DEBUG) {
            $array['type'] = get_class($exception);
            if (!$exception instanceof UserException) {
                $array['file'] = $exception->getFile();
                $array['line'] = $exception->getLine();
                $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof \yii\db\Exception) {
                    $array['error-info'] = $exception->errorInfo;
                }
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = self::convertExceptionToArray($prev);
        }

        return $array;
    }
}