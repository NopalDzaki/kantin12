<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #e1e4e8;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2980b9;
            padding-bottom: 15px;
        }

        h2 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }

        .info {
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .info-item {
            margin-bottom: 8px;
        }

        .info-item strong {
            display: inline-block;
            min-width: 100px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 15px;
        }

        thead {
            background-color: #2980b9;
            color: white;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }

        .text-right {
            text-align: right;
        }

        .summary {
            margin-top: 30px;
            font-size: 16px;
            border-top: 2px solid #ddd;
            padding-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .discount {
            color: #e74c3c;
            font-weight: bold;
        }

        .total-row {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #777;
            font-style: italic;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
                padding: 15px;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            th, td {
                padding: 8px 10px;
                font-size: 14px;
            }
            .info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>NOTA TRANSAKSI KANTIN SEKOLAH</h2>
            <h3>SMK Telkom Malang </h3>
            <p> Jl. Danau Ranau, Sawojajar, Kec. Kedungkandang, Kota Malang, Jawa Timur 65139</p>
        </div>

        <div class="info">
            <div>
                <div class="info-item">
                    <strong>Nama Siswa:</strong> {{ $transaksi->siswa->nama ?? '-' }}
                </div>
            </div>
            <div>
                <div class="info-item">
                    <strong>Stan:</strong> {{ $transaksi->stan->nama_stan ?? '-' }}
                </div>
                <div class="info-item">
                    <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaksi->tanggal)->translatedFormat('d F Y H:i') }}
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Menu</th>
                    <th>Qty</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDiskon = 0;
                    $subtotal = 0;
                @endphp
                @foreach($transaksi->detailTransaksi as $item)
                    @php
                        $hargaAsli = $item->harga_beli * $item->qty;
                        $hargaDiskon = $item->menu->harga_setelah_diskon * $item->qty;
                        $subtotal += $hargaAsli;
                        $totalDiskon += $hargaAsli - $hargaDiskon;
                    @endphp
                    <tr>
                        <td>{{ $item->menu->nama_makanan }}</td>
                        <td>{{ $item->qty }}</td>
                        <td class="text-right">Rp{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                        <td class="text-right">Rp{{ number_format($hargaAsli, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Diskon:</span>
                <span class="discount">- Rp{{ number_format($totalDiskon, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row total-row">
                <span>TOTAL:</span>
                <span>Rp{{ number_format($subtotal - $totalDiskon, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <p>Terima kasih telah berbelanja di kantin sekolah</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        </div>
    </div>
</body>
</html>