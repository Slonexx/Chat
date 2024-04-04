<?php
namespace App\Services\Settings\MessengerAttributes;

use App\Models\AttributeSettings;
use App\Models\MainSettings;

class MessengerAttributeService {

    public string $account_id;

    public mixed $entity_type;
    
    public mixed $name;

    public mixed $attribute_id;

    function __construct($accountId, $entity_type, $name) {
        $setting = MainSettings::join('messenger_attributes as a', 'main_settings.id', '=', 'a.main_settings_id')
            ->where("account_id", $accountId)
            ->where("entity_type", $entity_type)
            ->where("name", $name)
            ->get()
            ->first();
        if($setting !== null) {
            $this->account_id = $accountId;
            $this->entity_type = $setting->entity_type;
            $this->name = $setting->type;
            $this->attribute_id = $setting->attribute_id;
        } else {
            $this->account_id = $accountId;
            $this->entity_type = null;
            $this->name = null;
            $this->attribute_id = null;
        }
    }

}