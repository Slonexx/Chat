<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $body = '{
            "ms_token": "99178fa3c1660f5333fcfcfdb2df47045ecae7f4",
            "org": [
                {
                    "accessToken": "8acf24854488fe3bb8307a6b0966437e7b11e45667a123bef376716f3bd795d9",
                    "lineId": "36651",
                    "lineName": "TestLine#36651"
                }
            ],
            "lid": {
                "responsible": "1",
                "responsible_uuid": "9989675d-5130-11ee-0a80-0c7f00028929",
                "is_activity_order": 1,
                "organization": "72d4b01d-feab-11ec-0a80-0738000e5a8d",
                "organization_account": "8e45bb07-0660-11ed-0a80-03050014d9e1",
                "sales_channel_uid": "1c7e7a08-c58f-11ee-0a80-0ed30022accf",
                "project_uid": "cfbe8cff-08e1-11ed-0a80-0c2a001f865c",
                "states": "0768fe30-06ee-11ef-0a80-0f1300397b1a",
                "tasks": 1
            },
            "messengerAttributes": [
                {
                    "name": "telegram",
                    "attribute_id": "72200992-fd76-11ee-0a80-0b58005349b9"
                },
                {
                    "name": "telegram_bot",
                    "attribute_id": "725749cf-fd76-11ee-0a80-0904005192c3"
                },
                {
                    "name": "avito",
                    "attribute_id": "72854e3f-fd76-11ee-0a80-0b58005349c7"
                },
                {
                    "name": "vk",
                    "attribute_id": "72b994e1-fd76-11ee-0a80-0b58005349cc"
                },
                {
                    "name": "whatsapp",
                    "attribute_id": "72e58254-fd76-11ee-0a80-10b400512abf"
                },
                {
                    "name": "email",
                    "attribute_id": "7310f28f-fd76-11ee-0a80-0d7e00531563"
                },
                {
                    "name": "instagram",
                    "attribute_id": "73420ac8-fd76-11ee-0a80-0d7e00531566"
                },
                {
                    "name": "facebook",
                    "attribute_id": "7375d5ab-fd76-11ee-0a80-0904005192f7"
                }
            ]
        }';

        $decodedBody = json_decode($body, true);
        $response = $this->post('/api/integration/customerorder/create', $decodedBody);

        $response->assertStatus(200);
    }
}
