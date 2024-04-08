<script>
    function idCreatePoleChecked(e, checked) {
        if (checked)
            $(`#${e}`).toggle()
        else 
            $(`#${e}`).hide()
    }

    async function getInfoAndAppend(){
        let fields = await getFields();
        let addFields = await getAddFields();
        let idCreatePole = fields.data;
        appendFields(idCreatePole, 'idCreatePole')
        let idCreateAddPole = addFields.data;
        appendAddFields(idCreateAddPole, 'idCreateAddPole')
    }

    async function getInfoAndAppendUpdate(){
        let fields = await getFields();
        let addFields = await getAddFields();
        let idCreatePole = fields.data;
        appendFields(idCreatePole, 'idCreatePoleUpdate')
        let idCreateAddPole = addFields.data;
        appendAddFields(idCreateAddPole, 'idCreateAddPoleUpdate')
    }
</script>