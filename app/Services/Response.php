<?php
namespace App\Services;

class Response{
    public mixed $data;
    public int $statusCode;
    public bool $status;
    public ?string $message;
    /**
     * @var string[] $messageStack
     */
    public array $messageStack = [];

    /**
     * @param mixed $data message/response object
     * @param null|string $message additional message (empty by default)
     * @return \App\Services\Response
     * 
     * 
     * ```
     * //Examples:
     * 
     * $reS = new Response();
     * 
     * $reS->success("Dog Charlie was created");
     * $reS->success({ status : true }, "Request has status 200");
     * ```
     */
    public function success(mixed $data, ?string $message = null) : Response {
        $this->data = $data;
        $this->statusCode = 200;
        $this->status = true;
        if($message !== null)
            $this->message = $message;
        return $this;
    }

    /**
     * @param mixed $data message/response object
     * @param null|string $message additional message (empty by default)
     * @return \App\Services\Response
     * 
     * 
     * ```
     * //Examples:
     * 
     * $reS = new Response();
     * 
     * $reS->error("Error 500! Try later");
     * $reS->error("Bad data", "oh, no!");
     * $reS->error({ status : false }, "Request has status 400");
     * ```
     */
    public function error(mixed $data, ?string $message = null) : Response {
        $this->data = $data;
        $this->statusCode = 400;
        $this->status = true;
        if($message !== null)
            $this->message = $message;
        return $this;
    }

    /**
     * @param mixed $data message/response object
     * @param null|string $message additional message (empty by default)
     * @return \App\Services\Response
     * 
     * 
     * ```
     * //Examples:
     * 
     * $reS = new Response();
     * 
     * $reS->errorWith200("Error 500! Try later");
     * $reS->errorWith200("Bad data", "oh, no!");
     * $reS->error({ status : false }, "Request has status 400");
     * ```
     */
    public function errorWith200(mixed $data, ?string $message = null) : Response {
        $this->data = $data;
        $this->statusCode = 200;
        $this->status = false;
        if($message !== null)
            $this->message = $message;
        return $this;
    }

    public function addMessage(string $message) : Response {
        $this->message = $message;
        return $this;
    }

    /**
     * @param mixed $data message/response object
     * @param int $statusCode 
     * @param bool $status 
     * @param null|string $message additional message (empty by default)
     * @return \App\Services\Response
     * 
     * 
     * ```
     * //Examples:
     * 
     * $reS = new Response();
     * 
     * $reS->customResponse({ data: Dog Charlie was created }, 201, true);
     * $reS->customResponse({ data: Not found }, 404, false, "Данная страница не найдена");
     * ```
     */
    public function customResponse(mixed $data, int $statusCode, bool $status, ?string $message=null) : Response {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->status = $status;
        if($message !== null)
            $this->message = $message;
        return $this;
    }

    public function addInStack(string $message) : Response {
        $resMes = $this->message ?? false;
        if($resMes == false)
            $this->message = $message;
        else {
            array_unshift($this->messageStack, $this->message);
            $this->message = $message;
        }
        return $this;
    }

    public function addStackInStack(array $messageStack) : Response {
        $resMessageStack = $this->messageStack;
        $this->messageStack = array_merge($resMessageStack, $messageStack);
        return $this;
    }
}
