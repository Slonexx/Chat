<?php
$MsURL = "https://api.moysklad.ru/api/remap/1.2/";
//$restartCommand = "c:\OSPanel\Open Server Panel.exe" /restart;
return [

    "state" => "{$MsURL}entity/customerorder/metadata/states/",

    "agent" => "{$MsURL}entity/counterparty/",
    "agentMetadataAttributes" => "{$MsURL}entity/counterparty/metadata/attributes/",

    "cashinMetadataAttributes" => "{$MsURL}entity/cashin/metadata/attributes/",
    "companySettingsMetadata" => "{$MsURL}context/companysettings/metadata/",
    "customentity" => "{$MsURL}entity/customentity/",
    "customEntityMeta" => "{$MsURL}context/companysettings/metadata/customEntities/",
    "customerorder" => "{$MsURL}entity/customerorder/",
    "customerorderMetadataAttributes" => "{$MsURL}entity/customerorder/metadata/attributes/",

    "demandMetadataAttributes" => "{$MsURL}entity/demand/metadata/attributes/",
    "demand" => "{$MsURL}entity/demand/",

    "employee" => "{$MsURL}entity/employee/",
    "employeeMetadataAttributes" => "{$MsURL}entity/employee/metadata/attributes/",

    "factureoutMetadataAttributes" => "{$MsURL}entity/factureout/metadata/attributes/",
    "factureout" => "{$MsURL}entity/factureout/",

    "invoiceoutMetadataAttributes" => "{$MsURL}entity/invoiceout/metadata/attributes/",
    "invoiceout" => "{$MsURL}entity/invoiceout/",

    'moyskladJsonApiEndpointUrl' =>  "{$MsURL}",

    "organizationMetadataAttributes" => "{$MsURL}entity/organization/metadata/attributes/",
    "organization" => "{$MsURL}entity/organization/",

    "paymentinMetadataAttributes" => "{$MsURL}entity/paymentin/metadata/attributes/",
    "productMetadataAttributes" => "{$MsURL}entity/product/metadata/attributes/",
    "project" => "{$MsURL}entity/project/",

    "saleschannel" => "{$MsURL}entity/saleschannel/",
    "salesreturnMetadataAttributes" => "{$MsURL}entity/salesreturn/metadata/attributes/",
    "salesreturn" => "{$MsURL}entity/salesreturn/",

    "task" => "{$MsURL}entity/task/",


    'url' => env('APP_URL'),
    'url_' => env('APP_URL_'),

    'appId' => env('APP_ID'),
    'appUid' => env('APP_UID'),
    'secretKey' => env('SECRET_KEY'),

    'moyskladVendorApiEndpointUrl' =>  'https://apps-api.moysklad.ru/api/vendor/1.0',
    'moyskladJsonApiEndpointUrl' =>  'https://api.moysklad.ru/api/remap/1.2',
];
