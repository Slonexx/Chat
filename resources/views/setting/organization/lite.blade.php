<script>


    function btnCreatingEmployeeForOrgan() {
        if (MyEmployee.length > 0) createEmployeeForOrgan(MyEmployee[0].employeeId)
        else {
            messageViewAndHideText(true, 'К сожалению, в данный момент у вас нет доступных сотрудников. Пожалуйста, перейдите в раздел "Сотрудник и доступы" для добавления новых сотрудников.')
        }
    }


    function displayNameForOrganName(id) {
        let organization = window.document.getElementById('organizationSelect')
        window.document.getElementById('nameOrganization').innerText = organization.querySelector('option[value="' + id + '"]').textContent
    }


    function onEmployee(status, object, id) {

        switch (object) {
            case 'MyEmployee': {
                if (status) {
                    MyEmployee = MyEmployee.filter(employee => employee.employeeId !== id);
                } else {
                    window.document.getElementById('LineForEmployee_' + id).style.display = 'none'

                    let employeeS = document.getElementById('employeeSelect_' + id);
                    let licenses_ = document.getElementById('licenses_' + id);
                    while (employeeS.firstChild) {
                        employeeS.removeChild(employeeS.firstChild)
                    }
                    while (licenses_.firstChild) {
                        licenses_.removeChild(licenses_.firstChild)
                    }

                    MyEmployee = [].concat(MyEmployee, BaseMyEmployee.filter(employee => employee.employeeId === id))
                }
                break
            }
            case 'createMain': {

                if (deleteButtonBool) {

                    let settings = ajax_settings(baseURL + 'Setting/organization/delete/Licenses/' + accountId, "GET", {organId: id});
                    $.ajax(settings).done(function (json) {
                        if (json.status){
                            window.document.getElementById(id).remove();
                            onEmployee(false, 'MsOrgan', id)
                        } else {
                            window.document.getElementById('sleepInfoDelete').style.display = 'block'
                            window.document.getElementById('sleepInfoDelete').innerText = JSON.stringify(json.message)
                        }

                    })
                }

                break
            }
            case 'MsOrgan': {

                if (status) {

                    if (id == '0') {
                        MsOrgan = {}
                    } else MsOrgan = MsOrgan.filter(employee => employee.id !== id);
                } else {
                    if (id == '0') {
                        MsOrgan = BaseMsOrgan
                    } else MsOrgan = [].concat(MsOrgan, BaseMsOrgan.filter(employee => employee.id === id))
                }

                break
            }

        }

    }

    function activateCloseDelete(){
        deleteButtonBool = false
        window.document.getElementById('sleepInfoDelete').style.display = 'none'
    }

    function deleteAccount(id, name) {
        deleteButtonBool = true
        window.document.getElementById('sleepInfoDelete').style.display = 'block'
        setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление данных организации  ' + name + ' через ' + 5, 1000)

        for (let i = 1; i < 7; i++) {
            let time = 7 - i
            setTimeout(() => window.document.getElementById('messageInfoDelete').innerText = 'Удаление данных организации ' + name + ' через ' + time, i * 1000)
        }

        setTimeout(() => window.document.getElementById('sleepInfoDelete').style.display = 'none', 8 * 1000)
        setTimeout(() => onEmployee(true, 'createMain', id), 8 * 1000)
    }
</script>
