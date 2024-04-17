<div class="box">
    <form action="/Setting/scenario/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}" method="post" class="mt-2 ml-5 mr-5">
        @csrf <!-- {{ csrf_field() }} -->
        <div class="box mt-1 mb-4 columns p-0 gradient_layout_invert">
            <div class="column is-11 "> Создать сценарий</div>
            <div onclick="createScript()" class="col-1 has-text-right" style="font-size: 30px; cursor: pointer">
                <i class="fas fa-plus-circle"></i> &nbsp;
            </div>
        </div>

        <div class="box">

            <div class="mb-3 columns has-background-primary rounded text-white">
                <div class="column"> Название шаблона </div>
                <div class="column"> Тип документа </div>
                <div class="column"> Статус </div>
                <div class="column"> Канал продаж </div>
                <div class="column"> Проект </div>
                <div class="column is-1"> Удалить </div>
            </div>
            <div id="mainCreate">


            </div>

        </div>


        <button class="button is-outlined gradient_focus"> сохранить</button>
    </form>
</div>
