import sys
import json
import re
import string
import io
import mysql.connector
from datetime import datetime
from collections import Counter

# --- KONFIGURASI WINDOWS AGAR TIDAK ERROR ENCODING ---
# Kita set stdout ke utf-8, tapi kita juga hindari print karakter aneh
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# ============================================
# CONFIG DATABASE
# ============================================
DB_CONFIG = {
    'host': '127.0.0.1',    # PENTING: Gunakan IP 127.0.0.1 jangan localhost
    'user': 'root',
    'password': '',
    'database': 'tolaki_learning_db'
}

# ============================================
# NLP HELPER FUNCTIONS
# ============================================
def clean_text(text):
    text = text.lower()
    text = re.sub(r'\[.*?\]', '', text)
    # Hapus karakter non-ascii agar aman di Windows
    text = re.sub(r'[^\x00-\x7F]+', ' ', text)
    text = re.sub(r'[%s]' % re.escape(string.punctuation), '', text)
    text = re.sub(r'\w*\d\w*', '', text)
    return ' '.join(text.split())

def extract_keywords(text, top_n=10):
    words = clean_text(text).split()
    stopwords = {'dan', 'yang', 'di', 'ke', 'dari', 'ini', 'itu', 'untuk', 'pada', 'adalah', 'sebagai', 'dengan', 'karena', 'jika', 'bisa', 'saya'}
    filtered_words = [w for w in words if w not in stopwords and len(w) > 3]
    counter = Counter(filtered_words)
    return [word for word, count in counter.most_common(top_n)]

# ============================================
# MAIN LOGIC
# ============================================
def process_material(material_id):
    conn = None
    cursor = None
    
    print(f"[START] Processing Material ID: {material_id}")
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        cursor.execute("SELECT content_indo FROM materials WHERE id = %s", (material_id,))
        row = cursor.fetchone()
        
        if not row:
            print(f"[ERROR] Material ID {material_id} tidak ditemukan.")
            return False
            
        full_json = json.loads(row['content_indo'])

        # 1. Ekstraksi Deterministic Metadata (Knowledge Map)
        knowledge_map = full_json.get('knowledge_map', {})
        glossary = full_json.get('glossary_bilingual', [])
        
        # 2. Processing Structured Content (Big-O Optimization: O(n))
        processed_chunks = []
        sections = full_json.get('content_structured', [])
        
        for section in sections:
            section_name = section.get('section', 'General')
            for chunk in section.get('chunks', []):
                if chunk.get('type') in ['paragraph', 'heading', 'definition']:
                    processed_chunks.append({
                        'id': chunk.get('id'),
                        'section': section_name,
                        'text': chunk.get('content') or chunk.get('term'),
                        'keywords': chunk.get('keywords', [])
                    })

        # 3. Gabungkan Metadata untuk AI Lokal
        ai_data = json.dumps({
            'knowledge_base': {
                'summary': knowledge_map.get('context_summary'),
                'concepts': knowledge_map.get('key_concepts'),
                'glossary': glossary # Pivot untuk bahasa daerah
            },
            'chunks': processed_chunks
        })

        cursor.execute(
            "UPDATE materials SET ai_embeddings = %s, ai_status = 'ready' WHERE id = %s",
            (ai_data, material_id)
        )
        conn.commit()
        
        print(f"[SUCCESS] Berhasil memproses {len(processed_chunks)} chunks.")
        return True

    except mysql.connector.Error as err:
        print(f"[DB ERROR] {err}")
        return False
    except Exception as e:
        print(f"[SYSTEM ERROR] {e}")
        if conn and conn.is_connected():
            cursor.execute("UPDATE materials SET ai_status = 'failed' WHERE id = %s", (material_id,))
            conn.commit()
        return False
        
    finally:
        if cursor: cursor.close()
        if conn and conn.is_connected(): conn.close()

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("[USAGE] python ai_processor.py <material_id>")
        sys.exit(1)
    
    # Ambil ID dari argumen command line
    mat_id = int(sys.argv[1])
    success = process_material(mat_id)
    
    # Exit code: 0 jika sukses, 1 jika gagal
    sys.exit(0 if success else 1)