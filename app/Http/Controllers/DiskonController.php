<?php

namespace App\Http\Controllers;

use App\Models\Diskon;
use App\Models\Menu;
use App\Models\MenuDiskon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DiskonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            if (Auth::user()->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda bukan admin stan!, Anda tidak memiliki akses untuk melihat data diskon'
                ], 403);
            }

            $diskon = Diskon::with([
                'menu' => function ($query) {
                    $query->select('menu.*')
                        ->addSelect(DB::raw('ROUND(menu.harga - (menu.harga * diskon.persentase_diskon / 100), 2) as harga_setelah_diskon'));
                },
                'stan'
            ])
                ->where('id_stan', Auth::user()->stan->id)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $diskon,
                'message' => 'Data diskon berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data diskon: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        return view('diskon.create');
    }

    public function store(Request $request)
    {
        try {
            if (Auth::user()->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda bukan admin stan!, Anda tidak memiliki akses untuk menambah diskon'
                ], 403);
            }

            $validated = $request->validate([
                'nama_diskon' => 'required|string|max:255',
                'persentase_diskon' => 'required|numeric|min:0|max:100',
                'tanggal_awal' => 'required|date',
                'tanggal_akhir' => 'required|date|after:tanggal_awal',
                'menu_ids' => 'required|array',
                'menu_ids.*' => 'exists:menu,id'
            ]);

            $validated['id_stan'] = Auth::user()->stan->id;

            $menuIds = $request->menu_ids;
            $validMenus = Menu::whereIn('id', $menuIds)
                ->where('id_stan', Auth::user()->stan->id)
                ->pluck('id');

            if (count($validMenus) !== count($menuIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Beberapa menu yang dipilih bukan milik stan Anda'
                ], 400);
            }

            $diskonData = [
                'nama_diskon' => $validated['nama_diskon'],
                'persentase_diskon' => $validated['persentase_diskon'],
                'tanggal_awal' => $validated['tanggal_awal'],
                'tanggal_akhir' => $validated['tanggal_akhir'],
                'id_stan' => $validated['id_stan']
            ];

            $diskon = Diskon::create($diskonData);
            $diskon->menu()->attach($validMenus);

            return response()->json([
                'status' => 'success',
                'data' => $diskon->load('menu'),
                'message' => 'Diskon berhasil ditambahkan'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan diskon: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            if (Auth::user()->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda bukan admin stan!, Anda tidak memiliki akses untuk melihat detail diskon'
                ], 403);
            }

            $diskon = Diskon::with([
                'menu' => function ($query) {
                    $query->select('menu.*')
                        ->addSelect(DB::raw('ROUND(menu.harga - (menu.harga * diskon.persentase_diskon / 100), 2) as harga_setelah_diskon'));
                },
                'stan'
            ])->findOrFail($id);

            if ($diskon->id_stan !== Auth::user()->stan->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa melihat diskon stan Anda sendiri'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $diskon,
                'message' => 'Detail diskon berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Diskon tidak ditemukan'
            ], 404);
        }
    }

    public function edit($id)
    {
        $diskon = Diskon::findOrFail($id);
        return view('diskon.edit', compact('diskon'));
    }

    public function update(Request $request, $id)
    {
        try {
            if (Auth::user()->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda bukan admin stan!, Anda tidak memiliki akses untuk mengubah diskon'
                ], 403);
            }

            $diskon = Diskon::findOrFail($id);

            if ($diskon->id_stan !== Auth::user()->stan->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa mengubah diskon stan Anda sendiri'
                ], 403);
            }

            $validated = $request->validate([
                'nama_diskon' => 'required|string|max:255',
                'persentase_diskon' => 'required|numeric|min:0|max:100',
                'tanggal_awal' => 'required|date',
                'tanggal_akhir' => 'required|date|after:tanggal_awal',
                'menu_ids' => 'required|array',
                'menu_ids.*' => 'exists:menu,id'
            ]);

            $menuIds = $request->menu_ids;
            $validMenus = Menu::whereIn('id', $menuIds)
                ->where('id_stan', Auth::user()->stan->id)
                ->pluck('id');

            if (count($validMenus) !== count($menuIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Beberapa menu yang dipilih bukan milik stan Anda'
                ], 400);
            }

            $diskon->update($validated);
            $diskon->menu()->sync($validMenus);

            return response()->json([
                'status' => 'success',
                'data' => $diskon->load('menu'),
                'message' => 'Diskon berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui diskon: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (Auth::user()->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda bukan admin stan!, Anda tidak memiliki akses untuk menghapus diskon'
                ], 403);
            }

            $diskon = Diskon::findOrFail($id);

            if ($diskon->id_stan !== Auth::user()->stan->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda hanya bisa menghapus diskon stan Anda sendiri'
                ], 403);
            }

            $diskon->menu()->detach();
            $diskon->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Diskon berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus diskon: ' . $e->getMessage()
            ], 500);
        }
    }
}
