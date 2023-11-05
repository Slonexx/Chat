@extends('popup.index')
@section('content')

    <div class="main-container" style="height: 99%; width: 99%; background: rgb(220, 220, 220)" >
        <div class="content-container">
            <div class="toc-wrapper">
                <div class="row">
                    <span class="mx-2 mt-2"> &nbsp;
                        <img src="{{  Config::get("Global.url").'client.svg' }}" height="80%"  alt="">
                        <img src="{{  Config::get("Global.url").'client2.svg' }}" width="100px" height="100%"  alt="">
                    </span>
                </div>
                <div class="mt-3 mb-3 mx-1">
                    <div class="input-group">
                        <input type="search" id="search" class="form-control" />
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <hr>
                <ul id="toc" class="toc-list-h1">

                </ul>
            </div>
{{--

            <div class="page-wrapper">
                <div class="bg-white row rounded p-3">
                    <div class="row gradient-invert rounded p-1 " style="margin-top: -0.5rem">
                        <div class="col" style="margin-top: 0.25rem"><span class="text-black" style="font-size: 20px"> Шаблон сообщений  </span> </div>
                        <div class="col-3 text-right"><img src="{{  ( Config::get("Global") )['url'].'2logoHead.png' }}"  width="100%" alt=""></div>
                    </div>
                </div>
            </div>

--}}


            <div class="page-wrapper">
                <div id="main" class="container bg-white rounded" style="display: block">



                    <div class="content">
                        <div class="mx-1 mt-2 row gradient rounded p-2">
                            <div class="col mt-2">
                                <button onclick="refreshInformation()" class="btn gradient_focus text-black" type="button"> Проверить &nbsp; <i class="fas fa-undo-alt"></i></button>
                            </div>
                            <div class="col-6 text-end row">
                                <div class="col-2"></div>
                                <div class="col-10"><i class="fas fa-user-circle"></i> &nbsp; <span id="nameAgent"> ИМЯ КОНТРАГЕНТА </span></div>
                                <div class="col-2"></div>
                                <div class="col-10"><span id="phoneAgent"> номер телефона </span></div>
                            </div>
                           <div id="ImageOrGifHide" class="col d-flex justify-content-center rounded bg-white">
                               <img  src="{{ Config::get("Global.url").'loading.gif' }}" width="50%">
                           </div>
                        </div>


                            <div class="mt-3 row p-2">
                                <div id="phoneOrNameDiv" class="col-4">Номер телефона</div>
                                <input id="phoneOrName" class="form-control col" type="text" name="phoneOrName" value="">
                            </div>
                            <div class="mt-3 row p-2">
                                <div class="col-4">Мессенджер</div>
                                <select onchange="messengerName(this.value)" id="messenger" class="col form-select">

                                </select>
                            </div>
                            <div class="mt-2 row p-2">
                                <div class="col-4">Аккаунте</div>
                                <select id="linesId" class="col form-select">

                                </select>
                            </div>

                        <div id="errorMessageInContent" class="mt-2 alert alert-danger alert-dismissible fade show in text-center " style="display: none">
                        </div>
                        <div id="successMessageInContent" class="mt-2 alert alert-success alert-dismissible fade show in text-center " style="display: none">
                        </div>
                    </div>
                    <div class="footer row p-1 rounded">
                        <div class="input-group">
                            <textarea id="textMessage" class="form-control" maxlength="250" style="resize: none;"></textarea>
                            <button onclick="sendMessage()" id="sendMessage" class="btn gradient_focus p-5" type="button" style="display: none">
                                <i class="far fa-comment-dots text-white" style="font-size: 50px"></i>
                            </button>
                        </div>
                    </div>
                </div >

                <div id="ErrorMessage"  class="container rounded mt-2 alert alert-danger alert-dismissible fade show in text-center " style="display: none"></div>
            </div>

        </div>
    </div>

    @include('popup.style_template')

    <script>



        const url = "{{ Config::get("Global.url") }}" + 'Popup/template/message'

        let accountId = ''
        let object_Id = ''
        let entity_type = ''
        let license_id = ''
        let license_full = ''
        let employee_id = ''
        let agent = ''
        let phone = ''
        let chatId = ''

        let arrayMessageTemplate


        /*let receivedMessage = {
            "name":"OpenPopup",
            "messageId":1,
            "popupName":"TemplateMessage",
            "popupParameters":
                {
                    "accountId":"1dd5bd55-d141-11ec-0a80-055600047495",
                    "object_Id":"5f3023e9-05b3-11ee-0a80-06f20001197a",
                    "entity_type":"customerorder",

                    "build_query":"",

                    "employee":"e793faeb-e63a-11ec-0a80-0b4800079eb3",
                    "license_id":"36651",
                    "license_full":"Chat Line Test name#36651",
                    "nameAgent":"Сергей",
                    "phone":"+77750498888",

                }
        };*/

        window.addEventListener("message", function(event) {
        let receivedMessage = event.data
            search.value = '';
            textMessage.value = '';
            errorMessageInContent.style.display = 'none';
            successMessageInContent.style.display = 'none';

        if (receivedMessage.name === 'OpenPopup') {
            main.style.display = 'flex'
            accountId = receivedMessage.popupParameters.accountId
            object_Id = receivedMessage.popupParameters.object_Id
            entity_type = receivedMessage.popupParameters.entity_type
            license_id = receivedMessage.popupParameters.license_id
            license_full = receivedMessage.popupParameters.license_full
            employee_id = receivedMessage.popupParameters.employee
            phone = receivedMessage.popupParameters.phone
            agent = receivedMessage.popupParameters.nameAgent

            nameAgent.innerText = agent
            phoneAgent.innerText = phone
            //receivedMessage = []

            let data = {
                accountId: accountId,
                object_Id: object_Id,
                entity_type: entity_type,

                license_id: license_id,
                license_full: license_full,
                employee: employee_id,

                phoneOrName: phoneOrName.value,
                messenger: messenger.value,
                linesId: linesId.value,
                agent: agent,
                phone: phone,
            };

            let settings = ajax_settings(url+"/get/All", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url+entity_type+"/get/All"  + ' response ↓ ')
                console.log(json)

                if (json.status){
                    arrayMessageTemplate = json.data;
                    window.document.getElementById('phoneOrName').value = phone;

                    (json.data).forEach((item, id) => {
                        $('#toc').after(
                            $('<li><a class="mx-1"> <button onclick="innerTemplateMessage(\''+id+'\')" class="btn">'+item.name+'</button></a></li>')
                        );
                    });

                    let option1 = document.createElement("option")
                    option1.text = license_full
                    option1.value = license_id
                    linesId.appendChild(option1)

                } else {
                    main.style.display = 'none'
                    ErrorMessage.style.display = 'block'
                    ErrorMessage.innerText = json.data
                }
            })
            setTimeout(() => getMessenger(data), 1 * 1000)


            setTimeout(() => data = {
                accountId: accountId,
                object_Id: object_Id,
                entity_type: entity_type,

                license_id: license_id,
                license_full: license_full,
                employee: employee_id,

                phoneOrName: phoneOrName.value,
                messenger: messenger.value,
                linesId: linesId.value,
                agent: agent,
                phone: phone,
            }, 4.9 * 1000)
            setTimeout(() => getInformation(data), 5 * 1000)
        }
        });



        function sendMessage(){
            errorMessageInContent.style.display = 'none'
            if (textMessage.value == '') {
                errorMessageInContent.style.display = 'block'
                errorMessageInContent.innerText = 'Отсутствует сообщение, пожалуйста введите сообщение или выберите из меню слева'
            }
            if (chatId == '') {
                errorMessageInContent.style.display = 'block'
                errorMessageInContent.innerText = 'Отсутствует данные для отправки, пожалуйста нажмите на кнопку "проверить".'
            }

            let data = {
                accountId: accountId,
                object_Id: object_Id,
                entity_type: entity_type,

                employee: employee_id,
                messenger: messenger.value,
                linesId: linesId.value,
                chatId: chatId,
                text: textMessage.value,
            }

            let settings = ajax_settings(url+"/get/send/message", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url+entity_type+"/get/send/message"  + ' response ↓ ')
                console.log(json)

                if (json.status){
                    successMessageInContent.style.display = 'block'
                    successMessageInContent.innerText = 'Сообщение отправлено'
                } else {
                    errorMessageInContent.style.display = 'block'
                    errorMessageInContent.innerText = JSON.stringify(json.data)
                }
            })

        }


        function getMessenger(data){
            let settings = ajax_settings(url+"/get/information/messenger", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url+entity_type+"/get/information/messenger"  + ' response ↓ ')
                console.log(json)

                if (json.status){
                    (json.data).forEach((item) => {
                        let option1 = document.createElement("option")
                        option1.text = item.name
                        option1.value = item.value
                        messenger.appendChild(option1)
                    });

                    messengerName(messenger.value)

                } else {
                    errorMessageInContent.style.display = 'block'
                    errorMessageInContent.innerText = JSON.stringify(json.message)
                }

            })
        }

        function getInformation(data){
            ImageOrGifHide.style.cssText = 'display: flex !important;'
            errorMessageInContent.style.display = 'none'
            let settings = ajax_settings(url+"/get/information/chatapp", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url+entity_type+"/get/information/chatapp"  + ' response ↓ ')
                console.log(json)

                if (json.status){
                    window.document.getElementById('sendMessage').style.display = 'block'
                    chatId = json.data.chatId
                } else {
                    errorMessageInContent.style.display = 'block'
                    errorMessageInContent.innerText = JSON.stringify(json.message)
                }

                ImageOrGifHide.style.cssText = 'display: none !important;'

            })
        }

        function refreshInformation() {
            let data = {
                accountId: accountId,
                object_Id: object_Id,
                entity_type: entity_type,

                license_id: license_id,
                license_full: license_full,
                employee: employee_id,

                phoneOrName: phoneOrName.value,
                messenger: messenger.value,
                linesId: linesId.value,
                agent: agent,
                phone: phone,
            }
            getInformation(data)
        }

    </script>

    <script>
        function ajax_settings(url, method, data){
            return {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }
        function innerTemplateMessage(id){ textMessage.value = arrayMessageTemplate[id].message }

        function messengerName(value){
            if (value === 'telegram') phoneOrNameDiv.innerText = 'Номер телефона  /  Имя пользователя'
            else phoneOrNameDiv.innerText = 'Номер телефона'

        }
    </script>

@endsection
