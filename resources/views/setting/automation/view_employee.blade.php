<div id="view_employee_uid" style="display: none">
    <div class="box">
        <div class="columns field">
            <div class="column">
                <a onclick="back_is_html()" class="button is-outlined gradient_focus"> Назад </a>
            </div>
        </div>
    </div>

    <form id="formId"
          action="/Setting/automation/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}"
          method="post">
        @csrf
        <div class="box">

            <div class="box">
                <div class="notification is-info p-1">
                    <span class="icon has-text-white"> <i class="fas fa-info-circle"></i> </span>
                    <span>Предлагаем использовать данного сотрудника по умолчанию, когда изменения идут от сторонних приложений</span>
                </div>
                <div class="columns field">
                    <div class="column is-3">Сотрудник по умолчанию</div>
                    <div class="column">
                        <div class="select w-50 is-small is-link">
                            <select id="is_default" name="is_default" class="w-100">
                                <option value="0">Нет</option>
                                <option value="1">Да</option>
                            </select>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="columns field">
                    <div class="column mt-1 is-2">Линия</div>
                    <div class="column">
                        <div class="select w-100 is-small is-link">
                            <select id="is_line" name="is_line" onchange="set_is_messenger(this.value)" class="w-100">
                            </select>
                        </div>
                    </div>
                    <div class="mt-2 tag is-medium is-Light">
                        <span class="icon has-text-info"> <i class="fas fa-info-circle"></i> </span>
                        <span>Выберите линию с которой будет отправляться сообщение</span>
                    </div>
                </div>
                <div class="columns field">
                    <div class="column mt-1 is-2">Мессенджер</div>
                    <div class="column">
                        <div class="select w-100 is-small is-link">
                            <select id="is_messenger" name="is_messenger" class="w-100">
                            </select>
                        </div>
                    </div>
                    <div class="mt-2 tag is-medium is-Light">
                        <span class="icon has-text-info"> <i class="fas fa-info-circle"></i> </span>
                        <span>Выберите мессенджер с которой будет отправляться сообщение</span>
                    </div>
                </div>

                <hr>
                <div class="notification is-info p-1">
                    <span class="icon has-text-white"> <i class="fas fa-info-circle"></i> </span>
                    <span>Выберите для данного сотрудника сценарии</span>
                </div>
                <div id="div_list_template" class="row field text-center"></div>
            </div>

            <button class="button is-outlined gradient_focus"> сохранить</button>
        </div>
    </form>
</div>

<script>
    let arr_template = []
    let is_active_view = []
    let time_uid = null

    const div = window.document.getElementById('div_list_template')
    const is_default = window.document.getElementById('is_default')
    const is_line = window.document.getElementById('is_line')
    const is_messenger = window.document.getElementById('is_messenger')


    const $form = $('#formId');
    $form.on('submit', function () {
        if (arr_template.length > 0)
            arr_template.forEach(function (item) {
                $('<input>').attr({type: 'hidden', name: 'template[]', value: item}).appendTo($form);
            })
        $('<input>').attr({type: 'hidden', name: 'employee_id', value: time_uid}).appendTo($form);
    });


    function view_list_template(uid) {
        time_uid = uid
        div.innerText = ''
        is_default.value = '0'
        clearOption(is_line)
        clearOption(is_messenger)


        list_template.forEach(function (item) {
            $('#div_list_template').append(`<a id="${item.id}" onclick="is_set(this)" class="m-2 box col addStyleColumns"> ${item.template.toArray.title} </a>`);
        })

        createOptions(lines[uid], is_line)
        set_is_messenger(is_line.value)


        if (is_active_view.length !== 0) {
            is_default.value = (is_active_view.is_default).toString();
            is_line.value = is_active_view.line;
            set_is_messenger(is_active_view.line);
            is_messenger.value = is_active_view.messenger;

            (is_active_view.automation).forEach(function (item) {
                is_set(window.document.getElementById(item.scenario_id))
            })
        }
    }


    function set_is_messenger(value) {
        clearOption(is_messenger)
        lines[time_uid].forEach(function (item) {
            if (item.licenseId == value) createOptions(item.messenger, is_messenger)
        })
    }


    function is_set(box) {
        const id = box.id
        let is_push = true;

        (box.classList).forEach(function (item) {
            if (item == 'is_set_active') is_push = false
        })

        if (is_push) {
            box.classList.add('is_set_active');
            arr_template.push(id);
        } else {
            box.classList.remove('is_set_active');
            const index = arr_template.indexOf(id);
            if (index !== -1) arr_template.splice(index, 1);
        }


    }


    function createOptions(data, targetElement) {
        data.forEach((item) => {
            let option = document.createElement("option");

            option.text = item.name || item.name
            option.value = item.licenseId || item.type

            targetElement.appendChild(option);
        });
    }
    function clearOption(selected) {
        while (selected.firstChild) selected.removeChild(selected.firstChild)
    }
</script>


<style>
    .is_set_active {
        background-color: #19f496;
        color: white;
    }
</style>
