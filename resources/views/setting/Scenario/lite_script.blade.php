<script>
    function appendOptionsIsSaved(uuid, template_value, status_value) {
        if (gridChild != {}) {

            let template_option = isOption(template, template_value, false)
            let status_option = isOption(status_arr, status_value, true)

            for (let key in gridChild) {
                if (key != uuid) {
                    let status = window.document.getElementById('status_' + key)
                    let template = window.document.getElementById('template_' + key)

                    if (template_option !== undefined) template.appendChild(template_option)
                    if (status_option !== undefined) status.appendChild(status_option)
                }
            }
        }

        function isOption(name, value, is_id_and_name = true) {
            let option = document.createElement("option");

            if (is_id_and_name)
                for (let key in name) {
                    if (key.length > 0) {
                        name[key].forEach(function (item) {
                            if (item.id == value) {
                                option.text = item.name
                                option.value = item.id
                                return option
                            }
                        })
                    }
                }
            else name.forEach(function (item) {
                if (item.uuid == value) {
                    option.text = item.title
                    option.value = item.uuid
                    return option
                }
            });
            return option
        }
    }


    function clearOptionsIsSaved() {
        if (gridChild != {}) {
            for (let key in gridChild) {
                let status = window.document.getElementById('status_' + key)
                let template = window.document.getElementById('template_' + key)

                deleteOptions(status, 'status')
                deleteOptions(template, 'template')
            }
        }


        function deleteOptions(selectElement, name) {
            for (let i = 0; i < selectElement.options.length; i++) {
                if (is_activity[name].includes(selectElement.options[i].value) && selectElement.value !== selectElement.options[i].value) selectElement.remove(i);
            }
        }
    }


    function onChangeEntity(uuid) {
        removeFromArray(is_activity['template'], window.document.getElementById('template_' + uuid).value);
        removeFromArray(is_activity['status'], window.document.getElementById('status_' + uuid).value);
        createSelectOptionsALL(uuid)
    }


    function set_is_activity(name, this_html, id) {
        let oldValue = this_html.getAttribute("data-previous-index");
        this_html.setAttribute("data-previous-index", this_html.value);

        if (oldValue !== null) removeFromArray(is_activity[name], oldValue)

        is_activity[name].push(this_html.value)
    }

    function select_is_option(selected) {
        if (selected.options.length > 0) return true;
        else return false;
    }


    function isDeleteAjax(id) {


        return new Promise((resolve, reject) => {
            if (Saved.length > 0) {
                Saved.forEach(function (item) {
                    if (item.id == id) {
                        let settings = ajax_settings(url + 'Setting/scenario/' + accountId, "DELETE", item);
                        $.ajax(settings).done(function (json) {
                            console.log(json);
                            if (json.status) {
                                resolve(true);
                            } else {
                                reject(false);
                                notificationMessage.innerText = JSON.stringify(json.message);
                            }
                        });
                    }
                });
                resolve(true)
            } else {
                resolve(true)
            }
        });
    }


    function deleteScript(id) {
        isDeleteAjax(id)
            .then((is_deleted) => {
                console.log(is_deleted);
                if (is_deleted) {
                    let template = window.document.getElementById('template_' + id);
                    let status = window.document.getElementById('status_' + id);
                    appendOptionsIsSaved(id, template.value, status.value);
                    removeFromArray(is_activity['template'], template.value);
                    removeFromArray(is_activity['status'], status.value);
                    delete gridChild[id];
                    window.document.getElementById(id).remove();
                }
            })
            .catch((error) => {});
    }

</script>

<script>
    function removeFromArray(arr, value) {
        const index = arr.indexOf(value);
        if (index !== -1) {
            arr.splice(index, 1);
        }
    }

    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
</script>
