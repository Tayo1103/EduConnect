import sqlite3
import os
import contextlib
import requests
from flask import Flask, request, jsonify, make_response
from flask_cors import CORS
import json

# Inisialisasi aplikasi Flask
app = Flask(__name__)

# Menambahkan pengaturan CORS untuk semua permintaan dari localhost
CORS(app, resources={r"/grades/*": {"origins": "*"}})

# Nama dan jalur database
DB_NAME = "grade_data.db"
DB_PATH = os.path.join(os.path.dirname(__file__), DB_NAME)

# URL untuk layanan mahasiswa dan mata kuliah
STUDENT_PROVIDER_URL = os.getenv("STUDENT_PROVIDER_URL", "http://localhost:5001")
COURSE_PROVIDER_URL = os.getenv("COURSE_PROVIDER_URL", "http://localhost:5002")

@contextlib.contextmanager
def get_db_connection():
    conn = sqlite3.connect(DB_PATH)
    try:
        yield conn
    finally:
        conn.close()

# Inisialisasi database
def init_db():
    with get_db_connection() as conn:
        cursor = conn.cursor()
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS grades (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_id INTEGER NOT NULL,
                course_id INTEGER NOT NULL,
                score REAL NOT NULL CHECK(score >= 0 AND score <= 100),
                grade_index TEXT,
                sks INTEGER NOT NULL,
                course_name TEXT
            )
        ''')
        conn.commit()
    print(f"Grade Service: Database '{DB_NAME}' diinisialisasi.")

# --- Utilitas Penilaian ---
def calculate_index(score):
    if score >= 85.01: return "A"
    elif score >= 75.01: return "AB"
    elif score >= 65.01: return "B"
    elif score >= 55.01: return "BC"
    elif score >= 50.01: return "C"
    elif score >= 40.01: return "D"
    else: return "E"

# --- Utilitas untuk Memvalidasi Mata Kuliah ---
def get_course_details(course_id):
    url = f"{COURSE_PROVIDER_URL}/courses/{course_id}"
    try:
        response = requests.get(url, timeout=5)
        response.raise_for_status()
        return response.json()
    except:
        return None
    
# --- Utilitas untuk Memvalidasi Mahasiswa ---
def get_student_details(student_id):
    url = f"{STUDENT_PROVIDER_URL}/students/{student_id}"
    try:
        response = requests.get(url, timeout=5)
        response.raise_for_status()
        return response.json()
    except:
        return None

# --- Menambahkan Header CORS untuk Permintaan OPTIONS ---
@app.after_request
def after_request(response):
    response.headers.add('Access-Control-Allow-Origin', '*')
    response.headers.add('Access-Control-Allow-Headers', 'Content-Type,Authorization')
    response.headers.add('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,OPTIONS')
    return response

# --- Endpoint untuk Melihat Semua Nilai ---
@app.route('/grades', methods=['GET'])
def get_all_grades():
    try:
        with get_db_connection() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM grades")
            grades = cursor.fetchall()

        return jsonify([dict(row) for row in grades]), 200
    except Exception as e:
        return jsonify({'error': f'Kesalahan server - {e}'}), 500

# --- Endpoint untuk Menambahkan Nilai ---
@app.route('/grades', methods=['POST'])
def add_grades():
    if not request.is_json:
        return jsonify({'error': 'Request harus dalam format JSON'}), 400

    data_list = request.get_json()

    if not isinstance(data_list, list):
        return jsonify({'error': 'Input harus berupa list JSON'}), 400

    results = []

    for data in data_list:
        student_id = data.get('student_id')
        course_id = data.get('course_id')
        score = data.get('score')

        if not all([student_id, course_id, score]):
            results.append({'error': 'student_id, course_id, dan score diperlukan', 'data': data})
            continue

        if not (0 <= score <= 100):
            results.append({'error': 'Score harus antara 0-100', 'score': score})
            continue

        student = get_student_details(student_id)
        if not student:
            results.append({'error': 'Mahasiswa tidak ditemukan', 'student_id': student_id})
            continue

        course = get_course_details(course_id)
        if not course:
            results.append({'error': 'Mata kuliah tidak ditemukan', 'course_id': course_id})
            continue

        try:
            with get_db_connection() as conn:
                cursor = conn.cursor()
                cursor.execute('SELECT 1 FROM grades WHERE student_id=? AND course_id=?', (student_id, course_id))
                if cursor.fetchone():
                    results.append({
                        'error': 'Nilai untuk mata kuliah ini sudah ada',
                        'student_id': student_id,
                        'course_id': course_id
                    })
                    continue
                cursor.execute('''INSERT INTO grades (student_id, course_id, score, grade_index, sks, course_name)
                                  VALUES (?, ?, ?, ?, ?, ?)''',
                               (student_id, course_id, score,
                                calculate_index(score),
                                course.get('sks'), course.get('name')))
                conn.commit()
                grade_id = cursor.lastrowid

            results.append({
                'id': grade_id,
                'student_id': student_id,
                'student_name': student.get('name'),
                'nim': student.get('nim'),
                'jurusan': student.get('jurusan'),
                'course_id': course_id,
                'course_name': course.get('name'),
                'score': score,
                'grade_index': calculate_index(score),
                'sks': course.get('sks')
            })
        except Exception as e:
            results.append({'error': f'Gagal menyimpan nilai - {e}', 'data': data})

    return jsonify(results), 200

# --- Endpoint untuk Melihat Nilai dan IPK Mahasiswa ---
@app.route('/grades/student/<int:student_id>', methods=['GET'])
def get_student_grades(student_id):
    try:
        with get_db_connection() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM grades WHERE student_id = ?", (student_id,))
            grades = cursor.fetchall()

        if not grades:
            return jsonify({
                'grades': [],
                'total_sks': 0,
                'ipk': 0.0
            }), 200

        total_sks = 0
        total_weighted_score = 0
        results = []

        index_weight = {"A": 4.0, "AB": 3.5, "B": 3.0, "BC": 2.5, "C": 2.0, "D": 1.0, "E": 0.0}

        for row in grades:
            sks = row["sks"]
            grade_point = index_weight.get(row["grade_index"], 0.0)
            total_sks += sks
            total_weighted_score += grade_point * sks
            results.append(dict(row))

        ipk = round(total_weighted_score / total_sks, 2) if total_sks > 0 else 0

        return jsonify({
            'grades': results,
            'total_sks': total_sks,
            'ipk': ipk
        }), 200
    except Exception as e:
        return jsonify({'error': f'Kesalahan server - {e}'}), 500

# --- Mengambil Nilai Berdasarkan ID ---
@app.route('/grades/<int:grade_id>', methods=['GET'])
def get_grade_by_id(grade_id):
    try:
        with get_db_connection() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM grades WHERE id = ?", (grade_id,))
            grade = cursor.fetchone()

            if not grade:
                return jsonify({'error': 'Data nilai tidak ditemukan'}), 404

            return jsonify(dict(grade)), 200

    except Exception as e:
        return jsonify({'error': f'Gagal mengambil data nilai - {e}'}), 500

# --- Endpoint untuk Memperbarui Nilai ---
@app.route('/grades/<int:grade_id>', methods=['PUT'])
def update_grade(grade_id):
    if not request.is_json:
        return jsonify({'error': 'Request harus dalam format JSON'}), 400

    try:
        data = request.get_json()

        if isinstance(data, str):
            data = json.loads(data)

        score = data.get('score')

        if score is None or not (0 <= score <= 100):
            return jsonify({'error': 'Score harus antara 0-100'}), 400

        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM grades WHERE id = ?", (grade_id,))
            grade = cursor.fetchone()

            if not grade:
                return jsonify({'error': 'Data nilai tidak ditemukan'}), 404

            new_index = calculate_index(score)
            cursor.execute('''
                UPDATE grades
                SET score = ?, grade_index = ?
                WHERE id = ?
            ''', (score, new_index, grade_id))
            conn.commit()

        return jsonify({'message': 'Nilai berhasil diperbarui', 'id': grade_id, 'new_score': score, 'grade_index': new_index}), 200

    except Exception as e:
        return jsonify({'error': f'Gagal mengubah nilai - {e}'}), 500
    
# --- Endpoint untuk menghapus nilai ---
@app.route('/grades/<int:grade_id>', methods=['DELETE'])
def delete_grade(grade_id):
    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT * FROM grades WHERE id = ?", (grade_id,))
            if not cursor.fetchone():
                return jsonify({'error': 'Data nilai tidak ditemukan'}), 404

            cursor.execute("DELETE FROM grades WHERE id = ?", (grade_id,))
            conn.commit()

        return jsonify({'message': 'Nilai berhasil dihapus', 'id': grade_id}), 200

    except Exception as e:
        return jsonify({'error': f'Gagal menghapus nilai - {e}'}), 500

# --- Menjalankan Aplikasi ---
if __name__ == '__main__':
    init_db()
    app.run(host='0.0.0.0', port=5003, debug=True)
