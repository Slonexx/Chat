<script>
    //get fields
    function getFields(){
        let settings = ajax_settings_with_json(baseURL + 'Setting/template/info/fields' , "GET");
        return $.ajax(settings).done(function (json) {
            console.log(baseURL + 'Setting/template/info/fields'   + ' response ↓ ')
            console.log(json)

        })
        
    }

    function getAddFields(){
        let settings = ajax_settings_with_json(baseURL + `Setting/getAttributes/${accountId}` , "GET");
        return $.ajax(settings).done(function (json) {
            console.log(baseURL + `Setting/getAttributes/${accountId}`   + ' response ↓ ')
            console.log(json)

        })
        
    }
    //get fields

</script>