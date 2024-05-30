<?php

namespace App\Services\MoySklad;

use App\Clients\MoySklad;
use App\Exceptions\AgentControllerLogicException;
use App\Services\HandlerService;
use App\Services\MoySklad\Attributes\CustomorderS;
use App\Services\MoySklad\Entities\CustomEntityService;
use App\Services\MoySklad\Entities\TaskService;
use App\Services\MoySklad\RequestBody\Attributes\UpdateValuesService;
use App\Services\Response;
use Error;
use Exception;
use Illuminate\Support\Facades\Config;
use stdClass;

class AgentControllerLogicService
{

    private MoySklad $msC;

    private string $accountId;

    private Response $res;

    function __construct($accountId, MoySklad $MoySklad = null)
    {
        if ($MoySklad == null) $this->msC = new MoySklad($accountId);
        else  $this->msC = $MoySklad;
        $this->accountId = $accountId;
        $this->res = new Response();
    }

    /**
     * @throws AgentControllerLogicException
     */
    function createOrderAndAttributes($orderDbSettings, $agent, CustomerorderCreateLogicService $customOrderS, $responsible, $responsibleUuid, $isCreateOrder, $infoForTask)
    {
        $handlerS = new HandlerService();
        //agentId
        /* dd($responsible, $responsibleUuid);*/


        $agentHref = $agent->meta->href;
        $agentPhone = $agent->phone ?? false;
        $agentEmail = $agent->email ?? false;
        $agentId = basename($agentHref);
        //agentId
        $agentAttr = $agent->attributes ?? null;
        //findOrCreateAttribute
        $attributesS = new LidAttributesCreateService($this->accountId, $this->msC);
        $serviceFieldsNames = [
            "lid",
        ];
        //вынести выше
        $config = Config::get("lidAttributes");
        $serviceFields = array_filter($config, fn($key) => in_array($key, $serviceFieldsNames), ARRAY_FILTER_USE_KEY);
        //вынести выше
        $attributesS->findOrCreate($serviceFields, $isCreateOrder);
        //вынести выше
        //getCreatedAttribute
        $lidName = $serviceFields["lid"]->name;
        //ожидает ответа
        $valueName = $serviceFields["lid"]->values[0]->name;
        //вынести выше
        $updateValuesS = new UpdateValuesService();
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        $agentUpdateS = new AgentUpdateLogicService($this->accountId, $this->msC);


        try {
            //updateAgentAttribute
            if ($agentAttr == null) {
                $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
            } else {
                $settedAttribute = array_filter($agentAttr, fn($value) => $value->name == $lidName);
                if (empty($settedAttribute))
                    $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
                else {
                    $settedAttribute = array_shift($settedAttribute);
                    $settedAttributeValueName = $settedAttribute->value->name;
                    if ($settedAttributeValueName != $valueName) {
                        $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $valueName, $updateValuesS, $customEntityS);
                    }
                }

            }

        } catch (Exception|Error $e) {
            throw new AgentControllerLogicException("Ошибка при обновлении контрагента во время создания заказа", 1, $e);
        }

        try {
            //createOrder
            if ($isCreateOrder) {
                $organId = $orderDbSettings->organization;
                $organization_account = $orderDbSettings->organization_account;
                $project_uid = $orderDbSettings->project_uid;
                $sales_channel_uid = $orderDbSettings->sales_channel_uid;
                $states = $orderDbSettings->states;

                $orderAttrS = new CustomorderS($this->accountId, $this->msC);
                $orderAttrRes = $orderAttrS->getAllAttributes(true);
                $orderLidAttr = array_filter($orderAttrRes->data, fn($value) => $value->name == $lidName);
                $orderAttr = array_shift($orderLidAttr);
                $attributes = $updateValuesS->dictionary($customEntityS, $orderAttr, $valueName);

                //formation meta Logic
                $organMeta = $handlerS->FormationMetaById("organization", "organization", $organId);
                $organAccountMeta = null;
                if ($organization_account) {
                    $organAccountMeta = (object)['href' => [], 'type' => []];
                    $organAccountMeta->href = $organMeta->href . "/accounts/$organization_account";
                    $organAccountMeta->type = "account";
                }

                $projectMeta = null;
                if ($project_uid)
                    $projectMeta = $handlerS->FormationMetaById("project", "project", $project_uid);
                $salesChannelMeta = null;
                if ($sales_channel_uid)
                    $salesChannelMeta = $handlerS->FormationMetaById("saleschannel", "saleschannel", $sales_channel_uid);
                $stateMeta = null;

                if ($states)
                    $stateMeta = $handlerS->FormationMetaById("state", "state", $states);

                $preparedMetas = new stdClass();
                $preparedMetas->agent = $handlerS->FormationMeta($agent->meta);
                $preparedMetas->organization = $handlerS->FormationMeta($organMeta);

                $preparedMetas->organizationAccount = $handlerS->FormationMeta($organAccountMeta);
                $preparedMetas->project = $handlerS->FormationMeta($projectMeta);
                $preparedMetas->salesChannel = $handlerS->FormationMeta($salesChannelMeta);
                $preparedMetas->state = $handlerS->FormationMeta($stateMeta);

                if(property_exists($agent, 'owner'))
                    $agentOwnerId = basename($agent->owner->meta->href);
                else
                    $agentOwnerId = null;

                $order = $customOrderS->createBySettings($agentId, $agentOwnerId, $preparedMetas, $responsible, $responsibleUuid, $attributes);
                $tasks = $orderDbSettings->tasks;
                $lid = $orderDbSettings->lid;

                //create task
                if ($tasks) {
                    $bool = true;
                    $body = new stdClass();


                    if ($responsibleUuid == null) $bool = false;

                    if ($bool) {
                        $assignee = $handlerS->FormationMetaById("employee", "employee", $responsibleUuid);
                        $body->assignee = $handlerS->FormationMeta($assignee);
                        $lineName = $infoForTask->lineName;
                        $lineId = $infoForTask->lineId;

                        //prepareMessage
                        $message = "Клиент ожидает ответа ! " .
                            PHP_EOL . "Линия: $lineName#$lineId\r\n \r\n";

                        if ($agentPhone)
                            $message .= "Номер телефона: $agentPhone \r\n";

                        if ($agentEmail)
                            $message .= "Почта: $agentEmail \r\n";

                        $messengerAttrsObjs = Config::get("messengerAttributes");
                        $messengerNames = array_column($messengerAttrsObjs, "name");
                        $settedAttributes = array_filter($agentAttr, fn($value) => in_array($value->name, $messengerNames));
                        foreach ($settedAttributes as $item) {
                            $splittedArray = explode(" ", $item->name);
                            $messengerName = $splittedArray[0];
                            $message .= PHP_EOL . "$messengerName $item->value";
                        }

                        $body->description = $message;
                        $body->operation = $handlerS->FormationMeta($order->meta);
                        $taskS = new TaskService($this->accountId);

                        $taskS->create($body);
                    }

                }
            }


        } catch (Exception|Error $e) {
            throw new AgentControllerLogicException("Ошибка во время создания заказа", 2, $e);
        }

    }

    function updateAttributesIfNecessary($customerOrders)
    {
        $agentUpdateS = new AgentUpdateLogicService($this->accountId, $this->msC);
        $orderUpdateS = new CustomerorderUpdateLogicService($this->accountId, $this->msC);
        $updateValuesS = new UpdateValuesService();
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        $serviceFieldsNames = [
            "lid",
        ];

        //вынести выше
        $config = Config::get("lidAttributes");
        $serviceFields = array_filter($config, fn($key) => in_array($key, $serviceFieldsNames), ARRAY_FILTER_USE_KEY);
        $lidName = $serviceFields["lid"]->name;
        //ожидает ответа
        $waitAnswerValueName = $serviceFields["lid"]->values[0]->name;
        //отвеченный
        $answeredValueName = $serviceFields["lid"]->values[1]->name;

        //вынести выше
        foreach ($customerOrders as $order) {
            $agentId = $order->agent->id;
            $orderId = $order->id;
            $stateType = $order->state->stateType;
            $agentAttr = $order->agent->attributes;
            $orderAttr = $order->attributes ?? [];

            $nameAttr = "";
            if ($stateType == "Successful")
                $nameAttr = $answeredValueName;
            else
                $nameAttr = $waitAnswerValueName;

            try {
                $settedAttribute = array_filter($agentAttr, fn($value) => $value->name == $lidName);
                $settedAttribute = array_shift($settedAttribute);
                $settedAttributeValueName = $settedAttribute->value->name;
                if ($settedAttributeValueName != $nameAttr) {
                    $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
                }
            } catch (Exception|Error $e) {
                throw new AgentControllerLogicException("Ошибка при обновлении контрагента во время создания заказа(find RegularStateType)", 1, $e);
            }


            $settedAttribute = array_filter($orderAttr, fn($value) => $value->name == $lidName);
            $settedAttribute = array_shift($settedAttribute);

            if (isset( $settedAttribute->value)) $settedAttributeValueName = $settedAttribute->value->name;
            else $settedAttributeValueName = '';

            try {
                if ($settedAttributeValueName != $nameAttr) {
                    $orderUpdateS->orderUpdateLidAttribute($orderId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
                }
            } catch (Exception|Error $e) {
                throw new AgentControllerLogicException("Ошибка во время обновления доп.поля lid в заказе", 2, $e);
            }

        }

    }

    function setAttributeWaitAnswer(){
        $agentUpdateS = new AgentUpdateLogicService($this->accountId, $this->msC);
        $orderUpdateS = new CustomerorderUpdateLogicService($this->accountId, $this->msC);
        $updateValuesS = new UpdateValuesService();
        $customEntityS = new CustomEntityService($this->accountId, $this->msC);
        $serviceFieldsNames = [
            "lid",
        ];

        //вынести выше
        $config = Config::get("lidAttributes");
        $serviceFields = array_filter($config, fn($key) => in_array($key, $serviceFieldsNames), ARRAY_FILTER_USE_KEY);
        $lidName = $serviceFields["lid"]->name;
        //ожидает ответа
        $waitAnswerValueName = $serviceFields["lid"]->values[0]->name;

        //вынести выше
        $agentId = $order->agent->id;
        $orderId = $order->id;
        $stateType = $order->state->stateType;
        $agentAttr = $order->agent->attributes;
        $orderAttr = $order->attributes ?? [];

        $nameAttr = "";
        if ($stateType == "Successful")
            $nameAttr = $answeredValueName;
        else
            $nameAttr = $waitAnswerValueName;

        try {
            $settedAttribute = array_filter($agentAttr, fn($value) => $value->name == $lidName);
            $settedAttribute = array_shift($settedAttribute);
            $settedAttributeValueName = $settedAttribute->value->name;
            if ($settedAttributeValueName != $nameAttr) {
                $agentUpdateS->agentUpdateLidAttribute($agentId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
            }
        } catch (Exception|Error $e) {
            throw new AgentControllerLogicException("Ошибка при обновлении контрагента во время создания заказа(find RegularStateType)", 1, $e);
        }


        $settedAttribute = array_filter($orderAttr, fn($value) => $value->name == $lidName);
        $settedAttribute = array_shift($settedAttribute);

        if (isset( $settedAttribute->value)) $settedAttributeValueName = $settedAttribute->value->name;
        else $settedAttributeValueName = '';

        try {
            if ($settedAttributeValueName != $nameAttr) {
                $orderUpdateS->orderUpdateLidAttribute($orderId, $lidName, $nameAttr, $updateValuesS, $customEntityS);
            }
        } catch (Exception|Error $e) {
            throw new AgentControllerLogicException("Ошибка во время обновления доп.поля lid в заказе", 2, $e);
        }
    }


}
