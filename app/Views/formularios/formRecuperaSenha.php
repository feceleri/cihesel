<?= $this->extend('layout/templateLogin') ?>
<?= $this->section('conteudo') ?>
<form action="#">
    <div class="form-group row">
        <label class="col-md-3 col-form-label">User</label>
        <div class="col-md-9">
            <input type="text" class="form-control">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-3 col-form-label">Password</label>
        <div class="col-md-9">
            <input type="text" class="form-control">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-3 col-form-label">Password</label>
        <div class="col-md-9">
            <input type="text" class="form-control">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-3 col-form-label">Password</label>
        <div class="col-md-9">
            <input type="text" class="form-control">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-md-3 col-form-label">Password</label>
        <div class="col-md-9">
            <input type="text" class="form-control">
        </div>
    </div>
    <div class="text-right">
        <button type="submit" class="btn btn-secondary float-start">Esqueci a senha</button>
        <button type="submit" class="btn btn-primary float-end">Login</button>
        <p><br></p>
    </div>
</form>
<?= $this->endSection() ?>