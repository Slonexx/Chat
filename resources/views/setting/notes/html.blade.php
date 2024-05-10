<div id="html" class="box">
    <form action="/Setting/counterparty/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}" method="post">
        @csrf <!-- {{ csrf_field() }} -->
        <div class="notification is-info is-light">
            <div class="columns field">
                <div class="column is-4" style="font-size: 1.5rem">Создавать контрагентов</div>
                <div class="column">
                    <div class="form-check form-switch">
                        <input id="is_activity_agent" name="is_activity_agent" class="form-check-input input_checkbox" type="checkbox" checked>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="notification is-info p-1" style="font-size: 1rem">
                <span class="icon has-text-white"> <i class="fas fa-info-circle"></i> </span>
                <span>Будет сохраняться вся переписка в заметках.</span>
                <div><b>Важно: </b> формирование заметок доступно с опцией CRM в тарифе</div>
            </div>
            <div class="columns field">
                <div class="column is-3">Переписка в заметках</div>
                <div class="column">
                    <div class="select w-50 is-small is-link">
                        <select id="notes" name="notes" class="w-100">
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
                <span>Указывать линию и мессенджер в заметках</span>
            </div>
            <div class="columns field">
                <div class="column is-3">Указывать мессенджер в заметках</div>
                <div class="column">
                    <div class="select w-50 is-small is-link">
                        <select id="is_messenger" name="is_messenger" class="w-100">
                            <option value="0">Нет</option>
                            <option value="1">Да</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <button class="button is-outlined gradient_focus"> сохранить </button>
    </form>
</div>

<script>

    let model = @json($model);

    $(document).ready(function () { leading() });

    function leading(){
        let html_is_activity_agent = window.document.getElementById('is_activity_agent')
        let html_notes = window.document.getElementById('notes')
        let html_is_messenger = window.document.getElementById('is_messenger')

        console.log(model)

        if (model != null){
            if (model.is_activity_agent == '1') html_is_activity_agent.checked = true
            else html_is_activity_agent.checked = false

            html_notes.value = model.notes
            html_is_messenger.value = model.is_messenger
        }
    }

</script>

