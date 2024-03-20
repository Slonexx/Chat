@extends('popup.indexTemplate')
@extends('popup.baseFunction')
@section('content')

    <div class="main-container" style="height: 99%; width: 99%; margin-bottom: 50px">
        <div style="height: 100%">
            <div class="toc-wrapper">
                <div class="row">
                    <span class="mx-2 mt-2"> &nbsp;
                        <img src="{{  Config::get("Global.url").'client.svg' }}" height="80%" alt="">
                        <img src="{{  Config::get("Global.url").'client2.svg' }}" width="100px" height="100%" alt="">
                    </span>
                </div>
                <div class="mt-3 mb-3 mx-1">
                    <div class="input-group">
                        <input type="search" id="search" oninput="search(this)" class="form-control"/>
                        <button onclick="searchTemplate()" type="button" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <hr>
                <!-- <div id="templates" class="mt-2" style="display: block"></div> -->
                <ul id="toc" class="toc-list-h1-title" style="display: block">
                </ul>
            </div>
            <div class="page-wrapper content-container" style="height: 100vh;">
                <div id="mainsView" class="container bg-white rounded" style="display: flex">
                    <div class="content">
                        <div class="mx-1 mt-2 row gradient rounded p-2">
                            <div class="col mt-2">
                                <button onclick="refreshInformation()" class="btn gradient_focus text-black"
                                        type="button"> Проверить &nbsp; <i class="fas fa-undo-alt"></i></button>
                            </div>
                            <div class="col-6 text-end row">
                                <div class="col-2"></div>
                                <div class="col-10"><i class="fas fa-user-circle"></i> &nbsp; <span id="nameAgent"> ИМЯ КОНТРАГЕНТА </span>
                                </div>
                                <div class="col-2"></div>
                                <div class="col-10"><span id="phoneAgent"> номер телефона</span></div>
                            </div>
                            <div id="ImageOrGifHide" class="col d-flex justify-content-center rounded bg-white">
                                <img src="{{ Config::get("Global.url").'loading.gif' }}" width="50%">
                            </div>
                        </div>


                        <div class="mt-3 row p-2">
                            <div id="phoneOrNameDiv" class="col-4">Номер телефона</div>
                            <input id="phoneOrName" oninput="deleteSpaces(this)" class="form-control col" type="text" name="phoneOrName" value="">
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

                        <div id="errorMessageInContent"
                             class="mt-2 alert alert-danger alert-dismissible fade show in text-center "
                             style="display: none">
                        </div>
                        <div id="successMessageInContent"
                             class="mt-2 alert alert-success alert-dismissible fade show in text-center "
                             style="display: none">
                        </div>
                    </div>
                    <div class="footer row p-1 rounded">
                        <div class="input-group">
                            <textarea id="textMessage" class="form-control" maxlength="250"
                                      style="resize: none;"></textarea>
                            <button onclick="sendMessage()" id="sendMessage" class="btn gradient_focus p-5"
                                    type="button" style="display: none">
                                <i class="far fa-comment-dots text-white" style="font-size: 50px"></i>
                            </button>
                        </div>
                    </div>
                </div>
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


        let receivedMessage = {
            "name":"OpenPopup",
            "messageId":1,
            "popupName":"TemplateMessage",
            "popupParameters":
                {
                    "accountId":"1dd5bd55-d141-11ec-0a80-055600047495",
                    "object_Id":"277ca4f4-d6d6-11ee-0a80-0cc500080c42",
                    "entity_type":"demand",

                    "build_query":"",

                    "employee":"9989675d-5130-11ee-0a80-0c7f00028929",
                    "license_id":"36651",
                    "license_full":"Chat Line Test name#36651",
                    "nameAgent":"Сергей",
                    "phone":"+77777492857",

            }
        };

        window.addEventListener("message", function(event) {
        //let receivedMessage = event.data
            search.value = '';
            //toc.innerText = '';
            textMessage.value = '';
            errorMessageInContent.style.display = 'none';
            successMessageInContent.style.display = 'none';
            ImageOrGifHide.style.cssText = 'display: flex !important;'
            window.document.getElementById('sendMessage').style.display = 'none'
            while (messenger.firstChild) { messenger.removeChild(messenger.firstChild); }

            if (receivedMessage.name === 'OpenPopup') {
                mainsView.style.display = 'flex'
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
            receivedMessage = []

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

                let dataForTemplatesRequest = {
                    accountId: accountId,
                    object_Id: object_Id,
                    entity_type: entity_type
                }
                let settings = ajax_settings(url + "/get/All", "GET", dataForTemplatesRequest);
                $.ajax(settings).done(function (json) {
                    console.log(url + entity_type + "/get/All" + ' response ↓ ')
                    console.log(json)

                    if (json.status) {
                        arrayMessageTemplate = json.data;
                        window.document.getElementById('phoneOrName').value = phone;

                        (json.data).forEach((item) => {
                            const li = document.createElement('li');

                            const link = document.createElement('a');
                            link.className = 'mx-1';

                            const button = document.createElement('button');
                            button.id = item.uuid;
                            button.onclick = innerTemplateMessage
                            button.type = "button" 
                            button.className= "btn"
                            button.innerText = item.title

                            link.appendChild(button);
                            li.appendChild(link);

                            ul = document.getElementById('toc');

                            ul.appendChild(li);
                        });

                        let option1 = document.createElement("option")
                        option1.text = license_full
                        option1.value = license_id
                        linesId.appendChild(option1)

                    } else {
                        window.document.getElementById('mainsView').style.setProperty('display', '');
                        ErrorMessage.style.setProperty('block', 'none', 'important');
                        ErrorMessage.innerText = json.data
                    }
                })
                getMessenger(data)

                getInformation(data)
            }
        });

        phoneOrName.addEventListener("blur", function(event) {
            refreshInformation()
        });

        messenger.addEventListener("change", function(event) {
            refreshInformation()
        });

        function search(search) {
            let searchValue = transliterate(search.value.toLowerCase().trim())

            // Get all elements to search within
            let elements = Array.from(toc.children)

            // Loop through each element
            elements.forEach(function(element) {
                var text = element.textContent.toLowerCase(); // Get element's text content in lowercase

                if (text.startsWith(searchValue)) {
                    element.style.display = 'block'; // Show the element if it matches
                } else {
                    element.style.display = 'none'; // Hide the element if it doesn't match
                }
            });
        };

        function transliterate(text) {
            const translitMap = {
                "q": "й",
                "w": "ц",
                "e": "у",
                "r": "к",
                "t": "е",
                "y": "н",
                "u": "г",
                "i": "ш",
                "o": "щ",
                "p": "з",
                "[": "х",
                "]": "ъ",
                "a": "ф",
                "s": "ы",
                "d": "в",
                "f": "а",
                "g": "п",
                "h": "р",
                "j": "о",
                "k": "л",
                "l": "д",
                ";": "ж",
                "'": "э",
                "z": "я",
                "x": "ч",
                "c": "с",
                "v": "м",
                "b": "и",
                "n": "т",
                "m": "ь",
                ",": "б",
                ".": "ю",
                "/": "."
            };

            return text.split('').map(char => {
                return translitMap[char.toLowerCase()] || char;
            }).join('');
        }

        function deleteSpaces(e){
            const arrayDivided = e.value.split(" ");
            phoneOrName.value = arrayDivided.join("");
        }


        function sendMessage() {
            errorMessageInContent.style.display = 'none'
            if (textMessage.value == '') {
                errorMessageInContent.style.display = 'block'
                errorMessageInContent.innerText = 'Отсутствует сообщение, пожалуйста введите сообщение или выберите из меню слева'
            }
            if (chatId == '') {
                errorMessageInContent.style.display = 'block'
                errorMessageInContent.innerText = 'Отсутствует данные для отправки, пожалуйста нажмите на кнопку "проверить".'
            }

            if (textMessage.value != '' && chatId != '') {
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

                let settings = ajax_settings(url + "/get/send/message", "GET", data);
                $.ajax(settings).done(function (json) {
                    console.log(url + entity_type + "/get/send/message" + ' response ↓ ')
                    console.log(json)

                    if (json.status) {
                        successMessageInContent.style.display = 'block'
                        successMessageInContent.innerText = 'Сообщение отправлено'
                    } else {
                        errorMessageInContent.style.display = 'block'
                        errorMessageInContent.innerText = JSON.stringify(json.data)
                    }
                })
            }
        }

        function searchTemplate() {
            //toc.innerText = '';

            let data = {
                accountId: accountId,
                object_Id: object_Id,
                entity_type: entity_type,

                name: search.value
            };

            let settings = ajax_settings(url + "/get/where/name", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url + entity_type + "/get/where/name" + ' response ↓ ')
                console.log(json)

                if (json.status) {
                    arrayMessageTemplate = json.data;
                    window.document.getElementById('phoneOrName').value = phone;

                    (json.data).forEach((item, id) => {
                        // $('#templates').append(
                        //     $('<li><a class="mx-1"> <button onclick="innerTemplateMessage(\'' + id + '\')" class="btn">' + item.name + '</button></a></li>')
                        // );
                    });

                    let option1 = document.createElement("option")
                    option1.text = license_full
                    option1.value = license_id
                    linesId.appendChild(option1)

                } else {
                    mainsView.style.display = 'none'
                    ErrorMessage.style.display = 'block'
                    ErrorMessage.innerText = json.data
                }
            })
        }

        function getMessenger(data) {
            let settings = ajax_settings(url + "/get/information/messenger", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url + entity_type + "/get/information/messenger" + ' response ↓ ')
                console.log(json)

                if (json.status) {

                    if ((json.data).length > 0) {
                        (json.data).forEach((item) => {
                            let option1 = document.createElement("option")
                            option1.text = item.name
                            option1.value = item.value
                            messenger.appendChild(option1)
                        });

                        messengerName(messenger.value)
                    } else {
                        errorMessageInContent.style.display = 'block'
                        errorMessageInContent.innerText = "У данной линии отсутствуют мессенджеры"
                    }
                } else {
                    errorMessageInContent.style.display = 'block'
                    errorMessageInContent.innerText = JSON.stringify(json.message)
                }

            })
        }

        function getInformation(data) {
            ImageOrGifHide.style.cssText = 'display: flex !important;'
            errorMessageInContent.style.display = 'none'
            let settings = ajax_settings(url + "/get/information/chatapp", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url + entity_type + "/get/information/chatapp" + ' response ↓ ')
                console.log(json)

                if (json.status) {
                    window.document.getElementById('sendMessage').style.display = 'block'
                    chatId = json.data.chatId

                    if (json.hasOwnProperty('message')){
                        errorMessageInContent.style.display = 'block'
                        errorMessageInContent.innerText = JSON.stringify(json.message)
                    }

                } else {
                    errorMessageInContent.style.display = 'block'
                    errorMessageInContent.innerText = JSON.stringify(json.message)
                }

                ImageOrGifHide.style.cssText = 'display: none !important;'

            })
        }


        function ajax_settings(url, method, data) {
            return {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
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

        function innerTemplateMessage(e) {
            ImageOrGifHide.style.cssText = 'display: flex !important;'
            const baseURL = '{{  ( Config::get("Global") )['url'] }}'
            uuid = e.currentTarget.id
            const currentTemplateRow = arrayMessageTemplate.filter(value => value.uuid == uuid);
            let content = currentTemplateRow.shift()?.content;
            if(content != null){
                document.getElementById('textMessage').value = content;

            } else {
                
                let data = {
                    'entityType': entity_type,
                    'entityId': object_Id,
                    'templateId': uuid
                };

                let settings = ajax_settings_with_json(baseURL + 'Setting/getTemplate/' + accountId, "POST", data);
                $.ajax(settings)
                    .done(function (json, code, resObj) {
                        console.log(baseURL + 'Setting/getTemplate/' + accountId + ' response ↓ ');
                        console.log(json);

                        if (resObj.status == 200) {
                            document.getElementById('textMessage').value = json.data;

                            ImageOrGifHide.style.cssText = 'display: none !important;'
                        } else {
                            messageEmployee.style.display = 'block';
                            messageEmployee.innerText = json.data.message;
                            ImageOrGifHide.style.cssText = 'display: none !important;'
                        }

                    })
                    .fail(function (res) {
                        if(res.status == 400)
                        messageAddField.style.display = 'block'
                        messageAddField.innerText = res.responseJSON.message
                        ImageOrGifHide.style.cssText = 'display: none !important;'
                    });

            }

        }

        function messengerName(value) {
            if (value === 'telegram') phoneOrNameDiv.innerText = 'Номер телефона  /  Имя пользователя'
            else phoneOrNameDiv.innerText = 'Номер телефона'

        }
    </script>

@endsection
