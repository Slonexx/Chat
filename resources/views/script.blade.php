
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
