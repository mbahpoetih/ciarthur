<div class="card">
    <div class="card-body">
        <h3 class="text-center">Ubah Data <?= $title ?></h5>
            <p class="text-muted text-center mb-0"><?= sistem()->nama ?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form id="form_ubah" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="input_ubah_judul">Judul</label>
                        <input type="text" name="judul" id="input_ubah_judul" value="<?= @$data->judul ?>" autocomplete="off" class="form-control">
                        <?= validation_feedback('tambah', 'judul') ?>
                    </div>

                    <div class="form-group">
                        <label for="input_ubah_konten">Konten</label>
                        <textarea name="konten" id="input_ubah_konten" class="form-control tinymce"><?= @$data->konten ?></textarea>
                        <?= validation_feedback('tambah', 'konten') ?>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header pt-2 pb-2 d-flex flex-row justify-content-between align-items-center">
                            <div>Kategori</div>
                            <button type="button" id="tambah_kategori" class="btn btn-info btn-sm" title="Tambah Kategori"><i class="fa fa-plus"></i></button>
                        </div>
                        <div class="card-body pt-3 pb-3 pr-3 pl-3">
                            <select name="kategori_id" id="select_ubah_kategori_id" class="form-control">
                                <option></option>
                            </select>
                            <?= validation_feedback('ubah', 'kategori_id') ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header pt-2 pb-2">
                            Tag
                        </div>
                        <div class="card-body pt-3 pb-3 pr-3 pl-3">
                            <select name="tags[]" multiple id="select_ubah_tags" class="form-control">
                                <option></option>
                            </select>
                            <?= validation_feedback('ubah', 'tag') ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header pt-2 pb-2">
                            Gambar
                        </div>
                        <div class="card-body pt-3 pb-3 pr-3 pl-3">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" id="input_ubah_gambar" name="gambar" class="form-control">
                                            <label class="custom-file-label overflow-hidden" for="input_ubah_gambar">Choose file</label>
                                        </div>
                                        <?= validation_feedback('ubah', 'gambar') ?>
                                    </div>
                                </div>
                                <div class="col-sm-12 mt-2">
                                    <img id="show-cover" class="rounded img-fluid" src="<?= @$data->gambar && file_exists("./uploads/berita/" . @$data->gambar) ? base_url("uploads/berita/" . @$data->gambar) : base_url('assets/unnamed.png') ?>" alt="Image Cover">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <a href="<?= base_url($uri_segment) ?>" class="btn btn-secondary btn-pill">Kembali</a>
                    <button type="submit" class="btn btn-primary btn-pill">Simpan</button>
                    <button class="btn btn-primary loader" type="button" disabled style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Loading...
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- END Page Content -->