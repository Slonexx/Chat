@extends('popup.indexTemplate')
@extends('popup.baseFunction')
@section('content')

    <div class="main-container" style="height: 99%; width: 99%;">
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
            <ul id="toc" class="toc-list-h1-title" style="display: block">
            </ul>
        </div>
        <div class="page-wrapper content-container">

            <div id="ImageOrGifHide" style="display: none">
                <div class="box d-flex justify-content-center  mt-3">
                    <img src="{{ Config::get("Global.url").'loading.gif' }}" width="50%">
                </div>
            </div>

            <div id="contentView" style="display: none">
                <div class="box" style="margin: 1rem;">
                    <div class="columns">
                        <div class="column is-5">
                            <i class="fas fa-user-circle"></i> Контрагент <a target="_blank" id="nameAgent"> имя
                                агента </a> {{--телефон: <span id="phoneAgent"> номер телефона</span>--}}
                        </div>
                        <div class="column">
                            Данные из полей
                        </div>
                        <button onclick="refreshInformation()" class="btn gradient_focus text-black" type="button">
                            Проверить &nbsp; <i class="fas fa-undo-alt"></i></button>
                    </div>
                    <div id="is_dialogIds_a" class="mt-1 columns text-center">
                    </div>
                </div>

                <div id="mainsView" class="container bg-white rounded" style="display: flex">
                    <div class="content">


                        <div class="mt-3 row p-2">
                            <div id="phoneOrNameDiv" class="col-4">Номер телефона</div>
                            <input id="phoneOrName" oninput="deleteSpaces(this)" class="form-control col" type="text"
                                   name="phoneOrName" value="">
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


                        <div id="notificationInContent" class="notification text-center p-1"
                             style="display: none"></div>
                    </div>
                    <div class="footer row p-1 rounded">
                        <div class="input-group">
                            <textarea id="textMessage" class="form-control" maxlength="250"
                                      style="resize: none;"></textarea>
                            <button onclick="sendMessage()" id="sendMessage" class="btn gradient_focus p-5"
                                    type="button" style="display: none">
                                <i class="fas fa-external-link-alt text-white" style="font-size: 50px"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="ErrorMessage" class="notification text-center p-1" style="display: none"></div>
        </div>
    </div>

    @include('popup.style_template')

    <script>
        let phoneOrName = window.document.getElementById('phoneOrName')
        let messenger = window.document.getElementById('messenger')

        const url = "{{ Config::get("Global.url") }}" + 'Popup/template/message'

        let accountId = ''
        let object_Id = ''
        let entity_type = ''
        let license_id = ''
        let license_full = ''
        let employee_id = ''
        let agent = ''
        let phone = ''
        let dialogIds = []
        let chatId = ''

        let countTimerCycle = 0

        let arrayMessageTemplate


       /* let receivedMessage = {
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
                    "phone": "7750498821",
                    "dialogIds": {
                        "Telegram": "@SergeiIOne",
                        "WhatsApp": "77750498821@c.us",
                        "Номер телефона": "7750498821",
                        "Электронная почта": "s.ivanov@smartinnovations.kz"
                    }
                }
        };*/

        window.addEventListener("message", function (event) {
            const receivedMessage = event.data
            clearView()

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
                dialogIds = receivedMessage.popupParameters.dialogIds


                is_dialogIds_set(dialogIds)


                let nameAgent = window.document.getElementById('nameAgent')
                nameAgent.innerText = agent
                nameAgent.setAttribute('href', 'https://online.moysklad.ru/app/#Company/edit?id=' + employee_id);

                let dataForTemplatesRequest = {accountId: accountId, object_Id: object_Id, entity_type: entity_type}

                getTemplatesInformationAll(dataForTemplatesRequest);

                getMessenger(isSetData())
                getInformation(isSetData())
            }
        });


        function onClickAttAgent(html) {
            let compliances = {
                "WhatsApp": "grWhatsApp",
                "whatsapp": "grWhatsApp",
                "Telegram": "telegram",
                "telegram": "telegram",
                "Email": "email",
                "email": "email",
                "vk": "vkontakte",
                "instagram": "instagram",
                "telegram_bot": "telegramBot",
                "avito": "avito",
                "Номер телефона": "grWhatsApp",
                "Электронная почта": "email",
            }
            messenger.value = compliances[html.innerText]
            phoneOrName.value = dialogIds[html.innerText]
            getInformation(isSetData())
        }

    </script>


    <script>
        function getTemplatesInformationAll(data) {
            isViewImageOrGifHide(true)
            let settings = ajax_settings(url + "/get/All", "GET", data);
            $.ajax(settings).done(function (json) {
                isViewImageOrGifHide(false)
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
                        button.className = "btn"
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
                    isErrorMessage(JSON.stringify(json.data))
                }
            })
        }


        function getMessenger(data) {
            window.document.getElementById('notificationInContent').style.display = 'none'
            let messenger = window.document.getElementById('messenger')
            let settings = ajax_settings(url + "/get/information/messenger", "GET", data);
            $.ajax(settings).done(function (json) {
                if (json.status) {
                    if ((json.data).length > 0) {
                        createOptions(json.data, messenger)
                        messengerName(messenger.value)
                    } else isActiveInformation('У данной линии отсутствуют мессенджеры')
                } else isActiveInformation(JSON.stringify(json.message))
            })
        }

        function getInformation(data) {
            console.log(data.messenger)
            isViewImageOrGifHide(true)
            if (data.messenger == '' && countTimerCycle > 5) {
                setTimeout(function () {
                    countTimerCycle++
                    getInformation(isSetData())
                }, 500);
            } else {
                window.document.getElementById('notificationInContent').style.display = 'none'
                let settings = ajax_settings(url + "/get/information/chatapp", "GET", data);
                $.ajax(settings).done(function (json) {
                    isViewImageOrGifHide(false)
                    if (json.status) {
                        window.document.getElementById('sendMessage').style.display = 'block'
                        chatId = json.data.chatId
                        if (json.hasOwnProperty('message')) isActiveInformation(JSON.stringify(json.message))
                    }
                    else {
                        isActiveInformation(JSON.stringify(json.message))
                    }
                })
            }
        }


        function sendMessage() {
            let textMessage = window.document.getElementById('textMessage')
            let linesId = window.document.getElementById('linesId')

            window.document.getElementById('notificationInContent').style.display = 'none'
            if (textMessage.value == '') isActiveInformation('Отсутствует сообщение, пожалуйста введите сообщение или выберите из меню слева')
            if (chatId == '') isActiveInformation('Отсутствует данные для отправки, пожалуйста нажмите на кнопку "проверить".')

            if (textMessage.value != '' && chatId != '') {
                let data = {
                    accountId: accountId,
                    object_Id: object_Id,
                    entity_type: entity_type,

                    employee: employee_id,
                    messenger: messenger.value,
                    linesId: linesId.value,
                    chatId: chatId,
                    phoneOrName: phoneOrName.value,
                    text: textMessage.value,
                }

                let settings = ajax_settings(url + "/get/send/message", "GET", data);
                $.ajax(settings).done(function (json) {
                    if (json.status) isActiveInformation('Сообщение отправлено', false)
                    else isActiveInformation(JSON.stringify(json.data))
                })
            }
        }


        function is_dialogIds_set(dialog) {
            for (let key in dialog) {
                $('#is_dialogIds_a').append(`<a class="m-1 box column addStyleColumns" onclick="onClickAttAgent(this)"> ${key}</a>`);
            }
        }
    </script>

    <script>
        function clearView() { // ООбновление страницы удаление и запуск заново при инвенте
            /*Обновление присетов*/
            window.document.getElementById('is_dialogIds_a').innerHTML = ''

            /*Поиск*/
            window.document.getElementById('search').value = ''
            window.document.getElementById('toc').innerHTML = ''

            /*Шапка внутри бокса*/
            window.document.getElementById('nameAgent').innerText = ''
            /* window.document.getElementById('phoneAgent').innerText = ''*/

            /*ТЕЛО БОКСА*/
            window.document.getElementById('phoneOrName').value = ''
            window.document.getElementById('textMessage').value = ''
            clearOption(window.document.getElementById('messenger'))
            clearOption(window.document.getElementById('linesId'))

            window.document.getElementById('sendMessage').style.display = 'none'


            /*ХЗ*/
            window.document.getElementById('notificationInContent').style.display = 'none'
            isViewImageOrGifHide(false)

            countTimerCycle = 0
        }


        function isSetData() {
            let linesId = window.document.getElementById('linesId')

            return {
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
        }


        function isActiveInformation(text, bool = true) {
            let notification = window.document.getElementById('notificationInContent')
            notification.style.display = 'block'
            notification.classList.remove('is-info')
            notification.classList.remove('is-danger')
            notification.classList.remove('is-success')

            if (text == '"Невозможно проверить по данному мессенджеру. Вы можете отправить сообщение, если только вы уверены, что в данный чат существует"')
                notification.classList.add('is-info')
            else {
                if (bool) notification.classList.add('is-danger')
                else notification.classList.add('is-success')
            }


            notification.innerText = text
        }

        function isErrorMessage(text, bool = true) {
            let notification = window.document.getElementById('ErrorMessage')
            notification.style.display = 'block'
            notification.classList.remove('is-danger')
            notification.classList.remove('is-success')
            if (bool) notification.classList.add('is-danger')
            else notification.classList.add('is-success')

            notification.innerText = text
        }
    </script>
    {{--ДОП--}}
    <script>
        function isViewImageOrGifHide(bool = true) {
            let ImageOrGifHide = window.document.getElementById('ImageOrGifHide')
            let contentView = window.document.getElementById('contentView')
            if (bool) {
                ImageOrGifHide.style.display = 'block'
                contentView.style.display = 'none'
            } else {
                ImageOrGifHide.style.display = 'none'
                contentView.style.display = 'block'
            }
        }


        function refreshInformation() {
            let data = isSetData()
            getInformation(data)
        }

        function messengerName(value) {
            let phoneOrNameDiv = window.document.getElementById('phoneOrNameDiv')
            if (value === 'telegram') phoneOrNameDiv.innerText = '№ телефона  /  Логин'
            else if (value === 'email') phoneOrNameDiv.innerText = 'Электронная почта'
            else if (value === 'vkontakte') phoneOrNameDiv.innerText = 'ID контакта'
            else if (value === 'instagram') phoneOrNameDiv.innerText = 'Логин'
            else phoneOrNameDiv.innerText = '№ телефона '

        }

        function createOptions(data, targetElement) {
            data.forEach((item) => {
                let option = document.createElement("option");

                option.text = item.name
                option.value = item.id || item.value

                targetElement.appendChild(option);
            });
        }

        function clearOption(selected) {
            while (selected.firstChild) selected.removeChild(selected.firstChild)
        }

        function transliterate(text) {
            const transitMap = {
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
                return transitMap[char.toLowerCase()] || char;
            }).join('');
        }

        function deleteSpaces(e) {
            const arrayDivided = e.value.split(" ");
            phoneOrName.value = arrayDivided.join("");
        }
    </script>
    {{--Иванты--}}
    <script>
        phoneOrName.addEventListener("blur", function (event) {
            refreshInformation()
        });

        messenger.addEventListener("change", function (event) {
            refreshInformation()
        });
    </script>
    {{--Поиск и правое бок меню--}}
    <script>
        function search(search) {
            let searchValue = transliterate(search.value.toLowerCase().trim())
            let elements = Array.from(toc.children)
            elements.forEach(function (element) {
                let text = element.textContent.toLowerCase();

                if (text.startsWith(searchValue)) element.style.display = 'block';
                else element.style.display = 'none';
            });
        }

        function searchTemplate() {
            let data = {
                accountId: accountId,
                object_Id: object_Id,
                entity_type: entity_type,

                name: search.value
            };

            let settings = ajax_settings(url + "/get/where/name", "GET", data);
            $.ajax(settings).done(function (json) {
                if (json.status) {
                    arrayMessageTemplate = json.data;
                    window.document.getElementById('phoneOrName').value = phone;

                    let option1 = document.createElement("option")
                    option1.text = license_full
                    option1.value = license_id
                    linesId.appendChild(option1)

                } else {
                    mainsView.style.display = 'none'
                    isErrorMessage(JSON.stringify(json.data))
                }
            })
        }

        function innerTemplateMessage(e) {
            const baseURL = '{{  ( Config::get("Global") )['url'] }}'
            uuid = e.currentTarget.id
            const currentTemplateRow = arrayMessageTemplate.filter(value => value.uuid == uuid);
            let content = currentTemplateRow.shift()?.content;
            if (content != null) {
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

                        } else {
                            window.document.getElementById('messageEmployee').style.display = 'block';
                            window.document.getElementById('messageEmployee').innerText = json.data.message;
                        }

                    })
                    .fail(function (res) {
                        if (res.status == 400)
                            messageAddField.style.display = 'block'
                        messageAddField.innerText = res.responseJSON.message
                    });

            }

        }
    </script>
@endsection
