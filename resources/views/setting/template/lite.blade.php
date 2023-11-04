<script>


    function createOnClick(){
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
                            ' <div onclick="updateTemplate(\''+json.data.name_uid+'\')"  class="col-1 btn gradient_focus"> Изменить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                            ' <div  class="col-1"> </div> ' +
                            ' <div onclick="deleteAccount(\''+json.data.name_uid+'\' , \''+json.data.name+'\')"  class="col-1 btn gradient_focus"> Удалить <i class="fa-regular fa-circle-xmark"></i></div> ' +
                        ' </div> '
                    )

                }
                else {
                    messageEmployee.style.display = 'block'
                    messageEmployee.innerText = json.message
                }

            })


        }




    }

    function createOnClickUpdate(){
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
            let data = {
                name:  nameTemplateUpdate.value,
                organId: organizationSelectUpdate.value,
                idCreatePole: poles,
                idCreateAddPole: add_poles,
                message: messageTextAreaUpdate.value
            };

            let settings = ajax_settings(baseURL + 'Setting/template/create/poles/' + accountId , "GET", data);
            $.ajax(settings).done(function (json) {
                console.log(baseURL + 'Setting/template/create/poles/' + accountId   + ' response ↓ ')
                console.log(json)

                if (json.status) { showHideCreateUpdate('2') }
                else {
                    messageEmployee.style.display = 'block'
                    messageEmployee.innerText = json.message
                }

            })


        }




    }
    function updateTemplate(name_uid){
        showHideCreateUpdate('1')


        let data = {
            'nameUID':name_uid
        }

        let settings = ajax_settings(baseURL + 'Setting/template/nameuid/poles/' + accountId , "GET", data);
        $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/nameuid/poles/' + accountId   + ' response ↓ ')
            console.log(json)

            if (json.status) {
                nameTemplateUpdate.value = json.data.name
                organizationSelectUpdate.value = json.data.organId
                messageTextAreaUpdate.value = json.data.message



                let idCreatePole = (json.data.idCreatePole);
                for (let key in idCreatePole) {
                    if (idCreatePole.hasOwnProperty(key)) { let item = idCreatePole[key];
                        if (item.pole != null) { fuCreatePoleUpdate(); window.document.getElementById('pole_'+key).value = item.pole }
                    }
                }

                let idCreateAddPoleUpdate = (json.data.idCreateAddPole);
                for (let key in idCreateAddPoleUpdate) {
                    if (idCreateAddPoleUpdate.hasOwnProperty(key)) { let item = idCreateAddPoleUpdate[key];
                        if (item.add_pole != null) { fuCreateAddPoleUpdate();  }
                    }
                }


                setTimeout(() => f(), 5000)

                function f() {
                    for (let key in idCreateAddPoleUpdate) {
                        if (idCreateAddPoleUpdate.hasOwnProperty(key)) { let item = idCreateAddPoleUpdate[key];
                            if (item.add_pole != null) { window.document.getElementById('add_pole_'+key).value = item.add_pole  }
                        }
                    }
                }

            }
            else {
                messageEmployee.style.display = 'block'
                messageEmployee.innerText = json.message
            }

        })

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

    function fuCreatePoleUpdate() {
        let poles = document.getElementById('idCreatePoleUpdate').querySelectorAll('[id^="dev_pole_"]');
        let lastNumber = null;
        let parentElement = $('#idCreatePoleUpdate');
        let creating = true;

        for (let i = 0; i < poles.length; i++) {
            let currentId = poles[i].id;
            let currentNumber = parseInt(currentId.match(/\d+/)[0]);
            if (lastNumber !== null && currentNumber - lastNumber > 1) {
                for (let j = lastNumber + 1; j < currentNumber && j <= 10; j++) { createElementForIdUpdate(j, lastNumber); }
                creating = false;
                break;
            }
            lastNumber = currentNumber;
        }

        if (creating) {
            let nextNumber = lastNumber + 1;
            if (nextNumber <= 10) { createElementForIdUpdate(nextNumber, lastNumber);}
            else {
                messageEmployee.style.display = 'block'
                messageEmployee.innerText = 'Ограничение по создание полей. На данный момент можно создать только 10 полей'
            }
        }
    }
    function fuCreateAddPoleUpdate(){
        let poles = document.getElementById('idCreateAddPoleUpdate').querySelectorAll('[id^="dev_add_pole_"]');
        let lastNumber = null;
        let parentElement = $('#idCreateAddPoleUpdate');
        let creating = true;

        for (let i = 0; i < poles.length; i++) {
            let currentId = poles[i].id;
            let currentNumber = parseInt(currentId.match(/\d+/)[0]);

            if (lastNumber !== null && currentNumber - lastNumber > 1) {
                for (let j = lastNumber + 1; j < currentNumber && j <= 10; j++) {
                    createElementForIdAddUpdate(j, lastNumber);
                }
                creating = false;
                break;
            }

            lastNumber = currentNumber;
        }

        if (creating) {
            let nextNumber = lastNumber + 1;
            if (nextNumber <= 10) {
                createElementForIdAddUpdate(nextNumber, lastNumber);
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

    function createElementForIdUpdate(id, lastNumber) {
        let newElement = $('<div id="dev_pole_' + id + '" class="mt-2 row">' +
            '<div class="col-4">Выберите поле_' + id + '</div>' +
                '<select id="pole_' + id + '" class="col form-select">' +
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

        if (lastNumber === null) { $('#idCreatePoleUpdate').prepend(newElement); }
        else { $('#dev_pole_' + lastNumber).after(newElement); }
    }
    function createElementForIdAddUpdate(id, lastNumber) {
        let newElement = $('<div id="dev_add_pole_' + id + '" class="mt-2 row">' +
            '<div class="col-4">Выберите доп_поле_' + id + '</div>' +
            '<select id="add_pole_' + id + '" class="col form-select">' +
            '</select>' +
            '<button onclick="deletePole(\'' + "dev_add_pole_" + id + '\')" type="button" class="col-1 btn btn-outline-dark gradient_focus"><i class="far fa-times-circle"></i></button>' +
            '</div>');

        if (lastNumber === null) { $('#idCreateAddPoleUpdate').prepend(newElement); }
        else { $('#dev_add_pole_' + lastNumber).after(newElement); }
        createElementForAddSelectUpdate('add_pole_'+id)
    }


    function createElementForAddSelect(id) {
        let settings = ajax_settings(baseURL + 'Setting/template/get/attributes/' + accountId , "GET", []);
        $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/get/attributes/' + accountId   + ' response ↓ ')
            console.log(json)

            if (!idCreateAddPoleInput.checked) { idCreateAddPoleInput.checked = true; idCreateAddPoleChecked(idCreateAddPoleInput.checked) }

            let select = window.document.getElementById(id)
            while (select.firstChild) { select.removeChild(select.firstChild) }

            if (json.counterparty !== null) {

                (json.counterparty.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Контрагент)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.customerorder !== null) {

                (json.customerorder.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Заказ покупателя)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.demand !== null) {

                (json.demand.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Отгрузка)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.invoiceout !== null) {

                (json.invoiceout.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Счет покупателя)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.salesreturn !== null) {

                (json.salesreturn.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Возврат покупателя)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
        })
    }
    function createElementForAddSelectUpdate(id) {
        let settings = ajax_settings(baseURL + 'Setting/template/get/attributes/' + accountId , "GET", []);
        $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/get/attributes/' + accountId   + ' response ↓ ')
            console.log(json)

            if (!idCreateAddPoleInputUpdate.checked) { idCreateAddPoleInputUpdate.checked = true; idCreateAddPoleCheckedUpdate(idCreateAddPoleInputUpdate.checked) }

            let select = window.document.getElementById(id)
            while (select.firstChild) { select.removeChild(select.firstChild) }

            if (json.counterparty !== null) {

                (json.counterparty.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Контрагент)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.customerorder !== null) {

                (json.customerorder.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Заказ покупателя)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.demand !== null) {

                (json.demand.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Отгрузка)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.invoiceout !== null) {

                (json.invoiceout.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Счет покупателя)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
            if (json.salesreturn !== null) {

                (json.salesreturn.rows).forEach((item) => {
                    let option1 = document.createElement("option")
                    option1.text = item.name + '(Возврат покупателя)'
                    option1.value = item.id
                    select.appendChild(option1)
                });



            }
        })
    }



    function deletePole(id){ window.document.getElementById(id).remove() }


    function idCreateAddPoleChecked(checked){
        if (checked) {
            idCreateAddPole.style.display = 'block'
        } else idCreateAddPole.style.display = 'none'
    }
    function idCreatePoleChecked(checked) {
        if (checked) {
            idCreatePole.style.display = 'block'
        } else idCreatePole.style.display = 'none'
    }
    function idCreatePoleCheckedUpdate(checked) {
        if (checked) {
            idCreatePoleUpdate.style.display = 'block'
        } else idCreatePoleUpdate.style.display = 'none'
    }
    function idCreateAddPoleCheckedUpdate(checked){
        if (checked) {
            idCreateAddPoleUpdate.style.display = 'block'
        } else idCreateAddPoleUpdate.style.display = 'none'
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

        })
    }

</script>
