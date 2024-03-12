@extends('layout')
@section('item', 'link_4')
@section('content')
    @include('setting.script_setting_app')

    <div class="mx-1 mt-3 py-3 p-4 bg-white rounded">
        <div class="row  gradient rounded p-2 pb-2 mt-1" style="margin-top: -1rem">
            <div class="col-6" style="margin-top: 0.25rem"><span class="text-black" style="font-size: 20px"> Настройки → Дополнительные поля  </span>
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
                    <div class="col-6"> Название</div>
                    <div class="col"></div>
                    <div class="col-3 text-center"> Документ / Сущность</div>
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
            </div>

        </form>


        <div id="createAddFields" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
             aria-labelledby="createAddFields" aria-hidden="true">
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
                            <span>Создание доп. полей для шаблонов</span>
                        </div>

                        <button onclick="showHideCreate('2')" type="button"
                                class="close btn btn-outline-dark gradient_focus" data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">x</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="templateId">
                        <div id="messageAddField" class="alert alert-warning alert-primary fade show in text-center "
                             style="display: none"> Error
                        </div>

                        <div class="mt-2 row">
                            <div class="col-4">Название</div>
                            <input id="nameAddField" class="form-control col" type="text"
                                   placeholder="Придумайте название для доп. поля">
                        </div>
                        <div class="mt-2 row">
                            <div class="col-4">Доп. поле</div>
                            <select id="msAddFieldSelect" class="col form-select"> </select>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button id="create" onclick="createOnClick()" type="button"
                                class="col-2 btn btn-outline-dark gradient_focus">Сохранить
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('setting.add_fields.baseFunction')
    @include('setting.add_fields.lite')
    @include('setting.add_fields.update_lite')

    <script>
        const baseURL = '{{  ( Config::get("Global") )['url'] }}'
        let accountId = '{{ $accountId }}'

        let addFields = @json($addFieldsWithValues);
        let availableFields = @json($attributesWithoutFilled);
        
        const complianceList = {
            "demand": "отгрузка",
            "counterparty": "контрагент",
            "customerorder": "заказ покупателя",
            "invoiceout": "счёт покупателя",
            "salesreturn": "возврат покупателя",
        };

        const entriesFields = Object.entries(addFields);
        const associativeArray = [];

        for (const [entityType, addFields] of entriesFields) {
            for (let [name, uuid] of Object.entries(addFields)) {
                $('#main').append(
                ' <div id="' + uuid + '" class="row"> ' +
                ' <div class="col"> ' + name + ' </div> ' +
                ' <div class="col"></div> ' +
                ` <div class="col-3 text-center"> ${complianceList[entityType]} </div> ` +
                ' <div  class="col-1"> </div> ' +
                ' <div onclick="deleteTemplate(\'' + uuid + '\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                ' </div> '
                )
            }
        }

        function showHideCreate(val) {
            if (val === '1') {
                $('#createAddFields').modal('toggle')
                isLoading(false)
                messageAddField.style.display = 'none'
                messageAddField.innerText = ''
                nameAddField.value = ''
                
                while (msAddFieldSelect.firstChild) {
                    msAddFieldSelect.removeChild(msAddFieldSelect.firstChild);
                }
                
                for (const [entityType, addFields] of Object.entries(availableFields)) {
                    for (let field of addFields) {
                        let option = document.createElement("option");
                        option.id = field.id;
                        option.text = `${complianceList[entityType]} → ${field.name}`;
                        msAddFieldSelect.appendChild(option);
                    }
                }
                isLoading(true)
            } else {
                $('#createAddFields').modal('toggle')
            }
        }

        

    </script>

@endsection
