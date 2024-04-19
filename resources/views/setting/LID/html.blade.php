<div id="html" class="box">
    <form action="/Setting/lid/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}" method="post">
        @csrf <!-- {{ csrf_field() }} -->
        <div class="notification is-info is-light">
            <div class="columns field">
                <div class="column is-4" style="font-size: 1.5rem">Создание лидов</div>
                <div class="column">
                    <div class="form-check form-switch">
                        <input id="is_activity_settings" name="is_activity_settings" class="form-check-input input_checkbox" type="checkbox" checked>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="notification is-info p-1" style="font-size: 1rem">
                <span class="icon has-text-white"> <i class="fas fa-info-circle"></i> </span>
                <span>Создавать заказ покупателя, при новом обращении клиента.</span>
                <div>Пояснение: будет создаваться новый заказ покупателя при обращении клиента, если заказ уже создан будет проверяться по финальному статусу.</div>
                <div>Контрагент создается всегда с проверкой по полям (номер, электронная почта, и т.д.)</div>
            </div>
            <div class="columns field">
                <div class="column is-3">Заказ покупателя</div>
                <div class="column">
                    <div class="select w-50 is-small is-link">
                        <select id="is_activity_order" name="is_activity_order" class="w-100">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
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

        </div>
        <br>
        <button class="button is-outlined gradient_focus"> сохранить </button>
    </form>
</div>

<script>

    $(document).ready(function () { leading() });

    function leading(){
        let is_activity_settings = window.document.getElementById('is_activity_settings')
        let is_activity_order = window.document.getElementById('is_activity_order')
        let responsible = window.document.getElementById('responsible')
        let responsible_uuid = window.document.getElementById('responsible_uuid')

        if (model != null){
            if (model.is_activity_settings == '1') is_activity_settings.checked = true
            else is_activity_settings.checked = false

            is_activity_order.value = model.is_activity_order
            responsible.value = model.responsible
            on_responsible_uuid(responsible)

            if (model.responsible == '1'){
                responsible_uuid.value = model.responsible_uuid
            }
        }

    }


    function on_responsible_uuid(box){
        let div_employee = window.document.getElementById('div_employee')
        let responsible_uuid = window.document.getElementById('responsible_uuid')
        div_employee.style.display = 'none'
        clearOption(responsible_uuid)



        if (box.value == '1'){
            div_employee.style.display = ''
            createOptions(employee, responsible_uuid)
        }
    }

    function createOptions(data, targetElement) {
        data.forEach((item) => {
            let option = document.createElement("option");

            option.text = item.fullName
            option.value = item.id

            targetElement.appendChild(option);
        });
    }
    function clearOption(selected) {
        while (selected.firstChild) selected.removeChild(selected.firstChild)
    }

</script>

