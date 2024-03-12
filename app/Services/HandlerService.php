<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class HandlerService{
    private mixed $response = [
        "statusCode" => 200,
        "status" => true
    ];
    private mixed $badResponse = [
        "statusCode" => 400,
        "status" => false
    ];

    function __construct() {
        $this->response = json_decode(json_encode($this->response));
        $this->badResponse = json_decode(json_encode($this->badResponse));
    }
    /**
     * Обрабатывает ответ response/badResponse(в основном для Controllers)
     * @param object $response response/badResponse
     * @param bool $returnOnlyDataWithMessage
     * @param bool $returnFullBadResponse
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseHandler(object $response, bool $returnOnlyDataWithMessage = true, bool $returnFullBadResponse = true) {
        $answer = null;
        if($response->status) {
            if($returnOnlyDataWithMessage){
                $answer = (object) [
                    "data" => $response->data,
                    "message" => $response->message ?? ""
                ];
            } else {
                $answer = $response;
            }

        } else {
            if($returnFullBadResponse){
                $answer = $response;

            } else {
                $answer = (object) [
                    "data" => $response->data,
                    "message" => $response->message ?? ""
                ];
            }
        }
        $message = $answer->message ?? null;
        //check property and its content
        if($message !== null && $message === "")
            unset($answer->message);
        return response()->json($answer, $response->statusCode);

    }
    /**
     * Обрабатывает ответ response/badResponse и возращает ответ с сообщением в начале (использовать вместе с createResponseMessageFirst) (в основном для Controllers)
     * @param object $response response/badResponse
     * @param bool $returnOnlyDataWithMessage
     * @param bool $returnFullBadResponse
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseHandlerFirstMessage(object $response, bool $returnOnlyDataWithMessage = true, bool $returnFullBadResponse = true) {
        $answer = null;
        if($response->status) {
            if($returnOnlyDataWithMessage){
                $answer = (object) [
                    "message" => $response->message ?? "",
                    "data" => $response->data
                ];
            } else {
                $answer = $response;
            }

        } else {
            if($returnFullBadResponse){
                $answer = $response;

            } else {
                $answer = (object) [
                    "message" => $response->message ?? "",
                    "data" => $response->data
                ];
            }
        }
        $message = $answer->message ?? null;
        //check property and its content
        if($message !== null && $message === "")
            unset($answer->message);
        return response()->json($answer, $response->statusCode);

    }
    /**
     * Создаёт ответ для последующей обработки(в основном для Services)
     * @param bool $isSuccess good=true/bad=false
     * @param mixed $data message/response object
     * @param bool $always200 override status code of answer to 200 (false by default)
     * @param string $message additional message (empty by default)
     * @return object response/badResponse
     */
    public function createResponse(bool $isSuccess, mixed $data, bool $always200=false, string $message ="") : object {
        if($isSuccess) {
            $this->response->data = $data;
            if($message !== "")
                $this->response->message = $message;
            return $this->response;
        } else {
            if($always200){
                $mixedResponse = $this->badResponse;
                $mixedResponse->data = $data;
                $mixedResponse->statusCode = 200;
                if($message !== "")
                    $mixedResponse->message = $message;
                return $mixedResponse;
            }
            $this->badResponse->data = $data;
            if($message !== "")
                $this->badResponse->message = $message;
            return $this->badResponse;
        }
    }

    /**
     * Создаёт ответ для последующей обработки с сообщением в начале(в основном для Services)
     * @param bool $isSuccess good=true/bad=false
     * @param mixed $data message/response object
     * @param bool $always200 override status code of answer to 200 (false by default)
     * @param string $message additional message (empty by default)
     * @return object response/badResponse
     */
    public function createResponseMessageFirst(bool $isSuccess, mixed $data, bool $always200=false, string $message ="") : object {
        if($isSuccess) {
            $this->response->data = $data;
            if($message !== "")
                $this->response = (object) (['message' => $message] + (array) $this->response);
            return $this->response;
        } else {
            if($always200){
                $mixedResponse = $this->badResponse;
                if($message !== "")
                    $mixedResponse->message = $message;
                $mixedResponse->data = $data;
                $mixedResponse->statusCode = 200;
                return $mixedResponse;
            }
            $this->badResponse->data = $data;
            if($message !== "")
                $this->badResponse = (object) (['message' => $message] + (array) $this->badResponse);
            return $this->badResponse;
        }
    }

    /**
     * Создаёт ответ 200 для последующей обработки(в основном для Services) 
     * Является улучшенным методом
     * +  
     * @param bool $isSuccess good=true/bad=false
     * @param mixed $data message/response object
     * @param bool $always200 override status code of answer to 200 (false by default)
     * @param string $message additional message (empty by default)
     * @return object response/badResponse
     */
    public function success(mixed $data, string $message =null) : object {
        $this->response->data = $data;
        if($message !== "")
            $this->response->message = $message;
        return $this->response;
    }

    public function error(mixed $data, string $message = null, bool $always200=false) : object {
        if($always200){
            $mixedResponse = $this->badResponse;
            $mixedResponse->data = $data;
            $mixedResponse->statusCode = 200;
            if($message !== "")
                $mixedResponse->message = $message;
            return $mixedResponse;
        }
        $this->badResponse->data = $data;
        if($message !== null)
            $this->badResponse->message = $message;
        return $this->badResponse;
    }



    public function getInfoByRequest(Request $request){
        $arrayData = $request->all();
        $objData = json_decode(json_encode($arrayData));
        return $objData;
    }

    public function checkAccountIdAndBody($inputObject) : object {
        $hasBody = property_exists($inputObject, "body");
        $hasAccountId = property_exists($inputObject, "accountId");

        if(!$hasBody || !$hasAccountId) {
            if(!$hasBody && !$hasAccountId)
                $this->badResponse->data = "отсутствует accountId и body";
            else if(!$hasAccountId)
                $this->badResponse->data = "отсутствует accountId";
            else if(!$hasBody)
                $this->badResponse->data = "отсутствует body";
            return $this->badResponse;
        }
        $response = $this->response;
        unset($response->statusCode);
        return $response;
    }

    public function checkAccountId($inputObject) : object {
        $hasAccountId = property_exists($inputObject, "accountId");

        if(!$hasAccountId) {
            $this->badResponse->data = "отсутствует accountId";
            return $this->badResponse;
        }
        $response = $this->response;
        unset($response->statusCode);
        return $response;
    }

    public function FormationMeta($obj) : object {
        return (object) [
            "meta" => $obj
        ];
    }

    public function FormationAttribute(object $attributeMeta, mixed $value) : object {
        $attributeObj = $this->FormationMeta($attributeMeta);
        $attributeObj->value = $value;
        $objectOfAttributes = (object) [
            "attributes" => [
                $attributeObj
            ]
        ];
        return $objectOfAttributes;
    }

    public function FormationFileAttribute(object $attributeMeta, mixed $value) : object {
        $attributeObj = $this->FormationMeta($attributeMeta);
        $attributeObj->file = $value;
        $objectOfAttributes = (object) [
            "attributes" => [
                $attributeObj
            ]
        ];
        return $objectOfAttributes;
    }

    public function FormationMetaById($urlIdentifier, $typeMeta, $id){
        $url = Config::get("Global")[$urlIdentifier];
        return (object) [
            "href" => $url . $id,
            "type" => $typeMeta
        ];
    }

}
