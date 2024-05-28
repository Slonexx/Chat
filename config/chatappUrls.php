<?php
$mainURL = "https://api.chatapp.online/";
return [
    "licenses" => "{$mainURL}v1/licenses",
    "chats" => "{$mainURL}v1/licenses/%s/messengers/%s/chats/",
    "messages" => "{$mainURL}v1/licenses/%s/messengers/%s/chats/%s/messages",
    "webhooks" => "{$mainURL}/v1/callbackUrls"
];