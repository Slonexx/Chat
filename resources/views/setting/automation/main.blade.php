@extends('setting.temp_main_base')
@section('item', 'link_7')
@section('route_name', 'От какого сотрудника отправлять шаблоны автоматизации')
@section('form')
    <div id="notificationS" class="notification is-success" style="display: none"> <div id="notificationMessageS"></div> </div>

    @include('setting.automation.html')
    @include('setting.automation.view_employee')
    @include('setting.automation.main_script')

    <script>









        function ViewEmployee(uid){
            window.document.getElementById('html').style.display = 'none'
            window.document.getElementById('view_employee_uid').style.display = 'block'

            if (automation != null)
                automation.forEach(function (item){
                    if (uid == item.employee.id) is_active_view = item
                })
            view_list_template(uid)

        }

        function back_is_html(){
            is_active_view = []


            window.document.getElementById('html').style.display = 'block'
            window.document.getElementById('view_employee_uid').style.display = 'none'
        }




    </script>




@endsection

