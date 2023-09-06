<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>widget</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <script type="text/javascript"
            src="https://online.moysklad.ru/js/ns/appstore/app/v1/moysklad-iframe-expand-3.js"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>


</head>

<body>

@yield('content')

<style>
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 12pt;
    }

    .gradient {
        background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

</style>

<style>
    .gradient_focus:hover {
        color: white;
        border: 0px;
        background: rgb(26, 183, 183);
        background-image: linear-gradient(147deg, #17e18a 0%, #1ab7b7 74%);
    }

    .gradient_focus:active, .gradient_focus:focus {
        background-color: rgb(26, 183, 183);
        background-image: linear-gradient(147deg, #17e18a 0%, #1ab7b7 74%);
        border: 0px;
        background-size: 100%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        -moz-text-fill-color: transparent;
    }
</style>

</body>
</html>

