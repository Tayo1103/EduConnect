<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GradeController;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::resource('students', StudentController::class);
Route::resource('courses', CourseController::class);
Route::get('grades/create', [GradeController::class, 'create'])->name('grades.create');
Route::post('grades', [GradeController::class, 'store'])->name('grades.store');
Route::get('grades/result', [GradeController::class, 'result'])->name('grades.result');
Route::get('/grades/{id}/edit', [GradeController::class, 'edit'])->name('grades.edit');
Route::delete('/grades/{id}', [GradeController::class, 'destroy'])->name('grades.destroy');
Route::put('/grades/{id}', [GradeController::class, 'update'])->name('grades.update');
Route::get('/api/students/{id}', function ($id) {
    $student = Student::find($id);
    if (!$student) {
        return response()->json(['error' => 'Mahasiswa tidak ditemukan'], 404);
    }
    return response()->json($student);
});
