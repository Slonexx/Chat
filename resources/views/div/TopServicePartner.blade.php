<div class="row gradient rounded p-2 pb-2 mt-1">
    <div class="col-9"> <span id="HEAD_TOP_SERVICE" class="text-black" style="font-size: 20px">  </span> </div>
    <div class="col-3 text-center">
        <img src="{{  ( Config::get("Global") )['url'].'2logoHead.png' }}" width="100%"  alt="">
    </div>
</div>
    <script>
        function NAME_HEADER_TOP_SERVICE(name){
            window.document.getElementById('HEAD_TOP_SERVICE').innerText = name
        }
    </script>
{{-- Настройки &#8594; настройки интеграции --}}
