{{-- jQuery + DataTables (Yajra). Include sekali per halaman yang pakai DataTable. --}}
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>

<style>
    /* Tweak DataTables agar match tema Tailwind project (#a23f66 / #fdf2f5). */
    .dt-yajra-wrap { padding: 0 0.75rem 0.75rem; }
    .dt-yajra-wrap thead th { border-bottom: 1px solid #f1e4e8 !important; background: #fdf2f5; color: #334155; font-weight: 600; }
    .dt-yajra-wrap tbody tr:hover { background: #fdf8fa; }
    .dt-yajra-wrap tbody td { padding: 0.75rem 1rem; vertical-align: top; border-bottom: 1px solid #f5e9ed; }
    .dt-yajra-wrap thead th { padding: 0.75rem 1rem; }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { color: #475569; font-size: 0.875rem; padding-top: 0.75rem; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #a23f66 !important; color: #fff !important; border-color: #a23f66 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #fdf2f5 !important; color: #a23f66 !important; border-color: #eedde2 !important;
    }
    .dataTables_filter input { border: 1px solid #ece4e8; border-radius: 0.5rem; padding: 0.375rem 0.75rem; }
    .dataTables_length select { border: 1px solid #ece4e8; border-radius: 0.5rem; padding: 0.25rem 0.5rem; }
    .dataTables_processing { background: rgba(255,255,255,0.85) !important; }
</style>
