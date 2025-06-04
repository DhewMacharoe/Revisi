<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function index()
    {
        $admin = auth()->user();
        return view('profil.index', compact('admin'));
    }

    public function update(Request $request)
    {
        $admin = auth()->user();
    
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:admin,email,' . $admin->id,
            'password' => 'nullable|confirmed|min:6',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
    
        $admin->nama = $request->nama;
        $admin->email = $request->email;
    
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->store('foto_admin', 'public');
            $admin->foto = $foto;
        }
    
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }
    
        $admin->save();
    
        return back()->with('success', 'Profil berhasil diperbarui.');
    }
    
}
