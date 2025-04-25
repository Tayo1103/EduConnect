@extends('layouts.app')

@section('title', 'EduConnect | Edit Nilai Mahasiswa')

@section('content')
<form class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md" method="POST" action="{{ route('grades.update', $grade['id']) }}">
    @csrf
    @method('PUT')

    <h2 class="text-2xl font-semibold mb-4">Edit Nilai Mahasiswa</h2>

    <label for="student_id" class="block text-sm font-medium text-gray-700">Nama Mahasiswa</label>
    <select name="student_id_disabled" id="student_id" class="w-full p-2 border border-gray-300 rounded mb-1 bg-gray-100 cursor-not-allowed" disabled>
        @foreach($students as $student)
            <option value="{{ $student['id'] }}" {{ $student['id'] == $grade['student_id'] ? 'selected' : '' }}>
                {{ $student['name'] }}
            </option>
        @endforeach
    </select>

    <label for="course_id" class="block text-sm font-medium text-gray-700 mt-4">Mata Kuliah</label>
    <select name="course_id_disabled" id="course_id" class="w-full p-2 border border-gray-300 rounded mb-1 bg-gray-100 cursor-not-allowed" disabled>
        @foreach($courses as $course)
            <option value="{{ $course['id'] }}" {{ $course['id'] == $grade['course_id'] ? 'selected' : '' }}>
                {{ $course['name'] }}
            </option>
        @endforeach
    </select>

    <label for="score" class="block text-sm font-medium text-gray-700 mt-4">Nilai</label>
    <input type="number" name="score" id="score" step="0.01" value="{{ $grade['score'] }}" class="w-full p-2 border border-gray-300 rounded mb-1" min="0" max="100" required>

    <button type="submit" class="mt-6 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full w-full">Update Nilai</button>
</form>
@endsection
