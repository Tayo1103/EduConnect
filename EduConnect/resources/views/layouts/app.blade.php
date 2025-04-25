<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Grade System')</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('/logo.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-900">
    <nav class="bg-white shadow mb-6">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <img src="/logo.png" alt="Logo" class="w-8 h-8">
                <span class="text-lg font-bold text-blue-600">EduConnect</span>
            </a>
            <div class="space-x-2">
                <a href="/students" class="px-4 py-2 rounded-full transition-all duration-200 
                    {{ Request::is('students*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600' }}">
                    Mahasiswa
                </a>
                <a href="/courses" class="px-4 py-2 rounded-full transition-all duration-200 
                    {{ Request::is('courses*') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600' }}">
                    Mata Kuliah
                </a>
                <a href="/grades/create" class="px-4 py-2 rounded-full transition-all duration-200 
                    {{ Request::is('grades/create') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600' }}">
                    Input Nilai
                </a>
                <a href="/grades/result" class="px-4 py-2 rounded-full transition-all duration-200 
                    {{ Request::is('grades/result') ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600' }}">
                    Nilai Mahasiswa
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4">
        @yield('content')
    </main>

    <footer class="mt-10 bg-white border-t py-4">
        <div class="container mx-auto px-4 text-center text-sm text-gray-600">
            &copy; {{ date('Y') }} EduConnect. All rights reserved.
        </div>
    </footer>
</body>
</html>
