<script>
    function idCreatePoleChecked(e, checked) {
        if (checked)
            $(`#${e}`).toggle()
        else 
            $(`#${e}`).hide()
    }
</script>