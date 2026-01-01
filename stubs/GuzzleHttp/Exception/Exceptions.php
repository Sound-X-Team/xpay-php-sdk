<?php

namespace GuzzleHttp\Exception;

class RequestException extends \Exception
{
    public function getResponse(): ?\Psr\Http\Message\ResponseInterface 
    {
        return null;
    }
}

class ConnectException extends RequestException {}

class ClientException extends RequestException {}

class ServerException extends RequestException {}