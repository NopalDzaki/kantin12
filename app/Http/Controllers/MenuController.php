<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Stan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        try {
            $menu = Menu::with('stan')->get();

            $menu->transform(function ($item) {
                if (!empty($item['foto'])) {
                    $item['foto'] = url('storage/' . $item['foto']);
                }
                return $item;
            });

            return response()->json([
                'status' => 'success',
                'data' => $menu,
                'message' => 'Berhasil mengambil data menu'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data menu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk menambah menu'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'nama_makanan' => 'required|string|max:100',
                'harga' => 'required|numeric',
                'jenis' => 'required|in:makanan,minuman',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'deskripsi' => 'nullable|string',
                'id_stan' => 'required|exists:stan,id'
            ]);

            if (Auth::user()->stan->id != $validated['id_stan']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa menambah menu untuk stan Anda sendiri'
                ], 403);
            }

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();

                Storage::makeDirectory('public/menu');
                $path = $file->storeAs('public/menu', $filename);
                if (!$path) {
                    throw new \Exception('Gagal mengupload file');
                }

                $validated['foto'] = 'menu/' . $filename;
            }

            $menu = Menu::create($validated);

            if ($menu->foto) {
                $menu->foto = url('storage/' . $menu->foto);
            }

            return response()->json([
                'status' => 'success',
                'data' => $menu,
                'message' => 'Menu berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            if (isset($filename) && Storage::exists('public/menu/' . $filename)) {
                Storage::delete('public/menu/' . $filename);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan menu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $menu = Menu::with('stan')->findOrFail($id);

            if ($menu->foto) {
                $menu->foto = url('storage/' . $menu->foto);
            }

            return response()->json([
                'status' => 'success',
                'data' => $menu,
                'message' => 'Detail menu berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Menu tidak ditemukan'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk mengubah menu'
            ], 403);
        }

        try {
            $menu = Menu::findOrFail($id);

            if (Auth::user()->stan->id != $menu->id_stan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa mengubah menu stan Anda sendiri'
                ], 403);
            }

            $validated = $request->validate([
                'nama_makanan' => 'required|string|max:100',
                'harga' => 'required|numeric',
                'jenis' => 'required|in:makanan,minuman',
                'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'deskripsi' => 'nullable|string',
                'id_stan' => 'required|exists:stan,id'
            ]);

            if ($request->hasFile('foto')) {
                if ($menu->foto && Storage::exists('public/' . $menu->foto)) {
                    Storage::delete('public/' . $menu->foto);
                }

                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs('public/menu', $filename);
                if (!$path) {
                    throw new \Exception('Gagal mengupload file');
                }

                $validated['foto'] = 'menu/' . $filename;
            }

            $menu->update($validated);

            if ($menu->foto) {
                $menu->foto = url('storage/' . $menu->foto);
            }

            return response()->json([
                'status' => 'success',
                'data' => $menu,
                'message' => 'Menu berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui menu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin_stan') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk menghapus menu'
            ], 403);
        }

        try {
            $menu = Menu::findOrFail($id);

            if (Auth::user()->stan->id != $menu->id_stan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa menghapus menu stan Anda sendiri'
                ], 403);
            }

            if ($menu->foto && Storage::exists('public/' . $menu->foto)) {
                Storage::delete('public/' . $menu->foto);
            }

            $menu->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Menu berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus menu: ' . $e->getMessage()
            ], 500);
        }
    }
}
// nopaldzaki