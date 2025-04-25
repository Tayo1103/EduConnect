@extends('layouts.app')

@section('title', 'EduConnect | Tambah Mata Kuliah')

@section('content')
<form action="{{ route('courses.store') }}" method="POST" class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    @csrf
    <h2 class="text-2xl font-semibold mb-4">Tambah Mata Kuliah</h2>

    <label for="name" class="mt-2 block text-sm font-medium text-gray-700">
        Nama Mata Kuliah <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name"
        value="{{ old('name') }}"
        class="w-full p-2 border rounded mb-2 {{ $errors->has('name') ? 'border-red-500' : 'border-gray-300' }}">
    @error('name')
        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
    @enderror

    <label for="name" class="mt-2 block text-sm font-medium text-gray-700">
        Kode Mata Kuliah <span class="text-red-500">*</span>
    </label>
    <input type="text" name="code" id="code"
        value="{{ old('code') }}"
        class="w-full p-2 border rounded mb-2 {{ $errors->has('code') ? 'border-red-500' : 'border-gray-300' }}">
    @error('code')
        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
    @enderror

    <label for="name" class="mt-2 block text-sm font-medium text-gray-700">
        SKS <span class="text-red-500">*</span>
    </label>
    <input type="number" name="sks" id="sks"
        value="{{ old('sks') }}"
        class="w-full p-2 border rounded mb-1 {{ $errors->has('sks') ? 'border-red-500' : 'border-gray-300' }}">
    @error('sks')
        <p class="text-red-500 text-sm mb-2">{{ $message }}</p>
    @enderror

    <button type="submit"
        class="mt-6 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full w-full">Simpan</button>
</form>
@endsection