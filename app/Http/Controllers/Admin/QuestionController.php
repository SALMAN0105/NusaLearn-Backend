<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Material;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::select('id', 'title_indo')->get();
        $query = Question::with('material');

        if ($request->has('material_id') && $request->material_id != 'all') {
            $query->where('material_id', $request->material_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('question_text_indo', 'LIKE', '%' . $request->search . '%');
        }

        $questions = $query->latest()->paginate(10)->withQueryString();

        return view('admin.questions', compact('questions', 'materials'));
    }

    public function store(Request $request)
    {
        // 1. Validasi (Hapus tolaki)
        $request->validate([
            'material_id' => 'required|exists:materials,id',
            'question_text_indo' => 'required|string',
            'difficulty_weight' => 'required|integer|min:1|max:5',
            'option_a' => 'required',
            'option_b' => 'required',
            'option_c' => 'required',
            'option_d' => 'required',
            'correct_answer_key' => 'required|in:a,b,c,d',
        ]);

        // 2. Format JSON
        $options = [
            ['id' => 'a', 'text' => $request->option_a],
            ['id' => 'b', 'text' => $request->option_b],
            ['id' => 'c', 'text' => $request->option_c],
            ['id' => 'd', 'text' => $request->option_d],
        ];

        // 3. Simpan
        Question::create([
            'material_id' => $request->material_id,
            'question_text_indo' => $request->question_text_indo,
            // question_text_tolaki otomatis NULL
            'options_json' => $options,
            'correct_answer_key' => $request->correct_answer_key,
            'difficulty_weight' => $request->difficulty_weight,
        ]);

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }
}