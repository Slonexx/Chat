<script>
    let status_arr = @json($arr_meta);
    let project_arr = @json($arr_project);
    let saleschannel_arr = @json($arr_saleschannel);
    let template = @json($template);

    let Saved = @json($SavedCreateToArray);


    let gridChild = {};
    let is_activity = {
        'template': [],
        'status': [],
    }
    let timeSaved = {};


    $(document).ready(function () {
        leadingSaved()
    });

    function leadingSaved() {
        if (Saved !== null) {
            Saved.forEach(function (item) {
                createScript(item.id)
                setTimeout(function() {
                    let template = $(`#template_${item.id}`)
                    let entity = $(`#entity_${item.id}`)
                    let status = $(`#status_${item.id}`)
                    let project = $(`#project_${item.id}`)
                    let saleschannel = $(`#saleschannel_${item.id}`)

                    entity.val(item.entity)
                    onChangeEntity(item.id)



                    template.val(item.template_uuid)
                    status.val(item.status)
                    project.val(item.project)
                    saleschannel.val(item.channel)
                    set_is_activity('template', window.document.getElementById('template_'+item.id))
                }, 500)
            })
        }
    }


    function createScript(uuid = generateUUID()) {
        notificationMessage.innerText = ""

        let template = `template_${uuid}`;
        let entity = `entity_${uuid}`;
        let status = `status_${uuid}`;
        let project = `project_${uuid}`;
        let saleschannel = `saleschannel_${uuid}`;

        let childElement = $(
            ` <div id="${uuid}" class="columns">` +
            createColumnElement(uuid, false, template, 'template') +
            createColumnElement(uuid, true, entity) +
            createColumnElement(uuid, false, status) +
            createColumnElement(uuid, false, saleschannel) +
            createColumnElement(uuid, false, project) +
            `<div class="column is-1 text-center"> <span onclick="deleteScript('${uuid}')" class="fas fa-times-circle" style="font-size: 30px; cursor: pointer"></span> </div>
            </div>`);

        $('#mainCreate').append(childElement);

        gridChild[uuid] = true;

        if (!createSelectOptionsALL(uuid)) {
            deleteScript(uuid)
            notificationMessage.innerText = "Нельзя добавить больше, добавьте статусы или шаблоны"
        }
    }


    function createColumnElement(i, onchange, namespace, name = null) {
        let options = '';
        let onchange_on = ``;
        if (name != null) onchange_on = `set_is_activity('${name}', this)`;

        if (onchange === true) {
            onchange_on = `onChangeEntity('${i}')`;
            options = '<option value="0">Заказ покупателя</option> <option value="1">Отгрузки</option> <option value="2">Возврат покупателя</option> <option value="3">Счет покупателю</option>';
        }
        return '<div class="column"> <div class="select w-100 is-small"> <select onchange="' + onchange_on + '" id="' + namespace + '" name="' + namespace + '" class="w-100"> ' + options + ' </select> </div> </div>';
    }

    function createSelectOptionsALL(i) {
        const sTemplate = document.getElementById('template_' + i);
        const sElement = document.getElementById('status_' + i);
        const eName = document.getElementById('entity_' + i);
        const sProject = document.getElementById('project_' + i);
        const sSalesChannel = document.getElementById('saleschannel_' + i);

        function createOptions(name, data, targetElement, is_external = true) {
            data.forEach((item) => {
                let targetElementBool = true

                let option1 = document.createElement("option");
                if (is_external) {
                    option1.text = item.name;
                    option1.value = item.id
                } else {
                    option1.text = item.title;
                    option1.value = item.uuid
                }

                if (name === 'template' || name === 'status') {
                    if (is_activity[name].includes(option1.value)) targetElementBool = false
                }

                if (targetElementBool) targetElement.appendChild(option1);
            });
        }

        function clearOption(selected) {
            while (selected.firstChild) selected.removeChild(selected.firstChild)
        }

        let value = eName.value;
        let params = eName.options[value].text;

        clearOption(sTemplate)
        clearOption(sElement)
        clearOption(sProject)
        clearOption(sSalesChannel)

        createOptions('template', template, sTemplate, false);
        createOptions('project', [{id: 0, name: "Не выбрано"}], sProject);
        createOptions('project', project_arr, sProject);
        createOptions('saleschannel', [{id: 0, name: "Не выбрано"}], sSalesChannel);
        createOptions('saleschannel', saleschannel_arr, sSalesChannel);


        const optionsMap = {
            'Заказ покупателя': status_arr.customerorder,
            'Отгрузки': status_arr.demand,
            'Возврат покупателя': status_arr.salesreturn,
            'Счет покупателю': status_arr.invoiceout
        };
        const selectedOption = optionsMap[params];
        if (selectedOption)
            if (selectedOption.length > 0) createOptions('status', selectedOption, sElement)
            else createOptions('status', [{id: 0, name: "Отсутствует статусы"}], sElement)


        if (select_is_option(sTemplate)) {
            set_is_activity('template', sTemplate)
            clearOptionsIsSaved()
        } else return false


        return true
    }


    function clearOptionsIsSaved() {
        if (gridChild !== {}) {
            for (let key in gridChild) {
                let template = window.document.getElementById('template_' + key)

                deleteOptions(template, 'template')
            }
        }


        function deleteOptions(selectElement, name) {
            for (let i = 0; i < selectElement.options.length; i++) {
                if (is_activity[name].includes(selectElement.options[i].value) && selectElement.value !== selectElement.options[i].value) selectElement.remove(i);
            }
        }
    }


    function onChangeEntity(uuid) {
        removeFromArray(is_activity['template'], window.document.getElementById('template_' + uuid).value);
        createSelectOptionsALL(uuid)
    }


    function set_is_activity(name, this_html, id) {
        let oldValue = this_html.getAttribute("data-previous-index");
        this_html.setAttribute("data-previous-index", this_html.value);

        if (oldValue !== null) removeFromArray(is_activity[name], oldValue)

        is_activity[name].push(this_html.value)
    }

    function select_is_option(selected) {
        if (selected.options.length > 0) return true;
        else return false;
    }

</script>

<script>
    function deleteScript(id) {
        isDeleteAjax(id)
            .then((is_deleted) => {
                if (is_deleted) {
                    let template = window.document.getElementById('template_' + id);
                    appendOptionsIsSaved(id, template.value);
                    removeFromArray(is_activity['template'], template.value);
                    delete gridChild[id];
                    window.document.getElementById(id).remove();
                }
            })
            .catch((error) => {
                console.log('error')
                console.log(error)
            });
    }

    function isDeleteAjax(id) {
        return new Promise((resolve, reject) => {

            if (Saved == null) resolve(true)
            if (Saved.length <= 0) resolve(true)

            Saved.forEach(function (item) {
                if (item.id == id) {
                    let settings = ajax_settings(url + 'Setting/scenario/' + accountId, "DELETE", item);
                    $.ajax(settings).done(function (json) {
                        console.log(json);
                        if (json.status) {
                            resolve(true);
                        } else {
                            reject(false);
                            notificationMessage.innerText = JSON.stringify(json.message);
                        }
                    });
                }
            });
            resolve(true)
        });
    }

    function appendOptionsIsSaved(uuid, template_value) {
        if (gridChild != {})
            for (let key in gridChild) {
                if (key !== uuid) window.document.getElementById('template_' + key).appendChild(isOption(template, template_value))
            }


        function isOption(name, value) {
            let option = document.createElement("option");
            name.forEach(function (item) {
                if (item.uuid == value) {
                    option.text = item.title
                    option.value = item.uuid
                    return option
                }
            });
            return option
        }
    }
</script>

<script>
    function removeFromArray(arr, value) {
        const index = arr.indexOf(value);
        if (index !== -1) {
            arr.splice(index, 1);
        }
    }

    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            let r = Math.random() * 16 | 0,
                v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
</script>
