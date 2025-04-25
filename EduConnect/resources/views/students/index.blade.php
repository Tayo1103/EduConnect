@extends('layouts.app')

@section('title', 'EduConnect | Daftar Mahasiswa')

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

<script>
    function closeFlashMessage(id) {
        document.getElementById(id).style.display = 'none';
    }

    window.addEventListener('DOMContentLoaded', (event) => {
        setTimeout(() => {
            const successMessage = document.getElementById('flash-success');
            const errorMessage = document.getElementById('flash-error');

            if (successMessage) successMessage.style.display = 'none';
            if (errorMessage) errorMessage.style.display = 'none';
        }, 5000);
    });
</script>

<div class="container mx-auto mt-6">
    <h2 class="text-3xl font-semibold mb-4">Daftar Mahasiswa</h2>

    <div class="mt-4 mb-6">
        <a href="{{ route('students.create') }}" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-full shadow-md hover:bg-blue-600 transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
            Tambah Mahasiswa
        </a>
    </div>

    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg overflow-hidden">
        <thead class="bg-blue-100">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">No</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Nama</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Email</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">NIM</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Jurusan</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
                <tr class="hover:bg-blue-50 transition duration-300">
                    <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $student['name'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $student['email'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $student['nim'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 border-b">{{ $student['jurusan'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-700 border-b flex space-x-2">
                        <a href="{{ route('students.edit', $student['id']) }}" 
                            class="inline-block bg-yellow-500 text-black px-4 py-2 rounded-full shadow-md hover:bg-yellow-600 transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-opacity-50">
                            Edit
                        </a>

                        <form action="{{ route('students.destroy', $student['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data mahasiswa ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-block bg-red-500 text-white px-4 py-2 rounded-full shadow-md hover:bg-red-600 transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak Ada Data Mahasiswa</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
