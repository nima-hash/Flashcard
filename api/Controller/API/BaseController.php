<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../config/functions.php';

class BaseController
{
    
    //   automatically called when invoking inaccessible methods.
     
    public function __call($name, $arguments)
    {
        $this->sendOutput(json_encode(array('error' => 'API method not found.')), 404);
    }

    //  Send API output.
    protected function sendOutput($data, $statusCode = 200, $httpHeaders = array())
    {
        // header_remove('Set-Cookie'); 
        http_response_code($statusCode);

        // Set Content-Type header explicitly if not already set in $httpHeaders
        $contentTypeSet = false;
        foreach ($httpHeaders as $header) {
            if (stripos($header, 'Content-Type:') === 0) {
                $contentTypeSet = true;
                break;
            }
        }
        if (!$contentTypeSet) {
            header('Content-Type: application/json');
        }

        // Apply any additional headers
        foreach ($httpHeaders as $httpHeader) {
            header($httpHeader);
        }
        
        echo $data;
        exit;
    }
}