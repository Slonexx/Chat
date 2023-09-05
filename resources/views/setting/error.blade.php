@section('item', 'link_1')
@extends('layout')
@section('content')

    <div class="mx-1 mt-3 py-3 p-4 bg-white rounded">
        @include('div.TopServicePartner')
        <div id="message" class="mt-2 alert alert-danger text-center">  </div>
    </div>

    <script>
        let message = @json($message);
        NAME_HEADER_TOP_SERVICE("Ошибка приложения")
        window.document.getElementById('message').innerText = JSON.stringify(message)
    </script>

@endsection



