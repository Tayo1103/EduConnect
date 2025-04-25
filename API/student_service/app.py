# student_service/app.py
import sqlite3
import os
import contextlib
from flask import Flask, request, jsonify

# --- Inisialisasi Aplikasi Flask ---
app = Flask(__name__)
# Nama file database diubah menjadi student_data.db
DB_NAME = "student_data.db"
DB_PATH = os.path.join(os.path.dirname(__file__), DB_NAME)

# --- Utilitas Database ---
@contextlib.contextmanager
def get_db_connection():
    conn = sqlite3.connect(DB_PATH)
    try:
        yield conn
    finally:
        conn.close()

def init_db():
    """Inisialisasi database mahasiswa (student_data.db) jika belum ada."""
    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            # Tabel disesuaikan untuk mahasiswa
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS students (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL UNIQUE,
                    nim TEXT NOT NULL UNIQUE,
                    jurusan TEXT NOT NULL
                )
            ''')
            conn.commit()
        print(f"Provider Mahasiswa: Database '{DB_NAME}' diinisialisasi.")
    except Exception as e:
        print(f"Provider Mahasiswa: Gagal inisialisasi DB '{DB_NAME}' - {e}")
        raise

# --- API Endpoints ---

# Endpoint: POST /students
@app.route('/students', methods=['POST'])
def create_student():
    if not request.is_json:
        return jsonify({"error": "Request must be JSON"}), 400
    data = request.get_json()
    name = data.get('name')
    email = data.get('email')
    nim = data.get('nim')
    jurusan = data.get('jurusan')

    if not name or not email or not nim or not jurusan:
        return jsonify({"error": "Nama, email, NIM, dan jurusan diperlukan"}), 400

    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute(
                "INSERT INTO students (name, email, nim, jurusan) VALUES (?, ?, ?, ?)",
                (name, email, nim, jurusan)
            )
            conn.commit()
            student_id = cursor.lastrowid
        return jsonify({'id': student_id, 'name': name, 'email': email, 'nim': nim, 'jurusan': jurusan}), 201
    except sqlite3.IntegrityError:
        return jsonify({'error': 'Email atau NIM sudah ada'}), 409
    except Exception as e:
        app.logger.error(f"Error creating student: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500

# Endpoint: GET /students/<int:student_id>
@app.route('/students/<int:student_id>', methods=['GET'])
def get_student(student_id):
    try:
        with get_db_connection() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute("SELECT id, name, email, nim, jurusan FROM students WHERE id = ?", (student_id,))
            student = cursor.fetchone()
        if student:
            return jsonify(dict(student)), 200
        else:
            return jsonify({'error': 'Mahasiswa tidak ditemukan'}), 404
    except Exception as e:
        app.logger.error(f"Error fetching student {student_id}: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500
    
# Endpoint: GET /students
@app.route('/students', methods=['GET'])
def get_all_students():
    try:
        with get_db_connection() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute("SELECT id, name, email, nim, jurusan FROM students")
            students = cursor.fetchall()
        return jsonify([dict(student) for student in students]), 200
    except Exception as e:
        app.logger.error(f"Error fetching students: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500
    
# Endpoint: PUT /students/<int:student_id>
@app.route('/students/<int:student_id>', methods=['PUT'])
def update_student(student_id):
    if not request.is_json:
        return jsonify({"error": "Request must be JSON"}), 400
    data = request.get_json()
    
    name = data.get('name')
    email = data.get('email')
    nim = data.get('nim')
    jurusan = data.get('jurusan')

    if not name or not email or not nim or not jurusan:
        return jsonify({"error": "Nama, email, NIM, dan jurusan diperlukan"}), 400

    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT id FROM students WHERE id = ?", (student_id,))
            student = cursor.fetchone()

            if not student:
                return jsonify({'error': 'Mahasiswa tidak ditemukan'}), 404

            cursor.execute(
                """
                UPDATE students
                SET name = ?, email = ?, nim = ?, jurusan = ?
                WHERE id = ?
                """,
                (name, email, nim, jurusan, student_id)
            )
            conn.commit()
            return jsonify({'message': 'Data Mahasiswa Berhasil Diperbarui!'}), 200
    except sqlite3.IntegrityError:
        return jsonify({'error': 'Email atau NIM sudah ada'}), 409
    except Exception as e:
        app.logger.error(f"Error updating student {student_id}: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500
    
# Endpoint: DELETE /students/<int:student_id>
@app.route('/students/<int:student_id>', methods=['DELETE'])
def delete_student(student_id):
    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT id FROM students WHERE id = ?", (student_id,))
            student = cursor.fetchone()
            
            if student:
                cursor.execute("DELETE FROM students WHERE id = ?", (student_id,))
                conn.commit()
                return jsonify({'message': 'Data Mahasiswa Berhasil Dihapus!'}), 200
            else:
                return jsonify({'error': 'Mahasiswa tidak ditemukan'}), 404
    except Exception as e:
        app.logger.error(f"Error deleting student {student_id}: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500

# --- Menjalankan Aplikasi ---
if __name__ == '__main__':
    init_db()
    app.run(host='0.0.0.0', port=5001, debug=True)
