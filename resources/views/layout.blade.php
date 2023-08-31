<!doctype html>
<html lang="en">
    @include('head')
    <body style="background-color:#dcdcdc;">

           <div class="page headfull">
                <div class="sidenav">
                    <div class="p-2 gradient_invert pb-3 ">
                        <img src="{{  ( Config::get("Global") )['url'].'client.svg' }}" width="40px" height="40px"  alt="">
                        &nbsp;
                        <img class="mt-2" src="{{  ( Config::get("Global") )['url'].'client2.svg' }}" width="120px" height="100%"  alt="">
                        <div>{{ request()->fullName }}</div>
                        <div>{{ request()->uid }}</div>
                    </div>

                    <br>
                    <a id="link_1" href="/{{$accountId}}?isAdmin={{ request()->isAdmin }}">Главная </a>
                    @if ( request()->isAdmin == null )
                    @else
                    @if( request()->isAdmin == 'ALL')
                            <button id="btn_1" class="mt-1 dropdown-btn">Настройки <i class="fa fa-caret-down"></i> </button>
                            <div class="dropdown-container">
                                <a id="link_2" class="mt-1" href="/Setting/createToken/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Основное </a>
                                <a id="link_4" class="mt-1" href="/Setting/Document/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Документ </a>
                                <a id="link_5" class="mt-1" href="/Setting/Worker/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Доступ </a>
                                <a id="link_6" class="mt-1" href="/Setting/Automation/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Автоматизация </a>
                            </div>
                            <a id="link_7" class="mt-1" href="/kassa/change/{{$accountId}}?isAdmin={{ request()->isAdmin }}"> Смена </a>
                        @endif
                    @endif
                </div>
           </div>
           <div class="main head-full" style=""> @yield('content') </div>

           <script> (function(w,d,u){ var s=d.createElement('script');s.defer=true;s.src=u+'?'+(Date.now()/60000|0); var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h); })(window,document,'https://s3.wasabisys.com/cabinet.chatapp.online/widgetsFiles/30126/2023/08/30/30b4d0e9-5035-4d04-860d-7162e58d0ad7.js'); (function(w,d,u){ var s=d.createElement('script');s.defer=true;s.src=u+'?'+(Date.now()/60000|0); var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h); })(window,document,'https://s3.wasabisys.com/cabinet.chatapp.online/external/widget/v2/widget.js'); </script>
    </body>
</html>

<script>

        let item = '@yield('item')'

        window.document.getElementById(item).classList.add('active_sprint')
        if (item.replace(/[^+\d]/g, '') > 1 && item.replace(/[^+\d]/g, '') <= 6){
           this_click(window.document.getElementById('btn_1'))
        }

        function this_click(btn){
            btn.classList.toggle("active");
            let dropdownContent = btn.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        }

</script>

@include('style')
@include('script')


