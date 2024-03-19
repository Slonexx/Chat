<?php
namespace App\Services\MoySklad;

use App\Clients\MsClient;
use App\Clients\MoySklad;
use App\Models\OrderSettings;
use App\Models\Templates;
use App\Services\HandlerService;
use App\Services\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use SebastianBergmann\Template\Template;

class TemplateLogicService {
    private MoySklad $msC;

    private string $accountId;

    //private HandlerService $handlerS;

    function __construct($accountId) {
        $this->msC = new MoySklad($accountId);
        //$this->handlerS = new HandlerService();
        $this->accountId = $accountId;
    }

    function preparePositions($values){
        $values["{positions}"] = array_reduce($values["{positions}"], function($carry, $item){
            $carry .=  PHP_EOL . "{$item->name} {$item->count}шт.";
            return $carry;
        });
        return $values;
    }

    function insertIn($text, $values){
        return str_replace(array_keys($values), array_values($values), $text);
    }
    /**
     * @param object[] $fields
     * @return array $preparedFields
     */
    function prepareForExpandReq($fields){
        $preparedFields = [];
        foreach($fields as $field){
            $preparedFields[$field->keyword] = $field->expand_filter;
        }
        return $preparedFields;
    }

    function findAttributesFromTemplate($templateContent){
        $pattern = '/!\{(.*?)\}/';
        preg_match_all($pattern, $templateContent, $matches);
        return $matches[1];
    }

    function findAllInputForReplace($templateContent){
        $pattern = '/(?:!)?\{(.*?)\}/';
        preg_match_all($pattern, $templateContent, $matches);
        $unique_matches = array_unique($matches[1]); 
        return $unique_matches;
    }


}