<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Home</title>

        @fonts

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
            </style>
        @endif
        <style>
            /* Hilangkan tombol panah atas/bawah pada input type number */
            input[type=number]::-webkit-inner-spin-button,
            input[type=number]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
                appearance: textfield;
            }

            .clickable-row {
                cursor: pointer;
                transition: background-color 0.15s ease;
            }
            .clickable-row:hover {
                background-color: #f3f4f6;
            }
            .modal-content-lg {
                max-width: 600px !important;
                max-height: 90vh;
                overflow-y: auto;
            }
            .form-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem 1rem;
                padding: 1rem 1.5rem;
            }
            .form-grid .full-width {
                grid-column: span 2;
            }
            .form-grid .form-group {
                padding: 0;
            }
            .info-gaji {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 7px;
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
                color: #475569;
            }
        </style>
    </head>
    <body>
        <x-navbar />

        <div style="padding-top: 10px;">
            <input type="text" class="searchMain" placeholder="Cari...">
            <table style="width: calc(100% - 2rem);" class="name">
                <tbody>
                    @foreach ($rekapGaji as $data)
                    <tr class="clickable-row" onclick="openEditModal(this)" data-transaksi="{{ json_encode($data) }}" title="Klik untuk edit data">
                        <td>{{ $data->nama }}</td>
                        <td>{{ $data->absen ?? 0 }}</td>
                        <td>{{ $data->absen_2 ?? 0 }}</td>
                        <td>Rp {{ number_format($data->gaji,0,',','.') }}</td>
                        <td>Rp {{ number_format(($data->absen ?? 0) * ($data->gaji ?? 0),0,',','.') }}</td>
                        <td>Rp {{ number_format($data->bonbonan ?? 0,0,',','.') }}</td>
                        <td>Rp {{ number_format($data->bon ?? 0,0,',','.') }}</td>
                        <td>{{ !empty($data->bon_lain) ? 'Rp ' . number_format($data->bon_lain, 0, ',', '.') : '' }}</td>
                        <td>{{ $data->keterangan_bon_lain ?? '' }}</td>
                        <td>{{ !empty($data->bonus) ? 'Rp ' . number_format($data->bonus, 0, ',', '.') : '' }}</td>
                        <td>Rp {{ number_format($data->hasil_akhir ?? 0,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Modal Edit Absensi & Transaksi -->
        <div id="modalEditTransaksi" class="modal">
            <div class="modal-content modal-content-lg">
                <div class="modal-header">
                    <div>
                        <h3>Edit Data: <span id="modalNamaTukang" style="font-weight: 700;"></span></h3>
                        <div id="modalGajiInfo" class="info-gaji" style="margin-top: 0.5rem;">
                            Tarif Harian: <strong id="displayTarifHarian">Rp 0</strong>
                        </div>
                    </div>
                    <span class="close" onclick="closeEditModal()">&times;</span>
                </div>
                <form id="formEditTransaksi" action="{{ route('transaksi.save') }}" method="POST">
                    @csrf
                    <input type="hidden" id="edit_id_tukang" name="id_tukang">
                    <input type="hidden" id="edit_id_periode" name="id_periode" value="{{ $periodeSekarang->id_periode ?? '' }}">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_absen">Absen 1 (Hari)</label>
                            <input type="number" step="0.5" id="edit_absen" name="absen" class="form-input" placeholder="0" oninput="calculateHasilAkhir()">
                        </div>
                        <div class="form-group">
                            <label for="edit_absen_2">Absen 2 (Hari)</label>
                            <input type="number" step="0.5" id="edit_absen_2" name="absen_2" class="form-input" placeholder="0" oninput="calculateHasilAkhir()">
                        </div>

                        <div class="form-group">
                            <label for="edit_bonbonan">Bonbonan (Rp)</label>
                            <input type="number" id="edit_bonbonan" name="bonbonan" class="form-input" placeholder="0" oninput="calculateHasilAkhir()">
                        </div>
                        <div class="form-group">
                            <label for="edit_bon">Bon (Rp)</label>
                            <input type="number" id="edit_bon" name="bon" class="form-input" placeholder="0" oninput="calculateHasilAkhir()">
                        </div>

                        <div class="form-group">
                            <label for="edit_bon_lain">Bon Lain (Rp)</label>
                            <input type="number" id="edit_bon_lain" name="bon_lain" class="form-input" placeholder="0" oninput="calculateHasilAkhir()">
                        </div>
                        <div class="form-group">
                            <label for="edit_keterangan_bon_lain">Ket. Bon Lain</label>
                            <input type="text" id="edit_keterangan_bon_lain" name="keterangan_bon_lain" class="form-input" placeholder="Contoh: Beli rokok / semen">
                        </div>

                        <div class="form-group">
                            <label for="edit_bonus">Bonus (Rp)</label>
                            <input type="number" id="edit_bonus" name="bonus" class="form-input" placeholder="0" oninput="calculateHasilAkhir()">
                        </div>
                        <div class="form-group">
                            <label for="edit_hasil_akhir">Hasil Akhir (Rp)</label>
                            <input type="number" id="edit_hasil_akhir" name="hasil_akhir" class="form-input" style="font-weight: bold; background-color: #f1f5f9;">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                        <button type="submit" class="btn-submit">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            let currentGaji = 0;

            function openEditModal(row) {
                const data = JSON.parse(row.dataset.transaksi);
                currentGaji = parseFloat(data.gaji) || 0;

                document.getElementById('edit_id_tukang').value = data.id_tukang;
                document.getElementById('modalNamaTukang').innerText = data.nama;
                document.getElementById('displayTarifHarian').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentGaji);

                document.getElementById('edit_absen').value = data.absen !== null ? data.absen : '';
                document.getElementById('edit_absen_2').value = data.absen_2 !== null ? data.absen_2 : '';
                document.getElementById('edit_bonbonan').value = data.bonbonan !== null ? data.bonbonan : '';
                document.getElementById('edit_bon').value = data.bon !== null ? data.bon : '';
                document.getElementById('edit_bon_lain').value = data.bon_lain !== null ? data.bon_lain : '';
                document.getElementById('edit_keterangan_bon_lain').value = data.keterangan_bon_lain || '';
                document.getElementById('edit_bonus').value = data.bonus !== null ? data.bonus : '';

                if (data.hasil_akhir !== null) {
                    document.getElementById('edit_hasil_akhir').value = data.hasil_akhir;
                } else {
                    calculateHasilAkhir();
                }

                document.getElementById('modalEditTransaksi').style.display = 'flex';
            }

            function closeEditModal() {
                document.getElementById('modalEditTransaksi').style.display = 'none';
                document.getElementById('formEditTransaksi').reset();
            }

            function calculateHasilAkhir() {
                const absen = parseFloat(document.getElementById('edit_absen').value) || 0;
                const absen2 = parseFloat(document.getElementById('edit_absen_2').value) || 0;
                const bonbonan = parseFloat(document.getElementById('edit_bonbonan').value) || 0;
                const bon = parseFloat(document.getElementById('edit_bon').value) || 0;
                const bonLain = parseFloat(document.getElementById('edit_bon_lain').value) || 0;
                const bonus = parseFloat(document.getElementById('edit_bonus').value) || 0;

                const totalGaji = (absen + absen2) * currentGaji;
                const totalPotongan = bonbonan + bon + bonLain;
                const hasil = totalGaji + bonus - totalPotongan;

                document.getElementById('edit_hasil_akhir').value = Math.round(hasil);
            }

            window.addEventListener('click', function(event) {
                const modal = document.getElementById('modalEditTransaksi');
                if (event.target === modal) {
                    closeEditModal();
                }
            });
        </script>
    </body>
</html>
