<div id="html" class="box">
    <form action="/Setting/lid/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}"
          method="post">
        @csrf <!-- {{ csrf_field() }} -->
        <div class="notification is-info is-light">
            <div class="columns field">
                <div class="column is-4" style="font-size: 1.5rem">Создание лидов</div>
                <div class="column">
                    <div class="form-check form-switch">
                        <input id="is_activity_settings" name="is_activity_settings"
                               class="form-check-input input_checkbox" type="checkbox" checked>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="notification is-info p-1" style="font-size: 1rem">
                <span class="icon has-text-white"> <i class="fas fa-info-circle"></i> </span>
                <span>Создавать заказ покупателя, при новом обращении клиента.</span>
                <div>Пояснение: будет создаваться новый заказ покупателя при обращении клиента, если заказ уже создан
                    будет проверяться по финальному статусу.
                </div>
                <div>Контрагент создается всегда с проверкой по полям (номер, электронная почта, и т.д.)</div>
            </div>
            <div class="columns field">
                <div class="column is-3">Заказ покупателя</div>
                <div class="column">
                    <div class="select w-50 is-small is-link">
                        <select id="is_activity_order" name="is_activity_order" onchange="styleIsOrderViewParams(this.value)" class="w-100">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="isOrderYes" style="display: none">
                <div class="columns field">
                    <div class="column is-3">Организация</div>
                    <div class="column">
                        <div class="select w-50 is-small is-link">
                            <select id="organization" name="organization" onchange="onIsOrganizationAccount(this)" class="w-100">
                            </select>
                        </div>
                    </div>
                </div>
                <div id="style_organization_account" class="columns field" style="display: none">
                    <div class="column is-3">Счет организации</div>
                    <div class="column">
                        <div class="select w-50 is-small is-link">
                            <select id="organization_account" name="organization_account" class="w-100">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="columns field">
                    <div class="column is-3">Статус заказа покупателя</div>
                    <div class="column">
                        <div class="select w-50 is-small is-link">
                            <select id="states" name="states" class="w-100">
                            </select>
                        </div>
                    </div>
                </div>


                <div class="columns field">
                    <div class="column is-3">Проект</div>
                    <div class="column">
                        <div class="select w-50 is-small is-link">
                            <select id="project_uid" name="project_uid" class="w-100">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="columns field">
                    <div class="column is-3">Канал продаж</div>
                    <div class="column">
                        <div class="select w-50 is-small is-link">
                            <select id="sales_channel_uid" name="sales_channel_uid" class="w-100">
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="box">
            <div class="notification is-info p-1" style="font-size: 1rem">
                <span class="icon has-text-white"> <i class="fas fa-info-circle"></i> </span>
                <span>Создавать заказ покупателя, при новом обращении клиента.</span>
            </div>
            <div class="columns field">
                <div class="column is-3">Доступ</div>
                <div class="column">
                    <div class="select w-50 is-small is-link">
                        <select id="responsible" onchange="on_responsible_uuid(this)" name="responsible" class="w-100">
                            <option value="0">Не создавать на сотрудника</option>
                            <option value="1">Указать по умолчанию</option>
                            <option value="2">Указывать из доступа в контрагенте</option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="div_employee" class="field" style="display: none">
                <div class="columns field">
                    <div class="column is-3 ">Сотрудник</div>
                    <div class="column">
                        <div class="select w-50 is-small">
                            <select id="responsible_uuid" name="responsible_uuid" class="w-100"> </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="dev_tasks" style="display: none">
                <div class="notification is-info p-1" style="font-size: 1rem">
                    <span class="icon has-text-white"> <i class="fas fa-info-circle"></i> </span>
                    <span><b>Важно: </b> формирование задач доступно с опцией CRM в тарифе</span>
                </div>

                <div class="columns field">
                    <div class="column is-3">Задачи</div>
                    <div class="column">
                        <div class="select w-50 is-small is-link">
                            <select id="tasks" name="tasks" class="w-100">
                                <option value="0">Нет</option>
                                <option value="1">Да</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <br>
        <button class="button is-outlined gradient_focus"> сохранить</button>
    </form>
</div>

<script>

    const dev_organization = @json($organization);
    const dev_states = @json($states);
    const dev_project = @json($project);
    const dev_saleschannel = @json($saleschannel);

    const employee = @json($employee);

    const model = @json($model);

    $(document).ready(function () {
        setSelect()
        leading()


        styleIsOrderViewParams(window.document.getElementById('is_activity_order').value)
    });

    function styleIsOrderViewParams(value) {
        let isOrderYes = window.document.getElementById('isOrderYes')
        if (value === '1') isOrderYes.style.display = ''
        else  isOrderYes.style.display = 'none'

    }

    function leading() {
        let is_activity_settings = window.document.getElementById('is_activity_settings')
        let is_activity_order = window.document.getElementById('is_activity_order')
        let responsible = window.document.getElementById('responsible')
        let responsible_uuid = window.document.getElementById('responsible_uuid')

        let organization = window.document.getElementById('organization')
        let organization_account = window.document.getElementById('organization_account')
        let states = window.document.getElementById('states')
        let project_uid = window.document.getElementById('project_uid')
        let sales_channel_uid = window.document.getElementById('sales_channel_uid')
        let tasks = window.document.getElementById('tasks')


        if (model != null) {
            if (model.is_activity_settings == 1) is_activity_settings.checked = true
            else is_activity_settings.checked = false


            is_activity_order.value = model.is_activity_order
            if (model.is_activity_order == 1){
                if (model.organization != null) organization.value = model.organization
                if (model.organization_account != null) {
                    onIsOrganizationAccount(organization)
                    organization_account.value = model.organization_account
                }
                if (model.states != null) states.value = model.states

                if (model.project_uid != null) project_uid.value = model.project_uid
                if (model.sales_channel_uid != null) sales_channel_uid.value = model.sales_channel_uid
            }


            responsible.value = model.responsible
            on_responsible_uuid(responsible)

            if (model.responsible == 1) responsible_uuid.value = model.responsible_uuid
            if (model.responsible != '0' && model.responsible != null) if (model.tasks != null) tasks.value = model.tasks
        }

    }

    function setSelect() {
        let organization = window.document.getElementById('organization')
        let organization_account = window.document.getElementById('organization_account')
        let states = window.document.getElementById('states')
        let project_uid = window.document.getElementById('project_uid')
        let sales_channel_uid = window.document.getElementById('sales_channel_uid')

        clearOption(organization)
        clearOption(organization_account)
        clearOption(states)
        clearOption(project_uid)
        clearOption(sales_channel_uid)

        createOptions(dev_organization, organization)
        createOptions(dev_states, states)
        createOptions([{'name': 'Не выбирать', 'id': '0'}], project_uid)
        createOptions([{'name': 'Не выбирать', 'id': '0'}], sales_channel_uid)
        createOptions(dev_project, project_uid)
        createOptions(dev_saleschannel, sales_channel_uid)

        onIsOrganizationAccount(organization)
    }




    function onIsOrganizationAccount(box){
        let style_organization_account = window.document.getElementById('style_organization_account')
        let organization_account = window.document.getElementById('organization_account')

        clearOption(organization_account)
        let value = box.value

        dev_organization.forEach(function (item){
            if (item.id === value) {
                if (item.accounts.rows.length > 0) {
                    style_organization_account.style.display = ''
                    item.accounts.rows.forEach((item) => {
                        let option = document.createElement("option");

                        option.text = item.accountNumber
                        option.value = item.id

                        organization_account.appendChild(option);
                    });
                }
                else style_organization_account.style.display = 'none'
            }
        })
    }





    function on_responsible_uuid(box) {
        let div_employee = window.document.getElementById('div_employee')
        let responsible_uuid = window.document.getElementById('responsible_uuid')
        let dev_tasks = window.document.getElementById('dev_tasks')
        div_employee.style.display = 'none'
        clearOption(responsible_uuid)


        if (box.value == '1') {
            div_employee.style.display = ''
            createOptions(employee, responsible_uuid)
        }

        if (box.value == '0') dev_tasks.style.display = 'none'
        else dev_tasks.style.display = ''
    }

    function createOptions(data, targetElement) {
        data.forEach((item) => {
            let option = document.createElement("option");

            option.text = item.fullName || item.name
            option.value = item.id

            targetElement.appendChild(option);
        });
    }

    function clearOption(selected) {
        while (selected.firstChild) selected.removeChild(selected.firstChild)
    }

</script>

