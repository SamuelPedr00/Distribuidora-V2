<!-- Modal de Mensagens -->
<div id="msgModal" class="modal fade" tabindex="-1" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" id="msgModalContent">
            <div class="modal-header">
                <h5 class="modal-title" id="msgModalTitle">Notificação</h5>
                <button type="button" class="btn-close" id="msgModalClose" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i id="msgModalIcon" class="fas fa-info-circle fs-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p id="msgModalText" class="mb-0"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<style>
    #msgModal .border-success {
        border-color: #198754 !important;
        border-width: 2px;
    }

    #msgModal .border-success .modal-header {
        background-color: rgba(25, 135, 84, 0.1);
    }

    #msgModal .border-success #msgModalIcon {
        color: #198754;
    }

    #msgModal .border-danger {
        border-color: #dc3545 !important;
        border-width: 2px;
    }

    #msgModal .border-danger .modal-header {
        background-color: rgba(220, 53, 69, 0.1);
    }

    #msgModal .border-danger #msgModalIcon {
        color: #dc3545;
    }

    #msgModal .border-warning {
        border-color: #ffc107 !important;
        border-width: 2px;
    }

    #msgModal .border-warning .modal-header {
        background-color: rgba(255, 193, 7, 0.1);
    }

    #msgModal .border-warning #msgModalIcon {
        color: #ffc107;
    }
</style>
