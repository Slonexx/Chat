<!doctype html>
<html lang="en" style=" background-color:#dcdcdc; ">
@include('head')
<body style="background-color:#dcdcdc;">

<div class="page headfull">
    <div class="sidenav">
        <div class="p-2 gradient_invert pb-3 ">
            <img src="{{  ( Config::get("Global") )['url'].'client.svg' }}" width="40px" height="40px" alt="">
            &nbsp;
            <img class="mt-2" src="{{  ( Config::get("Global") )['url'].'client2.svg' }}" width="120px" height="100%"
                 alt="">
            <div class="mt-3" style="font-size: 14px"><i class="far fa-user"></i> {{ $fullName}} </div>
            <div style="font-size: 12px">  {{ $uid }} </div>
        </div>

        <br>
        <a id="link_1" href="/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
            Главное </a>
        <div id="setting" style="display: none">
            <button id="btn_1" class="mt-1 dropdown-btn">Настройки <i class="fa fa-caret-down"></i></button>
            <div class="dropdown-container">
                <a id="link_2" class="mt-1"
                   href="/Setting/createToken/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
                    Сотрудники и доступы </a>
                <a id="link_3" class="mt-1"
                   href="/Setting/organization/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
                    Организация и линии </a>
                <button id="btn_2" class="mt-1 dropdown-btn">Шаблоны сообщений <i class="fa fa-caret-down"></i></button>
                <div class="dropdown-container">
                    <a id="link_4" class="mt-1"
                       href="/Setting/addFields/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
                        Дополнительные поля </a>
                    <a id="link_5" class="mt-1"
                       href="/Setting/template/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
                        Создание шаблонов </a>
                </div>
                <button id="btn_3" class="mt-1 dropdown-btn">Автоматизация <i class="fa fa-caret-down"></i></button>
                <div class="dropdown-container">
                    <a id="link_6" class="mt-1"
                       href="/Setting/scenario/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
                        Сценарии </a>
                    <a id="link_7" class="mt-1"
                       href="/Setting/automation/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
                        Отправитель </a>
                    <a id="link_8" class="mt-1"
                       href="/Setting/lid/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">
                        ЛИД </a>
                </div>
            </div>
        </div>

        <a target="_blank" href="https://smartchatapp.bitrix24.site/instruktsiiponastroyke/"> Инструкция </a>

    </div>
</div>
<div id="main_heading" class="main"> @yield('content') </div>

@include('widgetChatApp')
@include('style')
@include('script')

</body>
</html>




