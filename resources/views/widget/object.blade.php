@extends('widget.widget')
@section('content')

    <div class="row gradient rounded p-2">
        <div class="col">
                <span id="GifOrImageHideOrGifHide">
                    <img src="{{  Config::get("Global.url").'client.svg' }}" width="50px" height="50px" alt="">
                    <img src="{{  Config::get("Global.url").'client2.svg' }}" width="100px" height="100%" alt="">
                </span>

            <img id="ImageOrGifHide" src="{{ Config::get("Global.url").'loading.gif' }}"
                 width="15%" alt="" style="display: none">
        </div>
    </div>

    <div id="messageErrorAlert" class=" mt-1 mx-3 p-2 alert alert-danger text-center "
         style="display: none; font-size: 12px; margin-bottom: 5px !important;"></div>



    <div id="main" class="mt-1 mx-4 text-center" style="display: none">
        <div class="row p-2">
            <div class="col">
                <button id="Chat" onclick="PopupShow('2')" class="w-100 btn btn-outline-dark gradient_focus"> Чат с
                    клиентом
                </button>
            </div>
            <div class="col">
                <button onclick="PopupShow('1')" class="w-100 btn btn-outline-dark gradient_focus"> Общий чат</button>
            </div>
        </div>
        <div class="row mt-2 text-center m-2">
            <button id="template" onclick="PopupShow('3')" class="btn btn-outline-dark gradient_focus"> Отправить
                шаблон
            </button>
        </div>
    </div>



    <script>
        const hostWindow = window.parent
        let messageId = 0
        let entityId

        let license_id
        let license_full
        let agent
        let phone

        let all
        let onToken

        let entity_type = "{{$entity}}"
        let accountId = "{{$accountId}}"
        let employee = @json($employee);
        let employeeId = "{{$employeeId}}"


        function PopupShow(status) {
            if (status === '1') {
                onViewPopup("Show", onToken)
            }
            if (status === '2') {
                onViewPopup("Show", all)
            }
            if (status === '3') {
                onViewPopup('TemplateMessage', all)
            }
        }


        /*let receivedMessage = {
            "name": "Open",
            "extensionPoint": "document.customerorder.edit",
            "objectId": "5f3023e9-05b3-11ee-0a80-06f20001197a",
            "messageId": 1,
            "displayMode": "expanded"
        }*/


        window.addEventListener("message", function (event) {
            const receivedMessage = event.data;
            hostWindow.postMessage({name: "OpenFeedback", correlationId: receivedMessage.messageId}, '*')

            if (receivedMessage.name === 'Open') {
                clearHTML();

                entityId = receivedMessage.objectId;
                let data = {
                    accountId: accountId,
                    entity_type: entity_type,
                    entityId: entityId,
                    employee: employee,
                };

                //receivedMessage = []

                let settings = ajax_settings("{{ Config::get("Global.url") }}" + 'widget/get/Data', 'GET', data)
                console.log('Widget setting attributes: ↓')
                console.log(settings)

                $.ajax(settings).done(function (json) {
                    window.document.getElementById('main').style.display = 'inline'
                    window.document.getElementById('GifOrImageHideOrGifHide').style.display = 'inline'
                    window.document.getElementById('ImageOrGifHide').style.display = 'none'

                    if (json.status) {
                        license_id = json.license_id
                        license_full = json.license_full
                        agent = json.agent
                        phone = json.phone

                        all = json.all
                        onToken = json.onToken
                    } else {
                        window.document.getElementById('messageErrorAlert').style.display = 'block'
                        window.document.getElementById('messageErrorAlert').innerText = json.message

                        if (json.onToken != '') {
                            window.document.getElementById('Chat').style.display = "none"
                            /*window.document.getElementById('template').style.display = "none"*/
                            onToken = json.onToken
                        } else {
                            window.document.getElementById('main').style.display = 'none'
                        }

                    }

                })
                    .fail(function (xhr) {
                        console.log(xhr)

                        let json = xhr.responseJSON
                        let message = 'ОШИБКА'

                        if (json.hasOwnProperty('message')) message = message + ' ' + json.message
                        if (json.hasOwnProperty('messageStack')) message = message + '\n\n' + json.messageStack;

                        window.document.getElementById('messageErrorAlert').style.display = 'block'
                        window.document.getElementById('messageErrorAlert').innerText = response.message

                    })

            }

        });






        function onViewPopup(name, onButtonParams) {
            messageId++;
            let sendingMessage = {
                name: "ShowPopupRequest",
                messageId: messageId,
                popupName: name,
                popupParameters: {
                    accountId: accountId,
                    object_Id: entityId,
                    entity_type: entity_type,
                    build_query: onButtonParams,

                    license_id: license_id,
                    license_full: license_full,
                    employee: employeeId,
                    nameAgent: agent,
                    phone: phone,
                },
            };
            console.log("Widget Sending : ↓")
            console.log(sendingMessage)
            hostWindow.postMessage(sendingMessage, '*');
        }
        function ajax_settings(url, method, data) {
            return {
                "url": url,
                "method": "GET",
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }

        function clearHTML(){
            window.document.getElementById('main').style.display = 'none'
            window.document.getElementById('messageErrorAlert').style.display = 'none'
            window.document.getElementById('Chat').style.display = "block"
            window.document.getElementById('template').style.display = "block"
            window.document.getElementById('GifOrImageHideOrGifHide').style.display = 'none'
            window.document.getElementById('ImageOrGifHide').style.display = 'inline'
        }
    </script>
@endsection
