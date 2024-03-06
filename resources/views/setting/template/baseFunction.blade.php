<script>

    function messageViewAndHideText(status, text){
        if (status){
            window.document.getElementById('messageEmployee').style.display = 'block'
            window.document.getElementById('messageEmployee').innerText = JSON.stringify(text)
        } else {
            window.document.getElementById('messageEmployee').style.display = 'none'
        }
    }

    function animationLoadingGifOrImage(status, GifOrImageHide, ImageOrGifHide){
        if (status) {
            GifOrImageHide.style.display = "none"
            ImageOrGifHide.style.display = "inline"
        } else {
            GifOrImageHide.style.display = "inline"
            ImageOrGifHide.style.display = "none"
        }
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

    function ajax_settings_with_json(url, method, data){
        return {
            "url": url,
            "method": method,
            "timeout": 0,
            "headers": {"Content-Type": "application/json",},
            "data": JSON.stringify(data),
        }
    }

    

</script>
