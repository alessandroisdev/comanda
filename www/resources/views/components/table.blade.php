@props(['id'])

<div class="table-responsive rounded-3 border border-slate-700 bg-slate-900 shadow-sm p-3">
    <table id="{{ $id }}" class="table table-striped table-hover align-middle w-100 table-dark-custom m-0">
        {{ $slot }}
    </table>
</div>

<style>
    /* Estilos personalizados para o DataTables em Modo Escuro Premium */
    .table-dark-custom {
        --bs-table-bg: #0f172a !important; /* slate-900 */
        --bs-table-striped-bg: #1e293b !important; /* slate-800 */
        --bs-table-hover-bg: #334155 !important; /* slate-700 */
        color: #f1f5f9 !important; /* slate-100 */
        border-color: #334155 !important;
    }
    .table-dark-custom th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        background-color: #1e293b !important; /* slate-800 */
        color: #94a3b8 !important; /* slate-400 */
        border-bottom: 2px solid #475569 !important; /* slate-600 */
        padding: 12px 16px !important;
    }
    .table-dark-custom td {
        padding: 14px 16px !important;
        border-bottom: 1px solid #1e293b !important;
    }
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_processing, 
    .dataTables_wrapper .dataTables_paginate {
        color: #94a3b8 !important; /* slate-400 */
        font-size: 0.875rem;
        margin-top: 15px;
        margin-bottom: 10px;
    }
    .dataTables_wrapper .dataTables_filter input {
        background-color: #1e293b !important;
        border: 1px solid #334155 !important;
        color: #f1f5f9 !important;
        border-radius: 6px;
        padding: 6px 12px;
        margin-left: 8px;
    }
    .dataTables_wrapper .dataTables_length select {
        background-color: #1e293b !important;
        border: 1px solid #334155 !important;
        color: #f1f5f9 !important;
        border-radius: 6px;
        padding: 4px 8px;
        margin: 0 4px;
    }
    .page-link {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    .page-item.active .page-link {
        background-color: #3b82f6 !important; /* blue-500 */
        border-color: #3b82f6 !important;
    }
    .page-item.disabled .page-link {
        background-color: #0f172a !important;
        color: #475569 !important;
    }
</style>
