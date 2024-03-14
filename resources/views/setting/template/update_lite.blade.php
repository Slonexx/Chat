<script>

    let counterparty
    let customerorder
    let demand
    let invoiceout
    let salesreturn

    function getAttributes(){
        isLeading(false);
        let settings = ajax_settings(baseURL + 'Setting/template/get/attributes/' + accountId, "GET", []);
        $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/get/attributes/' + accountId + ' response ↓ ');
            console.log(json);

            if (json.counterparty !== null) counterparty = json.counterparty.rows

            if (json.customerorder !== null) customerorder = json.customerorder.rows
            if (json.demand !== null) demand = json.demand.rows
            if (json.salesreturn !== null) salesreturn = json.salesreturn.rows

            if (json.invoiceout !== null) invoiceout = json.invoiceout.rows
            isLeading(true);
        });
    }

    function updateTemplate(name_uid){

        //idCreatePole.innerText = ''
        idCreateAddPole.innerText = ''

        isLeading(false);
        showHideCreateUpdate('1');


        let data = {
            'nameUID': name_uid
        };

        let settings = ajax_settings(baseURL + 'Setting/template/nameuid/poles/' + accountId, "GET", data);
        $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/nameuid/poles/' + accountId + ' response ↓ ');
            console.log(json);

            if (json.status) {
                let templateId = document.getElementById("templateId");
                templateId.value = name_uid;
                
                nameTemplateUpdate.value = json.data.title;
                //organizationSelectUpdate.value = json.data.organId;
                messageTextAreaUpdate.value = json.data.content;

                let idCreatePole = fields.responseJSON.data;
                for (let key in idCreatePole) {
                    let item = idCreatePole[key]
                    element = createAddField(key, item)
                    $("#idCreatePoleUpdate").append(element);
                }

                // let idCreateAddPoleUpdate = json.data.idCreateAddPole;
                // for (let key in idCreateAddPoleUpdate) {
                //     if (idCreateAddPoleUpdate.hasOwnProperty(key)) {
                //         let item = idCreateAddPoleUpdate[key];
                //         if (item.add_pole != null) {
                //             fuCreateAddPoleUpdate();
                //         }
                //     }
                // }

                // setTimeout(() => f(), 1000);

                // function f() {
                //     for (let key in idCreateAddPoleUpdate) {
                //         if (idCreateAddPoleUpdate.hasOwnProperty(key)) {
                //             let item = idCreateAddPoleUpdate[key];
                //             if (item.add_pole != null) {
                //                 window.document.getElementById('add_pole_'+key).value = item.add_pole;
                //             }
                //         }
                //     }
                // }
                isLeading(true);
            } else {
                messageEmployee.style.display = 'block';
                messageEmployee.innerText = json.message;
                isLeading(true);
            }

        });

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
                for (let j = lastNumber + 1; j < currentNumber && j <= 10; j++) {
                    createElementForIdUpdate(j, lastNumber);
                }
                creating = false;
                break;
            }
            lastNumber = currentNumber;
        }

        console.log(creating);

        if (creating) {
            let nextNumber = lastNumber + 1;
            if (nextNumber <= 10) {
                createElementForIdUpdate(nextNumber, lastNumber);
                console.log(nextNumber);
            } else {
                messageEmployee.style.display = 'block';
                messageEmployee.innerText = 'Ограничение по созданию полей. На данный момент можно создать только 10 полей';
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
                messageEmployee.style.display = 'block';
                messageEmployee.innerText = 'Ограничение по созданию дополнительных полей. На данный момент можно создать только 10 дополнительных полей';
            }
        }
    }
    
    
    function activateCloseDelete(){
        deleteButtonBool = false;
        window.document.getElementById('sleepInfoDelete').style.display = 'none';
    }
    function deleteAccount(id, name) {
        deleteButtonBool = true;
        window.document.getElementById('sleepInfoDelete').style.display = 'block';
        setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление: ' + name + ' через ' + 5, 1000);
        
        for (let i = 1; i < 7; i++) {
            let time = 7 - i;
            setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление: ' + name + ' через ' + time, i * 1000);
        }
        
        setTimeout(() => window.document.getElementById('sleepInfoDelete').style.display = 'none', 8 * 1000);
        setTimeout(() => onDeleteApi(id, name), 8 * 1000);
    }
    function onDeleteApi(id, name) {
        let data = {
            name_uid: id,
            name: name
        };
        let settings = ajax_settings(baseURL + 'Setting/template/delete/poles/' + accountId, "GET", data);
        $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/delete/poles/' + accountId + ' response ↓ ');
            console.log(json);
            
            if (json.status) {
                window.document.getElementById(id).remove();
            } else {
                messageEmployee.style.display = 'block';
                messageEmployee.innerText = json.message;
            }
        });
    }

    function createField(key, value) {
        let newElement = $('<div id="dev_pole_' + value + '" class="mt-2 row">' +
        '<div class="col-6">' + key + '</div>' +
            '<div class="col-4">{' + value + '}</div>' +
            '<div class="col-2">'+
                `<button class="btn btn-outline-secondary" id="dev_button_${value}">` +
                    '<img src="{{  ( Config::get("Global") )['url'].'copy.svg' }}" width="100%" height="100%" alt="">' +
                '</button>' +
            '</div>' +
        '</div>');
            
        return newElement
    }

    function createAddField(key, value) {
        let newElement = $('<div id="dev_add_pole_' + value + '" class="mt-2 row">' +
        '<div class="col-6">' + key + '</div>' +
            '<div class="col-4">{' + value + '}</div>' +
            '<div class="col-2">'+
                `<button class="btn btn-outline-secondary" id="dev_add_${value}">` +
                    '<img src="{{  ( Config::get("Global") )['url'].'copy.svg' }}" width="100%" height="100%" alt="">' +
                '</button>' +
            '</div>' +
        '</div>');
            
        return newElement
    }
    

</script>
