
@extends('widget.widget')

@section('content')


    <div class="row gradient rounded p-2">
        <div class="col-6">
            <div class="mx-2">
                <img src="{{  ( Config::get("Global") )['url'].'client.svg' }}" width="50px" height="50px"  alt="">
                <img src="{{  ( Config::get("Global") )['url'].'client2.svg' }}" width="100px" height="100%"  alt="">
            </div>
        </div>
        <div class="col-2 ">

        </div>
    </div>

    <div class="row mt-4 rounded bg-white">
        <div class="col-1"></div>
        <div class="col-10">
            <div class="text-center">
                <div class="p-2 bg-danger text-white" style="padding-bottom: 1.5rem !important;">
                    <span> <i class="fa-solid fa-ban "></i></span>
                    <span id="errorMessage" style="font-size: 10px;">

                    </span>

                </div>
            </div>
        </div>
    </div>


    <script>
        const hostWindow = window.parent

        window.addEventListener("message", function(event) {
            console.log(event.data)
            const receivedMessage = event.data;
            let sendingMessage = {
                name: "OpenFeedback",
                correlationId: receivedMessage.messageId
            };
            hostWindow.postMessage(sendingMessage, '*');

        })

        let app = @json($message);
        window.document.getElementById('errorMessage').innerText = JSON.stringify(app)




    </script>


@endsection

