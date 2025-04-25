@extends('layouts.app')

@section('title', 'EduConnect | Edit Mata Kuliah')

@section('content')
<form class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md" action="{{ route('courses.update', $course['id']) }}" method="POST">
    @csrf
    @method('PUT')

    <h2 class="text-2xl font-semibold mb-4">Edit Mata Kuliah</h2>
    
    <label for="name" class="block text-sm font-medium text-gray-700">
        Nama Mata Kuliah <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name" id="name" value="{{ old('name', $course['name']) }}" class="w-full p-2 border border-gray-300 rounded mb-4" required>

    <label for="code" class="block text-sm font-medium text-gray-700">
        Kode Mata Kuliah <span class="text-red-500">*</span>
    </label>
    <input type="text" name="code" id="code" value="{{ old('code', $course['code']) }}" class="w-full p-2 border border-gray-300 rounded mb-4" required>

    <label for="sks" class="block text-sm font-medium text-gray-700">
        SKS <span class="text-red-500">*</span>
    </label>
    <input type="number" name="sks" id="sks" value="{{ old('sks', $course['sks']) }}" class="w-full p-2 border border-gray-300 rounded mb-6" min="1" required>

    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full w-full">Update</button>
</form>
@endsection
