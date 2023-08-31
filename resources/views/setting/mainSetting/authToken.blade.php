@extends('layout')
@section('item', 'link_2')
@section('content')
    @include('setting.script_setting_app')
    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @include('div.TopServicePartner') <script>NAME_HEADER_TOP_SERVICE("Настройки → настройки интеграции")</script>
        @include('div.alert')
        @isset($message)
            <script>alertViewByColorName("danger", "{{ $message }}")</script>
        @endisset

        <form class="mt-3" action="/Setting/createToken/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="mb-3 row">
                <label for="token" class="col-3 col-form-label"> Токен приложения WebKassa </label>
                <div class="col-9">
                    <input id="token" type="text" name="token" placeholder="ключ доступа к WebKassa" class="form-control form-control-orange"
                           required maxlength="255" value="">
                </div>
            </div>
            <hr>
            <div class='d-flex justify-content-end text-black btnP' >
                <button class="btn btn-outline-dark textHover" data-bs-toggle="modal" data-bs-target="#modal"> Сохранить </button>
            </div>
        </form>
    </div>


    <script>
        let accountId = '{{ $accountId }}'

    </script>


@endsection

