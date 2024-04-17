<?php
/**
 * Добавлять только доп.поля для мессенждеров
 */
$nameIntegration = "ChatApp";
return [

    "lid" => (object) [
        "name" => "Lid для {$nameIntegration}",
        "values" => [
            (object) [
                "name" => "Ожидает ответа",
            ],
            (object) [
                "name" => "Отвеченный",
            ]
        ],
        'description' => 'Это дополнительное поле в интеграции '.$nameIntegration.'. Важно: не удаляйте его.'
    ],
];
