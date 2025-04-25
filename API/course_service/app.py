# course_service/app.py
import sqlite3
import os
import contextlib
from flask import Flask, request, jsonify

# --- Inisialisasi Aplikasi Flask ---
app = Flask(__name__)
DB_NAME = "course_data.db"
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
    """Inisialisasi database mata kuliah (course_data.db) jika belum ada."""
    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute('''
                CREATE TABLE IF NOT EXISTS courses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    code TEXT NOT NULL UNIQUE,
                    sks INTEGER NOT NULL CHECK(sks > 0)
                )
            ''')
            conn.commit()
        print(f"Provider Course: Database '{DB_NAME}' diinisialisasi.")
    except Exception as e:
        print(f"Provider Course: Gagal inisialisasi DB '{DB_NAME}' - {e}")
        raise

# --- API Endpoints ---

# POST /courses
@app.route('/courses', methods=['POST'])
def create_course():
    if not request.is_json:
        return jsonify({"error": "Request must be JSON"}), 400

    data = request.get_json()
    name = data.get('name')
    code = data.get('code')
    sks = data.get('sks')

    if not name or not code or sks is None:
        return jsonify({"error": "Nama, kode, dan SKS mata kuliah diperlukan"}), 400
    if not isinstance(sks, int) or sks <= 0:
        return jsonify({"error": "SKS harus berupa bilangan bulat positif"}), 400

    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute(
                "INSERT INTO courses (name, code, sks) VALUES (?, ?, ?)",
                (name, code, sks)
            )
            conn.commit()
            course_id = cursor.lastrowid
        return jsonify({'id': course_id, 'name': name, 'code': code, 'sks': sks}), 201
    except sqlite3.IntegrityError:
        return jsonify({'error': 'Kode mata kuliah sudah digunakan'}), 409
    except Exception as e:
        app.logger.error(f"Error creating course: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500

# GET /courses/<int:course_id>
@app.route('/courses/<int:course_id>', methods=['GET'])
def get_course(course_id):
    try:
        with get_db_connection() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute("SELECT id, name, code, sks FROM courses WHERE id = ?", (course_id,))
            course = cursor.fetchone()
        if course:
            return jsonify(dict(course)), 200
        else:
            return jsonify({'error': 'Mata kuliah tidak ditemukan'}), 404
    except Exception as e:
        app.logger.error(f"Error fetching course {course_id}: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500

# GET /courses
@app.route('/courses', methods=['GET'])
def get_all_courses():
    try:
        with get_db_connection() as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.cursor()
            cursor.execute("SELECT id, name, code, sks FROM courses")
            courses = cursor.fetchall()
        return jsonify([dict(course) for course in courses]), 200
    except Exception as e:
        app.logger.error(f"Error fetching courses: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500

# Endpoint: PUT /courses/<int:course_id>
@app.route('/courses/<int:course_id>', methods=['PUT', 'POST'])
def update_course(course_id):
    if not request.is_json:
        return jsonify({"error": "Request must be JSON"}), 400
    data = request.form or request.get_json()

    name = data.get('name')
    code = data.get('code')
    sks = data.get('sks')

    if not name or not code or sks is None:
        return jsonify({"error": "Nama, kode, dan SKS mata kuliah diperlukan"}), 400

    try:
        sks = int(sks)
        if sks <= 0:
            return jsonify({"error": "SKS harus berupa bilangan bulat positif"}), 400
    except (ValueError, TypeError):
        return jsonify({"error": "SKS harus berupa bilangan bulat positif"}), 400

    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT id, name, code, sks FROM courses WHERE id = ?", (course_id,))
            course = cursor.fetchone()

            if not course:
                return jsonify({'error': 'Mata kuliah tidak ditemukan'}), 404

            cursor.execute(
                """
                UPDATE courses
                SET name = ?, code = ?, sks = ?
                WHERE id = ?
                """,
                (name, code, sks, course_id)
            )
            conn.commit()
            return jsonify({'message': 'Data Mata Kuliah Berhasil Diperbarui!'}), 200
    except sqlite3.IntegrityError:
        print("[DEBUG] IntegrityError: kode mata kuliah bentrok")
        return jsonify({'error': 'Kode mata kuliah sudah digunakan'}), 409
    except Exception as e:
        app.logger.error(f"Error updating course {course_id}: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500

# DELETE /courses/<int:course_id>
@app.route('/courses/<int:course_id>', methods=['DELETE'])
def delete_course(course_id):
    try:
        with get_db_connection() as conn:
            cursor = conn.cursor()
            cursor.execute("SELECT id FROM courses WHERE id = ?", (course_id,))
            course = cursor.fetchone()

            if course:
                cursor.execute("DELETE FROM courses WHERE id = ?", (course_id,))
                conn.commit()
                return jsonify({'message': 'Data Mata Kuliah Berhasil Dihapus!'}), 200
            else:
                return jsonify({'error': 'Mata kuliah tidak ditemukan'}), 404
    except Exception as e:
        app.logger.error(f"Error deleting course {course_id}: {e}")
        return jsonify({'error': 'Kesalahan server internal'}), 500

# --- Menjalankan Aplikasi ---
if __name__ == '__main__':
    init_db()
    app.run(host='0.0.0.0', port=5002, debug=True)