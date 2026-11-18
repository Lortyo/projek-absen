<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-left">
            <select class="dropdown-tahun" id="dropdownTahun" onchange="filterPeriodeByTahun()">
                @foreach($tahunList as $tahun)
                    <option value="{{ $tahun }}" {{ $tahun == $tahunSekarang ? 'selected' : '' }}>
                        {{ $tahun }}
                    </option>
                @endforeach
            </select>
            <select class="dropdown-periode" id="dropdownPeriode" onchange="submitFilter(this.value)">
                <option value="0"></option>
                @php
                    $idPeriodeTerpilih = request('id_periode') ?? ($periodeSekarang->id_periode ?? null);
                    $periodeFiltered = $periode->where('tahun_periode', $tahunSekarang)->sortByDesc('periode_mulai');
                @endphp
                @foreach($periodeFiltered as $p)
                    <option value="{{ $p->id_periode }}" {{ $p->id_periode == $idPeriodeTerpilih ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::parse($p->periode_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($p->periode_selesai)->format('d M') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="navbar-actions">
            <button class="btn-tahun-baru" onclick="buatTahunBaru()">Tahun Baru</button>
            <button class="btn-periode-baru" onclick="buatPeriodeBaru()">Periode Baru</button>
            <button class="btn-add-tukang" onclick="openModal()">+ Tukang</button>
        </div>
    </div>
</nav>

<!-- Modal Tambah Tukang (Banyak Sekaligus) -->
<div id="modalTukang" class="modal">
    <div class="modal-content" style="max-width: 600px; width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header">
            <div>
                <h3 style="margin: 0; font-size: 1.3rem;">Tambah Tukang</h3>
                <span style="font-size: 0.85rem; color: #64748b;">
                    Input manual atau langsung <kbd style="background: #e2e8f0; padding: 2px 5px; border-radius: 4px; font-size: 0.75rem; border: 1px solid #cbd5e1;">Ctrl+V</kbd> paste dari Excel (kolom Nama & Gaji)
                </span>
            </div>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="formTukang" action="{{ route('tukang.store') }}" method="POST" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
            @csrf
            <div style="padding: 1rem 1.25rem; flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; font-size: 0.85rem; color: #475569;">
                            <th style="padding: 0.5rem; text-align: center; width: 35px; border: none;">No</th>
                            <th style="padding: 0.5rem; text-align: left; border: none;">Nama Tukang</th>
                            <th style="padding: 0.5rem; text-align: left; width: 170px; border: none;">Gaji / Hari (Rp)</th>
                            <th style="padding: 0.5rem; width: 30px; text-align: center; border: none;"></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyTukang">
                        <!-- Baris dinamis -->
                    </tbody>
                </table>
                <div style="margin-top: 0.75rem;">
                    <button type="button" onclick="tambahBarisTukang()" style="background: white; border: 1.5px dashed #94a3b8; padding: 0.45rem 1rem; border-radius: 7px; font-size: 0.875rem; cursor: pointer; color: #334155; font-weight: 500; width: 100%; transition: all 0.15s ease;">
                        + Tambah Baris Tukang
                    </button>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.25rem; border-top: 2px solid #f0f0f0;">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-submit">Simpan Semua</button>
            </div>
        </form>
    </div>
</div>

<script>
const allPeriode = @json($periode);

function submitFilter(periodeId) {
    if (!periodeId) return;

    window.location.href = window.location.pathname + '?id_periode=' + periodeId;
}

function filterPeriodeByTahun() {
    const tahunDipilih = document.getElementById('dropdownTahun').value;
    const dropdownPeriode = document.getElementById('dropdownPeriode');

    const periodeFiltered = allPeriode
        .filter(p => p.tahun_periode == tahunDipilih)
        .sort((a, b) => new Date(b.periode_mulai) - new Date(a.periode_mulai));

    dropdownPeriode.innerHTML = '';

    periodeFiltered.forEach((p, index) => {
        const option = document.createElement('option');
        option.value = p.id_periode;

        const mulai = new Date(p.periode_mulai);
        const selesai = new Date(p.periode_selesai);
        const formatMulai = mulai.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
        const formatSelesai = selesai.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });

        option.textContent = `${formatMulai} - ${formatSelesai}`;
        if (index === 0) option.selected = true;

        dropdownPeriode.appendChild(option);
    });
}

let tukangIndex = 0;

function tambahBarisTukang(nama = '', gaji = '') {
    const tbody = document.getElementById('tbodyTukang');
    const tr = document.createElement('tr');
    tr.style.borderBottom = '1px solid #f1f5f9';

    tr.innerHTML = `
        <td style="text-align: center; font-size: 0.85rem; color: #94a3b8; border: none; padding: 0.35rem 0.25rem;" class="nomor-baris"></td>
        <td style="padding: 0.35rem 0.4rem; border: none;">
            <input type="text" autocomplete="off" name="tukang[${tukangIndex}][nama]" class="form-input input-nama-tukang" value="${nama}" placeholder="Nama tukang" style="padding: 0.45rem 0.65rem; font-size: 0.9rem;">
        </td>
        <td style="padding: 0.35rem 0.4rem; border: none;">
            <input type="text" autocomplete="off" inputmode="numeric" name="tukang[${tukangIndex}][gaji]" class="form-input gaji-bulk-input" value="${gaji}" placeholder="0" oninput="formatBulkGaji(this)" onkeydown="checkBulkKey(event, this)" style="padding: 0.45rem 0.65rem; font-size: 0.9rem;">
        </td>
        <td style="padding: 0.35rem 0.25rem; text-align: center; border: none;">
            <button type="button" onclick="hapusBarisTukang(this)" style="background: none; border: none; color: #94a3b8; font-size: 1.25rem; cursor: pointer; line-height: 1; padding: 0 4px;" title="Hapus baris">&times;</button>
        </td>
    `;
    tbody.appendChild(tr);
    tukangIndex++;
    updateNomorBaris();
}

function hapusBarisTukang(btn) {
    const tbody = document.getElementById('tbodyTukang');
    if (tbody.children.length > 1) {
        btn.closest('tr').remove();
        updateNomorBaris();
    } else {
        const tr = btn.closest('tr');
        tr.querySelector('.input-nama-tukang').value = '';
        tr.querySelector('.gaji-bulk-input').value = '';
    }
}

function updateNomorBaris() {
    const rows = document.querySelectorAll('#tbodyTukang tr');
    rows.forEach((row, i) => {
        const noCell = row.querySelector('.nomor-baris');
        if (noCell) noCell.innerText = i + 1;
    });
}

function formatBulkGaji(el) {
    const clean = el.value.replace(/\D/g, '');
    el.value = clean ? new Intl.NumberFormat('id-ID').format(clean) : '';
}

function checkBulkKey(e, el) {
    if (e.key === 'Enter') {
        e.preventDefault();
        tambahBarisTukang();
        const inputs = document.querySelectorAll('#tbodyTukang .input-nama-tukang');
        if (inputs.length > 0) {
            inputs[inputs.length - 1].focus();
        }
    }
}

function openModal() {
    const tbody = document.getElementById('tbodyTukang');
    tbody.innerHTML = '';
    tukangIndex = 0;
    // Tampilkan 3 baris awal siap isi
    for (let i = 0; i < 3; i++) {
        tambahBarisTukang();
    }
    document.getElementById('modalTukang').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modalTukang').style.display = 'none';
    document.getElementById('formTukang').reset();
}

document.getElementById('formTukang').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#tbodyTukang tr');
    let hasValidRow = false;
    rows.forEach(row => {
        const namaInput = row.querySelector('.input-nama-tukang');
        if (namaInput && namaInput.value.trim() !== '') {
            hasValidRow = true;
        }
    });

    if (!hasValidRow) {
        e.preventDefault();
        alert('Silakan masukkan minimal satu nama tukang.');
        return;
    }

    document.querySelectorAll('.gaji-bulk-input').forEach(input => {
        if (input && input.value) {
            input.value = input.value.replace(/\./g, '');
        }
    });
});

function handleExcelPaste(e) {
    const clipboardData = e.clipboardData || window.clipboardData;
    if (!clipboardData) return;
    const text = clipboardData.getData('text');
    if (!text) return;

    const rawLines = text.split(/\r\n|\r|\n/);
    const hasMultipleRows = rawLines.filter(l => l.trim() !== '').length > 1;
    const hasTab = text.includes('\t');

    // Jika hanya paste teks tunggal tanpa tab/enter, biarkan fungsi paste biasa
    if (!hasMultipleRows && !hasTab) {
        return;
    }

    e.preventDefault();

    const parsedData = [];
    rawLines.forEach(line => {
        const trimmed = line.trim();
        if (!trimmed) return;

        let parts = trimmed.split('\t');
        if (parts.length === 1) {
            if (trimmed.includes(';')) parts = trimmed.split(';');
            else if (trimmed.includes(',')) parts = trimmed.split(',');
        }

        let nama = '';
        let gajiRaw = '';

        // Tangani format: No | Nama | Gaji vs Nama | Gaji
        if (parts.length >= 3 && /^\d+$/.test(parts[0].trim())) {
            nama = (parts[1] || '').trim();
            gajiRaw = (parts[2] || '').trim();
        } else {
            nama = (parts[0] || '').trim();
            gajiRaw = (parts[1] || '').trim();
        }

        // Abaikan baris header tabel jika ikut ter-copy
        if (/^(no|nama|nama tukang|tukang)$/i.test(nama)) {
            return;
        }

        // Bersihkan angka gaji (pisahkan desimal koma jika ada, ambil digit saja)
        let cleanGaji = gajiRaw.split(',')[0].replace(/\D/g, '');
        let formattedGaji = cleanGaji ? new Intl.NumberFormat('id-ID').format(cleanGaji) : '';

        if (nama !== '') {
            parsedData.push({
                nama: nama,
                gaji: formattedGaji
            });
        }
    });

    if (parsedData.length === 0) return;

    const tbody = document.getElementById('tbodyTukang');
    const existingRows = tbody.querySelectorAll('tr');

    // Bersihkan baris awal jika masih kosong semua
    let isAllEmpty = true;
    existingRows.forEach(row => {
        const n = row.querySelector('.input-nama-tukang')?.value.trim();
        const g = row.querySelector('.gaji-bulk-input')?.value.trim();
        if (n || g) isAllEmpty = false;
    });

    if (isAllEmpty) {
        tbody.innerHTML = '';
        tukangIndex = 0;
    }

    // Tambahkan baris-baris dari Excel
    parsedData.forEach(item => {
        tambahBarisTukang(item.nama, item.gaji);
    });
}

document.getElementById('modalTukang').addEventListener('paste', handleExcelPaste);

function buatPeriodeBaru() {
    if (confirm('Buat periode baru otomatis?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("periode.store") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
}

function buatTahunBaru() {
    if (confirm('Buat tahun baru otomatis?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("tahun.store") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('modalTukang');
    if (event.target == modal) {
        closeModal();
    }
}
</script>