<script>
    let status_arr = @json($arr_meta);
    let project_arr = @json($arr_project);
    let saleschannel_arr = @json($arr_saleschannel);
    let template = @json($template);

    let Saved = @json($SavedCreateToArray);


    let gridChild = {};
    let is_activity = {
        'template' : [],
        'status' : [],
    }
    let timeSaved = {

    };


    $(document).ready(function () { leadingSaved() });

    function leadingSaved(){
        if (Saved !== null) {
            Saved.forEach(function (item){
                createScript(item.id)

                let template = window.document.getElementById('template_'+item.id)
                let entity = window.document.getElementById('entity_'+item.id)
                let status = window.document.getElementById('status_'+item.id)
                let project = $(`#project_${item.id}`)
                let saleschannel = $(`#saleschannel_${item.id}`)

                template.value = item.template_uuid;
                entity.value = item.entity; onChangeEntity(item.id)
                status.value = item.status;

                project.val(item.project);
                saleschannel.val(item.channel);


                set_is_activity('status', status)
                set_is_activity('template', template)
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
            createColumnElement(uuid, true, entity,) +
            createColumnElement(uuid, false, status, 'status') +
            createColumnElement(uuid, false, saleschannel,) +
            createColumnElement(uuid, false, project ) +
            `<div class="column is-1 text-center"> <span onclick="deleteScript('${uuid}')" class="fas fa-times-circle" style="font-size: 30px; cursor: pointer"></span> </div>` +
            '</div>'
        );

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
        if (name!= null) onchange_on = `set_is_activity('${name}', this)`;

        if (onchange == true) { onchange_on =  `onChangeEntity('${i}')`;
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
                if (is_external){
                    option1.text = item.name;
                    option1.value = item.id
                } else {
                    option1.text = item.title;
                    option1.value = item.uuid
                }

                if (name === 'template' || name === 'status') {
                    if (is_activity[name].includes( option1.value )) targetElementBool = false
                }

                if (targetElementBool) targetElement.appendChild(option1);
            });
        }
        function clearOption(selected){ while (selected.firstChild) selected.removeChild(selected.firstChild) }

        let value = eName.value;
        let params = eName.options[value].text;

        clearOption(sTemplate)
        clearOption(sElement)
        clearOption(sProject)
        clearOption(sSalesChannel)

        createOptions('template', template, sTemplate, false);
        createOptions('project', [{id:0, name:"Не выбрано"}], sProject);
        createOptions('project', project_arr, sProject);
        createOptions('saleschannel', [{id:0, name:"Не выбрано"}], sSalesChannel);
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
            else createOptions('status', [{id:0, name:"Отсутствует статусы"}], sElement)

        if (select_is_option(sElement) && select_is_option(sTemplate)) {
            set_is_activity('status', sElement)
            set_is_activity('template', sTemplate)
            clearOptionsIsSaved()
        } else return false


        return true
    }

</script>
