@extends('popup.index')
@section('content')

    <div class="main-container" style="height: 720px">
        <iframe
            id="webchat"
            src=""
            sandbox="allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation allow-top-navigation-by-user-activation"
            allow="camera https://dialogs.pro/; microphone https://dialogs.pro/; clipboard-read https://dialogs.pro/; clipboard-write https://dialogs.pro/"
            width="100%"
            height="99%"
            style="border: 0;" >
        </iframe>
    </div>


    @include('popup.script_popup_app')
    @include('popup.style_popup_app')

    <script>

        const url = "{{ Config::get("Global.url") }}" + 'Popup/'

        let object_Id = ''
        let accountId = ''
        let entity_type = ''
        let build_query = ''


        /*let receivedMessage = {
            "name":"OpenPopup",
            "messageId":1,
            "popupName":"fiscalizationPopup",
            "popupParameters":
                {
                    "object_Id":"ac0c9983-acec-11ed-0a80-06ac001abb0c",
                    "accountId":"1dd5bd55-d141-11ec-0a80-055600047495",
                    "entity_type":"customerorder",
                }
        };*/

        window.addEventListener("message", function(event) {
        let receivedMessage = event.data

        if (receivedMessage.name === 'OpenPopup') {
            console.log(receivedMessage)
            console.log(receivedMessage.popupParameters)
            object_Id = receivedMessage.popupParameters.object_Id;
            accountId = receivedMessage.popupParameters.accountId;
            entity_type = receivedMessage.popupParameters.entity_type;
            build_query = receivedMessage.popupParameters.build_query;


            let iframe = document.getElementById('webchat');
            //iframe.src = 'https://dialogs.pro/?' + build_query;
            iframe.src = 'https://chat.chatapp.online/?' + build_query;

            /*let data = { object_Id: object_Id, accountId: accountId, };

            let settings = ajax_settings(url+entity_type+"/show", "GET", data);
            console.log(url+entity_type+"/show" + ' settings ↓ ')
            console.log(settings)

            $.ajax(settings).done(function (json) {
                console.log(url+entity_type+"/show"  + ' response ↓ ')
                console.log(json)


            })*/
        }
        });

        function ajax_settings(url, method, data){
            return {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }

    </script>

@endsection
