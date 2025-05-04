<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiswaController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login terlebih dahulu'
            ], 401);
        }

        try {
            $siswa = Siswa::with('user')->get();

            $siswa->transform(function ($item) {
                if (!empty($item['foto'])) {
                    $item['foto'] = url('storage/' . $item['foto']);
                }
                return $item;
            });

            return response()->json([
                'status' => 'success',
                'data' => $siswa,
                'message' => 'Data siswa berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login terlebih dahulu'
            ], 401);
        }

        try {
            $siswa = Siswa::with('user')->findOrFail($id);

            if ($siswa->foto) {
                $siswa->foto = url('storage/' . $siswa->foto);
            }

            return response()->json([
                'status' => 'success',
                'data' => $siswa,
                'message' => 'Detail siswa berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Siswa tidak ditemukan'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login terlebih dahulu'
            ], 401);
        }

        try {
            $validated = $request->validate([
                'nama_siswa' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'telp' => 'nullable|string|max:20',
                'id_user' => 'required|exists:users,id',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/siswa', $filename);

                if (!$path) {
                    throw new \Exception('Gagal mengupload file');
                }

                $validated['foto'] = 'siswa/' . $filename;
            }

            $siswa = Siswa::create($validated);

            if ($siswa->foto) {
                $siswa->foto = url('storage/' . $siswa->foto);
            }

            return response()->json([
                'status' => 'success',
                'data' => $siswa,
                'message' => 'Siswa berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login terlebih dahulu'
            ], 401);
        }

        try {
            $siswa = Siswa::findOrFail($id);

            $validated = $request->validate([
                'nama_siswa' => 'required|string|max:255',
                'alamat' => 'nullable|string',
                'telp' => 'nullable|string|max:20',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($request->hasFile('foto')) {
                if ($siswa->foto && Storage::exists('public/' . $siswa->foto)) {
                    Storage::delete('public/' . $siswa->foto);
                }

                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/siswa', $filename);

                if (!$path) {
                    throw new \Exception('Gagal mengupload file');
                }

                $validated['foto'] = 'siswa/' . $filename;
            }

            $siswa->update($validated);

            if ($siswa->foto) {
                $siswa->foto = url('storage/' . $siswa->foto);
            }

            return response()->json([
                'status' => 'success',
                'data' => $siswa,
                'message' => 'Data siswa berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengupdate siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login terlebih dahulu'
            ], 401);
        }

        try {
            $siswa = Siswa::findOrFail($id);

            if ($siswa->foto && Storage::exists('public/' . $siswa->foto)) {
                Storage::delete('public/' . $siswa->foto);
            }

            $siswa->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Siswa berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus siswa: ' . $e->getMessage()
            ], 500);
        }
    }
}
