@extends('layouts.app')

@section('title', 'EduConnect | Input Nilai')

@section('content')
@if(session('success'))
    <div id="flash-success" class="bg-green-200 text-green-800 p-4 rounded mb-4 relative">
        {{ session('success') }}
        <button onclick="closeFlashMessage('flash-success')" class="absolute top-0 right-0 px-2 py-1 text-gray-600 hover:text-gray-800">&times;</button>
    </div>
@elseif(session('error'))
    <div id="flash-error" class="bg-red-200 text-red-800 p-4 rounded mb-4 relative">
        {{ session('error') }}
        <button onclick="closeFlashMessage('flash-error')" class="absolute top-0 right-0 px-2 py-1 text-gray-600 hover:text-gray-800">&times;</button>
    </div>
@endif

<form id="gradeForm" class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
    @csrf

    <h2 class="text-2xl font-semibold mb-4">Input Nilai Mahasiswa</h2>

    <label for="student_id" class="mt-2 block text-sm font-medium text-gray-700">
        Nama Mahasiswa <span class="text-red-500">*</span>
    </label>
    <select name="student_id" id="student_id" class="w-full p-2 border border-gray-300 rounded mb-4" required>
        <option value="" disabled selected>Pilih Mahasiswa</option>
        @foreach($students as $student)
            <option value="{{ $student['id'] }}">{{ $student['name'] }}</option>
        @endforeach
    </select>

    <label for="course_id" class="mt-2 block text-sm font-medium text-gray-700">
        Nama Mata Kuliah <span class="text-red-500">*</span>
    </label>
    <select name="course_id" id="course_id" class="w-full p-2 border border-gray-300 rounded mb-4" required>
        <option value="" disabled selected>Pilih Mata Kuliah</option>
        @foreach($courses as $course)
            <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
        @endforeach
    </select>

    <label for="grade" class="mt-2 block text-sm font-medium text-gray-700">
        Nilai Mata Kuliah <span class="text-red-500">*</span>
    </label>
    <input type="number" name="grade" id="grade" step="0.01" class="w-full p-2 border border-gray-300 rounded mb-4" required>

    <button type="submit" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full w-full">Simpan</button>
</form>

<div id="gradeTable" class="max-w-2xl mx-auto mt-6 hidden">
    <table class="w-full border text-sm text-left text-gray-700 shadow-md rounded-lg overflow-hidden">
        <thead class="bg-blue-100">
            <tr>
                <th class="px-4 py-2">Mahasiswa</th>
                <th class="px-4 py-2">NIM</th>
                <th class="px-4 py-2">Jurusan</th>
                <th class="px-4 py-2">Mata Kuliah</th>
                <th class="px-4 py-2">SKS</th>
                <th class="px-4 py-2">Nilai</th>
                <th class="px-4 py-2">Indeks</th>
            </tr>
        </thead>
        <tbody id="tableBody"></tbody>
    </table>
</div>

<script>
document.getElementById('gradeForm').reset();
document.getElementById('gradeForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const studentId = document.getElementById('student_id').value;
    const courseId = document.getElementById('course_id').value;
    const score = document.getElementById('grade').value;

    console.log('Sending data:', {
        student_id: studentId,
        course_id: courseId,
        score: score
    });

    try {
        const response = await fetch('http://localhost:5003/grades', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify([{
                student_id: parseInt(studentId),
                course_id: parseInt(courseId),
                score: parseFloat(score)
            }])
        });

        console.log('Response status:', response.status);
        if (!response.ok) {
            const errorText = await response.text();
            console.log('Error response:', errorText);
            alert('Gagal mengirim data!');
            return;
        }

        const result = await response.json();
        console.log('API result:', result);

        const table = document.getElementById('gradeTable');
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = "";

        result.forEach(row => {
            if (!row.error) {
                const newRow = `
                    <tr class="border-t">
                        <td class="px-4 py-2">${row.student_name}</td>
                        <td class="px-4 py-2">${row.nim}</td>
                        <td class="px-4 py-2">${row.jurusan}</td>
                        <td class="px-4 py-2">${row.course_name}</td>
                        <td class="px-4 py-2">${row.sks}</td>
                        <td class="px-4 py-2">${parseFloat(row.score).toFixed(2)}</td>
                        <td class="px-4 py-2">${row.grade_index}</td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', newRow);
                table.classList.remove('hidden');
            } else {
                alert(row.error);
            }
        });

    } catch (error) {
        console.log('Fetch error:', error);
        alert('Gagal mengirim data!');
    }
});
</script>
@endsection
