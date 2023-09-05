<script>


    function deleteAccount(id, name) {
        console.log(id)
        console.log(name)
        deleteButtonBool = true
        window.document.getElementById('sleepInfoDelete').style.display = 'block'
        setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление данных сотрудника '+name+' через ' + 5, 1000)

        for (let i = 1; i < 7; i++) {
            let time = 7 - i
            setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление данных сотрудника '+name+' через ' + time, i * 1000)
        }

        setTimeout(() => window.document.getElementById('sleepInfoDelete').style.display = 'none',8 * 1000)
        setTimeout(() => deleteAccountRow(id) , 8 * 1000)
    }


    function activateCloseDelete(){
        deleteButtonBool = false
        window.document.getElementById('sleepInfoDelete').style.display = 'none'
    }


    function ajax_settings(url, method, data){
        return {
            "url": url,
            "method": method,
            "timeout": 0,
            "headers": {"Content-Type": "application/json",},
            "data": data,
        }
    }

</script>
