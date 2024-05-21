<!doctype html>
<html lang="en" style=" background-color:#dcdcdc; ">
@include('head')


<body class="bg_popup">

@yield('content')

<style>
    .bg_popup{
        background-image: url({{  ( Config::get("Global") )['url'].'beg.png' }});
    }
    .addStyleColumns{
        padding-bottom: 0.2rem !important;
        padding-top: 0.2rem !important;
        text-decoration: none;

    }
</style>

</body>
</html>

