@extends('layouts.app')

@section('title', 'EduConnect | Edit Mahasiswa')

@section('content')
<form class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md" action="{{ route('students.update', $student['id']) }}" method="POST">
    @csrf
    @method('PUT')

    <h2 class="text-2xl font-semibold mb-4">Edit Mahasiswa</h2>
    
    <label for="name" class="block text-sm font-medium text-gray-700">
        Nama Mahasiswa <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name" value="{{ old('name', $student['name']) }}" class="w-full p-2 border border-gray-300 rounded mb-4" required>
    
    <label for="email" class="block text-sm font-medium text-gray-700">
        Email <span class="text-red-500">*</span>
    </label>
    <input type="email" name="email" id="email" value="{{ old('email', $student['email']) }}" class="w-full p-2 border border-gray-300 rounded mb-4" required>
    
    <label for="nim" class="block text-sm font-medium text-gray-700">
        NIM <span class="text-red-500">*</span>
    </label>
    <input type="text" name="nim" id="nim" value="{{ old('nim', $student['nim']) }}" class="w-full p-2 border border-gray-300 rounded mb-4" required>
    
    <label for="jurusan" class="block text-sm font-medium text-gray-700">
        Jurusan <span class="text-red-500">*</span>
    </label>
    <input type="text" name="jurusan" id="jurusan" value="{{ old('jurusan', $student['jurusan']) }}" class="w-full p-2 border border-gray-300 rounded mb-6" required>

    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full w-full">Update</button>
</form>
@endsection
