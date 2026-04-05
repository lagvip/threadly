<!DOCTYPE html>
<html lang="vi">
<head>
    @include('client.partials.head')
    @stack('styles')
</head>
<body class="theme-color-5">

    @include('client.partials.header')

    @yield('content')

    @include('client.partials.footer')

    @include('client.partials.ai-chat-widget')
    
    @include('client.partials.scripts')

    @stack('scripts')
</body>
</html>
