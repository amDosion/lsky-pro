<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Vite Test - {{ \App\Utils::config(\App\Enums\ConfigKey::AppName) }}</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    @vite(['resources/css/fontawesome.less', 'resources/css/common.less', 'resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('css/lsky-ui.css') }}">

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-100">

    @yield('content')

    <script src="{{ asset('js/vendor/jquery.min.js') }}"></script>
    @vite('resources/js/app.js')
    <script>
        let setSwitch = function (e) {
            if (e.checked) {
                $(e).closest('.switch').find('input[type=hidden]').remove();
            } else {
                $(e).before('<input type="hidden" name="'+e.name+'" value="0" />');
            }
        }

        $('.switch input[type=checkbox]').each(function () {
            setSwitch(this);
        }).click(function () {
            setSwitch(this);
        });
    </script>
    @stack('scripts')
</body>
</html>
