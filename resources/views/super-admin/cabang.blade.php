@extends("layouts.master")
@section("main-content")
<div class="dashboard-container">
    @if (session("success"))
    <div class="alert alert-success">
        {{session("success") }}
    </div>
    @endif

    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addModal">Tambah Nasabah</button>
    <table class="table table-striped" id="nasabah-table">
        <thead>
        <tr>
            <th>ID Cabang</th>
            <th>Nama Cabang</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($cabangs as $cabang)
            <tr>
                <td>{{ $cabang->id_cabang }}</td>
                <td>{{ $cabang->nama_cabang}}</td>
                <td>
            <button class="btn btn-danger btn-sm delete-btn" data-id_cabang="{{ $cabang->id_cabang }}" data-toggle="modal" data-target="#deleteModal">Delete</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Tambah Data Cabang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addForm" method="POST" action="{{ route('super-admin.cabang.add') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="addCabang">Cabang</label>
                        <input type="text" class="form-control" id="addCabang" name="nama_cabang"> 
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

<script>
   // Delete button click event
    $('.delete-btn').on('click', function() {
    var id_cabang = $(this).data('id_cabang'); 
    $('#deleteForm').attr('action', '/super-admin/cabang/delete/' + id_cabang); 
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