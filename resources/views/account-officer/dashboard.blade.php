@extends("layouts.master")
@section("main-content")
<div class="dashboard-container">
    @if (session("success"))
    <div class="alert alert-success">
        {{session("success") }}
    </div>
    @endif

    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addModal">Tambah Data</button>
    <div class="flex justify-between mb-4">
    <div class="mb-2">
        <form method="GET" action="{{ route('account-officer.dashboard') }}">
            <select name="date_filter" onchange="this.form.submit()" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
                <option value="">Last 30 days</option>
                <option value="last_7_days" {{ request('date_filter') == 'last_7_days' ? 'selected' : '' }}>Last 7 days</option>
                <option value="last_30_days" {{ request('date_filter') == 'last_30_days' ? 'selected' : '' }}>Last 30 days</option>
                <option value="last_month" {{ request('date_filter') == 'last_month' ? 'selected' : '' }}>Last month</option>
                <option value="last_year" {{ request('date_filter') == 'last_year' ? 'selected' : '' }}>Last year</option>
            </select>
        </form>
    </div>
    <div>
        <form method="GET" action="{{ route('account-officer.dashboard') }}">
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name, branch, region" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
            
            <select name="cabang_filter" onchange="this.form.submit()" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
                <option value="">Cabang</option>
                @foreach($cabangs as $cabang)
                    <option value="{{ $cabang->id_cabang }}" {{ request('cabang_filter') == $cabang->id_cabang ? 'selected' : '' }}>{{ $cabang->nama_cabang }}</option>
                @endforeach
            </select>

            <select name="wilayah_filter" onchange="this.form.submit()" class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded">
                <option value="">Wilayah</option>
                @foreach($kantorkas as $wilayah)
                    <option value="{{ $wilayah->id_kantorkas }}" {{ request('wilayah_filter') == $wilayah->id_kantorkas ? 'selected' : '' }}>{{ $wilayah->nama_wilayah }}</option>
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
                <th>Nama</th>
                <th>Progress SP</th>
                <th>Diserahkan</th>
                <th>Gambar</th>
                <th>Aksi</th>
                <th>Created At</th>
            </tr>
        </thead>
        @foreach($suratPeringatans as $suratPeringatan)
            @php
                $nasabah = $nasabahs->firstWhere('no', $suratPeringatan->no);
            @endphp
            <tr>
                <td>{{ $nasabah ? $nasabah->nama : 'N/A' }}</td>
                <td>
                <span class="tingkat-{{ $suratPeringatan->tingkat }}">
                {{-- Optional visual indicators here --}}
            </span>
            Tingkat {{ $suratPeringatan->tingkat }}
                </td>
                <td>{{ $suratPeringatan->diserahkan ?? 'Belum ada Data' }}</td>
                <!-- <td>
                {{-- Tampilkan link untuk file PDF --}}
                @if(pathinfo($suratPeringatan->scan_pdf, PATHINFO_EXTENSION) === 'pdf')
                    <a href="{{ asset('storage/' . $suratPeringatan->scan_pdf) }}" target="_blank">View PDF</a>
                @else
                    No PDF
                @endif -->
            </td>
            <td>
                {{-- Tampilkan gambar jika file adalah gambar --}}
                @if(in_array(pathinfo($suratPeringatan->bukti_gambar, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif','webp']))
                    <img src="{{ asset('storage/' . $suratPeringatan->bukti_gambar) }}" alt="Bukti Gambar" style="width: 50px;">
                @else
                    No Image
                @endif
            </td>
                <td>
                    <!-- <button class="btn btn-primary btn-sm edit-btn" data-no="{{ $suratPeringatan->no }}" data-toggle="modal" data-target="#editModal">Edit</button> -->
                    <!-- <button class="btn btn-info btn-sm detail-btn" data-no="{{ $nasabah->nama }}" data-toggle="modal" data-target="#detailModal">Detail</button> -->
                    <button class="btn btn-danger btn-sm delete-btn" data-id_peringatan="{{ $suratPeringatan->id_peringatan }}" data-toggle="modal" data-target="#deleteModal">Delete</button>
                </td>
                <td>{{ $nasabah->created_at }}</td>
            </tr>
        @endforeach
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
            <form id="addForm" method="POST" action="{{ route('account-officer.nasabah.add') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Nama Field -->
                    <select class="form-control" id="addNama" name="nama" required>
                        <option value="">Pilih Nasabah</option>
                        @foreach($nasabahNames as $no => $nama)
                            <option value="{{ $nama }}">{{ $nama }}</option>
                        @endforeach
                    </select>
                    <!-- Tingkat Field -->
                    <div class="form-group">
                        <label for="addTingkat">Progress SP</label>
                        <select class="form-control" id="addTingkat" name="tingkat" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>

                    <!-- Tanggal Field -->
                    <div class="form-group">
                        <label for="addTanggal">Diserahkan</label>
                        <input type="datetime-local" class="form-control" id="addTanggal" name="diserahkan" required>
                    </div>

                    <!-- Bukti Gambar Field -->
                    <div class="form-group">
                        <label for="addBuktiGambar">Bukti Gambar</label>
                        <input type="file" class="form-control" id="addBuktiGambar" name="bukti_gambar" accept="image/*" required>
                    </div>

                    <!-- Scan PDF Field -->
                    <!-- <div class="form-group">
                        <label for="addScanPdf">Scan PDF</label>
                        <input type="file" class="form-control" id="addScanPdf" name="scan_pdf" accept="application/pdf" required>
                    </div> -->
                </div>
                <div class="form-group">
                        <label for="account_officer"></label>
                        <input type="hidden" id="account_officer" value="{{ auth()->user()->name }}" readonly>
                        <input type="hidden" name="id_account_officer" value="{{ auth()->user()->id }}">
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Surat Peringatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="detailNama">Nama</label>
                    <input type="text" class="form-control" id="detailNama" readonly>
                </div>
                <div class="form-group">
                    <label for="detailTingkat">Progress SP</label>
                    <input type="text" class="form-control" id="detailTingkat" readonly>
                </div>
                <div class="form-group">
                    <label for="detailTanggal">Tanggal</label>
                    <input type="text" class="form-control" id="detailTanggal" readonly>
                </div>
                <div class="form-group">
                    <label for="detailPdf">Scan PDF</label>
                    <p id="detailPdf"></p>
                </div>
                <div class="form-group">
                    <label for="detailGambar">Bukti Gambar</label>
                    <img id="detailGambar" src="" alt="Bukti Gambar" style="max-width: 100%; margin-top: 10px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
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
                <input type="hidden" id="deleteIdPeringatan" name="id_peringatan">
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
    $(document).ready(function () {
    var table = $('#nasabah-table').DataTable({
        columnDefs: [
            { targets: 4, orderable: false },  // Disable sorting for "Aksi"
            { targets: 3, orderable: false },   // Disable sorting for "Keterangan"
            { targets: 5, visible: false },     // Hide the "Created At" column
            { targets: 0, orderData: 5 }        // Sort "No" based on the 7th column (created_at)
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

        // Detail button click event
        $('.detail-btn').on('click', function() {
            var no = $(this).data('no');
            var nasabah = @json($nasabahs->keyBy('nama'));
            var data = nasabah[no];
            

            $('#detailNama').val(data.nama);
            $('#detailTingkat').val(data.tingkat);
            $('#detailTanggal').val(data.tanggal);
            $('#detailPdf').val(data.pdf);
            $('#detailGambar').val(data.bukti_gambar);
            // $('#detailAccountOfficer').val(data.user.name);
            $('#detailAccountOfficer').val(data.account_officer ? data.account_officer.name : ''); // Mengakses nama account officer dari relasi account_officer
            // $('#detailAccountOfficer').val(data.admin_kas ? data.admin_kas.name : '');


        });

        // Delete button click event
        $('.delete-btn').on('click', function() {
            console.log('Delete button clicked');
            var id_peringatan = $(this).data('id_peringatan');
            console.log('ID Peringatan:', id_peringatan);
        
            $('#deleteForm').attr('action', '/account-officer/nasabah/delete/' + id_peringatan);
        });

        // // Calculate total for add form
        // function calculateAddTotal() {
        //     var pokok = parseFloat($('#addPokok').val()) || 0;
        //     var bunga = parseFloat($('#addBunga').val()) || 0;
        //     var denda = parseFloat($('#addDenda').val()) || 0;
        //     var total = pokok + bunga + denda;
        //     $('#addTotal').val(total);
        // }

        // Calculate total for edit form
        // function calculateEditTotal() {
        //     var pokok = parseFloat($('#editPokok').val()) || 0;
        //     var bunga = parseFloat($('#editBunga').val()) || 0;
        //     var denda = parseFloat($('#editDenda').val()) || 0;
        //     var total = pokok + bunga + denda;
        //     $('#editTotal').val(total);
        // }

        // Attach events for calculating total on input change
        // $('#addPokok, #addBunga, #addDenda').on('input', calculateAddTotal);
        // $('#editPokok, #editBunga, #editDenda').on('input', calculateEditTotal);
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
</script>

@endsection
