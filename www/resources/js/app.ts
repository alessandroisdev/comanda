import 'bootstrap';
import jQuery from 'jquery';
import DataTable from 'datatables.net-bs5';

// Expor jQuery e DataTables globalmente no escopo do window
(window as any).$ = (window as any).jQuery = jQuery;
(window as any).DataTable = DataTable;

console.log('Comanda Frontend: Bootstrap 5, DataTables.net e TypeScript carregados com sucesso localmente!');
