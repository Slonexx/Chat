<?php

namespace App\Services;

use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ExcErrHandler
{
    /**
     * @param array $messageStack стек сообщений с контроллера. 
     * Нужен чтобы если какие-то этапы успешно завершились вывести их чтобы можно было посмотреть что отработало корректно.
     * @param Exception | Error $e 
     */
    function outputErrorStack(array $messageStack, Exception | Error $e){
        try{
            $current = $e;
            $messages = [];
            $statusCode = 500;//or HTTP Exception code
    
            while ($current !== null) {
                $filePath = $current->getFile();
                $fileLine = $current->getLine();
                $message = $current->getMessage();
    
                $nextError = $current->getPrevious();
    
                $parts = explode('|', $message);
    
                if (count($parts) === 2) {
                    $text = $parts[0];
                    $json_str = array_pop($parts);
    
                    $value = [
                        "message" => $text,
                        "data" => json_decode($json_str)
                    ];
                    if($nextError === null){
                        $messageStack["message"] = $text;
                        $code = $current->getCode();
                        if($code >= 400)
                            $statusCode = $code;
                    }
                } else {
                    $value = [
                        "message" => $message
                    ];
                    if($nextError === null){
                        $messageStack["message"] = $message;
                        $code = $current->getCode();
                        if($code >= 400)
                            $statusCode = $code;
                    }
                }
    
    
                $fileName = basename($filePath);
    
                $key = "{$fileName}:{$fileLine}";
    
                $messages[] = [
                    $key => $value
                ];
                $current = $current->getPrevious();
            }
            $messageStack["error"] = $messages;
            return response()->json($messageStack, $statusCode);
        } catch(Exception | Error $e){
            return response()->json("error in processing excs and errors", 500);
        }
        
    }
    
}