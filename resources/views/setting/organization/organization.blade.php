@extends('layout')
@section('item', 'link_3')
@section('content')
    @include('setting.script_setting_app')

    <div class="mx-1 mt-3 py-3 p-4 bg-white rounded">
        <div class="row  gradient rounded p-2 pb-2 mt-1" style="margin-top: -1rem">
            <div class="col-6" style="margin-top: 0.25rem"><span class="text-black" style="font-size: 20px"> Настройки → Организация и линии  </span>
            </div>
            <div class="col-3 d-flex justify-content-end ">

            </div>
            <div class="col-3 text-right"><img src="{{  ( Config::get("Global") )['url'].'2logoHead.png' }}"  width="100%" alt=""></div>
        </div>

        @include('div.alert')
        @isset($message)
            @if($message != '')
                <script>alertViewByColorName("danger", "{{ $message }}")</script>
            @endif
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
              action="/Setting/organization/{{ $accountId }}?isAdmin={{ $isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}" method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div class="">

                <div class="row bg-info rounded text-white">
                    <div class="col-3 mx-3"> Название организации </div>
                    <div class="col"> Линия </div>
                    <div class="col-1"></div>
                    <div class="col-1"> Удалить </div>
                </div>

                <div id="main" class="mt-3"></div>
            </div>


            <hr>

            <div class="row">
                <div class="col">
                    <div onclick="showHideCreate('1')" class="btn btn-outline-dark gradient_focus"> добавить </div>
                </div>
                <div class="col">
                    <div class='d-flex justify-content-end text-black btnP'>
                        <button class="btn btn-outline-dark gradient_focus"> Дальше → </button>
                    </div>
                </div>
            </div>
        </form>


        <div id="createOrganization" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"  aria-labelledby="createOrganization" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <div class="modal-title" style="font-size: 16px">
                            <span id="GifAndImage">
                                <img id="GifOrImageHide" src="{{  ( Config::get("Global") )['url'].'client.svg' }}"
                                     width="15%" alt="">
                                <img id="ImageOrGifHide" src="{{  ( Config::get("Global") )['url'].'loading.gif' }}"
                                     width="15%" alt="" style="display: none">
                            </span>
                            <span>Компания: <span id="nameOrganization"></span> </span>
                        </div>

                        <button onclick="showHideCreate('2')" type="button"
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
                            <div class="col-4">Организация</div>
                            <select onchange="displayNameForOrganName(this.value)" id="organizationSelect" name="employee" class="col form-select"></select>
                        </div>
                        <hr>

                        <div id="createLineForEmployee" class="mt-2"> </div>

                    </div>

                    <div class="modal-footer">
                        <button onclick="btnCreatingEmployeeForOrgan()" type="button" class="btn btn-outline-dark gradient_focus"> Добавить сотрудника </button>
                        <button id="btn_createOnClick" onclick="createOnClick()" type="button" class="btn btn-outline-dark gradient_focus">Сохранить</button>
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

    @include('setting.organization.baseFunction')
    @include('setting.organization.lite')

    <script>
        const baseURL = '{{  ( Config::get("Global") )['url'] }}'
        const BaseMsOrgan = @json($MsOrgan);
        const BaseMyEmployee = @json($MyEmployee);

        let MsOrgan = @json($MsOrgan);
        let MyEmployee = @json($MyEmployee);
        let accountId = '{{ $accountId }}'
        let saveOrgan = @json($saveOrgan);


        let createMain = []
        let deleteButtonBool = false

        if (saveOrgan.length > 0) {
            saveOrgan.forEach((item) => {
                const organId = item.organId;
                const organName = item.organName;
                const lineName = item.lineName;

                if (!createMain[organId]) {
                    createMain[organId] = {
                        id: organId,
                        name: organName,
                        line: lineName, // Инициализация line строкой
                    };

                    onEmployee(true, 'MsOrgan', organId)
                } else {
                    // Добавляем уникальное значение lineName к существующей строке
                    if (!createMain[organId].line.includes(lineName)) {
                        createMain[organId].line += ', ' + lineName;
                    }
                }
            });
            createMain = Object.values(createMain);
            createMainForOrgan()
        }



        function showHideCreate(val) {
            if (val === '1') {
                $('#createOrganization').modal('toggle')

                while (organizationSelect.firstChild) { organizationSelect.removeChild(organizationSelect.firstChild); }
                createLineForEmployee.innerText = ''
                messageEmployee.style.display = 'none'


                if (MsOrgan.length > 0) {
                    let organization = window.document.getElementById('organizationSelect')
                    clearOption(organization)
                    createOptions(MsOrgan, organization)

                    window.document.getElementById('nameOrganization').innerText = organization.querySelector('option[value="' + organization.value + '"]').textContent
                    MyEmployee.forEach((item) => {
                        $('#createLineForEmployee').append(
                            ' <div id="LineForEmployee_'+ item.employeeId +'" style="display:none;">' +
                            ' <div class="mt-2 row"> ' +
                            ' <div class="col-4">Сотрудник</div> ' +
                            ' <select onchange="createLicensesHttp( \''+item.employeeId+'\', this.value)" id="employeeSelect_'+item.employeeId+'" class="col form-select"></select> ' +
                            '</div> ' +
                            ' <div class="mt-2 row"> ' +
                            ' <div class="col-4">Линия</div> ' +
                            ' <select onchange="SelectLicenses(this.value, \''+item.employeeId+'\')" id="licenses_'+ item.employeeId +'" class="col form-select"></select> ' +
                            ' </div> ' +
                            ' <div class="mt-2 row"> ' +
                            ' <div class="col"></div> ' +
                            ' <div class="col"></div> ' +
                            '<button onclick="onEmployee(false, \''+"MyEmployee"+'\', \''+item.employeeId+'\')" type="button" class="col btn btn-outline-dark gradient_focus">Удалить сотрудника</button>' +
                            ' </div> ' +
                            ' </div> '
                        )
                    })
                    createEmployeeForOrgan(MyEmployee[0].employeeId)
                } else {
                    messageViewAndHideText(true, 'К сожалению, в данный момент у вас нет доступных организаций.')
                }



            } else {
                $('#createOrganization').modal('toggle')
            }

        }



        function SelectLicenses(value, ChatAppliancesId){
            animationLoadingGifOrImage(true,  window.document.getElementById('GifOrImageHide'),  window.document.getElementById('ImageOrGifHide'))
            let data = { employeeId: ChatAppliancesId };
            let settings = ajax_settings(baseURL + 'Setting/organization/get/Licenses/' + accountId , "GET", data);

            $.ajax(settings).done(function (json) {
                animationLoadingGifOrImage(false,  window.document.getElementById('GifOrImageHide'),  window.document.getElementById('ImageOrGifHide'))

                if (json.status) {
                    (json.data).forEach((item) => {
                      if (item.licenseId == value) {

                          const now = new Date(); // Создаем объект Date, представляющий текущую дату и время
                          const unixTimestamp = Math.floor(now.getTime() / 1000); // Получаем текущий таймстамп (в секундах)

                          if (item.licenseTo < unixTimestamp) {
                              window.document.getElementById('messageEmployee').style.display = 'block'
                              messageViewAndHideText(true, 'У данной линии просрочен срок действия')
                          }
                      }

                    });
                } else {
                    messageViewAndHideText(true, json.data)
                }


            })

        }

        function createEmployeeForOrgan(employeeId){

            window.document.getElementById('LineForEmployee_'+employeeId).style.display = 'inline'

            let employee = window.document.getElementById('employeeSelect_'+employeeId)
            clearOption(employee)
            createOptions(MyEmployee, employee, false)

            createLicensesHttp(employeeId, employee.value)
            onEmployee(true,'MyEmployee', employee.value)

            BaseMyEmployee.forEach((item) => {
               if (item.employeeId !== employeeId && window.document.getElementById('LineForEmployee_'+employeeId).style.display !== 'none' ) {
                   $("#employeeSelect_" + item.employeeId + " option[value=" + employee.value + "]").remove();
               }
            })


        }



       function createLicensesHttp(MsOrganValue, ChatAppliancesId){
           animationLoadingGifOrImage(true,  window.document.getElementById('GifOrImageHide'),  window.document.getElementById('ImageOrGifHide'))


           messageViewAndHideText(false, '')
           let licenses = window.document.getElementById('licenses_' + MsOrganValue )
           while (licenses.firstChild) { licenses.removeChild(licenses.firstChild); }


           let data = { employeeId: ChatAppliancesId };
           let settings = ajax_settings(baseURL + 'Setting/organization/get/Licenses/' + accountId , "GET", data);

           $.ajax(settings).done(function (json) {
               animationLoadingGifOrImage(false,  window.document.getElementById('GifOrImageHide'),  window.document.getElementById('ImageOrGifHide'))

               if (json.status) {
                   (json.data).forEach((item) => {
                       let option1 = document.createElement("option")
                       let textLicense = item.licenseName

                       if (textLicense == "") { textLicense = "Отсутствует название " }

                       option1.text = item.licenseName + '#' +  item.licenseId
                       option1.value = item.licenseId
                       licenses.appendChild(option1)
                   });
               } else {
                   messageViewAndHideText(true, json.data)
               }


           })

       }


        function createOnClick() {
            messageViewAndHideText(false, '')
            let organId =  window.document.getElementById('organizationSelect')
            if (organId.value == '') { messageViewAndHideText(true, 'Отсутствуют данные по организации') }

            let elements = document.querySelectorAll('[id^="LineForEmployee_"]');

            let data = {}

            elements.forEach(function(element, id) {


                if (element.style.display !== 'none') {
                    let employeeSelect = element.querySelector('select[id^="employeeSelect_"]');
                    let licensesSelect = element.querySelector('select[id^="licenses_"]');

                    if (employeeSelect.value == '') { messageViewAndHideText(true, 'Отсутствуют данные по сотруднику') }
                    if (licensesSelect.value == '') { messageViewAndHideText(true, 'Отсутствуют данные по линии') }

                    data[id] = {
                        organId: organId.value,
                        organName: organId.querySelector('option[value="' +  organId.value + '"]').textContent,
                        employeeId: employeeSelect.value,
                        employeeName: employeeSelect.querySelector('option[value="' +  employeeSelect.value + '"]').textContent,
                        lineId: licensesSelect.value,
                        lineName: licensesSelect.querySelector('option[value="' +  licensesSelect.value + '"]').textContent,
                    };
                }

            });



            let settings = ajax_settings(baseURL + 'Setting/organization/create/Licenses/' + accountId , "GET", data);
            console.log(settings)
            console.log(data)

            $.ajax(settings).done(function (json) {
                animationLoadingGifOrImage(false,  window.document.getElementById('GifOrImageHide'),  window.document.getElementById('ImageOrGifHide'))

                if (json.status) {

                    createMain.push({
                        id: json.data.id,
                        name: json.data.name,
                        line: json.data.line,
                    });

                    createMainForOrgan()
                    onEmployee(true, 'MsOrgan', organId.value)
                    showHideCreate('2')
                } else {
                    messageViewAndHideText(true, json.message)
                }

            })
        }



        function createMainForOrgan(){
            createMain.forEach((item) => {

                if (item.id == '0') {
                    let main = document.getElementById('main');
                    while (main.firstChild) { main.removeChild(main.firstChild) }
                }


                $('#main').append(
                    ' <div id="'+item.id+'" class="row"> ' +
                        ' <div class="col-3 mx-3"> '+item.name+' </div> ' +
                        ' <div class="col"> '+item.line+' </div> ' +
                        ' <div class="col-1"></div> ' +
                        ' <div onclick="deleteAccount(\''+item.id+'\' , \''+item.name+'\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div>' +
                    ' </div> '
                )
            })


            createMain = [];

        }






        function btnCreatingEmployeeForOrgan() {
            if (MyEmployee.length > 0) createEmployeeForOrgan(MyEmployee[0].employeeId)
            else {
                messageViewAndHideText(true, 'К сожалению, в данный момент у вас нет доступных сотрудников. Пожалуйста, перейдите в раздел "Сотрудник и доступы" для добавления новых сотрудников.')
            }
        }


        function displayNameForOrganName(id) {
            let organization = window.document.getElementById('organizationSelect')
            window.document.getElementById('nameOrganization').innerText = organization.querySelector('option[value="' + id + '"]').textContent
        }


        function onEmployee(status, object, id) {

            switch (object) {
                case 'MyEmployee': {
                    if (status) {
                        MyEmployee = MyEmployee.filter(employee => employee.employeeId !== id);
                    } else {
                        window.document.getElementById('LineForEmployee_' + id).style.display = 'none'

                        let employeeS = document.getElementById('employeeSelect_' + id);
                        let licenses_ = document.getElementById('licenses_' + id);
                        while (employeeS.firstChild) {
                            employeeS.removeChild(employeeS.firstChild)
                        }
                        while (licenses_.firstChild) {
                            licenses_.removeChild(licenses_.firstChild)
                        }

                        MyEmployee = [].concat(MyEmployee, BaseMyEmployee.filter(employee => employee.employeeId === id))
                    }
                    break
                }
                case 'createMain': {

                    if (deleteButtonBool) {

                        let settings = ajax_settings(baseURL + 'Setting/organization/delete/Licenses/' + accountId, "GET", {organId: id});
                        $.ajax(settings).done(function (json) {
                            if (json.status){
                                window.document.getElementById(id).remove();
                                onEmployee(false, 'MsOrgan', id)
                            } else {
                                window.document.getElementById('sleepInfoDelete').style.display = 'block'
                                window.document.getElementById('sleepInfoDelete').innerText = JSON.stringify(json.message)
                            }

                        })
                    }

                    break
                }
                case 'MsOrgan': {

                    if (status) {

                        if (id == '0') {
                            MsOrgan = {}
                        } else MsOrgan = MsOrgan.filter(employee => employee.id !== id);
                    } else {
                        if (id == '0') {
                            MsOrgan = BaseMsOrgan
                        } else MsOrgan = [].concat(MsOrgan, BaseMsOrgan.filter(employee => employee.id === id))
                    }

                    break
                }

            }

        }

        function activateCloseDelete(){
            deleteButtonBool = false
            window.document.getElementById('sleepInfoDelete').style.display = 'none'
        }

        function deleteAccount(id, name) {
            deleteButtonBool = true
            window.document.getElementById('sleepInfoDelete').style.display = 'block'
            setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление данных организации  ' + name + ' через ' + 5, 1000)

            for (let i = 1; i < 7; i++) {
                let time = 7 - i
                setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление данных организации ' + name + ' через ' + time, i * 1000)
            }

            setTimeout(() => window.document.getElementById('sleepInfoDelete').style.display = 'none', 8 * 1000)
            setTimeout(() => onEmployee(true, 'createMain', id), 8 * 1000)
        }





        function createOptions(data, targetElement, is_employee = true) {
            data.forEach((item) => {
                let option = document.createElement("option");

                if (is_employee){
                    option.text = item.name
                    option.value = item.id
                } else {
                    option.text = item.employeeName
                    option.value = item.employeeId
                }


                targetElement.appendChild(option);
            });
        }
        function clearOption(selected) {
            while (selected.firstChild) selected.removeChild(selected.firstChild)
        }
    </script>

@endsection
