<script>
    function appendFields(fields, parentElement){

        for (let key in fields) {
            let item = fields[key]
            element = createField(key, item)
            parentE = $(`#${parentElement}`).append(element);
            appenedE = parentE[0].lastElementChild
            appenedE.addEventListener('click', (e) => {
                let targetDivId = e.currentTarget.id;
                let targetId = targetDivId.split('_').pop();
                navigator.clipboard.writeText(`{${targetId}}`)
                    .then(() => {
                        let button = document.getElementById(`dev_button_${targetId}`)
                        button.children[0].src = "{{  ( Config::get("Global") )['url'].'copied.svg' }}"
                    })
                    .catch(err => {
                        console.error('Не удалось скопировать текст: ', err);
                    });
            });
        }
    }

    function appendAddFields(addFields, parentElement){

        for (let [entityType, array] of Object.entries(addFields)) {
            for (let [userVar, MsName] of Object.entries(array)) {

                element = createAddField(MsName, userVar)
                parentE = $(`#${parentElement}`).append(element);
                appenedE = parentE[0].lastElementChild
                appenedE.addEventListener('click', (e) => {
                    let targetDivId = e.currentTarget.id;
                    let targetId = targetDivId.split('_').pop();
                    navigator.clipboard.writeText(`!{${targetId}}`)
                        .then(() => {
                            let button = document.getElementById(`dev_add_button_${targetId}`)
                            button.children[0].src = "{{  ( Config::get("Global") )['url'].'copied.svg' }}"
                        })
                        .catch(err => {
                            console.error('Не удалось скопировать текст: ', err);
                        });
                });
            }
            
        }
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
            '<div class="col-4">!{' + value + '}</div>' +
            '<div class="col-2">'+
                `<button class="btn btn-outline-secondary" id="dev_add_button_${value}">` +
                    '<img src="{{  ( Config::get("Global") )['url'].'copy.svg' }}" width="100%" height="100%" alt="">' +
                '</button>' +
            '</div>' +
        '</div>');
            
        return newElement
    }
</script>