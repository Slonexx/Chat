<script>


    function createAddField(){

        isLoading(false)
        let req = true

        if (nameAddField.value === '') {
            messageAddField.style.display = 'block'
            messageAddField.innerText = 'Отсутствует название доп.поля'
            req = false
        }

        if (req) {
            allOptions = msAddFieldSelect.options
            index = msAddFieldSelect.selectedIndex
            option = allOptions[index]
            entityTypeFromSelectElement = option.getAttribute('data-value')
            let data = {
                name:  nameAddField.value,
                entityType: entityTypeFromSelectElement,
                uuid: option.id,
            };

            let settings = ajax_settings_with_json(baseURL + `Setting/addFields/${accountId}`, "POST", data);
            $.ajax(settings).done(function (json, code, resObj) {
                console.log(baseURL + `/Setting/addFields/${accountId} response ↓ `)
                console.log(json)

                if (resObj.status == 200) {

                    showHideCreate('2')
                    let callbackData = json.data
                    $('#main').append(
                        ' <div id="' + callbackData.msUuid + '" class="row"> ' +
                        ' <div class="col"> ' + data.name + ' </div> ' +
                        ' <div class="col"></div> ' +
                        ` <div class="col-3 text-center"> ${complianceList[entityTypeFromSelectElement]} </div> ` +
                        ' <div  class="col-1"> </div> ' +
                        ' <div onclick="deleteAddField(\'' + callbackData.msUuid + '\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                        ' </div> '
                    )



                    isLoading(true)
                }
                else {
                    messageAddField.style.display = 'block'
                    messageAddField.innerText = json.message
                    isLoading(true)
                }

            }).fail(function (res) {
                if(res.status == 400)
                messageAddField.style.display = 'block'
                messageAddField.innerText = res.responseJSON.message
                isLoading(true)
            })


        }
        else isLoading(true)
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



    function createElementForAddSelect(id) {
        isLeading(false)
        if (!idCreateAddPoleInput.checked) { idCreateAddPoleInput.checked = true; idCreateAddPoleChecked(idCreateAddPoleInput.checked) }

        let select = window.document.getElementById(id)
        while (select.firstChild) { select.removeChild(select.firstChild) }


        if (counterparty !== null) counterparty.forEach((item) => {
            let option1 = document.createElement("option")
            option1.text = item.name + '(Контрагент)'
            option1.value = item.id
            select.appendChild(option1)
        });

        if (customerorder !== null) customerorder.forEach((item) => {
            let option1 = document.createElement("option")
            option1.text = item.name + '(Заказ покупателя)'
            option1.value = item.id
            select.appendChild(option1)
        });
        if (demand !== null) demand.forEach((item) => {
            let option1 = document.createElement("option")
            option1.text = item.name + '(Отгрузка)'
            option1.value = item.id
            select.appendChild(option1)
        });
        if (salesreturn !== null) salesreturn.forEach((item) => {
            let option1 = document.createElement("option")
            option1.text = item.name + '(Возврат покупателя)'
            option1.value = item.id
            select.appendChild(option1)
        });


        if (invoiceout !== null) invoiceout.forEach((item) => {
            let option1 = document.createElement("option")
            option1.text = item.name + '(Счет покупателя)'
            option1.value = item.id
            select.appendChild(option1)
        });
        isLeading(true)
    }



    function deletePole(id){ window.document.getElementById(id).remove() }

    function deleteAddField(uuid){
        let settings = ajax_settings_with_json(baseURL + `Setting/addFields/${accountId}/${uuid}` , "DELETE");
        $.ajax(settings)
            .done(function (json) {
                console.log(baseURL + `Setting/addFields/${accountId}/${uuid}`   + ' response ↓ ')
                console.log(json)

            })
            .fail(function (res) {
                if(res.status == 400)
                messageAddField.style.display = 'block'
                messageAddField.innerText = res.responseJSON.message
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


    function isLoading(params) {
        if (params) {
            GifOrImageHide.style.display = ""
            ImageOrGifHide.style.display = "none"
        } else {

            GifOrImageHide.style.display = "none"
            ImageOrGifHide.style.display = ""
        }
    }
</script>
