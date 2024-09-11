@extends("layouts.master")
@section("main-content")
<div class="dashboard-container">
    @if (session("success"))
    <div class="alert alert-success">
        {{session("success") }}
    </div>
    @endif

    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addModal">Tambah Key</button>
    <form action="{{ route('super-admin.keys.import') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".xlsx, .xls">
    <button type="submit" class="btn btn-primary">Import Excel</button>
</form>

    <table class="table table-striped" id="nasabah-table">
        <thead>
        <tr>
            <th>Key</th>
            <th>Jabatan</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($keys as $key)
            <tr>
            <td>{{ $key->key }}</td>
            <td>{{ $key->jabatannama->nama_jabatan }}</td>
            <td>
            <button class="btn btn-danger btn-sm delete-btn" data-key="{{ $key->key }}" data-toggle="modal" data-target="#deleteModal">Delete</button>
            </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Tambah Data Kantorkas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addForm" method="POST" action="{{ route('super-admin.key.add') }}">
                @csrf
                <div class="modal-body">
                <div class="form-group">
                        <label for="addKantorkas">Key</label>
                        <input type="text" class="form-control" id="addKantorkas" name="key" value="{{ $uniqueKey }}" readonly> 
                    </div>
                    <div class="form-group">
                        <label for="addKantorkas">Jabatan</label>
                        <select class="form-control" id="addKantorkas" name="jabatan">
                            <option value="1">Direksi</option>
                            <option value="2">Kepala Cabang</option>
                            <option value="3">Supervisor</option>
                            <option value="4">Admin Kas</option>
                            <option value="5">Account Officer</option>
                        </select>
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

<!-- Modal for Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Data Key</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="deleteForm" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data key ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Delete button click event
    $('.delete-btn').on('click', function() {
    var key = $(this).data('key'); 
    $('#deleteForm').attr('action', '/super-admin/key/delete/' + key); 
});
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