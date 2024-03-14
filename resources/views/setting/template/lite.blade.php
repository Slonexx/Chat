<script>


    function createOnClick(){

        idCreatePoleUpdate.innerText = ''
        idCreateAddPoleUpdate.innerText = ''

        isLeading(false)
        let req = true

        let pole_ = document.getElementById('idCreatePole').querySelectorAll('[id^="pole_"]')
        let poles = {}
        for (let i = 0; i < pole_.length; i++) {
           poles[ parseInt((pole_[i].id).match(/\d+/)[0]) ] = pole_[i].value
        }

        let add_pole_ = document.getElementById('idCreateAddPole').querySelectorAll('[id^="add_pole_"]')
        let add_poles = {}
        for (let i = 0; i < add_pole_.length; i++) {
            add_poles[ parseInt((add_pole_[i].id).match(/\d+/)[0]) ] = add_pole_[i].value
        }

        if (nameTemplate.value === '') {
            messageEmployee.style.display = 'block'
            messageEmployee.innerText = 'Отсутствует название шаблона'
            req = false
        }


        if (messageTextArea.value === '') {
            messageEmployee.style.display = 'block'
            messageEmployee.innerText = 'Отсутствует сообщение'
            req = false
        }

        if (req) {
            let data = {
                name:  nameTemplate.value,
                organId: organizationSelect.value,
                idCreatePole: poles,
                idCreateAddPole: add_poles,
                message: messageTextArea.value
            };

            let settings = ajax_settings(baseURL + 'Setting/template/create/poles/' + accountId , "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(baseURL + 'Setting/template/create/poles/' + accountId   + ' response ↓ ')
                console.log(json)

                if (json.status) {

                    showHideCreate('2')

                    $('#main').append(
                        ' <div id="'+json.data.name_uid+'" class="row"> ' +
                            ' <div class="col-3"> '+nameTemplate.value+' </div> ' +
                            ' <div class="col"></div> ' +
                            ' <div onclick="updateTemplate(\''+json.data.uuid+'\')"  class="col-1 btn gradient_focus"> Изменить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                            ' <div  class="col-1"> </div> ' +
                            ' <div onclick="deleteAccount(\''+json.data.name_uid+'\' , \''+json.data.name+'\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                        ' </div> '
                    )



                    isLeading(true)
                }
                else {
                    messageEmployee.style.display = 'block'
                    messageEmployee.innerText = json.message
                    isLeading(true)
                }

            })


        }
        else isLeading(true)
    }

    function createOnClickUpdate(){
        isLeading(false)
        let req = true

        let pole_ = document.getElementById('idCreatePoleUpdate').querySelectorAll('[id^="pole_"]')
        let poles = {}
        for (let i = 0; i < pole_.length; i++) {
            poles[ parseInt((pole_[i].id).match(/\d+/)[0]) ] = pole_[i].value
        }

        let add_pole_ = document.getElementById('idCreateAddPoleUpdate').querySelectorAll('[id^="add_pole_"]')
        let add_poles = {}
        for (let i = 0; i < add_pole_.length; i++) {
            add_poles[ parseInt((add_pole_[i].id).match(/\d+/)[0]) ] = add_pole_[i].value
        }

        if (nameTemplateUpdate.value === '') {
            messageEmployee.style.display = 'block'
            messageEmployee.innerText = 'Отсутствует название шаблона'
            req = false
        }


        if (messageTextAreaUpdate.value === '') {
            messageEmployee.style.display = 'block'
            messageEmployee.innerText = 'Отсутствует сообщение'
            req = false
        }

        if (req) {
            let templateId = document.getElementById("templateId").value;
            let data = {
                uuid: templateId,
                name:  nameTemplateUpdate.value,
                // organId: organizationSelectUpdate.value,
                // idCreatePole: poles,
                // idCreateAddPole: add_poles,
                message: messageTextAreaUpdate.value
            };

            let settings = ajax_settings_with_json(baseURL + 'Setting/template/{accountId}' + accountId , "PUT", data);
            $.ajax(settings).done(function (json) {
                console.log(baseURL + 'Setting/template/' + accountId   + ' response ↓ ')
                console.log(json)

                if (json.status) {
                    showHideCreateUpdate('2')
                    isLeading(true)
                }
                else {
                    messageEmployee.style.display = 'block'
                    messageEmployee.innerText = json.message
                    isLeading(true)
                }

            })


        }




    }




    function fuCreatePole() {
        let poles = document.getElementById('idCreatePole').querySelectorAll('[id^="dev_pole_"]');
        let lastNumber = null;
        let parentElement = $('#idCreatePole');
        let creating = true;

        for (let i = 0; i < poles.length; i++) {
            let currentId = poles[i].id;
            let currentNumber = parseInt(currentId.match(/\d+/)[0]);
            if (lastNumber !== null && currentNumber - lastNumber > 1) {
                for (let j = lastNumber + 1; j < currentNumber && j <= 10; j++) { createElementForId(j, lastNumber); }
                creating = false;
                break;
            }
            lastNumber = currentNumber;
        }

        if (creating) {
            let nextNumber = lastNumber + 1;
            if (nextNumber <= 10) { createElementForId(nextNumber, lastNumber);}
            else {
                messageEmployee.style.display = 'block'
                messageEmployee.innerText = 'Ограничение по создание полей. На данный момент можно создать только 10 полей'
            }
        }
    }
    function fuCreateAddPole(){
        let poles = document.getElementById('idCreateAddPole').querySelectorAll('[id^="dev_add_pole_"]');
        let lastNumber = null;
        let parentElement = $('#idCreatePole');
        let creating = true;

        for (let i = 0; i < poles.length; i++) {
            let currentId = poles[i].id;
            let currentNumber = parseInt(currentId.match(/\d+/)[0]);

            if (lastNumber !== null && currentNumber - lastNumber > 1) {
                for (let j = lastNumber + 1; j < currentNumber && j <= 10; j++) {
                    createElementForIdAdd(j, lastNumber);
                }
                creating = false;
                break;
            }

            lastNumber = currentNumber;
        }

        if (creating) {
            let nextNumber = lastNumber + 1;
            if (nextNumber <= 10) {
                createElementForIdAdd(nextNumber, lastNumber);
            } else {
                messageEmployee.style.display = 'block'
                messageEmployee.innerText = 'Ограничение по создание доп полей. На данный момент можно создать только 10 доп полей'
            }
        }
    }



    function createElementForId(id, lastNumber) {
        let newElement = $('<div id="dev_pole_' + id + '" class="mt-2 row">' +
            '<div class="col-4">Выберите поле_' + id + '</div>' +
            '<select id="pole_' + id + '" class="col form-select">' +
            '<option value="12">Имя контрагента</option>' +
            '<option value="0">Название документа</option>' +
            '<option value="1">Организация (название)</option>' +
            '<option value="2">План отгрузки</option>' +
            '<option value="3">Канал продаж (название)</option>' +
            '<option value="4">Валюта (название)</option>' +
            '<option value="5">Склад (название)</option>' +
            '<option value="6">Договор (номер)</option>' +
            '<option value="7">Проект (название)</option>' +
            '<option value="8">Адрес доставки</option>' +
            '<option value="9">Комментарий</option>' +
            '<option value="10">Статус документа (название)</option>' +
            '<option value="11">Общая сумма товаров (Итого)</option>' +
            '</select>' +
            '<button onclick="deletePole(\'' + "dev_pole_" + id + '\')" type="button" class="col-1 btn btn-outline-dark gradient_focus"><i class="far fa-times-circle"></i></button>' +
            '</div>');

        if (lastNumber === null) { $('#idCreatePole').prepend(newElement); }
        else { $('#dev_pole_' + lastNumber).after(newElement); }
    }
    function createElementForIdAdd(id, lastNumber) {
        let newElement = $('<div id="dev_add_pole_' + id + '" class="mt-2 row">' +
            '<div class="col-4">Выберите доп_поле_' + id + '</div>' +
            '<select id="add_pole_' + id + '" class="col form-select">' +
            '</select>' +
            '<button onclick="deletePole(\'' + "dev_add_pole_" + id + '\')" type="button" class="col-1 btn btn-outline-dark gradient_focus"><i class="far fa-times-circle"></i></button>' +
            '</div>');

        if (lastNumber === null) { $('#idCreateAddPole').prepend(newElement); }
        else { $('#dev_add_pole_' + lastNumber).after(newElement); }
        createElementForAddSelect('add_pole_'+id)
    }



    function deletePole(id){ window.document.getElementById(id).remove() }

    function deleteTemplate(uuid){
        let settings = ajax_settings_with_json(baseURL + `Setting/template/${accountId}/${uuid}` , "DELETE");
        $.ajax(settings).done(function (json) {
                console.log(baseURL + `Setting/template/${accountId}/${uuid}`   + ' response ↓ ')
                console.log(json)

            })
        document.getElementById(uuid).remove() 
    }


    function idCreateAddPoleChecked(checked){
        if (checked) {
            idCreateAddPole.style.display = 'block'
        } else idCreateAddPole.style.display = 'none'
    }
    function idCreatePoleChecked(e, checked) {
        if (checked)
            $(`#${e}`).toggle()
        else 
            $(`#${e}`).hide()
    }



    function activateCloseDelete(){
        deleteButtonBool = false
        window.document.getElementById('sleepInfoDelete').style.display = 'none'
    }
    function deleteAccount(id, name) {
        deleteButtonBool = true
        window.document.getElementById('sleepInfoDelete').style.display = 'block'
        setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление: ' + name + ' через ' + 5, 1000)

        for (let i = 1; i < 7; i++) { let time = 7 - i
            setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление: ' + name + ' через ' + time, i * 1000)
        }

        setTimeout(() => window.document.getElementById('sleepInfoDelete').style.display = 'none', 8 * 1000)
        setTimeout(() => onDeleteApi(id, name), 8 * 1000)
    }
    function onDeleteApi(id, name) {
        isLeading(false)
        let data = {
            name_uid: id,
            name: name,
        }
        let settings = ajax_settings(baseURL + 'Setting/template/delete/poles/' + accountId , "GET", data)
        $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/delete/poles/' + accountId   + ' response ↓ ')
            console.log(json)

            if (json.status) {
                window.document.getElementById(id).remove()
            } else {
                messageEmployee.style.display = 'block'
                messageEmployee.innerText = json.message
            }
            isLeading(true)
        })
    }


    function isLeading(params) {
        if (params) {
            updateGifOrImageHide.style.display = ""
            updateImageOrGifHide.style.display = "none"

            GifOrImageHide.style.display = ""
            ImageOrGifHide.style.display = "none"
        } else {
            updateGifOrImageHide.style.display = "none"
            updateImageOrGifHide.style.display = ""

            GifOrImageHide.style.display = "none"
            ImageOrGifHide.style.display = ""
        }
    }
</script>
