@extends('layout')
@section('item', 'link_6')
@section('content')
    @include('setting.script_setting_app')

    <div class="mx-1 mt-3 py-3 p-4 bg-white rounded">
        <div class="row  gradient rounded p-2 pb-2 mt-1" style="margin-top: -1rem">
            <div class="col-6" style="margin-top: 0.25rem"><span class="text-black" style="font-size: 20px"> Настройки → Автоматизация </span>
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

        <form action="/Setting/automationSetting/{{$accountId}}?isAdmin={{$isAdmin}}" method="post" class="mt-2 ml-5 mr-5">
            @csrf <!-- {{ csrf_field() }} -->
            <div class="box mt-1 mb-4 columns p-0 gradient_layout_invert">
                <div onclick="createScript()" class="col-1 has-text-right" style="font-size: 30px; cursor: pointer">
                    <i class="fas fa-plus-circle"></i> &nbsp;
                </div>
            </div>

            <div class="box">

                <div class="mb-3 columns has-background-primary rounded text-white">
                    <div class="column"> Код группы (касса) </div>
                    <div class="column"> Тип документа</div>
                    <div class="column"> Статус</div>
                    <div class="column"> Тип оплаты</div>
                    <div class="column"> Канал продаж</div>
                    <div class="column"> Проект</div>
                    <div class="column is-1"> Удалить</div>
                </div>
                <div id="mainCreate">


                </div>

            </div>


            <button class="button is-outlined gradient_focus"> сохранить</button>
        </form>


        <form class="mt-3"
              action="/Setting/template/{{ $accountId }}?isAdmin={{ $isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}"
              method="post">
            @csrf <!-- {{ csrf_field() }} -->

            <div class="">

                <div class="row bg-info rounded text-white">
                    <div class="col-1 entity"> Тип сущности</div>
                    <div class="col"></div>
                    <div class="col-1 text-center status"> Статус</div>
                    <div class="col-1"></div>
                    <div class="col-1 text-center channel"> Канал продаж</div>
                    <div class="col-1"></div>
                    <div class="col-1 text-center project"> Проект</div>
                    <div class="col-1"></div>
                    <div class="col-1 text-center"> Шаблон</div>
                    <div class="col-1"></div>
                    <div class="col-1 text-center"> Удалить</div>
                </div>

                <div id="mainAutomation" class="mt-3"></div>
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
    </div>


    @include('setting.automatization.requests')
    @include('setting.automatization.fields')
    @include('setting.automatization.baseFunction')
    @include('setting.automatization.lite')
    @include('setting.automatization.update_lite')
    @include('setting.automatization.logic')
    

    <script>
        const baseURL = '{{  ( Config::get("Global") )['url'] }}'
        let accountId = '{{ $accountId }}'
        let savedAuto = @json($savedAuto);

        document.addEventListener('DOMContentLoaded', function () {
            savedAuto.forEach((item) => {

                $('#mainAutomation').append(
                    ' <div id="' + item.uuid + '" class="row"> ' +
                    ' <div class="col"> ' + item.entity + ' </div> ' +
                    ' <div class="col"></div> ' +
                    `<div class="col-1 text-center status"> ${item.status}</div>` +
                    '<div class="col-1"></div>' +
                    `<div class="col-1 text-center channel"> ${item.channel}</div>` +
                    '<div class="col-1"></div>' +
                    `<div class="col-1 text-center project"> ${item.project}</div>` +
                    '<div class="col-1"></div>' +
                    '<div class="col-1 text-center"> Шаблон</div>' +
                    '<div class="col-1"></div>' +
                    ' <div onclick="deleteTemplate(\'' + item.uuid + '\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                    ' </div> '
                )

            });

            const entityTypeSelect = document.getElementById('entity_type');
            const projectSelect = document.getElementById('project');
            const salesChannelSelect = document.getElementById('sales_channel');
            const templateSelect = document.getElementById('template');
            const statusesSelect = document.getElementById('status');
        })


        // let saveOrgan = {{--@json($saveOrgan); --}}
        // let saveTemplate = {{--@json($template);--}}
        // let fields = getFields();
        // let addFields = getAddFields();
        // let jsonMessage = {{--@json($message);--}}
        


        // if (jsonMessage !== '') {
        //     if (jsonMessage === 'Настройки сохранились') {
        //         alertViewByColorName("success", jsonMessage)
        //     } else {
        //         alertViewByColorName("danger", jsonMessage)
        //     }
        // }


        // if (saveTemplate.length > 0) {
        //     saveTemplate.forEach((item) => {

        //         $('#mainAutomation').append(
        //             ' <div id="' + item.uuid + '" class="row"> ' +
        //             ' <div class="col"> ' + item.title + ' </div> ' +
        //             ' <div class="col"></div> ' +
        //             ' <div  onclick="updateTemplate(\'' + item.uuid + '\')" class="col-1 btn gradient_focus"> Изменить <i class="fa-regular fa-circle-xmark"></i></div> ' +
        //             ' <div  class="col-1"> </div> ' +
        //             ' <div onclick="deleteTemplate(\'' + item.uuid + '\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div> ' +
        //             ' </div> '
        //         )

        //     });
        // }

        // //getAttributes()

        // function showHideCreate(val) {
        //     if (val === '1') {
        //         $('#createOrganization').modal('toggle')
        //         isLeading(false)
        //         messageEmployee.style.display = 'none'
        //         messageEmployee.innerText = ''
        //         nameTemplate.value = ''
        //         messageTextArea.value = ''
        //         //addPoles.click()
        //         //pole_1.value = '1'

        //         while (organizationSelect.firstChild) {
        //             organizationSelect.removeChild(organizationSelect.firstChild);
        //         }

        //         if (saveOrgan.length > 0) {
        //             saveOrgan.forEach((item) => {
        //                 let option1 = document.createElement("option");
        //                 option1.text = item.organName;
        //                 option1.value = item.organId;

        //                 // Проверяем, есть ли уже такая опция в селекте
        //                 let optionExists = false;
        //                 for (let i = 0; i < organizationSelect.options.length; i++) {
        //                     if (organizationSelect.options[i].value === option1.value || organizationSelect.options[i].text === option1.text) {
        //                         optionExists = true;
        //                         break;
        //                     }
        //                 }

        //                 if (!optionExists) {
        //                     organizationSelect.appendChild(option1);
        //                 }
        //             });
        //         } else {
        //             messageEmployee.style.display = 'block';
        //             messageEmployee.innerText = 'Отсутствует связи с "Организации и линии"';

        //             saveOrgan.forEach((item) => {
        //                 let option1 = document.createElement("option");
        //                 option1.text = "Все организации";
        //                 option1.value = "0";

        //                 // Проверяем, есть ли уже такая опция в селекте
        //                 let optionExists = false;
        //                 for (let i = 0; i < organizationSelect.options.length; i++) {
        //                     if (organizationSelect.options[i].value === option1.value || organizationSelect.options[i].text === option1.text) {
        //                         optionExists = true;
        //                         break;
        //                     }
        //                 }

        //                 if (!optionExists) {
        //                     organizationSelect.appendChild(option1);
        //                 }
        //             });
        //         }
        //         isLeading(true)
        //         let idCreatePole = fields.responseJSON.data;
        //         appendFields(idCreatePole, 'idCreatePole')
        //         let idCreateAddPole = addFields.responseJSON.data;
        //         appendAddFields(idCreateAddPole, 'idCreateAddPole')
        //     } else {
        //         $("#idCreatePole").empty()
        //         $('#createOrganization').modal('toggle')
        //     }
        // }

        // function showHideCreateUpdate(val) {
        //     if (val === '1') {
        //         $('#updateOrganization').modal('toggle')

        //         isLeading(false)

        //         idCreatePoleUpdate.innerText = ''
        //         idCreateAddPoleUpdate.innerText = ''

        //         while (organizationSelectUpdate.firstChild) organizationSelectUpdate.removeChild(organizationSelectUpdate.firstChild);

        //         if (saveOrgan.length > 0) {
        //             saveOrgan.forEach((item) => {
        //                 let option1 = document.createElement("option");
        //                 option1.text = item.organName;
        //                 option1.value = item.organId;

        //                 // Проверяем, есть ли уже такая опция в селекте
        //                 let optionExists = false;
        //                 for (let i = 0; i < organizationSelectUpdate.options.length; i++) {
        //                     if (organizationSelectUpdate.options[i].value === option1.value || organizationSelectUpdate.options[i].text === option1.text) {
        //                         optionExists = true;
        //                         break;
        //                     }
        //                 }

        //                 if (!optionExists) {
        //                     organizationSelectUpdate.appendChild(option1);
        //                 }
        //             });
        //         } else {
        //             messageEmployee.style.display = 'block';
        //             messageEmployee.innerText = 'Отсутствует связи с "Организации и линии"';

        //             saveOrgan.forEach((item) => {
        //                 let option1 = document.createElement("option");
        //                 option1.text = "Все организации";
        //                 option1.value = "0";

        //                 // Проверяем, есть ли уже такая опция в селекте
        //                 let optionExists = false;
        //                 for (let i = 0; i < organizationSelectUpdate.options.length; i++) {
        //                     if (organizationSelectUpdate.options[i].value === option1.value || organizationSelectUpdate.options[i].text === option1.text) {
        //                         optionExists = true;
        //                         break;
        //                     }
        //                 }

        //                 if (!optionExists) {
        //                     organizationSelectUpdate.appendChild(option1);
        //                 }
        //             });
        //         }
        //         let idCreatePole = fields.responseJSON.data;
        //         appendFields(idCreatePole, 'idCreatePoleUpdate')
        //         let idCreateAddPole = addFields.responseJSON.data;
        //         appendAddFields(idCreateAddPole, 'idCreateAddPoleUpdate')

                
        //         isLeading(true)
        //     } else {
        //         $('#updateOrganization').modal('toggle')
        //     }
        // }

        

    </script>

@endsection
