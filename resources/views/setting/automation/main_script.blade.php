<script>
    let employees = @json($employeeModel);
    let list_template = @json($list_template);
    let automation = @json($automation);
    let lines = @json($lines);

    $(document).ready(function () { leadingEmpl() });


    function leadingEmpl(){
        mainCreate.innerText = ''



        employees.forEach(function (item, index){
            let is_default = ''
            let countAutomation = 0

            if (automation != null)
                automation.forEach(function (auto_item){
                    if (auto_item.employee.id == item.id) {
                        countAutomation = (auto_item.automation).length
                        if (auto_item.is_default == 1)is_default = '✓'
                    }
                })

            $('#mainCreate').append(
                `<a id="${item.id}" onclick="ViewEmployee('${item.id}')" class="mt-0 box columns addStyleColumns">
                    <div class="column is-1">${index}</div>
                    <div class="column"> ${item.employeeName} </div>
                    <div class="column is-1 text-center"> ${countAutomation} </div>
                    <div class="column is-1">  </div>
                    <div class="column is-1"> ${is_default} </div>
                </a>`);
        })

    }


function set_is_active_view(item){
}


</script>
