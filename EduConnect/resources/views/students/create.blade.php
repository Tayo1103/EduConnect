@extends('layouts.app')

@section('title', 'EduConnect | Tambah Mahasiswa')

@section('content')
<form class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md" action="{{ route('students.store') }}" method="POST">
    @csrf
    <h2 class="text-2xl font-semibold mb-4">Tambah Mahasiswa</h2>

    <label for="name" class="mt-2 block text-sm font-medium text-gray-700">
        Nama Mahasiswa <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
        value="{{ old('name') }}"
        class="w-full p-2 border rounded mb-1 {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}">
    @error('name')
        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
    @enderror

    <label for="email" class="mt-2 block text-sm font-medium text-gray-700">
        Email <span class="text-red-500">*</span>
    </label>
    <input type="email" name="email" id="email"
        value="{{ old('email') }}"
        class="w-full p-2 border rounded mb-1 {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }}">
    @error('email')
        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
    @enderror

    <label for="nim" class="mt-2 block text-sm font-medium text-gray-700">
        NIM <span class="text-red-500">*</span>
    </label>
    <input type="text" name="nim" id="nim"
        value="{{ old('nim') }}"
        class="w-full p-2 border rounded mb-1 {{ $errors->has('nim') ? 'border-red-500' : 'border-gray-300' }}">
    @error('nim')
        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
    @enderror

    <label for="jurusan" class="mt-2 block text-sm font-medium text-gray-700">
        Jurusan <span class="text-red-500">*</span>
    </label>
    <input type="text" name="jurusan" id="jurusan"
        value="{{ old('jurusan') }}"
        class="w-full p-2 border rounded mb-1 {{ $errors->has('jurusan') ? 'border-red-500' : 'border-gray-300' }}">
    @error('jurusan')
        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
    @enderror

    <button type="submit" class="mt-6 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full w-full">
        Simpan
    </button>
</form>
@endsection