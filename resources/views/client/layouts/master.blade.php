<!DOCTYPE html>
<html lang="vi">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('client.partials.head')

    @vite(['resources/js/app.js'])

    @stack('styles')
</head>
<body class="theme-color-5">

    @include('client.partials.header')

    @yield('content')

    @include('client.partials.footer')

    @include('client.partials.ai-chat-widget')

    @include('client.partials.live-chat-widget')

    @include('client.partials.scripts')

    @stack('scripts')
</body>
</html>
