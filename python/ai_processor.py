import sys
import json
import re
import math
import string
import io
import hashlib
import mysql.connector
import urllib.request
import urllib.error
from datetime import datetime
from collections import Counter

# ── Encoding fix untuk Windows ──────────────────────────────────────────────
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding="utf-8", errors="replace")

# ════════════════════════════════════════════════════════════════════════════
# KONFIGURASI
# ════════════════════════════════════════════════════════════════════════════
DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "root",
    "database": "tolaki_learning_db",
}

OPENAI_BASE_URL = "https://api.chatanywhere.tech/v1"
OPENAI_API_KEY  = "sk-o44DmAnE8ceq5OWSqECLINUi1ugCeTbWAdGYsPyh1QjBmXou"
OPENAI_MODEL    = "gpt-4o-mini"

# ════════════════════════════════════════════════════════════════════════════
# LAYER 1 — LIGHTWEIGHT NLP (tetap dipakai sebagai fallback/validasi)
# ════════════════════════════════════════════════════════════════════════════

STOPWORDS_ID = {
    "dan","yang","di","ke","dari","ini","itu","untuk","pada","adalah","sebagai",
    "dengan","karena","jika","bisa","saya","kamu","dia","mereka","kita","kami",
    "akan","telah","sudah","juga","lebih","tidak","atau","tetapi","namun","bahwa",
    "kepada","oleh","ketika","saat","setelah","sebelum","dalam","oleh","agar",
    "jadi","ialah","yakni","yaitu","seperti","antara","setiap","semua","banyak",
}

def clean_text(text: str) -> str:
    text = text.lower()
    text = re.sub(r'\[.*?\]', '', text)
    text = re.sub(r'[^\w\s]', ' ', text)
    text = re.sub(r'\d+', '', text)
    return ' '.join(text.split())

def extract_keywords_local(text: str, top_n: int = 15) -> list[str]:
    words = clean_text(text).split()
    filtered = [w for w in words if w not in STOPWORDS_ID and len(w) > 3]
    counter = Counter(filtered)
    return [w for w, _ in counter.most_common(top_n)]

def chunk_text(text: str, max_chars: int = 400) -> list[str]:
    """Potong teks menjadi chunk semantik berdasarkan kalimat."""
    sentences = re.split(r'(?<=[.!?])\s+', text)
    chunks, current = [], ""
    for s in sentences:
        if len(current) + len(s) < max_chars:
            current += " " + s
        else:
            if current.strip():
                chunks.append(current.strip())
            current = s
    if current.strip():
        chunks.append(current.strip())
    return chunks

def compute_tfidf_scores(chunks: list[str]) -> list[dict]:
    """
    Hitung TF-IDF ringan per chunk. Dipakai sebagai embedding numerik
    yang bisa dilakukan dot-product di Dart (tanpa model ML).
    Hanya simpan top-15 term per chunk untuk hemat memori.
    """
    N = len(chunks)
    df: dict[str, int] = {}
    tokenized = []
    for chunk in chunks:
        tokens = set(clean_text(chunk).split()) - STOPWORDS_ID
        tokenized.append(tokens)
        for t in tokens:
            df[t] = df.get(t, 0) + 1

    result = []
    for i, chunk in enumerate(chunks):
        tokens = clean_text(chunk).split()
        tf: dict[str, float] = {}
        for t in tokens:
            if t in STOPWORDS_ID or len(t) < 3:
                continue
            tf[t] = tf.get(t, 0) + 1
        total = sum(tf.values()) or 1
        tfidf = {}
        for term, freq in tf.items():
            idf = math.log((N + 1) / (df.get(term, 0) + 1))
            tfidf[term] = round((freq / total) * idf, 4)
        top_terms = sorted(tfidf.items(), key=lambda x: -x[1])[:15]
        result.append({
            "chunk_id": i,
            "text": chunk,
            "keywords": [t for t, _ in top_terms],
            "tfidf": dict(top_terms),
        })
    return result


# ════════════════════════════════════════════════════════════════════════════
# LAYER 2 — CLOUD KNOWLEDGE EXTRACTION
# ════════════════════════════════════════════════════════════════════════════

def call_openai(system_prompt: str, user_content: str, max_tokens: int = 2048) -> dict | None:
    """Wrapper HTTP murni (tidak pakai library openai) agar ringan di server."""
    url = f"{OPENAI_BASE_URL}/chat/completions"
    payload = json.dumps({
        "model": OPENAI_MODEL,
        "response_format": {"type": "json_object"},
        "messages": [
            {"role": "system", "content": system_prompt},
            {"role": "user",   "content": user_content},
        ],
        "temperature": 0.2,
        "max_tokens": max_tokens,
    }).encode("utf-8")

    req = urllib.request.Request(
        url,
        data=payload,
        headers={
            "Content-Type": "application/json",
            "Authorization": f"Bearer {OPENAI_API_KEY}",
        },
        method="POST",
    )
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            content = data["choices"][0]["message"]["content"]
            return json.loads(content)
    except (urllib.error.URLError, KeyError, json.JSONDecodeError) as e:
        print(f"[OPENAI ERROR] {e}")
        return None


SYSTEM_PROMPT_KNOWLEDGE = """
Kamu adalah sistem Knowledge Extraction Engine untuk aplikasi pendidikan NusaLearn.
Tugasmu: analisis materi pelajaran dan ekstrak pengetahuan ke format JSON yang PADAT dan KOMPREHENSIF.
Ini akan disimpan di perangkat mobile siswa untuk AI offline.

Output WAJIB JSON dengan struktur berikut (JANGAN tambah field lain):
{
  "summary": "<ringkasan 3-5 kalimat, mencakup inti materi>",
  "detailed_summary": "<ringkasan panjang 8-12 kalimat, menjelaskan alur dan poin penting>",
  "concepts": [
    {
      "term": "<nama konsep>",
      "explanation": "<penjelasan 2-4 kalimat yang self-contained>",
      "examples": ["<contoh 1>", "<contoh 2>"],
      "related_terms": ["<term terkait 1>", "<term terkait 2>"]
    }
  ],
  "qa_pairs": [
    {
      "question": "<pertanyaan yang mungkin diajukan siswa>",
      "answer": "<jawaban lengkap dan edukatif>",
      "keywords": ["<kata kunci>"]
    }
  ],
  "glossary": [
    {
      "term": "<istilah Indonesia>",
      "term_local": "<istilah bahasa daerah jika ada, kosongkan jika tidak ada>",
      "content": "<definisi lengkap>",
      "context": "<konteks penggunaan dalam materi>"
    }
  ],
  "key_facts": ["<fakta penting 1>", "<fakta penting 2>"],
  "themes": ["<tema utama 1>", "<tema 2>"],
  "difficulty_hints": {
    "easy_questions": ["<pertanyaan mudah>"],
    "hard_questions": ["<pertanyaan sulit>"]
  }
}

PENTING:
- Minimal 5 concepts, 8 qa_pairs, 5 glossary items
- qa_pairs WAJIB mencakup tipe pertanyaan berikut:
  * apa, mengapa, bagaimana, siapa, kapan, di mana
  * asal-usul dan lokasi tokoh/tempat/kejadian
  * definisi istilah penting dalam materi
- Semua penjelasan harus self-contained (bisa dipahami tanpa membaca teks asli)
- Bahasa: Indonesia yang baku dan mudah dipahami siswa SD/SMP
"""


def extract_knowledge_with_ai(material_text: str, title: str) -> dict | None:
    """
    Kirim full teks materi ke cloud AI, dapatkan knowledge base yang kaya.
    Dibatasi 6000 karakter untuk hemat token.
    """
    truncated = material_text[:6000]
    user_content = f"""
Judul Materi: {title}

Isi Materi:
{truncated}

Ekstrak seluruh pengetahuan dari materi di atas ke format JSON yang telah ditentukan.
Pastikan qa_pairs mencakup semua topik penting dari materi ini,
termasuk pertanyaan tentang asal-usul, lokasi, dan definisi istilah.
"""
    return call_openai(SYSTEM_PROMPT_KNOWLEDGE, user_content, max_tokens=3000)


SYSTEM_PROMPT_NARRATIVE = """
Kamu adalah sistem ekstraksi pengetahuan naratif untuk aplikasi pendidikan NusaLearn.
Analisis materi yang diberikan dan ekstrak semua informasi tentang TOKOH, ALUR, dan TEMA.

Output WAJIB JSON dengan struktur berikut:
{
  "content_type": "narrative" | "informational" | "mixed",
  "characters": [
    {
      "name": "<nama tokoh>",
      "role": "utama" | "sampingan" | "antagonis",
      "description": "<deskripsi tokoh 2-3 kalimat>",
      "traits": ["<sifat 1>", "<sifat 2>"],
      "name_local": "<nama dalam bahasa daerah jika ada>"
    }
  ],
  "plot": {
    "setting_place": "<tempat kejadian>",
    "setting_time": "<waktu/era kejadian>",
    "beginning": "<pembuka cerita>",
    "conflict": "<konflik utama>",
    "resolution": "<penyelesaian>"
  },
  "themes": ["<tema 1>", "<tema 2>"],
  "moral_values": ["<nilai moral 1>", "<nilai moral 2>"],
  "narrative_qa": [
    {
      "question": "<pertanyaan tentang tokoh/alur/tema>",
      "answer": "<jawaban lengkap>",
      "category": "tokoh" | "alur" | "tema" | "setting" | "nilai_moral",
      "keywords": ["<kata kunci>"],
      "keywords_local": ["<kata kunci bahasa daerah>"]
    }
  ]
}

PENTING:
- narrative_qa MINIMAL 15 pasang, mencakup berbagai tipe pertanyaan:
  * Siapa tokoh utama? Siapa saja karakter dalam cerita?
  * Apa sifat/watak tokoh X?
  * Di mana/kapan cerita ini berlangsung?
  * Apa konflik utama cerita?
  * Apa pesan moral/amanat cerita?
  * Bagaimana akhir cerita?
  * Dari mana asal tokoh X / cerita ini berasal?
- Jika materi bukan cerita (informational), isi characters/plot dengan array kosong
  tapi tetap isi narrative_qa dengan pertanyaan faktual tentang isi materi
- Bahasa: Indonesia baku, mudah dipahami siswa SD/SMP
"""


def generate_narrative_dataset(full_text: str, title: str, lang_code: str, glossary_bilingual: list) -> dict | None:
    """Generate dataset naratif khusus (tokoh, alur, tema, dll.)"""
    kamus_hint = ""
    if glossary_bilingual and lang_code not in ("id", "global"):
        sample = glossary_bilingual[:30]
        kamus_hint = f"\nKamus Daerah (gunakan untuk keywords_local): {json.dumps(sample, ensure_ascii=False)}"

    user_content = f"""
Judul Materi: {title}
Kode Bahasa: {lang_code}
{kamus_hint}

Isi Materi:
{full_text[:5000]}

Ekstrak seluruh informasi naratif dari materi di atas.
Jika ada tokoh/karakter, pastikan semua disebutkan di characters.
Sertakan pertanyaan asal-usul tokoh dan asal daerah cerita di narrative_qa.
"""
    return call_openai(SYSTEM_PROMPT_NARRATIVE, user_content, max_tokens=2500)


# ════════════════════════════════════════════════════════════════════════════
# BUG FIX — SYSTEM_PROMPT_BILINGUAL: format translation_hints dibalik
# SEBELUM (salah): {"kata_indo": "kata_lokal"}   ← AI mengisi ini
# SESUDAH (benar): {"kata_lokal": "kata_indo"}   ← yang dipakai _normalizeQuery() di Dart
#
# Dart _normalizeQuery() melakukan:
#   hints.forEach((local, indo) {
#     result = result.replaceAll(local, indo);  // ganti lokal → indo
#   });
# Artinya key HARUS kata lokal, value HARUS kata Indonesia.
# ════════════════════════════════════════════════════════════════════════════

SYSTEM_PROMPT_BILINGUAL = """
Kamu adalah sistem bilingual untuk aplikasi NusaLearn yang mendukung bahasa daerah Indonesia.
Tugasmu: buat QA pairs bilingual dari knowledge base yang ada.

Output WAJIB JSON:
{
  "bilingual_qa": [
    {
      "question_indo": "<pertanyaan dalam Bahasa Indonesia>",
      "question_local": "<pertanyaan dalam bahasa daerah — gunakan kamus yang disediakan>",
      "answer_indo": "<jawaban dalam Bahasa Indonesia>",
      "answer_local": "<jawaban dalam bahasa daerah>",
      "keywords_indo": ["<keyword Indonesia>"],
      "keywords_local": ["<keyword bahasa daerah>"]
    }
  ],
  "translation_hints": {
    "<kata_lokal>": "<kata_Indonesia>"
  }
}

PENTING untuk translation_hints:
- Key HARUS kata/frasa dalam bahasa DAERAH
- Value HARUS kata/frasa padanannya dalam Bahasa INDONESIA
- Contoh benar: {"ino": "siapa", "tokohu ute": "tokoh utama", "ine-ine": "cerita"}
- Contoh SALAH: {"siapa": "ino"} — ini terbalik dan tidak akan berfungsi
- Sertakan minimal 10 pasang kata yang paling sering muncul dalam pertanyaan siswa

Buat minimal 5 qa bilingual.
Jika tidak ada kamus daerah, isi question_local dan answer_local dengan string kosong
dan translation_hints dengan object kosong {}.
"""

def generate_bilingual_qa(
    qa_pairs: list[dict],
    glossary_bilingual: list[dict],
    language_code: str
) -> dict | None:
    """Buat QA bilingual berdasarkan qa_pairs yang sudah ada dan kamus daerah."""
    if language_code in ("id", "global") or not glossary_bilingual:
        return None

    kamus_sample = glossary_bilingual[:50]
    qa_sample = qa_pairs[:8]

    user_content = f"""
Kode Bahasa Daerah: {language_code}
Kamus Daerah (sample): {json.dumps(kamus_sample, ensure_ascii=False)}

QA Pairs yang perlu ditranslasi:
{json.dumps(qa_sample, ensure_ascii=False)}

Buat versi bilingual dari QA pairs di atas.
INGAT: translation_hints harus berformat {{kata_lokal: kata_Indonesia}},
bukan terbalik. Contoh: {{"ino": "siapa", "tokohu ute": "tokoh utama"}}
"""
    return call_openai(SYSTEM_PROMPT_BILINGUAL, user_content, max_tokens=2000)


# ════════════════════════════════════════════════════════════════════════════
# LAYER 3 — ASSEMBLY & OPTIMIZATION
# ════════════════════════════════════════════════════════════════════════════

def build_inverted_index(chunks_with_tfidf: list[dict]) -> dict[str, list[int]]:
    """
    Buat inverted index: term → [chunk_id, ...].
    Dipakai di Dart untuk RAG lookup O(1) tanpa loop.
    """
    index: dict[str, list[int]] = {}
    for chunk in chunks_with_tfidf:
        for kw in chunk.get("keywords", []):
            if kw not in index:
                index[kw] = []
            index[kw].append(chunk["chunk_id"])
    return index


def extract_all_text(full_json: dict) -> str:
    """Gabungkan semua teks dari content_structured."""
    parts = []

    km = full_json.get("knowledge_map", {})
    if km.get("context_summary"):
        parts.append(km["context_summary"])

    for section in full_json.get("content_structured", []):
        for chunk in section.get("chunks", []):
            text = chunk.get("content") or chunk.get("term") or ""
            if text:
                parts.append(text)

    return " ".join(parts)


def compute_content_hash(text: str) -> str:
    return hashlib.md5(text.encode("utf-8")).hexdigest()[:12]

# ════════════════════════════════════════════════════════════════════════════
# MAIN PIPELINE
# ════════════════════════════════════════════════════════════════════════════

def _sanitize_narrative(data: dict) -> dict:
    """
    Pastikan semua field yang diharapkan Map tidak berisi List.
    GPT kadang mengisi plot: [] untuk materi non-naratif.
    """
    if isinstance(data.get('plot'), list):
        data['plot'] = {}
    # Pastikan field List memang List
    for field in ('characters', 'themes', 'moral_values', 'narrative_qa'):
        if not isinstance(data.get(field), list):
            data[field] = []
    return data

# ════════════════════════════════════════════════════════════════════════════
# MAIN PIPELINE
# ════════════════════════════════════════════════════════════════════════════

def process_material(material_id: int) -> bool:
    print(f"\n{'='*60}")
    print(f"[START] NusaLearn AI Processor v2 — Material ID: {material_id}")
    print(f"{'='*60}")

    conn = cursor = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        # ── 1. Fetch materi ──────────────────────────────────────────────
        cursor.execute(
            "SELECT konten, judul, kode_bahasa FROM materi WHERE id = %s",
            (material_id,)
        )
        row = cursor.fetchone()
        if not row:
            print(f"[ERROR] Material {material_id} tidak ditemukan.")
            return False

        title        = row["judul"] or "Untitled"
        lang_code    = row["kode_bahasa"] or "id"

        raw_content = row["konten"]
        while isinstance(raw_content, str):
            try:
                raw_content = json.loads(raw_content)
            except json.JSONDecodeError:
                break
                
        full_json = raw_content
        
        if not isinstance(full_json, dict):
            print(f"[ERROR] Struktur content_indo tidak valid. Diharapkan Dictionary, didapatkan {type(full_json)}")
            return False
        full_text    = extract_all_text(full_json)
        content_hash = compute_content_hash(full_text)

        print(f"[INFO] Materi: '{title}' | Bahasa: {lang_code}")
        print(f"[INFO] Panjang teks: {len(full_text)} karakter")

        # ── 2. Cloud Knowledge Extraction ───────────────────────────────
        print("[STEP 1] Memanggil Cloud AI untuk Knowledge Extraction...")
        ai_knowledge = extract_knowledge_with_ai(full_text, title)

        if ai_knowledge:
            print(f"[OK] Dapat: {len(ai_knowledge.get('concepts',[]))} concepts, "
                  f"{len(ai_knowledge.get('qa_pairs',[]))} qa_pairs, "
                  f"{len(ai_knowledge.get('glossary',[]))} glossary")
        else:
            print("[WARN] Cloud AI gagal → pakai fallback local NLP")
            km = full_json.get("knowledge_map", {})
            ai_knowledge = {
                "summary": km.get("context_summary", ""),
                "detailed_summary": km.get("context_summary", ""),
                "concepts": [
                    {"term": c.get("term", ""), "explanation": c.get("explanation", ""),
                     "examples": [], "related_terms": []}
                    for c in km.get("key_concepts", [])
                ],
                "qa_pairs": [],
                "glossary": full_json.get("glossary_bilingual", []),
                "key_facts": [],
                "themes": km.get("themes", []),
                "difficulty_hints": {"easy_questions": [], "hard_questions": []},
            }

        # ── 3. TF-IDF Chunking ───────────────────────────────────────────
        print("[STEP 2] Membangun chunk + TF-IDF index...")
        raw_chunks = chunk_text(full_text, max_chars=350)
        enriched_chunks = compute_tfidf_scores(raw_chunks)
        inverted_index = build_inverted_index(enriched_chunks)

        print(f"[OK] {len(enriched_chunks)} chunks, {len(inverted_index)} terms di index")

        # ── 4. Bilingual QA (opsional) ───────────────────────────────────
        bilingual_data = None
        if lang_code not in ("id", "global"):
            print(f"[STEP 3] Generating bilingual QA untuk bahasa: {lang_code}...")
            glossary_bilingual = full_json.get("glossary_bilingual", [])
            if not glossary_bilingual:
                glossary_bilingual = [
                    {"term": g.get("term", ""), "term_local": g.get("term_local", "")}
                    for g in ai_knowledge.get("glossary", [])
                    if g.get("term_local")
                ]
            bilingual_data = generate_bilingual_qa(
                ai_knowledge.get("qa_pairs", []),
                glossary_bilingual,
                lang_code,
            )
            if bilingual_data:
                print(f"[OK] {len(bilingual_data.get('bilingual_qa',[]))} bilingual QA pairs")
                print(f"[OK] {len(bilingual_data.get('translation_hints',{}))} translation hints")

        # ── 4b. Narrative Dataset ────────────────────────────────────────
        # BUG FIX: blok ini sebelumnya punya indentasi salah (ada di dalam
        # blok if lang_code). Sekarang sejajar dengan STEP lain di level try.
        print("[STEP 4b] Generating Narrative Dataset...")
        glossary_bilingual_nd = full_json.get("glossary_bilingual", [])
        narrative_data = generate_narrative_dataset(full_text, title, lang_code, glossary_bilingual_nd)

        if narrative_data:
            narrative_data = _sanitize_narrative(narrative_data)
            print(f"[OK] Narrative: {len(narrative_data.get('characters', []))} karakter, "
                  f"{len(narrative_data.get('narrative_qa', []))} QA naratif")
        else:
            print("[WARN] Narrative dataset gagal di-generate → pakai fallback kosong")
            narrative_data = {
                "content_type": "informational",
                "characters": [],
                "plot": {},
                "themes": [],
                "moral_values": [],
                "narrative_qa": [],
            }

        # ── 5. Rakitan final ai_embeddings ───────────────────────────────
        print("[STEP 5] Merakit struktur final ai_embeddings...")

        knowledge_base = {
            "summary":          ai_knowledge.get("summary", ""),
            "detailed_summary": ai_knowledge.get("detailed_summary", ""),
            "concepts":         ai_knowledge.get("concepts", []),
            "glossary":         ai_knowledge.get("glossary", []),
            "key_facts":        ai_knowledge.get("key_facts", []),
            "themes":           ai_knowledge.get("themes", []),
            "difficulty_hints": ai_knowledge.get("difficulty_hints", {}),
        }

        ai_embeddings = {
            "schema_version":  "2.1",
            "processed_at":    datetime.now().isoformat(),
            "content_hash":    content_hash,
            "language_code":   lang_code,
            "narrative_dataset": narrative_data,

            # Dipakai oleh LocalAIControllerService (kompatibel versi lama)
            "knowledge_base":  knowledge_base,

            # QA Pairs untuk RAG lookup
            "qa_pairs":        ai_knowledge.get("qa_pairs", []),

            # TF-IDF chunks untuk Local RAG
            "chunks":          enriched_chunks,

            # Inverted index untuk O(1) retrieval
            "inverted_index":  inverted_index,

            # Bilingual support
            "bilingual_qa":    bilingual_data.get("bilingual_qa", []) if bilingual_data else [],
            "translation_hints": bilingual_data.get("translation_hints", {}) if bilingual_data else {},
        }

        ai_embeddings_json = json.dumps(ai_embeddings, ensure_ascii=False)
        size_kb = len(ai_embeddings_json.encode("utf-8")) / 1024

        print(f"[INFO] Ukuran ai_embeddings: {size_kb:.1f} KB")

        # ── 6. Simpan ke DB ──────────────────────────────────────────────
        cursor.execute(
            "UPDATE materi SET ai_embeddings = %s, status_ai = 'ready', "
            "ai_diproses_pada = NOW() WHERE id = %s",
            (ai_embeddings_json, material_id),
        )
        conn.commit()

        print(f"\n[SUCCESS] Material {material_id} berhasil diproses!")
        print(f"  • Concepts      : {len(knowledge_base['concepts'])}")
        print(f"  • QA Pairs      : {len(ai_embeddings['qa_pairs'])}")
        print(f"  • Chunks        : {len(enriched_chunks)}")
        print(f"  • Index terms   : {len(inverted_index)}")
        print(f"  • Bilingual     : {len(ai_embeddings['bilingual_qa'])} pairs")
        print(f"  • Trans. hints  : {len(ai_embeddings['translation_hints'])} kata")
        print(f"  • Characters    : {len(narrative_data.get('characters', []))}")
        print(f"  • Narrative QA  : {len(narrative_data.get('narrative_qa', []))}")
        print(f"  • Size          : {size_kb:.1f} KB")
        return True

    except mysql.connector.Error as err:
        print(f"[DB ERROR] {err}")
        return False
    except Exception as e:
        import traceback
        print(f"[SYSTEM ERROR] {e}")
        traceback.print_exc()
        if conn and conn.is_connected() and cursor:
            try:
                cursor.execute(
                    "UPDATE materi SET status_ai = 'failed' WHERE id = %s",
                    (material_id,),
                )
                conn.commit()
            except Exception:
                pass
        return False
    finally:
        if cursor:
            cursor.close()
        if conn and conn.is_connected():
            conn.close()


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("[USAGE] python ai_processor.py <material_id>")
        sys.exit(1)
    success = process_material(int(sys.argv[1]))
    sys.exit(0 if success else 1)
