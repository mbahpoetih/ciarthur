<form id="form_tambah" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <div class="row m-0">

            <div class="col-sm-3 text-left">
                <label class="mt-2" for="input_tambah_nama">Prodi</label>
            </div>
            <div class="col-sm-9">
                <input type="text" id="input_tambah_nama" name="nama" class="form-control" placeholder="Masukkan nama fakultas" autocomplete="off" onkeydown="return event.keyCode != 13;">
                <?= validation_feedback('tambah', 'nama') ?>
            </div>

        </div>
    </div>
    <div class="form-group">
        <div class="row m-0">
            <div class="col-sm-3 text-left">
                <label class="mt-2" for="select_tambah_fakultas_id">Fakultas</label>
            </div>
            <div class="col-sm-9">
                <select id="select_tambah_fakultas_id" name="fakultas_id" class="form-control text-left" onkeydown="return event.keyCode != 13;">
                </select>
                <?= validation_feedback('tambah', 'fakultas_id') ?>
            </div>
        </div>
    </div>
</form>