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

<!-- Modal Tambah Tukang -->
<div id="modalTukang" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Tukang Baru</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="formTukang" action="{{ route('tukang.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama">Nama Tukang</label>
                <input type="text" id="nama" name="nama" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="gaji">Gaji Per Hari</label>
                <input type="number" id="gaji" name="gaji" class="form-input" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-submit">Simpan</button>
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

function openModal() {
    document.getElementById('modalTukang').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modalTukang').style.display = 'none';
    document.getElementById('formTukang').reset();
}

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