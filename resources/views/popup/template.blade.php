@extends('popup.index')
@section('content')

    <div class="main-container" style="height: 99%; width: 99%; background: rgb(220, 220, 220)" >
        <div class="content-container">
            <div class="toc-wrapper">
                <div class="row">
                    <span class="mx-2 mt-2">
                        <img src="{{  Config::get("Global.url").'client.svg' }}" height="80%"  alt="">
                        <img src="{{  Config::get("Global.url").'client2.svg' }}" width="100px" height="100%"  alt="">
                    </span>
                </div>
                <div class="mt-3 mb-3 mx-1">
                    <div class="input-group">
                        <input type="search" id="form1" class="form-control" />
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <ul id="toc" class="toc-list-h1">

                </ul>
            </div>

            <div class="page-wrapper">
                <div class="bg-white row rounded p-3">

                    <div class="row gradient-invert rounded p-1 " style="margin-top: -0.5rem">
                        <div class="col" style="margin-top: 0.25rem"><span class="text-black" style="font-size: 20px"> Шаблон сообщений  </span>
                        </div>
                        <div class="col-3 text-right"><img src="{{  ( Config::get("Global") )['url'].'2logoHead.png' }}"  width="100%" alt=""></div>
                    </div>



                </div>
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
        let phone = ''


        let receivedMessage = {
            "name":"OpenPopup",
            "messageId":1,
            "popupName":"TemplateMessage",
            "popupParameters":
                {
                    "accountId":"1dd5bd55-d141-11ec-0a80-055600047495",
                    "object_Id":"5f3023e9-05b3-11ee-0a80-06f20001197a",
                    "entity_type":"customerorder",

                    "build_query":"",

                    "license_id":"36651",
                    "phone":"+77750498888",

                }
        };

        window.addEventListener("message", function(event) {
        //let receivedMessage = event.data

        if (receivedMessage.name === 'OpenPopup') {

            accountId = receivedMessage.popupParameters.accountId
            object_Id = receivedMessage.popupParameters.object_Id
            entity_type = receivedMessage.popupParameters.entity_type
            license_id = receivedMessage.popupParameters.license_id
            phone = receivedMessage.popupParameters.phone

            receivedMessage = []

            let data = {
                accountId: accountId,
                object_Id: object_Id,
                entity_type: entity_type,
            };

            let settings = ajax_settings(url+"/get/All", "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(url+entity_type+"/get/All"  + ' response ↓ ')
                console.log(json)

                if (json.status){

                    (json.data).forEach((item) => {
                        $('#toc').after(
                            $('<li><a class="mx-1"> <button class="btn">'+item.name+'</button></a></li>')
                        );
                    });



                } else {

                }

            })

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
