<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public function index()
    {
        try {
            $response = Http::get(config('services.course_api.url') . '/courses');

            if ($response->successful()) {
                $courses = $response->json();
            } else {
                $courses = [];
                session()->flash('error', 'Gagal Mengambil Data Matakuliah, Layanan Tidak Tersedia!');
                return view('courses.index', ['courses' => $courses]);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal Menghubungi Layanan Matakuliah, Coba Lagi Nanti!');
            return view('courses.index', ['courses' => []]);
        }

        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        return view('courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
            'sks' => 'required|numeric|min:1',
        ], [
            'sks.min' => 'SKS tidak boleh nol atau kurang dari nol!',
        ]);

        $response = Http::get(config('services.course_api.url') . '/courses');

        if ($response->successful()) {
            $existing = $response->json();
        } else {
            $existing = [];
        }

        foreach ($existing as $course) {
            if ($course['code'] === $request->code) {
                return back()->withInput()->withErrors(['code' => 'Kode Mata Kuliah Sudah Digunakan!']);
            }
        }

        $response = Http::post(config('services.course_api.url') . '/courses', [
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'sks' => (int) $request->input('sks'),
        ]);

        if ($response->successful()) {
            return redirect()->route('courses.index')->with('success', 'Data Mata Kuliah Berhasil Ditambahkan!');
        }

        return back()->with('error', $response->json()['error'] ?? 'Gagal Menambahkan Data Mata Kuliah!');
    }

    public function edit($id)
    {
        try {
            $response = Http::get(config('services.course_api.url') . '/courses/' . $id);

            if ($response->successful()) {
                $course = $response->json();
                return view('courses.edit', compact('course'));
            } else {
                return redirect()->route('courses.index')->with('error', 'Data Mata Kuliah Tidak Ditemukan!');
            }
        } catch (\Exception $e) {
            return redirect()->route('courses.index')->with('error', 'Gagal Menghubungi Layanan Mata Kuliah!');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
            'sks' => 'required|numeric|min:1',
        ], [
            'sks.min' => 'SKS tidak boleh nol atau kurang dari nol!',
        ]);

        try {
            $response = Http::put(config('services.course_api.url') . '/courses/' . $id, $request->only('name', 'code', 'sks'));         

            if ($response->successful()) {
                return redirect()->route('courses.index')->with('success', 'Data Mata Kuliah Berhasil Diperbarui!');
            } else {
                $errorMessage = $response->json()['error'] ?? 'Gagal Memperbarui Data Mata Kuliah!';
                return back()->withInput()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal Menghubungi Layanan Mata Kuliah!');
        }
    }

    public function destroy($id)
    {
        try {
            $response = Http::delete(config('services.course_api.url') . '/courses/' . $id);

            Log::info('Delete Course Response Status: ' . $response->status());
            Log::info('Delete Course Response Body: ' . $response->body());

            if ($response->successful()) {
                return redirect()->route('courses.index')->with('success', 'Data Mata Kuliah Berhasil Dihapus!');
            } else {
                return redirect()->route('courses.index')->with('error', 'Gagal Menghapus Data Mata Kuliah!');
            }
        } catch (\Exception $e) {
            Log::error('Error while deleting course: ' . $e->getMessage());
            return redirect()->route('courses.index')->with('error', 'Gagal Menghubungi Layanan Mata Kuliah, Coba Lagi Nanti!');
        }
    }
}
