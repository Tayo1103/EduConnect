@extends('layouts.app')

@section('title', 'EduConnect | Cari Nilai Mahasiswa')

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

<form class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md" method="GET" action="{{ route('grades.result') }}">
    <h2 class="text-2xl font-semibold mb-4">Cari Nilai Mahasiswa</h2>

    <label for="student_id" class="mt-2 block text-sm font-medium text-gray-700">
        Nama Mahasiswa <span class="text-red-500">*</span>
    </label>
    <select name="student_id" id="student_id" class="w-full p-2 border border-gray-300 rounded mb-1" required>
        <option value="" disabled selected>Pilih Mahasiswa</option>
        @foreach($students as $student)
            <option value="{{ $student['id'] }}">{{ $student['name'] }}</option>
        @endforeach
    </select>

    <button type="submit" class="mt-6 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full w-full">Cari</button>
</form>

@if($gradesData !== null)
    <div class="max-w-4xl mx-auto mt-8">
        <h3 class="text-xl font-semibold mb-4">Daftar Nilai Mahasiswa</h3>

        @if(!empty($gradesData['grades']) && is_array($gradesData['grades']))
            <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg overflow-hidden">
                <thead class="bg-blue-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">No</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Nama Mata Kuliah</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">SKS</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Nilai</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Indeks Huruf</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gradesData['grades'] as $index => $grade)
                        <tr class="hover:bg-blue-50 transition duration-300">
                            <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $grade['course_name'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $grade['sks'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ number_format($grade['score'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $grade['grade_index'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 border-b">
                                <div class="flex gap-2">
                                    <a href="{{ route('grades.edit', ['id' => $grade['id']]) }}" 
                                       class="inline-block bg-yellow-500 text-black px-4 py-2 rounded-full shadow-md hover:bg-yellow-600 transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">
                                        Edit
                                    </a>

                                    <form action="{{ route('grades.destroy', ['id' => $grade['id']]) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus nilai ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-block bg-red-500 text-white px-4 py-2 rounded-full shadow-md hover:bg-red-600 transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 text-right text-lg font-bold text-gray-700">
                Total SKS: {{ $gradesData['total_sks'] }} | IPK: {{ $gradesData['ipk'] }}
            </div>
        @else
            <div class="text-center text-gray-600 text-lg py-10 border border-gray-300 bg-gray-50 rounded-lg">
                Tidak ada Data Nilai.
            </div>
        @endif
    </div>
@endif
@endsection
