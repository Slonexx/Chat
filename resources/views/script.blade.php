<script>
    if ('{{$isAdmin}}' === 'ALL') {
        window.document.getElementById('setting').style.display = 'block'
    }

    let item = '@yield('item')'

    window.document.getElementById(item).classList.add('active_sprint')
    if (item.replace(/[^+\d]/g, '') > 1 && item.replace(/[^+\d]/g, '') <= 9) {
        this_click(window.document.getElementById('btn_1'))
        if (item.replace(/[^+\d]/g, '') > 3 && item.replace(/[^+\d]/g, '') <= 5) {
        this_click(window.document.getElementById('btn_2'))
        }
        if (item.replace(/[^+\d]/g, '') >= 6 && item.replace(/[^+\d]/g, '') <= 9) {
        this_click(window.document.getElementById('btn_3'))
        }
    }


    function this_click(btn) {
        btn.classList.toggle("active");
        let dropdownContent = btn.nextElementSibling;
        if (dropdownContent.style.display === "block") {
            dropdownContent.style.display = "none";
        } else {
            dropdownContent.style.display = "block";
        }
    }

    let dropdown = document.getElementsByClassName("dropdown-btn");
    let i;

    for (i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            let dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }


</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Функция для обновления высоты body
        function updateBodyHeight() {
            let main = document.getElementById('main_heading');

            // Получаем все видимые элементы внутри <main>
            let visibleElements = Array.from(main.children).filter(element => {
                return window.getComputedStyle(element).display !== "none";
            });

            // Вычисляем общую высоту видимых элементов
            let totalHeight = visibleElements.reduce((acc, element) => {
                return acc + element.offsetHeight;
            }, 0);

            // Вычисляем новую высоту для <body> (totalHeight + 5%)
            let newBodyHeight = totalHeight * 1.20
            if (totalHeight < 600) newBodyHeight = 720;

            // Устанавливаем новую высоту для <body>
            document.body.style.height = newBodyHeight + "px";
        }

        // Создаем экземпляр MutationObserver с функцией обратного вызова
        let observer = new MutationObserver(updateBodyHeight);

        // Конфигурация для отслеживания изменений в дереве DOM
        let config = { childList: true, subtree: true, attributes: true, characterData: true };

        // Запускаем отслеживание изменений
        observer.observe(document.body, config);
    });
</script>
