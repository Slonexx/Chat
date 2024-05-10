<?php
$MsURL = "https://api.moysklad.ru/api/remap/1.2/";
return [
    "agent" => "{$MsURL}entity/counterparty/",
    "agentMetadataAttributes" => "{$MsURL}entity/counterparty/metadata/attributes/",

    "companySettingsMetadata" => "{$MsURL}context/companysettings/metadata/",
    "counterpartyNotes" => "{$MsURL}entity/counterparty/{counterpartyId}/notes/",
    "customentity" => "{$MsURL}entity/customentity/",
    "customEntityMeta" => "{$MsURL}context/companysettings/metadata/customEntities/",
    "customerorder" => "{$MsURL}entity/customerorder/",
    "customerorderMetadataAttributes" => "{$MsURL}entity/customerorder/metadata/attributes/",

    "task" => "{$MsURL}entity/task/",
];