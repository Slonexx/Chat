@extends('layout')
@section('item', 'link_2')
@section('content')
    @include('setting.script_setting_app')

    <div class="mx-1 mt-3 py-3 p-4 bg-white rounded">
        <div class="row  gradient rounded p-2 pb-2 mt-1" style="margin-top: -1rem">
            <div class="col-6" style="margin-top: 0.25rem"><span class="text-black" style="font-size: 20px"> Настройки → Сотрудники и доступы  </span>
            </div>
            <div class="col-3 d-flex justify-content-end ">
                <button onclick="showHideCreateEmployee('1')" class="btn btn-outline-dark gradient_focus"> добавить
                </button>
            </div>
            <div class="col-3 text-right"><img src="{{  ( Config::get("Global") )['url'].'2logoHead.png' }}"  width="100%" alt=""></div>
        </div>

        @include('div.alert')
        @isset($message)
            <script>alertViewByColorName("danger", "{{ $message }}")</script>
        @endisset
        <div id="sleepInfoDelete" class="mt-2 alert alert-info fade show in text-center text-black " style="display: none">
                <div class="row">
                    <div class="col-10 mt-1" id="messageInfoDelete"></div>

                    <div class='col d-flex justify-content-end text-black btnP' style="font-size: 14px">
                        <button onclick="activateCloseDelete()" class="btn  gradient_focus"> отмена </button>
                    </div>
                </div>

            </div>


        <form class="mt-3"
              action="/Setting/createToken/{{ $accountId }}?isAdmin={{ $isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}"
              method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div id="main_div" class="">

                <div class="row bg-info rounded text-white">
                    <div class="col mx-3"> Имя сотрудника</div>
                    <div class="col"> Доступ</div>
                    <div class="col-1"> Изменить</div>
                    <div class="col-1"></div>
                    <div class="col-1"> Удалить</div>
                </div>

                <div id="mainEmployees" class="mt-3"></div>
            </div>


            <hr>
            <div class='d-flex justify-content-end text-black btnP'>
                <button class="btn btn-outline-dark gradient_focus"> Дальше → </button>
            </div>
        </form>


        <div id="createEmployees" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"  aria-labelledby="createEmployees" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span id="GifAndImage">
                                <img id="GifOrImageHide" src="{{  ( Config::get("Global") )['url'].'client.svg' }}"
                                     width="15%" alt="">
                                <img id="ImageOrGifHide" src="{{  ( Config::get("Global") )['url'].'loading.gif' }}"
                                     width="15%" alt="" style="display: none">
                            </span>

                            Доступ к чатам
                        </h5>

                        <button onclick="showHideCreateEmployee('2')" type="button"
                                class="close btn btn-outline-dark gradient_focus" data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div id="messageEmployee" class="alert alert-warning alert-primary fade show in text-center "
                             style="display: none"> Error
                        </div>


                        <div class="mt-2 row">
                            <div class="col-4">Сотрудник</div>
                            <select id="employeeSelect" name="employee" class="col form-select"></select>
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Электронная почта</div>
                            <input class="form-control col" id="email" type="email" name="email"
                                   placeholder="example@gmail.com" value="">
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Пароль</div>
                            <input class="form-control col" id="password" type="text" name="password"
                                   placeholder="**** **** **** **** ****" value="">
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">APP ID</div>
                            <input class="form-control col" id="appId" type="text" name="appId" placeholder="app_1111_1"
                                   value="">
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Доступ</div>
                            <select id="access" name="access" class="col form-select">
                                <option value="0">Полный доступ</option>
                                <option value="1">Доступ к документом</option>
                                <option value="2">Доступ к контрагентом</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button onclick="examinationEmployee()" type="button" class="btn btn-outline-dark gradient_focus"> Проверить </button>
                        <button id="btn_createEmployee" onclick="createEmployee()" type="button" class="btn btn-outline-dark gradient_focus">Проверить и добавить</button>
                    </div>

                </div>
            </div>
        </div>

        <div id="updateEmployees" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"  aria-labelledby="createEmployees" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span id="GifAndImage">
                                <img id="GifOrImageHide2" src="{{  ( Config::get("Global") )['url'].'client.svg' }}"
                                     width="15%" alt="">
                                <img id="ImageOrGifHide2" src="{{  ( Config::get("Global") )['url'].'loading.gif' }}"
                                     width="15%" alt="" style="display: none">
                            </span>

                            Изменение доступа
                        </h5>

                        <button onclick="showHideUpdateEmployee('2')" type="button"
                                class="close btn btn-outline-dark gradient_focus" data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div id="messageEmployee2" class="alert alert-warning alert-primary fade show in text-center "
                             style="display: none"> Error
                        </div>


                        <input class="form-control col" id="employee2" type="text" name="employee2" value="" style="display: none">
                        <input class="form-control col" id="employeeName" type="text" name="employeeName" value="" style="display: none">

                        <div class="mt-2 row">
                            <div class="col-4">Электронная почта</div>
                            <input class="form-control col" id="email2" type="email" name="email2"
                                   placeholder="example@gmail.com" value="">
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Пароль</div>
                            <input class="form-control col" id="password2" type="text" name="password2"
                                   placeholder="**** **** **** **** ****" value="">
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">APP ID</div>
                            <input class="form-control col" id="appId2" type="text" name="appId2" placeholder="app_1111_1"
                                   value="">
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Доступ</div>
                            <select id="access2" name="access2" class="col form-select">
                                <option value="0">Полный доступ</option>
                                <option value="1">Доступ к документом</option>
                                <option value="2">Доступ к контрагентом</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button onclick="updateEmployee()" type="button" class="btn btn-outline-dark gradient_focus"> Изменить </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('setting.mainSetting.baseFunction')

    <script>
        const baseURL = '{{  ( Config::get("Global") )['url'] }}'
        const BaseMsEmployee = @json($MsEmployee);

        let MsEmployee = @json($MsEmployee);
        let MyEmployee = @json($MyEmployee);
        let accountId = '{{ $accountId }}'
        let deleteButtonBool = false

       if (MyEmployee.length !== 0) {

           MyEmployee.forEach((item) => {

               let eValue = item.employeeId

               let email = item.email
               let password = item.password
               let appId = item.appId
               let aValue = item.access

               $('#mainEmployees').append(' <div id="'+eValue+'" class="row">' +
                   '<div class="col mx-3"> '+item.employeeName+' </div>' +
                   '<div class="col"> '+nameAccess(aValue)+' </div>' +
                   '<div onclick="showHideUpdateEmployee(\''+1+'\',\''+eValue+'\')" class="col-1 btn gradient_focus"> Изменить</div>' +
                   '<div class="col-1"></div>' +
                   '<div onclick="deleteAccount(\''+eValue+'\', \''+item.employeeName+'\')" class="col-1 btn gradient_focus"><i class="fa-regular fa-circle-xmark"></i></div>' +
                   '</div>')
               onEmployee(true, eValue)
           });
       }


        function updateEmployee(){
            let employee = window.document.getElementById('employee2').value
            let employeeName = window.document.getElementById('employeeName').value


            let email = window.document.getElementById('email2').value
            let password = window.document.getElementById('password2').value
            let appId = window.document.getElementById('appId2').value
            let access = window.document.getElementById('access2').value


            examination('fast', "update" ,employee, employeeName,  email, password, appId, access)
        }


        function showHideUpdateEmployee(val, id) {
            if (val === '1') {
                $('#updateEmployees').modal('toggle')

                let data = {
                    employee: id,
                };

                let settings = ajax_settings(baseURL + 'Setting/get/employee/' + accountId , "GET", data);
                console.log(baseURL + 'Setting/get/employee/' + accountId  + ' settings ↓ ')
                console.log(settings)

                $.ajax(settings).done(function (json) {
                    console.log(baseURL + 'Setting/get/employee/' + accountId   + ' response ↓ ')
                    console.log(json)

                    window.document.getElementById('employee2').value = json.employeeId
                    window.document.getElementById('employeeName').value = json.employeeName
                    window.document.getElementById('email2').value = json.email
                    window.document.getElementById('password2').value = json.password
                    window.document.getElementById('appId2').value = json.appId
                    window.document.getElementById('access2').value = json.access

                })

            } else {
                $('#updateEmployees').modal('toggle')
            }

        }

        function showHideCreateEmployee(val) {
            if (val === '1') {
                $('#createEmployees').modal('toggle')

                let selectElement = window.document.getElementById('employeeSelect')

                while (selectElement.firstChild) {
                    selectElement.removeChild(selectElement.firstChild);
                }

                MsEmployee.forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name
                    option1.value = item.id
                    selectElement.appendChild(option1)
                });

            } else {
                $('#createEmployees').modal('toggle')
            }

        }

        function createEmployee() {
            let employee = window.document.getElementById('employeeSelect')

            let eValue = employee.value
            let eOption = employee.querySelector('option[value="' + eValue + '"]');


            let email = window.document.getElementById('email').value
            let password = window.document.getElementById('password').value
            let appId = window.document.getElementById('appId').value
            let access = window.document.getElementById('access')

            examination('fast', 'create', employee.value, eOption.textContent,  email, password, appId, access.value)

        }

        function examinationEmployee(){
            window.document.getElementById('GifOrImageHide').style.display = "none"
            window.document.getElementById('ImageOrGifHide').style.display = "inline"

            let employee = window.document.getElementById('employeeSelect')
            let eOption = employee.querySelector('option[value="' + employee.value + '"]');

            let email = window.document.getElementById('email').value
            let password = window.document.getElementById('password').value
            let appId = window.document.getElementById('appId').value
            let access = window.document.getElementById('access')

            examination('examination', 'create', employee.value, eOption.textContent, email, password, appId, access.value)
        }

        function examination(status, createOrUpdate, eValue, eName, email, password, appId, access){

            let data = {
                employee: eValue,
                employeeName: eName,

                email: email,
                password: password,
                appId: appId,

                access: access,
            };

            let settings = ajax_settings(baseURL + 'Setting/create/employee/' + accountId , "GET", data);
            console.log(baseURL + 'Setting/create/employee/' + accountId  + ' settings ↓ ')
            console.log(settings)

            $.ajax(settings).done(function (json) {
                console.log(baseURL + 'Setting/create/employee/' + accountId   + ' response ↓ ')
                console.log(json)
                window.document.getElementById('GifOrImageHide').style.display = "inline"
                window.document.getElementById('ImageOrGifHide').style.display = "none"

                if (json.status === 200) {

                    if (status === 'examination') {
                        window.document.getElementById('messageEmployee').style.display = "block"
                        window.document.getElementById('messageEmployee').innerText = JSON.stringify(json.message);
                        window.document.getElementById('btn_createEmployee').innerText = "Добавить";
                    } else {
                        if (createOrUpdate === 'create') {
                            showHideCreateEmployee('2')
                            onEmployee(true, eValue)
                        } else {
                            showHideUpdateEmployee('2', eValue)
                            window.document.getElementById(eValue).remove()
                        }

                        $('#mainEmployees').append(' <div id="'+eValue+'" class="row">' +
                            '<div class="col mx-3"> '+eName+' </div>' +
                            '<div class="col"> '+nameAccess(access)+' </div>' +
                            '<div class="col-1 btn gradient_focus"> Изменить</div>' +
                            '<div class="col-1"></div>' +
                            '<div onclick="deleteAccount(\''+eValue+'\', \''+eName+'\')" class="col-1 btn gradient_focus"><i class="fa-regular fa-circle-xmark"></i></div>' +
                            '</div>')



                    }


                } else  {
                    if (createOrUpdate === 'create'){
                        window.document.getElementById('messageEmployee').style.display = "block"
                        window.document.getElementById('messageEmployee').innerText = JSON.stringify(json.message);
                    }
                    else {
                        window.document.getElementById('messageEmployee2').style.display = "block"
                        window.document.getElementById('messageEmployee2').innerText = JSON.stringify(json.message);
                    }

                }
            })

        }


        function nameAccess(aValue){
           let nameAccess = ''

           if (aValue === '0') {
               nameAccess = 'Полный доступ'
           }
           if (aValue === '1') {
               nameAccess = 'Доступ к документом'
           }
           if (aValue === '2') {
               nameAccess = 'Доступ к контрагентом'
           }
           return nameAccess
       }

        function deleteAccountRow(id){
            if (deleteButtonBool) {
                window.document.getElementById(id).remove()

                let data = {
                    employee: id,
                };

                let settings = ajax_settings(baseURL + 'Setting/delete/employee/' + accountId , "GET", data);
                console.log(baseURL + 'Setting/delete/employee/' + accountId  + ' settings ↓ ')
                console.log(settings)

                $.ajax(settings).done(function (json) {
                    console.log(baseURL + 'Setting/delete/employee/' + accountId   + ' response ↓ ')
                    console.log(json)
                    onEmployee(false, id)
                })

            }

        }


       function onEmployee(status, id){
           if (status) {
               MsEmployee = MsEmployee.filter(employee => employee.id !== id);
           } else {
               let employee = BaseMsEmployee.filter(employee => employee.id === id);
               MsEmployee = [].concat(MsEmployee, employee)
           }

        }

    </script>

@endsection

