<!doctype html>
<html lang="en">
@include('head')
 <style>
     html {
         background: url('https://colorlib.com/wp/wp-content/uploads/sites/2/404-error-template-18.png') ;
         -webkit-background-size: cover;
         -moz-background-size: cover;
         -o-background-size: cover;
         background-size: cover;
     }
 </style>
<body class="">

<div class="gradient rounded p-2">

    <div class="text-center">
        <img src="{{  ( Config::get("Global") )['url'].'client.svg' }}" width="50px" height="50px"  alt="">
        <img src="{{  ( Config::get("Global") )['url'].'client2.svg' }}" width="100px" height="100%"  alt="">
        Данная интеграция находится в МоемСкладе
    </div>

</div>

</body>

<style>
    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #444444;
        font-size: 18pt;
        background-color: #ffffff;
    }
    .gradient{
        background-image: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
</style>

</html>

