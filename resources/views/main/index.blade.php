@extends('layout')
@section('item', 'link_1')
@section('content')

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3 main-container content-container">
        @if ( request()->isAdmin != null and request()->isAdmin != 'ALL' )
            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center "> Доступ к настройкам есть только у администратора
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div id="message" class="mt-2 alert alert-info alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @include('div.TopServicePartner')


            <div class="row mt-3 ">
                <div class="col-6">
                    <div class="row">
                        <img style="width: 15%;" src="https://cdn-ru.bitrix24.kz/b9797699/sale/paysystem/field/090/090baf35a143b2d4afbc0cadb54f549b/logo_200x200.png">
                        <div> <strong>ОБЩЕНИЕ С КЛИЕНТАМИ ИЗ МОЕГОСКЛАДА</strong></div>
                        <div class="">Переписывайтесь с клиентами прямо из МоегоСклада с помощью удобных каналов коммуникации.</div>
                    </div>
                </div>
                <div class="col-6">
                    <img style="width: 10%;" src="https://cdn-ru.bitrix24.kz/b9797699/landing/4b8/4b85d07027a34ad4fa604c402dcee6b4/2023_09_28_19_01_54_1x_png">
                    <div class="mt"> <strong>CHATAPP DIALOG</strong></div>
                    <div class="">
                        <div> Единое окно для сбора и обработки всех сообщений из мессенджеров, социальных сетей и электронной почты.</div>
                    </div>
                </div>
            </div>

            <div class="row mt-3 ">
                <div class="col-6">
                    <div class="row">
                        <img style="width: 15%;" src="https://cdn-ru.bitrix24.kz/b9797699/landing/244/24459c884f02145508a55e45f7a7d718/dokumenty_1x.png">
                        <div> <strong>ОПТИМИЗАЦИЯ РАБОТЫ ОПЕРАТОРОВ</strong></div>
                        <div class="">Мониторинг нагрузки и времени реакции в реальном времени
                            Просмотр оценок (средняя максимальная) и отзывов
                            Просмотр детальной статистики по каждому каналу
                            Распределение ролей и прав доступа
                            Сценарии распределения диалогов</div>
                    </div>
                </div>
                <div class="col-6">
                    <img style="width: 10%;" src="https://cdn-ru.bitrix24.kz/b9797699/landing/34e/34ea864b396aa62bdcb48fa31ae21e9c/analitika_1x_png">
                    <div class="mt"> <strong>СТАТИСТИКА И АНАЛИТИКА</strong></div>
                    <div class="">
                        <div> Виджет для сайта стимулирует клиента на общение и позволяет:</div>
                        <div> 	&bull; Видеть детальную информацию о клиентах, включая источники обращения</div>
                        <div> 	&bull; Анализировать эффективность рекламных кампании через Roistat и CallTouch.</div>


                    </div>
                </div>
            </div>


            <div class="row mt-4">
                <div class="col-6">
                    <img style="width: 50px" src="https://cdn-ru.bitrix24.kz/b9797699/landing/0ac/0ac2895cea94302bb70d9499bd769592/14_dney_1x.png">
                    <div class=""> <strong>14 ДНЕЙ БЕСПЛАТНО</strong></div>
                    <div class="">
                        Мы на 1000% уверены в своем приложении и поэтому готовы предоставить 14 дней, чтобы Вы могли оценить его возможности и уникальность.
                    </div>
                </div>
                <div class="col-6">
                    <img style="width: 50px" src="https://cdn-ru.bitrix24.kz/b9797699/landing/a5d/a5d76d6870e8154035060f40b9848dca/Skoro_1x.png">
                    <div class=""> <strong>НОВЫЕ ВОЗМОЖНОСТИ</strong></div>
                    <div class="">
                        Мы не стоим на месте, поэтому совсем скоро вы сможете оценить новые фишки в нашем приложении. Ну и будем признательны за обратную связь.
                    </div>
                </div>
            </div>

    </div>
    <script>
        NAME_HEADER_TOP_SERVICE("Возможности интеграции")
        document.getElementById('message').style.display = 'none'
    </script>
@endsection

