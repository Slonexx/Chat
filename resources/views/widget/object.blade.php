@extends('widget.widget')
@section('content')


        <div class="row gradient rounded p-2">
            <div class="col-4">
                <img src="{{  ( Config::get("Global") )['url'].'client.svg' }}" width="50px" height="50px"  alt="">
                <img src="{{  ( Config::get("Global") )['url'].'client2.svg' }}" width="100px" height="100%"  alt="">
            </div>
        </div>

        <div id="messageGoodAlert" class=" mt-1 mx-3 p-2 alert alert-success text-center " style="display: none; font-size: 12px; margin-bottom: 5px !important;">    </div>
        <div id="messageErrorAlert" class=" mt-1 mx-3 p-2 alert alert-danger text-center " style="display: none; font-size: 12px; margin-bottom: 5px !important;">    </div>



        <div  class="mt-1 mx-4 text-center">
            <div class="row">

            </div>
        </div>



    <script>
        const hostWindow = window.parent
        let Global_messageId = 0
        let accountId = "{{$accountId}}"
        let Global_object_Id
        let entity_type = "{{$entity}}"
        let employee = "{{$employee}}"

        console.log(employee)




        function ajax_settings(url, method, data){
            return {
                 "url": url,
                 "method": "GET",
                 "timeout": 0,
                 "headers": {"Content-Type": "application/json",},
                 "data": data,
             }
        }



        //let receivedMessage = {"name":"Open","extensionPoint":"document.customerorder.edit","objectId":"ac0c9983-acec-11ed-0a80-06ac001abb0c","messageId":5,"displayMode":"expanded"}

        window.addEventListener("message", function(event) {

        window.document.getElementById('messageGoodAlert').style.display = 'none'
        window.document.getElementById('messageErrorAlert').style.display = 'none'
        window.document.getElementById('CloseChangeWebKassa').style.display = 'none'

        const receivedMessage = event.data;
        if (receivedMessage.name === 'Open') {

            Global_object_Id = receivedMessage.objectId;
            let data = {
                accountId: accountId,
                entity_type: entity_type,
                objectId: Global_object_Id,
            };

            let sendingMessage = {
                name: "OpenFeedback",
                correlationId: receivedMessage.messageId
            };
            hostWindow.postMessage(sendingMessage, '*');

            //receivedMessage = null;

            let settings = ajax_settings("{{Config::get("Global")['url']}}"+'widget/Info/Attributes/', 'GET', data)
            console.log('Widget setting attributes: ↓')
            console.log(settings)

          /*  $.ajax(settings).done(function (response) {
                console.log("{{Config::get("Global")['url']}}" + 'widget/Info/Attributes/ response ↓ ')
                console.log(settings)

                let sendingMessage = {
                    name: "OpenFeedback",
                    correlationId: receivedMessage.messageId
                };
                hostWindow.postMessage(sendingMessage, '*');


            });*/
        }

         });



        function fiscalization(){
            Global_messageId++;
            let sendingMessage = {
                name: "ShowPopupRequest",
                messageId: Global_messageId,
                popupName: "fiscalizationPopup",
                popupParameters: {
                    object_Id:Global_object_Id,
                    accountId:accountId,
                    entity_type:entity_type,
                },
            };
            console.log("Widget Sending : ↓" )
            console.log(sendingMessage)
            hostWindow.postMessage(sendingMessage, '*');
        }


    </script>
@endsection
