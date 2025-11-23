<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // READ: Tampilkan List User
    public function index()
    {
        // Urutkan user terbaru di atas
        $users = User::orderBy('created_at', 'desc')->get();
        return view('users.index', compact('users'));
    }

    // CREATE: Simpan User Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,operator'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi Password
            'role' => $request->role
        ]);

        return back()->with('success', 'User baru berhasil ditambahkan.');
    }

    // UPDATE: Simpan Perubahan User
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            // Validasi unik email, KECUALI untuk ID user ini sendiri
            'email' => 'required|email|unique:users,email,'.$id, 
            'role' => 'required|in:admin,operator',
            'password' => 'nullable|min:6' // Password opsional saat edit
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // Hanya update password jika admin mengisinya
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Data user diperbarui.');
    }

    // DELETE: Hapus User
    public function destroy($id)
    {
        // Cegah hapus diri sendiri
        if (Auth::user()->id == $id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        User::destroy($id);
        return back()->with('success', 'User berhasil dihapus.');
    }
}