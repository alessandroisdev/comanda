import * as bootstrap from 'bootstrap';
(window as any).bootstrap = bootstrap;
import jQuery from 'jquery';
import DataTable from 'datatables.net-bs5';
import { ComandaDataTable } from './core/datatable/ComandaDataTable';

// Expor utilitários globalmente no escopo do window
(window as any).$ = (window as any).jQuery = jQuery;
(window as any).DataTable = DataTable;
(window as any).ComandaDataTable = ComandaDataTable;

console.log('Comanda Frontend: Bootstrap 5, DataTables.net, TypeScript e ComandaDataTable carregados!');
