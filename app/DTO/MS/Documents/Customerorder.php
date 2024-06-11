<?php
namespace App\DTO\MS\Documents;

use DateTime;
use Exception;
use Faker\Core\Uuid;

class CustomerorderException extends Exception {} 
/**
 * Объект документа заказа покупателя в МойСклад
 * Используется как УДОБНАЯ прослойка для создания и обновления заказа покупателяв МойСклад
 * Актуальную информацию по id можно запросить в методе *название метода*
 * Данный объект НЕ КОПИРУЕТ с МойСклад в его свойства так как это будет неактуально потому что 
 * в этом классе отсутствуют свойства только для чтения, которые МойСклад не даёт поменять.
 */
class Customerorder{

    public bool $applicable;

    public string $code;

    public DateTime $deliveryPlannedMoment;

    public string $description;

    public string $externalCode;

    public DateTime $moment;

    public string $name;

    public bool $shared;

    public string $shipmentAddress;

    public object $shipmentAddressFull;

    public string $syncId;

    public string $taxSystem;

    public string $vatEnabled;

    public string $vatIncluded;

    /**
     * meta fields required
     */
    public $agent;
    public $organization;


    /**
     * meta fields
     */
    public $agentAccount;
    public $contract;
    public $files;
    public $group;
    public $meta;
    public $organizationAccount;
    public $owner;
    public $project;
    public $salesChannel;
    public $state;
    public $store;
    
    
    /**
     * array []
     */
    public $attributes;
    public $positions;


}