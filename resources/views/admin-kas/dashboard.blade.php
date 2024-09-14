@extends("layouts.master")
@section("main-content")
<div class="dashboard-container">
    @if (session("success"))
    <div class="alert alert-success">
        {{session("success") }}
    </div>
    @endif

    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addModal">Tambah Nasabah</button>
    <form action="{{ route('admin-kas.nasabah.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".xlsx, .xls">
    <button type="submit" class="btn btn-primary">Import Excel</button>
    </form>

    <div class="flex justify-between mb-4">
    <div class="mb-2">
            <form method="GET" action="{{ route('admin-kas.dashboard') }}">
                <select name="date_filter" onchange="this.form.submit()"
                    class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
                    <option value="">Last 30 days</option>
                    <option value="last_7_days" {{ request('date_filter')=='last_7_days' ? 'selected' : '' }}>Last 7
                        days</option>
                    <option value="last_30_days" {{ request('date_filter')=='last_30_days' ? 'selected' : '' }}>Last 30
                        days</option>
                    <option value="last_month" {{ request('date_filter')=='last_month' ? 'selected' : '' }}>Last month
                    </option>
                    <option value="last_year" {{ request('date_filter')=='last_year' ? 'selected' : '' }}>Last year
                    </option>
                </select>
            </form>
        </div>
        <div>
            <form method="GET" action="{{ route('admin-kas.dashboard') }}">
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                    placeholder="Search"
                    class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">

                <select name="cabang_filter" onchange="this.form.submit()"
                    class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
                    <option value="">Cabang</option>
                    @foreach($cabangs as $cabang)
                    <option value="{{ $cabang->id_cabang }}" {{ request('cabang_filter')==$cabang->id_cabang ?
                        'selected' : '' }}>{{ $cabang->nama_cabang }}</option>
                    @endforeach
                </select>

                <select name="wilayah_filter" onchange="this.form.submit()"
                    class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
                    <option value="">Kantor Kas</option>
                    @foreach($kantorkas as $wilayah)
                    <option value="{{ $wilayah->id_kantorkas }}" {{ request('wilayah_filter')==$wilayah->id_kantorkas ?
                        'selected' : '' }}>{{ $wilayah->nama_kantorkas }}</option>
                    @endforeach
                </select>
                <select name="per_page" onchange="this.form.submit()" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>Show 10</option>
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>Show 20</option>
                    <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>Show 30</option>
                    <option value="" {{ request('per_page') === null ? 'selected' : '' }}>Show All</option>
                </select>
            </form>
        </div>
    </div>

    <table class="table table-striped" id="nasabah-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Total</th>
                <th>Keterangan</th>
                <th>Progres SP</th>
                <th>Aksi</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nasabahs as $index => $nasabah)
            <tr>
                <td>{{ $nasabah->no }}</td>
                <td>{{ $nasabah->nama }}</td>
                <td>{{ $nasabah->total }}</td>
                <td>{{ $nasabah->keterangan }}</td>
                <td>
                @php
            $matchingSp = $suratPeringatans->where('no', $nasabah->no)->sortByDesc('dibuat')->values();
            $totalSp = $matchingSp->count();
            @endphp

            @if($totalSp > 0)
            <div class="sp-indicators">
                @for($i = $totalSp - 1; $i >= 0; $i--)
                <span class="tingkat-{{ $matchingSp[$i]->tingkat }}"
                    title="Tingkat {{ $matchingSp[$i]->tingkat }} - {{ $matchingSp[$i]->dibuat }}"
                    data-toggle="modal" data-target="#modalDetail{{ $index }}-{{ $i }}">
                </span>

                        <!-- Modal Detail SP -->
                        <div class="modal fade" id="modalDetail{{ $index }}-{{ $i }}" tabindex="-1" role="dialog"
                    aria-labelledby="modalDetailLabel{{ $index }}-{{ $i }}" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalDetailLabel{{ $index }}-{{ $i }}">Surat Peringatan {{
                                    $matchingSp[$i]->tingkat }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Menampilkan detail dari surat_peringatans -->
                                <p>No: {{ $matchingSp[$i]->no }}</p>
                                <p>Nama: {{ $matchingSp[$i]->nama }}</p>
                                <p>Tingkat: {{ $matchingSp[$i]->tingkat }}</p>
                                <p>Dibuat: {{ $matchingSp[$i]->dibuat }}</p>
                                <p>Diserahkan: {{ $matchingSp[$i]->diserahkan }}</p>
                                <p>Kembali: {{ $matchingSp[$i]->kembali }}</p>

                                <!-- Menampilkan Bukti Gambar -->
                                @if($matchingSp[$i]->bukti_gambar)
                                <p>Bukti Gambar:</p>
                                <img src="{{ asset('storage/'.$matchingSp[$i]->bukti_gambar) }}"
                                    alt="Bukti Gambar" class="img-fluid">
                                @endif

                                <!-- Menampilkan Scan PDF -->
                                @if($matchingSp[$i]->scan_pdf)
                                <p>Scan PDF:</p>

                                <button onclick="openPdf('{{ asset('storage/'.$matchingSp[$i]->scan_pdf) }}')" class="btn btn-primary">Lihat PDF</button>
                                @endif
                                
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
            @else
            N/A
            @endif

                </td>
                <td>
                    <button class="btn btn-primary btn-sm edit-btn" data-no="{{ $nasabah->no }}" data-toggle="modal"
                        data-target="#editModal">Edit</button>
                    <button class="btn btn-info btn-sm detail-btn" data-no="{{ $nasabah->no }}" data-toggle="modal"
                        data-target="#detailModal">Detail</button>
                    <button class="btn btn-danger btn-sm delete-btn" data-no="{{ $nasabah->no }}" data-toggle="modal"
                        data-target="#deleteModal">Delete</button>
                    <button class="btn btn-success mt-1 tambah-btn" data-no="{{ $nasabah->no}}" data-toggle="modal"
                        data-target="#addSurat">Tambah SP</button>
                </td>
                <td>{{ $nasabah->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($nasabahs instanceof \Illuminate\Pagination\AbstractPaginator)
    {{ $nasabahs->links('pagination::bootstrap-4') }}
@endif
</div>

<!-- Modal for Add -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Tambah Data Nasabah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addForm" method="POST" action="{{ route('admin-kas.nasabah.add') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="addNo">No</label>
                        <input type="text" class="form-control" id="addNo" name="no" required>
                    </div>
                    <div class="form-group">
                        <label for="addNama">Nama</label>
                        <input type="text" class="form-control" id="addNama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="addPokok">Pokok</label>
                        <input type="number" class="form-control" id="addPokok" name="pokok" required>
                    </div>
                    <div class="form-group">
                        <label for="addBunga">Bunga</label>
                        <input type="number" class="form-control" id="addBunga" name="bunga" required>
                    </div>
                    <div class="form-group">
                        <label for="addDenda">Denda</label>
                        <input type="number" class="form-control" id="addDenda" name="denda" required>
                    </div>
                    <div class="form-group">
                        <label for="addTotal">Total</label>
                        <input type="number" class="form-control" id="addTotal" name="total" readonly>
                    </div>
                    <div class="form-group">
                        <label for="addKeterangan">Keterangan</label>
                        <textarea class="form-control" id="addKeterangan" name="keterangan" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="addCabang">Cabang</label>
                        <input type="text" class="form-control" id="addCabang" name="id_cabang" 
                            value="{{ $cabangs->where('id_cabang', auth()->user()->id_cabang)->first()->nama_cabang }}" readonly>
                            <input type="hidden" class="form-control" id="addCabang" name="id_cabang" 
                            value="{{ auth()->user()->id_cabang }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="addWilayah">Kantor Kas</label>
                        <input type="text" class="form-control" id="addWilayah" name="id_kantorkas" 
                            value="{{ $kantorkas->where('id_kantorkas', auth()->user()->id_kantorkas)->first()->nama_kantorkas }}" readonly>
                            <input type="hidden" class="form-control" id="addWilayah" name="id_kantorkas" 
                            value="{{ auth()->user()->id_kantorkas }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="addAccountOfficer">Account Officer</label>
                        <select class="form-control" id="addAccountOfficer" name="id_account_officer" required>
                            @foreach($accountOfficers as $accountOfficer)
                                @if ($accountOfficer->id_cabang == auth()->user()->id_cabang && 
                                    $accountOfficer->id_kantorkas == auth()->user()->id_kantorkas)
                                    <option value="{{ $accountOfficer->id }}">{{ $accountOfficer->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="admin_kas"></label>
                        <input type="hidden" id="admin_kas" value="{{ auth()->user()->name }}" readonly>
                        <input type="hidden" name="id_admin_kas" value="{{ auth()->user()->id }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Surat -->
<div class="modal fade" id="addSurat" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Tambah Data Surat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addSuratForm" method="POST" action="{{ route('admin-kas.nasabah.surat') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tambahNo">No</label>
                        <input type="text" class="form-control" id="tambahNo" name="no" readonly>
                    </div>
                    <div class="form-group">
                        <label for="addTingkat">Progress SP</label>
                        <select class="form-control" id="addTingkat" name="tingkat" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="addTtd">Dibuat</label>
                        <input type="datetime-local" class="form-control" id="addTtd" name="dibuat">
                    </div>
                    <div class="form-group">
                        <label for="addKembali">Kembali</label>
                        <input type="datetime-local" class="form-control" id="addKembali" name="kembali">
                    </div>
                    <div class="form-group">
                        <label for="addScanPdf">Scan PDF</label>
                        <input type="file" class="form-control" id="addScanPdf" name="scan_pdf" accept="application/pdf"
                            required>
                    </div>
                    <!-- <div class="form-group">
                        <label for="addAccountOfficer">Account Officer</label>
                        <select class="form-control select2" id="addAccountOfficer" name="id_account_officer" required>
                            <option value="">Pilih Account Officer</option>
                            @foreach($accountOfficers as $accountOfficer)
                                <option value="{{ $accountOfficer->id }}">{{ $accountOfficer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="admin_kas"></label>
                        <input type="hidden" id="admin_kas" value="{{ auth()->user()->name }}" readonly>
                        <input type="hidden" name="id_admin_kas" value="{{ auth()->user()->id }}">
                    </div> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Edit -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Data Nasabah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST" action="">
                @csrf
                @method('POST')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editNo">No</label>
                        <input type="text" class="form-control" id="editNo" name="no" readonly>
                    </div>
                    <div class="form-group">
                        <label for="editNama">Nama</label>
                        <input type="text" class="form-control" id="editNama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="editPokok">Pokok</label>
                        <input type="number" class="form-control" id="editPokok" name="pokok" required>
                    </div>
                    <div class="form-group">
                        <label for="editBunga">Bunga</label>
                        <input type="number" class="form-control" id="editBunga" name="bunga" required>
                    </div>
                    <div class="form-group">
                        <label for="editDenda">Denda</label>
                        <input type="number" class="form-control" id="editDenda" name="denda" required>
                    </div>
                    <div class="form-group">
                        <label for="editTotal">Total</label>
                        <input type="number" class="form-control" id="editTotal" name="total" readonly>
                    </div>
                    <div class="form-group">
                        <label for="editKeterangan">Keterangan</label>
                        <textarea class="form-control" id="editKeterangan" name="keterangan" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editCabang">Cabang</label>
                        <select class="form-control" id="editCabang" name="id_cabang" required>
                            @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id_cabang }}">{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editWilayah">Wilayah</label>
                        <select class="form-control" id="editWilayah" name="id_kantorkas" required>
                            @foreach($kantorkas as $wilayah)
                            <option value="{{ $wilayah->id_kantorkas }}">{{ $wilayah->nama_kantorkas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editAccountOfficer">Account Officer</label>
                        <select class="form-control" id="editAccountOfficer" name="id_account_officer" required>
                            @foreach($accountOfficers as $accountOfficer)
                            <option value="{{ $accountOfficer->id }}">{{ $accountOfficer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="id_admin_kas" value="{{ auth()->user()->id }}">
                    <div class="form-group">
                        <label for="admin_kas"></label>
                        <input type="hidden" id="admin_kas" value="{{ auth()->user()->name }}" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Data Nasabah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="detailNo">No</label>
                    <input type="text" class="form-control" id="detailNo" name="no" readonly>
                </div>
                <div class="form-group">
                    <label for="detailNama">Nama</label>
                    <input type="text" class="form-control" id="detailNama" name="nama" readonly>
                </div>
                <div class="form-group">
                    <label for="detailPokok">Pokok</label>
                    <input type="number" class="form-control" id="detailPokok" name="pokok" readonly>
                </div>
                <div class="form-group">
                    <label for="detailBunga">Bunga</label>
                    <input type="number" class="form-control" id="detailBunga" name="bunga" readonly>
                </div>
                <div class="form-group">
                    <label for="detailDenda">Denda</label>
                    <input type="number" class="form-control" id="detailDenda" name="denda" readonly>
                </div>
                <div class="form-group">
                    <label for="detailTotal">Total</label>
                    <input type="number" class="form-control" id="detailTotal" name="total" readonly>
                </div>
                <div class="form-group">
                    <label for="detailKeterangan">Keterangan</label>
                    <textarea class="form-control" id="detailKeterangan" name="keterangan" readonly></textarea>
                </div>
                <div class="form-group">
                    <label for="detailCabang">Cabang</label>
                    <input type="text" class="form-control" id="detailCabang" name="id_cabang" readonly>
                </div>
                <div class="form-group">
                    <label for="detailKantorKas">Wilayah</label>
                    <input type="text" class="form-control" id="detailKantorKas" name="id_kantorkas" readonly>
                </div>
                <div class="form-group">
                    <label for="detailAccountOfficer">Account Officer</label>
                    <input type="text" class="form-control" id="detailAccountOfficer" name="id_account_officer"
                        readonly>
                </div>
                <div class="form-group">
                    <label for="detailAdminKas">Admin Kas</label>
                    <input type="text" class="form-control" id="detailAdminKas" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Data Nasabah</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data nasabah ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js"></script> 
<script src="https://cdn.datatables.net/2.1.6/js/dataTables.js" defer></script>
<script>
    document.getElementById("menuButton").onclick = function() {
    document.getElementById("menuDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.menu-button')) {
    var dropdowns = document.getElementsByClassName("menu-dropdown");
    for (var i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}
    $(document).ready(function () {
    var table = $('#nasabah-table').DataTable({
        columnDefs: [
            { targets: 5, orderable: false },  // Disable sorting for "Aksi"
            { targets: 3, orderable: false },   // Disable sorting for "Keterangan"
            { targets: 6, visible: false },     // Hide the "Created At" column
            { targets: 0, orderData: 6 }        // Sort "No" based on the 7th column (created_at)
        ],
        info: false,        // Disable the information summary
        paging: false,       // Disable pagination
        searching: false,    // Disable the default search bar
        order: [[0, 'desc']] // Initial sorting: "No" column descending (latest first)
        });
    });

    document.getElementById('search').addEventListener('keyup', function (event) {
        const query = event.target.value;
        const table = document.getElementById('nasabah-table');
        const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j].innerText.toLowerCase().includes(query.toLowerCase())) {
                    match = true;
                    break;
                }
            }

            if (match) {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    });
    // Edit button click event
    $('.edit-btn').on('click', function () {
        var no = $(this).data('no');
        $.ajax({
            url: '/admin-kas/nasabah/edit/' + no,
            method: 'GET',
            success: function (data) {
                // Populate the modal with data
                $('#editNo').val(data.no);
                $('#editNama').val(data.nama);
                $('#editPokok').val(data.pokok);
                $('#editBunga').val(data.bunga);
                $('#editDenda').val(data.denda);
                $('#editTotal').val(data.total);
                $('#editKeterangan').val(data.keterangan);
                $('#editTtd').val(data.ttd);
                $('#editKembali').val(data.kembali);
                $('#editCabang').val(data.nama_cabang);
                $('#editWilayah').val(data.nama_kantorkas);
                $('#editAccountOfficer').val(data.id_account_officer);
                $('#detailAdminKas').val(data.adminKas ? data.adminKas.name : '');

                // Set the form action to the update route with the correct no
                $('#editForm').attr('action', '/admin-kas/nasabah/update/' + no);
                $('#editForm').find('input[name="_method"]').val('PUT'); // Set the method to PUT


                // Menampilkan modal
                $('#editModal').modal('show');
            },
            error: function (xhr) {
                alert('Terjadi kesalahan saat memuat data.');
            }
        });
    });

    // Detail button click event
    $('.detail-btn').on('click', function () {
        var no = $(this).data('no');
        var nasabah = @json($nasabahs -> keyBy('no'));
        var data = nasabah[no];


        $('#detailNo').val(data.no);
        $('#detailNama').val(data.nama);
        $('#detailPokok').val(data.pokok);
        $('#detailBunga').val(data.bunga);
        $('#detailDenda').val(data.denda);
        $('#detailTotal').val(data.total);
        $('#detailKeterangan').val(data.keterangan);
        // $('#detailTtd').val(data.ttd);
        // $('#detailKembali').val(data.kembali);
        $('#detailCabang').val(data.cabang.nama_cabang);
        $('#detailKantorKas').val(data.kantorkas.nama_kantorkas);
        // $('#detailAccountOfficer').val(data.user.name);
        $('#detailAccountOfficer').val(data.account_officer ? data.account_officer.name : ''); // Mengakses nama account officer dari relasi account_officer
        $('#detailAdminKas').val(data.admin_kas ? data.admin_kas.name : '');


    });
    new DataTable('#nasabah-table');
    // Delete button click event
    $('.delete-btn').on('click', function () {
        var no = $(this).data('no');
        $('#deleteNo').val(no);
        $('#deleteForm').attr('action', '/admin-kas/nasabah/delete/' + no);
    });

    // Tambah button
    $('.tambah-btn').on('click', function () {
        var no = $(this).data('no');
        $.ajax({
            url: '/admin-kas/nasabah/edit/' + no,
            method: 'GET',
            success: function (data) {
                // Populate the modal with data
                $('#tambahNo').val(data.no);
                // $('#editNama').val(data.nama);
                // $('#editPokok').val(data.pokok);
                // $('#editBunga').val(data.bunga);
                // $('#editDenda').val(data.denda);
                // $('#editTotal').val(data.total);
                // $('#editKeterangan').val(data.keterangan);
                // $('#editTtd').val(data.ttd);
                // $('#editKembali').val(data.kembali);
                // $('#editCabang').val(data.nama_cabang);
                // $('#editWilayah').val(data.nama_kantorkas);
                // $('#editAccountOfficer').val(data.id_account_officer);
                // $('#detailAdminKas').val(data.adminKas ? data.adminKas.name : '');

                // Set the form action to the update route with the correct no
                $('#editForm').attr('action', '/admin-kas/nasabah/update/' + no);
                $('#editForm').find('input[name="_method"]').val('PUT'); // Set the method to PUT


                // Menampilkan modal
                $('#editModal').modal('show');
            },
            error: function (xhr) {
                alert('Terjadi kesalahan saat memuat data.');
            }
        });
    });
    function openPdf(url) {
        window.open(url);
    }

    // Calculate total for add form
    function calculateAddTotal() {
        var pokok = parseFloat($('#addPokok').val()) || 0;
        var bunga = parseFloat($('#addBunga').val()) || 0;
        var denda = parseFloat($('#addDenda').val()) || 0;
        var total = pokok + bunga + denda;
        $('#addTotal').val(total);
    }

    // Calculate total for edit form
    function calculateEditTotal() {
        var pokok = parseFloat($('#editPokok').val()) || 0;
        var bunga = parseFloat($('#editBunga').val()) || 0;
        var denda = parseFloat($('#editDenda').val()) || 0;
        var total = pokok + bunga + denda;
        $('#editTotal').val(total);
    }

    // Attach events for calculating total on input change
    $('#addPokok, #addBunga, #addDenda').on('input', calculateAddTotal);
    $('#editPokok, #editBunga, #editDenda').on('input', calculateEditTotal);
</script>

@endsection