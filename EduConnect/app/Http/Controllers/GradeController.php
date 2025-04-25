<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GradeController extends Controller
{
    public function create()
    {
        try {
            $studentsResponse = Http::get(config('services.student_api.url') . '/students');
            $coursesResponse = Http::get(config('services.course_api.url') . '/courses');

            if ($studentsResponse->successful() && $coursesResponse->successful()) {
                $students = $studentsResponse->json();
                $courses = $coursesResponse->json();

                return view('grades.create', compact('students', 'courses'));
            } else {
                return redirect()->route('grades.create')->with('error', 'Gagal mengambil data mahasiswa atau mata kuliah!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengambil data mahasiswa atau mata kuliah!');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer',
            'course_id' => 'required|integer',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post('http://localhost:5003/grades', json_encode([
            [
                'student_id' => (int) $validated['student_id'],
                'course_id' => (int) $validated['course_id'],
                'score' => (float) $validated['score'],
            ]
        ]));

        if (!$response->successful()) {
            $error = $response->json();
            return response()->json(['error' => 'Gagal menyimpan nilai', 'details' => $error], 500);
        }

        return response()->json($response->json(), 201);
    }

    public function result(Request $request)
    {
        $studentId = $request->input('student_id');
        $students = [];
        $gradesData = null;

        try {
            $response = Http::timeout(5)->get('http://localhost:5001/students');
            $students = $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            $students = [];
        }

        if ($studentId) {
            try {
                $gradesResponse = Http::timeout(5)->get("http://localhost:5003/grades/student/{$studentId}");
                if ($gradesResponse->status() === 404) {
                    $gradesData = [
                        'grades' => [],
                        'total_sks' => 0,
                        'ipk' => 0.0
                    ];
                } elseif ($gradesResponse->successful()) {
                    $json = $gradesResponse->json();
                    $gradesData = array_merge([
                        'grades' => [],
                        'total_sks' => 0,
                        'ipk' => 0.0
                    ], $json);
                }                
            } catch (\Exception $e) {
                $gradesData = null;
            }
        }          

        return view('grades.result', compact('students', 'gradesData'));
    }

    public function edit($id)
    {
        try {
            $response = Http::get('http://localhost:5003/grades/' . $id);
            
            if ($response->successful()) {
                $grade = $response->json();
    
                $studentsResponse = Http::get(config('services.student_api.url') . '/students');
                $coursesResponse = Http::get(config('services.course_api.url') . '/courses');
    
                if ($studentsResponse->successful() && $coursesResponse->successful()) {
                    $students = $studentsResponse->json();
                    $courses = $coursesResponse->json();
    
                    return view('grades.edit', compact('grade', 'students', 'courses'));
                } else {
                    return redirect()->route('grades.result')->with('error', 'Gagal mengambil data mahasiswa atau mata kuliah!');
                }
            } else {
                return redirect()->route('grades.result')->with('error', 'Nilai tidak ditemukan!');
            }
        } catch (\Exception $e) {
            return redirect()->route('grades.result')->with('error', 'Terjadi kesalahan saat mengambil data!');
        }
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->put("http://localhost:5003/grades/{$id}", json_encode([
            'score' => (float) $validated['score'],
        ]));

        if (!$response->successful()) {
            $error = $response->json();
            return response()->json(['error' => 'Gagal mengupdate nilai', 'details' => $error], 500);
        }

        return redirect()->route('grades.result')->with('success', 'Nilai berhasil diperbarui!');
    }  

    public function destroy($id)
    {
        try {
            $response = Http::delete("http://localhost:5003/grades/{$id}");
    
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal menghapus nilai'], 500);
            }
    
            return redirect()->route('grades.result')->with('success', 'Nilai berhasil dihapus!');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus nilai!'], 500);
        }
    }    
}
