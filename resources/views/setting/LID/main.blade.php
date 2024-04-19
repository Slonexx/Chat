@extends('setting.temp_main_base')
@section('item', 'link_8')
@section('route_name', 'ЛИД')
@section('form')
    <div id="notificationS" class="notification is-success" style="display: none"> <div id="notificationMessageS"></div> </div>

    <script>
        const employee = @json($employee);
        const model = @json($model);
    </script>


    @include('setting.LID.html')





@endsection


















<style>
    .input_checkbox{
        font-size: 1.5rem;
        width: 3em !important;
        border-color: red !important;
    }
    .input_checkbox:focus{
        box-shadow: 0 0 0 .25rem rgb(255, 0, 0) !important;
    }
    .input_checkbox:hover{
        box-shadow: 0 0 0 .25rem rgb(255, 0, 0) !important;
    }
    .input_checkbox:checked{
        background-color: #00ff00 !important;
        border-color: #00ff00 !important;
    }
    .input_checkbox:checked:hover{
        box-shadow: 0 0 0 .25rem rgb(23, 225, 138) !important;
    }
    .input_checkbox:checked:focus{
        box-shadow: 0 0 0 .25rem rgba(23, 225, 138, 0) !important;
    }

</style>
