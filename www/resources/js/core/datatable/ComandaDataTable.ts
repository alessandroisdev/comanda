import jQuery from 'jquery';
import DataTable from 'datatables.net-bs5';

export interface ComandaDataTableConfig {
    tableId: string;
    ajaxUrl: string;
    columns: any[];
    order?: any[];
    extraParams?: () => Record<string, any>;
    onDeleteSuccess?: () => void;
}

export class ComandaDataTable {
    private tableId: string;
    private ajaxUrl: string;
    private columns: any[];
    private order: any[];
    private extraParams?: () => Record<string, any>;
    private onDeleteSuccess?: () => void;
    private instance: any = null;

    constructor(config: ComandaDataTableConfig) {
        this.tableId = config.tableId;
        this.ajaxUrl = config.ajaxUrl;
        this.columns = config.columns;
        this.order = config.order || [[0, 'desc']];
        this.extraParams = config.extraParams;
        this.onDeleteSuccess = config.onDeleteSuccess;

        this.init();
    }

    /**
     * Inicializa a instância física do DataTable.
     */
    private init(): void {
        const tableElement = jQuery(`#${this.tableId}`);
        if (tableElement.length === 0) {
            console.warn(`[ComandaDataTable] Elemento #${this.tableId} não encontrado no DOM.`);
            return;
        }

        // Adicionar classes visuais elegantes do Bootstrap 5
        tableElement.addClass('table table-striped table-hover align-middle w-100 table-dark-custom');

        // Capturar o token CSRF obrigatório do Laravel a partir da tag meta correspondente
        const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || '';

        this.instance = tableElement.DataTable({
            processing: true,
            serverSide: true,
            order: this.order,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            language: {
                url: '/js/datatables-pt-BR.json', // Tradução localizada que podemos carregar
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>'
            },
            ajax: {
                url: this.ajaxUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: (d: any) => {
                    // Mesclar parâmetros padrão do DataTables com filtros customizados
                    if (this.extraParams) {
                        const extra = this.extraParams();
                        return jQuery.extend({}, d, extra);
                    }
                    return d;
                },
                error: (xhr) => {
                    console.error('[ComandaDataTable] Erro na requisição AJAX:', xhr);
                    this.showToast('Erro ao carregar os dados da tabela.', 'danger');
                }
            },
            columns: this.columns
        });

        this.registerEvents();
    }

    /**
     * Registra listeners de eventos comuns da tabela, como deleção.
     */
    private registerEvents(): void {
        const tableElement = jQuery(`#${this.tableId}`);

        // Ações genéricas de deleção AJAX vinculadas a elementos com class="btn-delete-row"
        tableElement.on('click', '.btn-delete-row', (e) => {
            e.preventDefault();
            const btn = jQuery(e.currentTarget);
            const uuid = btn.data('uuid');
            const deleteUrl = btn.data('url');

            if (!uuid || !deleteUrl) return;

            this.confirmDelete(uuid, deleteUrl);
        });
    }

    /**
     * Lança a confirmação de deleção usando modal ou toast, evitando alert/confirm.
     */
    private confirmDelete(uuid: string, deleteUrl: string): void {
        // Como o confirm() nativo é expressamente proibido, usaremos um modal Bootstrap criado sob demanda
        const modalId = `modal-delete-${uuid}`;
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-slate-900 border border-slate-700 text-white rounded-3 shadow-lg">
                        <div class="modal-header border-bottom border-slate-700">
                            <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Confirmar Exclusão</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Tem certeza de que deseja excluir este registro de forma definitiva? Esta ação registrará um log de auditoria.</p>
                        </div>
                        <div class="modal-footer border-top border-slate-700">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger btn-confirm-delete-action">Excluir</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        jQuery('body').append(modalHtml);
        const modalElement = jQuery(`#${modalId}`);
        const bootstrapModal = new (window as any).bootstrap.Modal(modalElement[0]);
        bootstrapModal.show();

        modalElement.find('.btn-confirm-delete-action').on('click', () => {
            const btn = modalElement.find('.btn-confirm-delete-action');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');

            const csrfToken = jQuery('meta[name="csrf-token"]').attr('content') || '';

            jQuery.ajax({
                url: deleteUrl,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: (response) => {
                    bootstrapModal.hide();
                    modalElement.remove();
                    this.showToast('Registro excluído com sucesso!', 'success');
                    this.reload();
                    if (this.onDeleteSuccess) {
                        this.onDeleteSuccess();
                    }
                },
                error: (xhr) => {
                    console.error('[ComandaDataTable] Erro ao excluir registro:', xhr);
                    btn.prop('disabled', false).text('Excluir');
                    bootstrapModal.hide();
                    modalElement.remove();
                    this.showToast('Erro ao excluir o registro. Verifique suas permissões.', 'danger');
                }
            });
        });

        // Limpar DOM ao fechar modal
        modalElement.on('hidden.bs.modal', () => {
            modalElement.remove();
        });
    }

    /**
     * Recarrega os dados do DataTable mantendo a paginação.
     */
    public reload(): void {
        if (this.instance) {
            this.instance.ajax.reload(null, false);
        }
    }

    /**
     * Exibe um Toast do Bootstrap 5 de forma dinâmica e elegante.
     */
    private showToast(message: string, type: 'success' | 'danger' | 'warning' | 'info'): void {
        const toastContainerId = 'comanda-toast-container';
        let container = jQuery(`#${toastContainerId}`);
        
        if (container.length === 0) {
            jQuery('body').append(`<div id="${toastContainerId}" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>`);
            container = jQuery(`#${toastContainerId}`);
        }

        const toastId = `toast-${Date.now()}`;
        const bgClass = type === 'success' ? 'bg-success' : type === 'danger' ? 'bg-danger' : type === 'warning' ? 'bg-warning text-dark' : 'bg-info text-dark';
        
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0 rounded-3 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body font-sans fw-semibold">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        container.append(toastHtml);
        const toastElement = jQuery(`#${toastId}`);
        const bootstrapToast = new (window as any).bootstrap.Toast(toastElement[0]);
        bootstrapToast.show();

        toastElement.on('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
}
