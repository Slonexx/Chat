<div id="html" class="box">
   {{-- <form action="/Setting/automation/{{$accountId}}?isAdmin={{ request()->isAdmin }}&fullName={{ $fullName }}&uid={{ $uid }}" method="post" class="mt-2 ml-5 mr-5">
        @csrf <!-- {{ csrf_field() }} -->--}}
        <div class="box mt-1 mb-3 columns p-0 has-background-primary rounded text-white">
            <div class="column is-9 "> Список сотрудников </div>
            <div class="column is-1 text-center"> Используется </div>
            <div class="column is-2 has-text-right"> По умолчанию </div>
        </div>

        <div id="mainCreate">

            <a id="'1'"  onclick="ViewEmployee('1')" class="mt-0 box columns addStyleColumns">
                <div class="column is-1"> 1 </div>
                <div class="column"> Сергей </div>
                <div class="column is-1"> ✓ </div>
            </a>

        </div>

        <br>
       {{-- <button class="button is-outlined gradient_focus"> сохранить </button>
    </form>--}}
</div>

<style>
    .addStyleColumns{
        padding-bottom: 0.2rem !important;
        padding-top: 0.2rem !important;
        text-decoration: none;

    }
</style>
