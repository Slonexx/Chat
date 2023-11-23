@extends('popup.index')
@section('content')

    <div class="main-container content-container" style="height: 100%">
        <iframe
            id="web-chat"
            src=""
            sandbox="allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation allow-top-navigation-by-user-activation"
            allow="camera https://dialogs.pro/; microphone https://dialogs.pro/; clipboard-read https://dialogs.pro/; clipboard-write https://dialogs.pro/"
            width="100%"
            height="98%"
            style="border: 0;">
        </iframe>
    </div>
    <script>

        const iframe = document.getElementById('iframe');

        // Устанавливаем начальную высоту, чтобы избежать прокрутки на стороне iframe
        iframe.style.height = 'auto';

        // Функция для обновления высоты iframe на основе его содержимого
        function setIframeHeight() {
            // Получаем высоту содержимого в iframe
            const bodyHeight = iframe.contentWindow.document.body.scrollHeight;
            console.log(bodyHeight)

            // Устанавливаем высоту iframe равной высоте содержимого
            iframe.style.height = `${bodyHeight}px`;
        }

    </script>



    <script>

        const url = "{{ Config::get("Global.url") }}" + 'Popup/'

        let object_Id = ''
        let accountId = ''
        let entity_type = ''
        let build_query = ''

        window.addEventListener("message", function (event) {
            let receivedMessage = event.data

            if (receivedMessage.name === 'OpenPopup') {
                console.log(receivedMessage)
                console.log(receivedMessage.popupParameters)
                object_Id = receivedMessage.popupParameters.object_Id;
                accountId = receivedMessage.popupParameters.accountId;
                entity_type = receivedMessage.popupParameters.entity_type;
                build_query = receivedMessage.popupParameters.build_query;


                let iframe = document.getElementById('web-chat');
                iframe.src = 'https://dialogs.pro/?' + build_query;
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

@endsection
