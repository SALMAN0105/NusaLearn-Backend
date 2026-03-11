<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'siswa');

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $students = $query->latest()->paginate(15);
        return view('admin.students', compact('students'));
    }


    public function destroy(User $student)
    {
        $student->delete();
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}