@extends('layout')
@section('item', 'link_4')
@section('content')
    @include('setting.script_setting_app')

    <div class="mx-1 mt-3 py-3 p-4 bg-white rounded">
        <div class="row  gradient rounded p-2 pb-2 mt-1" style="margin-top: -1rem">
            <div class="col-6" style="margin-top: 0.25rem"><span class="text-black" style="font-size: 20px"> Настройки → Шаблон сообщений  </span>
            </div>
            <div class="col-3 d-flex justify-content-end ">
            </div>
            <div class="col-3 text-right"><img src="{{  ( Config::get("Global") )['url'].'2logoHead.png' }}"
                                               width="100%" alt=""></div>
        </div>

        @include('div.alert')
        <div id="sleepInfoDelete" class="mt-2 alert alert-info fade show in text-center text-black "
             style="display: none">
            <div class="row">
                <div class="col-10 mt-1" id="messageInfoDelete"></div>

                <div class='col d-flex justify-content-end text-black btnP' style="font-size: 14px">
                    <button onclick="activateCloseDelete()" class="btn  gradient_focus"> отмена</button>
                </div>
            </div>

        </div>


        <form class="mt-3"
              action="/Setting/template/{{ $accountId }}?isAdmin={{ $isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}"
              method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div class="">

                <div class="row bg-info rounded text-white">
                    <div class="col-3"> Название</div>
                    <div class="col"></div>
                    <div class="col-1 text-center"> Изменить</div>
                    <div class="col-1"></div>
                    <div class="col-1 text-center"> Удалить</div>
                </div>

                <div id="main" class="mt-3"></div>
            </div>


            <hr>


            <div class="row">
                <div class="col">
                    <div onclick="showHideCreate('1')" class="btn btn-outline-dark gradient_focus"> Добавить
                    </div>
                </div>
                <!-- <div class="col">
                    <div class='d-flex justify-content-end text-black btnP'>
                        <button class="btn btn-outline-dark gradient_focus"> Сохранить</button>
                    </div>
                </div> -->
            </div>

        </form>


        <div id="createOrganization" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
             aria-labelledby="createOrganization" aria-hidden="true">
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
                            <span>Создание шаблона сообщений</span>
                        </div>

                        <button onclick="showHideCreate('2')" type="button"
                                class="close btn btn-outline-dark gradient_focus" data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="templateId">
                        <div id="messageEmployee" class="alert alert-warning alert-primary fade show in text-center "
                             style="display: none"> Error
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Название</div>
                            <input id="nameTemplate" class="form-control col" type="text"
                                   placeholder="Придумайте название для шаблона">
                        </div>
                        <div class="mt-2 row">
                            <div class="col-4">Организация</div>
                            <select id="organizationSelect" class="col form-select"> </select>
                        </div>
                        <hr>

                        <div class="rounded row gradient">
                            <div class="col-11 mt-1"> Основные поля</div>
                            <div class="col form-check form-switch">
                                <input onchange="idCreatePoleChecked(this.checked)" class="mt-2 form-check-input"
                                       type="checkbox" checked>
                            </div>
                        </div>

                        <div class="mt-1 rounded row gradient">
                            <div class="col-11 mt-1"> Дополнительные поля</div>
                            <div class="col form-check form-switch">
                                <input id="idCreateAddPoleInput" onchange="idCreateAddPoleChecked(this.checked)"
                                       class="mt-2 form-check-input" type="checkbox">
                            </div>
                        </div>
                        <div id="idCreateAddPole" class="mt-2" style="display: none"></div>

                        <hr>
                        <textarea id="messageTextArea" class="form-control" rows="3"
                                  placeholder="Пример: 'Здравствуйте, это компания поле_1, хотите сделать еще заказ ?'"></textarea>


                    </div>

                    <div class="modal-footer">
                        <button id="addPoles" onclick="" type="button"
                                class="col-3 btn btn-outline-dark gradient_focus">Добавить поле
                        </button>
                        <button onclick="fuCreateAddPole()" type="button"
                                class="col-3 btn btn-outline-dark gradient_focus">Добавить доп поле
                        </button>
                        <div class="col"></div>
                        <button id="btn_createOnClick" onclick="createOnClick()" type="button"
                                class="col-2 btn btn-outline-dark gradient_focus">Сохранить
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <div id="updateOrganization" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
             aria-labelledby="updateOrganization" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <div class="modal-title" style="font-size: 16px">
                            <span id="GifAndImage">
                                <img id="updateGifOrImageHide"
                                     src="{{  ( Config::get("Global") )['url'].'client.svg' }}"
                                     width="15%" alt="">
                                <img id="updateImageOrGifHide"
                                     src="{{  ( Config::get("Global") )['url'].'loading.gif' }}"
                                     width="15%" alt="" style="display: none">
                            </span>
                            <span>Изменения шаблона</span>
                        </div>

                        <button onclick="showHideCreateUpdate('2')" type="button"
                                class="close btn btn-outline-dark gradient_focus" data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div id="messageEmployeeUpdate"
                             class="alert alert-warning alert-primary fade show in text-center " style="display: none">
                            Error
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Название</div>
                            <input id="nameTemplateUpdate" class="form-control col" type="text"
                                   placeholder="Придумайте название для шаблона" disabled>
                        </div>
                        <div class="mt-2 row">
                            <div class="col-4">Организация</div>
                            <select id="organizationSelectUpdate" class="col form-select"> </select>
                        </div>
                        <hr>

                        <div class="rounded row gradient">
                            <div class="col-11 mt-1"> Основные поля</div>
                            <div class="col form-check form-switch">
                                <input id="idCreatePoleInputUpdate" onchange="idCreatePoleChecked(this.checked)"
                                       class="mt-2 form-check-input" type="checkbox" checked>
                            </div>
                        </div>
                        <div id="idCreatePoleUpdate" class="mt-2" style="display: block"></div>

                        <div class="mt-1 rounded row gradient">
                            <div class="col-11 mt-1"> Дополнительные поля</div>
                            <div class="col form-check form-switch">
                                <input id="idCreateAddPoleInputUpdate" onchange="idCreateAddPoleChecked(this.checked)"
                                       class="mt-2 form-check-input" type="checkbox">
                            </div>
                        </div>
                        <div id="idCreateAddPoleUpdate" class="mt-2" style="display: none"></div>

                        <hr>
                        <textarea id="messageTextAreaUpdate" class="form-control" rows="3"
                                  placeholder="Пример: 'Здравствуйте, это компания поле_1, хотите сделать еще заказ ?'"></textarea>


                    </div>

                    <div class="modal-footer">
                        <button id="addPolesUpdate" onclick="fuCreatePoleUpdate()" type="button"
                                class="col-3 btn btn-outline-dark gradient_focus">Добавить поле
                        </button>
                        <button onclick="fuCreateAddPoleUpdate()" type="button"
                                class="col-3 btn btn-outline-dark gradient_focus">Добавить доп поле
                        </button>
                        <div class="col"></div>
                        <button id="btn_createOnClickUpdate" onclick="createOnClickUpdate()" type="button"
                                class="col-2 btn btn-outline-dark gradient_focus">Изменить
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('setting.template.baseFunction')
    @include('setting.template.lite')
    @include('setting.template.update_lite')

    <script>
        const baseURL = '{{  ( Config::get("Global") )['url'] }}'
        let accountId = '{{ $accountId }}'
        let saveOrgan = @json($saveOrgan);
        let saveTemplate = @json($template);
        let fields = getFields();
        let jsonMessage = @json($message);


        if (jsonMessage !== '') {
            if (jsonMessage === 'Настройки сохранились') {
                alertViewByColorName("success", jsonMessage)
            } else {
                alertViewByColorName("danger", jsonMessage)
            }
        }


        if (saveTemplate.length > 0) {
            saveTemplate.forEach((item) => {

                $('#main').append(
                    ' <div id="' + item.uuid + '" class="row"> ' +
                    ' <div class="col"> ' + item.title + ' </div> ' +
                    ' <div class="col"></div> ' +
                    ' <div  onclick="updateTemplate(\'' + item.uuid + '\')" class="col-1 btn gradient_focus"> Изменить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                    ' <div  class="col-1"> </div> ' +
                    ' <div onclick="deleteAccount(\'' + item.uuid + '\' , \'' + item.title + '\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                    ' </div> '
                )

            });
        }

        getAttributes()

        function showHideCreate(val) {
            if (val === '1') {
                $('#createOrganization').modal('toggle')
                isLeading(false)
                messageEmployee.style.display = 'none'
                messageEmployee.innerText = ''
                nameTemplate.value = ''
                //idCreatePole.innerText = ''
                idCreateAddPole.innerText = ''
                messageTextArea.value = ''
                addPoles.click()
                //pole_1.value = '1'

                while (organizationSelect.firstChild) {
                    organizationSelect.removeChild(organizationSelect.firstChild);
                }

                if (saveOrgan.length > 0) {
                    saveOrgan.forEach((item) => {
                        let option1 = document.createElement("option");
                        option1.text = item.organName;
                        option1.value = item.organId;

                        // Проверяем, есть ли уже такая опция в селекте
                        let optionExists = false;
                        for (let i = 0; i < organizationSelect.options.length; i++) {
                            if (organizationSelect.options[i].value === option1.value || organizationSelect.options[i].text === option1.text) {
                                optionExists = true;
                                break;
                            }
                        }

                        if (!optionExists) {
                            organizationSelect.appendChild(option1);
                        }
                    });
                } else {
                    messageEmployee.style.display = 'block';
                    messageEmployee.innerText = 'Отсутствует связи с "Организации и линии"';

                    saveOrgan.forEach((item) => {
                        let option1 = document.createElement("option");
                        option1.text = "Все организации";
                        option1.value = "0";

                        // Проверяем, есть ли уже такая опция в селекте
                        let optionExists = false;
                        for (let i = 0; i < organizationSelect.options.length; i++) {
                            if (organizationSelect.options[i].value === option1.value || organizationSelect.options[i].text === option1.text) {
                                optionExists = true;
                                break;
                            }
                        }

                        if (!optionExists) {
                            organizationSelect.appendChild(option1);
                        }
                    });
                }
                isLeading(true)
            } else {
                $('#createOrganization').modal('toggle')
            }
        }

        function showHideCreateUpdate(val) {
            if (val === '1') {
                $('#updateOrganization').modal('toggle')

                isLeading(false)

                idCreatePoleUpdate.innerText = ''
                idCreateAddPoleUpdate.innerText = ''

                while (organizationSelectUpdate.firstChild) organizationSelectUpdate.removeChild(organizationSelectUpdate.firstChild);

                if (saveOrgan.length > 0) {
                    saveOrgan.forEach((item) => {
                        let option1 = document.createElement("option");
                        option1.text = item.organName;
                        option1.value = item.organId;

                        // Проверяем, есть ли уже такая опция в селекте
                        let optionExists = false;
                        for (let i = 0; i < organizationSelectUpdate.options.length; i++) {
                            if (organizationSelectUpdate.options[i].value === option1.value || organizationSelectUpdate.options[i].text === option1.text) {
                                optionExists = true;
                                break;
                            }
                        }

                        if (!optionExists) {
                            organizationSelectUpdate.appendChild(option1);
                        }
                    });
                } else {
                    messageEmployee.style.display = 'block';
                    messageEmployee.innerText = 'Отсутствует связи с "Организации и линии"';

                    saveOrgan.forEach((item) => {
                        let option1 = document.createElement("option");
                        option1.text = "Все организации";
                        option1.value = "0";

                        // Проверяем, есть ли уже такая опция в селекте
                        let optionExists = false;
                        for (let i = 0; i < organizationSelectUpdate.options.length; i++) {
                            if (organizationSelectUpdate.options[i].value === option1.value || organizationSelectUpdate.options[i].text === option1.text) {
                                optionExists = true;
                                break;
                            }
                        }

                        if (!optionExists) {
                            organizationSelectUpdate.appendChild(option1);
                        }
                    });
                }
                isLeading(true)
            } else {
                $('#updateOrganization').modal('toggle')
            }
        }

        

    </script>

@endsection
