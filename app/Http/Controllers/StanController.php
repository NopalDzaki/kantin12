<?php

namespace App\Http\Controllers;

use App\Models\Stan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StanController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silahkan login terlebih dahulu'
            ], 401);
        }

        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk melihat data stan karena anda bukan admin stan'
            ], 403);
        }

        try {
            $stan = Stan::with('user')->get();
            return response()->json([
                'status' => 'success',
                'data' => $stan,
                'message' => 'Data stan berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data stan: ' . $e->getMessage()
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

        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk melihat detail stan'
            ], 403);
        }

        try {
            $stan = Stan::with('user')->findOrFail($id);

            // Pastikan admin_stan hanya bisa melihat stannya sendiri
            if (Auth::user()->stan->id != $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa melihat stan Anda sendiri'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $stan,
                'message' => 'Detail stan berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stan tidak ditemukan'
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

        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk menambah stan'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'nama_stan' => 'required|string|max:100',
                'nama_pemilik' => 'required|string|max:100',
                'Telp' => 'nullable|string|max:20',
                'id_user' => 'required|exists:users,id'
            ]);

            if (Stan::where('id_user', $validated['id_user'])->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User ini sudah memiliki stan'
                ], 400);
            }

            $stan = Stan::create($validated);

            return response()->json([
                'status' => 'success',
                'data' => $stan,
                'message' => 'Stan berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan stan: ' . $e->getMessage()
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

        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk mengubah stan'
            ], 403);
        }

        try {
            $stan = Stan::findOrFail($id);

            if (Auth::user()->stan->id != $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa mengubah stan Anda sendiri'
                ], 403);
            }

            $validated = $request->validate([
                'nama_stan' => 'required|string|max:100',
                'nama_pemilik' => 'required|string|max:100',
                'Telp' => 'nullable|string|max:20',
            ]);

            $stan->update($validated);

            return response()->json([
                'status' => 'success',
                'data' => $stan,
                'message' => 'Stan berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui stan: ' . $e->getMessage()
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

        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk menghapus stan'
            ], 403);
        }

        try {
            $stan = Stan::findOrFail($id);

            if (Auth::user()->stan->id != $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa menghapus stan Anda sendiri'
                ], 403);
            }

            $stan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Stan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus stan: ' . $e->getMessage()
            ], 500);
        }
    }
}
// by nopaldzaki