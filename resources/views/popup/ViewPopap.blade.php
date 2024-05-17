@extends('popup.index')
<body id="body" class="bg-white">
@section('content')

    <div id="max_content" class="main-container content-container">
        <iframe
            id="web-chat"
            src=""
            sandbox="allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation allow-top-navigation-by-user-activation"
            allow="camera https://dialogs.pro/; microphone https://dialogs.pro/; clipboard-read https://dialogs.pro/; clipboard-write https://dialogs.pro/"
            width="100%"
            style="border: 0;">
        </iframe>
    </div>



    <script>
        var height = document.body.clientHeight;
        body.style.height = height
        max_content.style.height = height - 2


        const url = "{{ Config::get("Global.url") }}" + 'Popup/'

        let object_Id = ''
        let accountId = ''
        let entity_type = ''
        let build_query = ''


        /*let receivedMessage = {
            "name": "OpenPopup",
            "messageId": 1,
            "popupName": "TemplateMessage",
            "popupParameters":
                {
                    "accountId": "1dd5bd55-d141-11ec-0a80-055600047495",
                    "object_Id": "5f3023e9-05b3-11ee-0a80-06f20001197a",
                    "entity_type": "customerorder",
                    "build_query": "api%5Baccess_token%5D=5a1ab57d8e258110ebc4f1ca8f151cebefe0f06be96d3772d074aac1f9f49c94&api%5Blicense_id%5D=36651&api%5Bcrm_domain%5D=smartchatapp.kz&api%5Bemployee_ext_code%5D=e793faeb-e63a-11ec-0a80-0b4800079eb3&crm%5Bphones%5D%5B0%5D=7750498821&crm%5BdialogIds%5D%5B0%5D=%40SergeiIOne&crm%5BdialogIds%5D%5B1%5D=77750498821%40c.us",
                    "license_id": "36651",
                    "license_full": "TestLine#36651",
                    "employee": "e793faeb-e63a-11ec-0a80-0b4800079eb3",
                    "nameAgent": "WR Ap21",
                    "phone": "7750498821"
                }
        };*/


        window.addEventListener("message", function (event) {
            let receivedMessage = event.data

            if (receivedMessage.name === 'OpenPopup') {
                object_Id = receivedMessage.popupParameters.object_Id;
                accountId = receivedMessage.popupParameters.accountId;
                entity_type = receivedMessage.popupParameters.entity_type;
                build_query = receivedMessage.popupParameters.build_query;


                let iframe = document.getElementById('web-chat');
                iframe.src = 'https://dialogs.pro/?' + build_query;
                iframe.style.height = height - 5
                //iframe.src = 'https://chat.chatapp.online/?' + build_query;
            }
        });

        function ajax_settings(url, method, data) {
            return {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }

    </script>


</body>
@endsection
