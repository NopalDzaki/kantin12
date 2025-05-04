<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class TransaksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getdetailall()
    {
        try {
            $user = Auth::user();

            if ($user->role === 'admin_stan') {
                $transaksi = Transaksi::with(['detailTransaksi.menu', 'siswa'])
                    ->where('id_stan', $user->stan->id)
                    ->get();
            } else {
                $transaksi = Transaksi::with(['detailTransaksi.menu', 'stan'])
                    ->where('id_siswa', $user->siswa->id)
                    ->get();
            }

            return response()->json([
                'status' => 'success',
                'data' => $transaksi
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getdetail($id)
    {
        try {
            $user = Auth::user();
            $transaksi = Transaksi::with(['detailTransaksi.menu', 'siswa', 'stan'])->findOrFail($id);

            if ($user->role === 'admin_stan' && $transaksi->id_stan !== $user->stan->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($user->role === 'siswa' && $transaksi->id_siswa !== $user->siswa->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $transaksi
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function order(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'siswa') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only students can create orders'
                ], 403);
            }

            $validated = $request->validate([
                'id_stan' => 'required|exists:stan,id',
                'items' => 'required|array',
                'items.*.id_menu' => 'required|exists:menu,id',
                'items.*.qty' => 'required|integer|min:1'
            ]);

            DB::beginTransaction();

            $transaksi = Transaksi::create([
                'tanggal' => now(),
                'id_stan' => $validated['id_stan'],
                'id_siswa' => $user->siswa->id,
                'status' => 'belum dikonfirm'
            ]);

            foreach ($validated['items'] as $item) {
                $menu = Menu::findOrFail($item['id_menu']);
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id,
                    'id_menu' => $item['id_menu'],
                    'qty' => $item['qty'],
                    'harga_beli' => $menu->harga
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $transaksi->load('detailTransaksi.menu'),
                'message' => 'Order created successfully'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tambahitem(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'siswa') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya siswa yang dapat menambah item'
                ], 403);
            }

            $transaksi = Transaksi::findOrFail($id);

            if ($transaksi->id_siswa !== $user->siswa->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke transaksi ini'
                ], 403);
            }

            if ($transaksi->status !== 'belum dikonfirm') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menambah item pada pesanan yang sudah dikonfirmasi'
                ], 400);
            }

            $validated = $request->validate([
                'id_menu' => 'required|exists:menu,id',
                'qty' => 'required|integer|min:1'
            ]);

            $menu = Menu::findOrFail($validated['id_menu']);

            if ($menu->id_stan !== $transaksi->id_stan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Menu harus dari stan yang sama'
                ], 400);
            }

            $existingItem = DetailTransaksi::where('id_transaksi', $transaksi->id)
                ->where('id_menu', $validated['id_menu'])
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'qty' => $existingItem->qty + $validated['qty']
                ]);
                $detailTransaksi = $existingItem;
            } else {
                $detailTransaksi = DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id,
                    'id_menu' => $validated['id_menu'],
                    'qty' => $validated['qty'],
                    'harga_beli' => $menu->harga
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $detailTransaksi->load(['menu' => function ($query) {
                    $query->with('diskon');
                }]),
                'message' => 'Item berhasil ditambahkan'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only stan admin can update order status'
                ], 403);
            }

            $transaksi = Transaksi::findOrFail($id);

            if ($transaksi->id_stan !== $user->stan->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validated = $request->validate([
                'status' => 'required|in:belum dikonfirm,dimasak,diantar,sampai'
            ]);

            $transaksi->update($validated);

            return response()->json([
                'status' => 'success',
                'data' => $transaksi,
                'message' => 'Order status updated successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $transaksi = Transaksi::findOrFail($id);

            if ($user->role === 'siswa' && $transaksi->id_siswa !== $user->siswa->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($user->role === 'admin_stan' && $transaksi->id_stan !== $user->stan->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($transaksi->status !== 'belum dikonfirm') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete confirmed order'
                ], 400);
            }

            $transaksi->detailTransaksi()->delete();
            $transaksi->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Order deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getHistoriByBulan(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'siswa') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya siswa yang dapat melihat histori transaksi'
                ], 403);
            }

            $validated = $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000'
            ]);

            $transaksi = Transaksi::with(['detailTransaksi.menu', 'stan'])
                ->where('id_siswa', $user->siswa->id)
                ->whereMonth('tanggal', $validated['bulan'])
                ->whereYear('tanggal', $validated['tahun'])
                ->orderBy('tanggal', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $transaksi,
                'message' => 'Histori transaksi berhasil diambil'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cetakNota($id)
    {
        try {
            $user = Auth::user();
            $transaksi = Transaksi::with(['detailTransaksi.menu', 'siswa', 'stan'])->findOrFail($id);

            if ($user->role === 'siswa' && $transaksi->id_siswa !== $user->siswa->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses ke transaksi ini'
                ], 403);
            }

            $data = [
                'transaksi' => $transaksi,
                'total' => $transaksi->detailTransaksi->sum(function($detail) {
                    return $detail->qty * $detail->harga_beli;
                })
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Data nota berhasil diambil'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getPesananByBulan(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya admin stan yang dapat melihat data pemesanan'
                ], 403);
            }

            $validated = $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000'
            ]);

            $transaksi = Transaksi::with(['detailTransaksi.menu', 'siswa'])
                ->where('id_stan', $user->stan->id)
                ->whereMonth('tanggal', $validated['bulan'])
                ->whereYear('tanggal', $validated['tahun'])
                ->orderBy('tanggal', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $transaksi,
                'message' => 'Data pemesanan berhasil diambil'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getRekapPemasukan(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user->role !== 'admin_stan') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Hanya admin stan yang dapat melihat rekap pemasukan'
                ], 403);
            }

            $validated = $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2000'
            ]);

            $transaksi = Transaksi::with(['detailTransaksi.menu'])
                ->where('id_stan', $user->stan->id)
                ->whereMonth('tanggal', $validated['bulan'])
                ->whereYear('tanggal', $validated['tahun'])
                ->get();

            $totalPemasukan = 0;
            $jumlahTransaksi = $transaksi->count();
            $detailPemasukan = [];

            foreach ($transaksi as $t) {
                $totalTransaksi = $t->detailTransaksi->sum(function($detail) {
                    return $detail->qty * $detail->harga_beli;
                });
                $totalPemasukan += $totalTransaksi;

                $detailPemasukan[] = [
                    'tanggal' => $t->tanggal,
                    'total' => $totalTransaksi,
                    'status' => $t->status
                ];
            }

            $data = [
                'total_pemasukan' => $totalPemasukan,
                'jumlah_transaksi' => $jumlahTransaksi,
                'detail_pemasukan' => $detailPemasukan,
                'bulan' => $validated['bulan'],
                'tahun' => $validated['tahun']
            ];

            return response()->json([
                'status' => 'success',
                'data' => $data,
                'message' => 'Rekap pemasukan berhasil diambil'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
