@extends('setting.temp_main_base')
@section('item', 'link_6')
@section('route_name', 'создание сценариев')
@section('form')
    <div id="notificationS" class="notification is-success" style="display: none"> <div id="notificationMessageS"></div> </div>

    @include('setting.Scenario.lite_script')
    @include('setting.Scenario.main_script')


    @include('setting.Scenario.html')


    <script>


        function hideAllScript() {
            notificationMessage.innerText = ''
            create.style.display = 'none'
        }


        function showPage(block, bool, display = 'block') {
            block.style.display = 'none'
            if (bool) block.style.display = display
        }
    </script>
@endsection

