@extends('layouts.app')

@section('title', 'EduConnect')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[75vh] text-center animate-fade-in-up">
    <div class="mb-6">
        <img src="/logo.png" alt="EduConnect Logo" class="mx-auto w-28 h-28">
    </div>

    <h1 class="text-4xl font-extrabold text-blue-600 mb-2 drop-shadow-sm">
        EduConnect
    </h1>

    <p class="text-lg text-gray-600 max-w-md mx-auto mb-6">
        Selamat datang di <span class="font-semibold text-blue-500">EduConnect</span>, sistem pengelolaan nilai mahasiswa berbasis Laravel API.
    </p>

    <a href="{{ route('grades.result') }}" class="inline-block bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-lg font-medium px-6 py-3 rounded-full shadow-md transition transform hover:scale-105 hover:shadow-lg">
        Lihat Nilai Mahasiswa
    </a>
</div>

<style>
    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.8s ease-out both;
    }
</style>
@endsection
