<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function index()
    {
        try {
            $response = Http::get(config('services.student_api.url') . '/students');

            if ($response->successful()) {
                $students = $response->json();
            } else {
                $students = [];
                session()->flash('error', 'Gagal Mengambil Data Mahasiswa, Layanan Tidak Tersedia!');
                return view('students.index', ['students' => $students]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Menghubungi Layanan Mahasiswa, Coba Lagi Nanti!');
            return view('students.index', ['students' => []]);
        }

        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'nim' => 'required',
            'jurusan' => 'required',
        ]);

        $response = Http::get(config('services.student_api.url') . '/students');

        if ($response->successful()) {
            $existing = $response->json();
        } else {
            $existing = [];
        }

        foreach ($existing as $student) {
            if ($student['email'] === $request->email) {
                return back()->withInput()->withErrors(['email' => 'Email Sudah Digunakan!']);
            }
            if ($student['nim'] === $request->nim) {
                return back()->withInput()->withErrors(['nim' => 'NIM Sudah Digunakan!']);
            }
        }

        $response = Http::post(config('services.student_api.url') . '/students', $request->only('name', 'email', 'nim', 'jurusan'));

        if ($response->successful()) {
            return redirect()->route('students.index')->with('success', 'Data Mahasiswa Berhasil Ditambahkan!');
        }

        return back()->with('error', 'Gagal Menambahkan Data Mahasiswa!');
    }

    public function edit($id)
    {
        try {
            $response = Http::get(config('services.student_api.url') . '/students/' . $id);

            if ($response->successful()) {
                $student = $response->json();
                return view('students.edit', compact('student'));
            } else {
                return redirect()->route('students.index')->with('error', 'Data Mahasiswa Tidak Ditemukan!');
            }
        } catch (\Exception $e) {
            return redirect()->route('students.index')->with('error', 'Gagal Menghubungi Layanan Mahasiswa!');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'nim' => 'required',
            'jurusan' => 'required',
        ]);

        try {
            $response = Http::put(config('services.student_api.url') . '/students/' . $id, $request->only('name', 'email', 'nim', 'jurusan'));

            if ($response->successful()) {
                return redirect()->route('students.index')->with('success', 'Data Mahasiswa Berhasil Diperbarui!');
            } else {
                return back()->withInput()->with('error', 'Gagal Memperbarui Data Mahasiswa!');
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal Menghubungi Layanan Mahasiswa!');
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::delete(config('services.student_api.url') . '/students/' . $id);
            
            Log::info('Delete Response Status: ' . $response->status());
            Log::info('Delete Response Body: ' . $response->body());

            if ($response->successful()) {
                return redirect()->route('students.index')->with('success', 'Data Mahasiswa Berhasil Dihapus!');
            } else {
                return redirect()->route('students.index')->with('error', 'Gagal Menghapus Data Mahasiswa!');
            }
        } catch (\Exception $e) {
            Log::error('Error while deleting student: ' . $e->getMessage());
            return redirect()->route('students.index')->with('error', 'Gagal Menghubungi Layanan Mahasiswa, Coba Lagi Nanti!');
        }
    }
}
