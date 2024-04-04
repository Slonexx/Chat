<?php
/**
 * Добавлять только доп.поля для мессенждеров
 */
$nameIntegration = "ChatApp";
return [
    "telegram" => (object) [
        "name" => "Telegram для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],
    "telegram_bot" => (object) [
        "name" => "Telegram bot для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "viber_bot" => (object) [
        "name" => "Viber bot для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "avito" => (object) [
        "name" => "Avito для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "vk" => (object) [
        "name" => "VKontakte для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "whatsapp" => (object) [
        "name" => "WhatsApp для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "email" => (object) [
        "name" => "Email для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "CRM" => (object) [
        "name" => "CRM для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "altegio" => (object) [
        "name" => "Altegio для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "instagram" => (object) [
        "name" => "Instagram для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],

    "facebook" => (object) [
        "name" => "Facebook для {$nameIntegration}",
        "type" => "string",
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],
];