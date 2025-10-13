<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-new">
            <div class="modal-body">
                <div></div>
                <i id="modalM_logo" style="font-size:100px;" class=""></i>
                <h4 class="mt-3" id="modalM_title"></h4>
                <p id="modalM_description"></p>
                <button type="button" id="btnConfirmModal" class="btn btn-success">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<button type="button" id="modalBtnReport" class="btn btn-success d-none" data-bs-toggle="modal" data-bs-target="#confirmModal">
    Abrir Confirmación
</button>

<style>
    .modal-content-new {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
    }
    .successModal{
        color: #14e407 !important;
    }
    .dangerModal{
        color:rgb(228, 7, 7) !important;
    }
    .warningModal{
        color:rgb(228, 195, 7) !important;
    }
</style>
