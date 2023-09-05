<!doctype html>
<html lang="en">
@include('head')
<body style="background-color:#dcdcdc;">

<div class="page headfull">
    <div class="sidenav">
        <div class="p-2 gradient_invert pb-3 ">
            <img src="{{  ( Config::get("Global") )['url'].'client.svg' }}" width="40px" height="40px" alt="">
            &nbsp;
            <img class="mt-2" src="{{  ( Config::get("Global") )['url'].'client2.svg' }}" width="120px" height="100%"
                 alt="">
            <div class="mt-3" style="font-size: 14px"><i class="fa-solid fa-user"
                                                         style="font-size: 20px"></i> {{ $fullName}} </div>
            <div style="font-size: 12px">  {{ $uid }} </div>
        </div>

        <br>
        <a id="link_1" href="/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}">Главная </a>
        <div id="setting" style="display: none">
            <button id="btn_1" class="mt-1 dropdown-btn">Настройки <i class="fa fa-caret-down"></i></button>
            <div class="dropdown-container">
                <a id="link_2" class="mt-1"
                   href="/Setting/createToken/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}"> Авторизация </a>
            </div>
        </div>
    </div>
</div>
<div class="main head-full" style=""> @yield('content') </div>

@include('widgetChatApp')
@include('style')
@include('script')

</body>
</html>




