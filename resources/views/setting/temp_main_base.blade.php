@extends('layout')
@section('item', 'link_2')
@section('content')
    <div class="box is_box_p">
        @include('div.TopServicePartner')
        <script>NAME_HEADER_TOP_SERVICE("Настройки → " + '@yield('route_name')')</script>
        @include('div.alert')
        <div id="notification" class="notification is-danger" style="display: none">
            <div id="notificationMessage"></div>
        </div>
    </div>
    <div class=""> @yield('form') </div>
    <script>
        /*EVENT*/
        const notificationMessage = document.getElementById('notificationMessage');
        const notification = document.getElementById('notification');
        const notificationS = document.getElementById('notificationS');
        const notificationMessageS = document.getElementById('notificationMessageS');

        const observer = new MutationObserver(function (mutationsList, observer) {
            // Проверяем, есть ли текст в notificationMessage
            if (notificationMessage.innerText.trim() !== '') {
                // Показываем уведомление
                notification.style.display = 'block';
            } else {
                // Скрываем уведомление
                notification.style.display = 'none';
            }
        });
        observer.observe(notificationMessage, {subtree: true, characterData: true, childList: true});


        if (notificationMessageS != null) {
            const observer_1 = new MutationObserver(function (mutationsList, observer) {
                // Проверяем, есть ли текст в notificationMessage
                if (notificationMessageS.innerText.trim() !== '') {
                    // Показываем уведомление
                    notificationS.style.display = 'block';
                } else {
                    // Скрываем уведомление
                    notificationS.style.display = 'none';
                }
            });
            observer_1.observe(notificationMessageS, {subtree: true, characterData: true, childList: true});
        }
    </script>



    <script>
        let url = '{{ Config::get("Global")['url'] }}';
        let accountId = '{{ $accountId }}'


        let message = @json($message);
        if (message != '') {
            if (message == 'Настройки сохранены') notificationMessageS.innerText = message
            else {
                notificationMessage.style.display = 'block'
                notificationMessage.innerText = JSON.stringify(message)
            }
        }



        function hideOrViewPass() {
            document.getElementById('pass').type = (document.getElementById('pass').type === 'text') ? 'password' : 'text';
        }


        function ajax_settings(url, method, data) {
            const settings = {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json"},
                "data": data,
            };

            if (method.toUpperCase() === 'POST' || method.toUpperCase() === 'DELETE') settings.data = JSON.stringify(data);

            return settings;
        }
    </script>
@endsection

<style>
    .is_box_p{
        margin-top: 1.0rem !important;
        padding-top: 0.2rem !important;
        padding-bottom: 0.2rem !important;
    }
</style>
