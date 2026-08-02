<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Template System
    |--------------------------------------------------------------------------
    |
    | This option controls which template theme the datatable uses.
    | Supported themes: 'tailwind', 'bootstrap'
    |
    | The 'tailwind' theme provides Tailwind CSS based styling.
    | The 'bootstrap' theme provides Bootstrap 5+ based styling.
    |
    */
    'template' => env('DATATABLE_TEMPLATE', 'tailwind'),

    /*
    |--------------------------------------------------------------------------
    | Default Per Page Options
    |--------------------------------------------------------------------------
    |
    | This option controls the default pagination options that are shown
    | in the datatable. You can modify these to any values you want.
    | Use 'all' to represent "All" records.
    |
    */
    'per_page_options' => [10, 25, 50, 100, 'all'],

    /*
    |--------------------------------------------------------------------------
    | Export Options
    |--------------------------------------------------------------------------
    |
    | Configure the export functionality of the datatable here.
    |
    */
    'export' => [
        'enabled' => true,
        'types' => ['excel', 'pdf'], // supported types: 'excel', 'pdf'
        'orientation' => 'portrait', // portrait or landscape
        'paper_size' => 'a4',
        'dropdown' => [
            'position' => 'top', // top, bottom, both
            'trigger_class' => 'inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-[#f5c542]/20 bg-black/30 text-[#f5f5f0]/70 hover:border-[#f5c542]/50 hover:text-[#f5c542] transition',
            'menu_class' => 'absolute left-0 z-10 mt-2 w-35 origin-top-right rounded-lg bg-[#0a0a0a] border border-[#f5c542]/15 shadow-xl focus:outline-none',
            'item_class' => 'block w-full px-4 py-2 text-sm text-[#f5f5f0]/70 hover:bg-white/[0.04] hover:text-[#f5c542] text-left transition',
            'trigger_text' => 'Export',
            'excel_text' => 'Excel',
            'pdf_text' => 'PDF',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CSS Classes
    |--------------------------------------------------------------------------
    |
    | Here you can modify the CSS classes for various elements of the datatable.
    | This allows for complete customization of the datatable's appearance.
    |
    | Restyled to match the Kadi Kings casino theme: gold (#f5c542) accents on
    | near-black surfaces, Cinzel for anything header-like, and the same
    | glass-card / btn-casino-primary classes used across the rest of the app
    | (defined in resources/css/app.css) rather than re-declaring the same
    | look with raw utilities everywhere.
    |
    */
    'theme' => [
        'wrapper' => 'w-full grid grid-cols-4',

        // Filter panel
        'filter_panel' => 'glass-card transition duration-300 ease-in-out p-4 border-r border-[#f5c542]/10',
        'filter_header' => 'flex justify-between items-center',
        'filter_header_title' => 'font-cinzel text-xs uppercase tracking-widest text-[#6b6b6b]',
        'filter_close_button' => 'inline-flex items-center text-sm font-medium text-[#f5f5f0]/70 disabled:pointer-events-none cursor-pointer hover:text-[#f5c542]',
        'filter_close_button_icon' => 'shrink-0 size-5',
        'filter_content' => 'flex flex-col mt-4 space-y-4',
        'filter_label' => 'text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]',
        'filter_list' => 'list-filter',
        'filter_items' => 'max-w-sm space-y-3',
        'filter_item' => 'flex gap-2 items-center justify-between',
        'filter_input_wrapper' => 'relative',
        'filter_input' => 'py-2.5 px-4 block w-full rounded-lg border border-[#f5c542]/15 bg-black/30 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:ring-1 focus:ring-[#f5c542]/30 disabled:opacity-50 disabled:pointer-events-none',
        'filter_select_wrapper' => 'absolute inset-y-0 start-0 flex items-center text-[#6b6b6b]',
        'filter_select' => 'text-sm block w-30 border-l border-[#f5c542]/15 rounded-l bg-black/30 text-[#f5f5f0] py-3',
        'filter_select_label' => 'sr-only',
        'filter_delete_button' => 'inline-flex items-center text-xs font-medium text-[#f5f5f0]/70 disabled:pointer-events-none cursor-pointer hover:text-red-400',
        'filter_delete_button_wrapper' => '',
        'filter_delete_button_icon' => 'shrink-0 size-5',
        'filter_actions' => 'flex items-center justify-between',
        'filter_add_button' => 'py-2 pl-2 pr-3 inline-flex items-center gap-x-1 text-xs font-semibold rounded-lg border border-[#f5c542]/20 bg-black/30 text-[#f5f5f0]/70 hover:border-[#f5c542]/50 hover:text-[#f5c542] focus:outline-hidden disabled:opacity-50 disabled:cursor-not-allowed transition',
        'filter_add_button_icon' => 'shrink-0 size-5',
        'filter_reset_button' => 'py-2 px-3 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-[#f5c542]/20 text-[#f5f5f0]/70 hover:border-[#f5c542]/40 focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none transition',
        'filter_reset_button_icon' => 'shrink-0 size-5',
        'filter_apply_button' => 'btn-casino-primary py-2 px-3 inline-flex items-center gap-x-2 text-xs rounded-lg disabled:opacity-50 disabled:pointer-events-none',
        'filter_apply_button_icon' => 'shrink-0 size-5',
        'filter_button' => 'py-2 px-2 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-[#f5c542]/20 bg-black/30 text-[#f5f5f0]/70 hover:border-[#f5c542]/50 hover:text-[#f5c542] focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none transition',
        'filter_button_icon' => 'shrink-0 size-5',
        'filter_main_content_span' => '$filter ? "col-span-3" : "col-span-4"',

        'main_wrapper' => 'col-span-4',
        'main_wrapper_with_filter' => 'col-span-3',

        'search_wrapper' => 'pb-4 px-3 pt-3 flex flex-col sm:flex-row items-center justify-between gap-4',
        'controls_wrapper' => 'flex justify-between sm:flex-row items-center gap-4 w-full justify-between pt-3 px-3',

        // Per page select
        'per_page_wrapper' => 'flex items-center gap-2',
        'per_page_select' => 'w-20 py-2 px-3 block rounded-lg border border-[#f5c542]/15 bg-black/30 text-xs text-[#f5f5f0] focus:border-[#f5c542]/50 focus:ring-1 focus:ring-[#f5c542]/30 disabled:opacity-50 disabled:pointer-events-none',
        'per_page_text' => 'text-xs text-[#6b6b6b]',
        'controls_layout_top' => 'flex items-center justify-between gap-4',
        'controls_layout_bottom' => 'flex items-center justify-between gap-2',

        // Search input
        'search_input_wrapper' => 'w-full sm:w-auto relative',
        'search_input' => 'w-full sm:w-64 pl-9 rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:ring-1 focus:ring-[#f5c542]/30 shadow-sm disabled:cursor-not-allowed disabled:opacity-50',
        'search_icon_wrapper' => 'absolute inset-y-0 left-0 flex items-center pl-3',
        'search_icon' => 'h-4 w-4 text-[#6b6b6b]',
        'export_wrapper' => 'flex gap-2 items-center',
        'export_dropdown_wrapper' => 'relative',
        'export_dropdown_arrow' => '-mr-1 ml-2 h-4 w-4',
        'export_button' => 'px-4 py-2 text-sm font-medium text-[#f5f5f0]/70 border border-[#f5c542]/20 hover:border-[#f5c542]/50 hover:text-[#f5c542] rounded-lg transition',
        'export_icon' => 'w-4 h-4 mr-2',

        // Table
        'table_wrapper' => 'glass-card overflow-x-auto p-0',
        'table' => 'min-w-full divide-y divide-white/5',

        // Table head
        'thead' => '',
        'thead_row' => 'border-b border-[#f5c542]/10',
        'th' => 'px-6 py-3 text-left text-[10px] font-semibold text-[#6b6b6b] uppercase tracking-widest',
        'th_sort_button' => 'group inline-flex items-center gap-x-1 hover:text-[#f5c542] transition',
        'th_sort_icon_wrapper' => 'inline-flex flex-col leading-none transition',
        'th_sort_icon_active' => 'size-2.5 text-[#f5c542]',
        'th_sort_icon_inactive' => 'size-2.5 text-gray-700 group-hover:text-[#f5c542]/50',
        'th_text' => 'text-[#6b6b6b]',

        // Table body
        'tbody' => 'divide-y divide-white/5',
        'tr' => 'hover:bg-white/[0.02] transition',
        'td' => 'px-6 py-4 whitespace-nowrap text-sm text-[#f5f5f0]',

        // Column-specific cell styling
        'td_name' => 'font-cinzel font-bold text-[#f5f5f0]',
        'td_key' => 'text-[#6b6b6b] text-xs',
        'td_actions' => 'text-right',

        // Empty state
        'empty_wrapper' => 'px-6 py-12 text-center',
        'empty_content' => 'flex flex-col items-center justify-center',
        'empty_icon' => 'size-10 text-[#6b6b6b] mb-2',
        'empty_text' => 'text-[#6b6b6b] text-sm',

        // Pagination
        'pagination_wrapper' => 'px-6 py-4 border-t border-[#f5c542]/10',

        // Date filter
        'date_filter_button' => 'inline-flex items-center gap-x-2 px-3 py-2 text-sm font-medium rounded-lg border border-[#f5c542]/20 bg-black/30 text-[#f5f5f0]/70 hover:border-[#f5c542]/50 hover:text-[#f5c542]',
        'date_filter_button_icon' => 'size-4',
        'date_filter_modal' => 'relative max-w-md w-full mx-4 bg-[#0a0a0a] rounded-xl shadow-xl border border-[#f5c542]/30',
        'date_filter_header' => 'flex items-center justify-between px-6 py-4 border-b border-[#f5c542]/10',
        'date_filter_title' => 'font-cinzel text-lg font-bold text-[#f5f5f0]',
        'date_filter_close' => 'inline-flex items-center text-sm font-medium text-[#6b6b6b] cursor-pointer hover:text-[#f5c542]',
        'date_filter_body' => 'px-6 py-4 space-y-4',
        'date_filter_column_label' => 'text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b] mb-1 block',
        'date_filter_column_select' => 'w-full py-2 px-3 block rounded-lg border border-[#f5c542]/15 bg-black/30 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:ring-1 focus:ring-[#f5c542]/30',
        'date_filter_date_row' => 'flex items-end gap-3',
        'date_filter_date_group' => 'flex flex-col flex-1',
        'date_filter_date_input' => 'py-2 px-3 block rounded-lg border border-[#f5c542]/15 bg-black/30 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:ring-1 focus:ring-[#f5c542]/30 w-full',
        'date_filter_date_separator' => 'text-sm text-[#6b6b6b] pb-2',
        'date_filter_footer' => 'flex items-center justify-end gap-2 px-6 py-4 border-t border-[#f5c542]/10',
        'date_filter_apply' => 'btn-casino-primary py-2 px-4 text-sm rounded-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed',
        'date_filter_reset' => 'py-2 px-4 text-sm font-medium text-[#f5f5f0]/70 border border-[#f5c542]/20 rounded-lg hover:border-[#f5c542]/40 cursor-pointer',
        'date_filter_cancel' => 'py-2 px-4 text-sm font-medium text-[#f5f5f0]/70 border border-[#f5c542]/20 rounded-lg hover:border-[#f5c542]/40 cursor-pointer',
        'date_filter_badge' => 'inline-flex items-center gap-1 px-2 py-1 text-xs font-medium bg-[#f5c542]/10 text-[#f5c542] rounded-full border border-[#f5c542]/25',
        'date_filter_badge_remove' => 'text-[#f5c542] hover:text-[#f5f5f0] cursor-pointer text-lg leading-none',

        // Custom export modal
        'custom_export_modal' => 'relative max-w-3xl w-full mx-4 bg-[#0a0a0a] rounded-xl shadow-xl border border-[#f5c542]/30',
        'custom_export_header' => 'flex items-center justify-between px-6 py-4 border-b border-[#f5c542]/10',
        'custom_export_title' => 'font-cinzel text-lg font-bold text-[#f5f5f0]',
        'custom_export_close' => 'inline-flex items-center text-sm font-medium text-[#6b6b6b] cursor-pointer hover:text-[#f5c542]',
        'custom_export_body' => 'px-6 py-4 max-h-96 overflow-y-auto',
        'custom_export_select_all' => 'flex gap-3 mb-4',
        'custom_export_select_all_btn' => 'text-sm font-medium text-[#f5c542] hover:text-[#f5f5f0] cursor-pointer',
        'custom_export_deselect_all_btn' => 'text-sm font-medium text-[#6b6b6b] hover:text-[#f5f5f0] cursor-pointer',
        'custom_export_columns' => 'space-y-4',
        'custom_export_group' => '',
        'custom_export_group_title' => 'text-[10px] font-semibold text-[#6b6b6b] uppercase tracking-widest mb-2',
        'custom_export_group_columns' => 'grid grid-cols-2 sm:grid-cols-3 gap-2',
        'custom_export_label' => 'inline-flex items-center gap-2 text-sm text-[#f5f5f0]/70 cursor-pointer hover:text-[#f5f5f0]',
        'custom_export_checkbox' => 'rounded border-[#f5c542]/30 bg-black/30 text-[#f5c542] focus:ring-[#f5c542]/30',
        'custom_export_footer' => 'flex items-center justify-between px-6 py-4 border-t border-[#f5c542]/10',
        'custom_export_type_wrapper' => 'flex items-center gap-2',
        'custom_export_type_label' => 'text-sm text-[#6b6b6b]',
        'custom_export_type_select' => 'py-2 px-3 block rounded-lg border border-[#f5c542]/15 bg-black/30 text-sm text-[#f5f5f0] focus:border-[#f5c542]/50 focus:ring-1 focus:ring-[#f5c542]/30 w-25',
        'custom_export_actions' => 'flex items-center gap-2',
        'custom_export_cancel' => 'py-2 px-4 text-sm font-medium text-[#f5f5f0]/70 border border-[#f5c542]/20 rounded-lg hover:border-[#f5c542]/40 cursor-pointer',
        'custom_export_submit' => 'btn-casino-primary py-2 px-4 text-sm rounded-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Template Classes Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Bootstrap template rendering and styling.
    |
    */
    'bootstrap_theme' => [
        // Container & Layout
        'container' => 'container-fluid px-0',
        'card' => 'card border shadow-sm',
        'card_header' => 'card-header bg-white border-bottom p-3',
        'card_body' => 'card-body',
        'card_footer' => 'card-footer bg-white border-top py-3',

        // Filter Panel
        'filter_panel' => 'card border-0 shadow-sm h-100',
        'filter_header' => 'card-header bg-primary bg-opacity-10 border-bottom py-4',
        'filter_header_title' => 'mb-0 fw-600',
        'filter_close_button' => 'btn-close',
        'filter_content' => 'card-body',
        'filter_items' => 'filter-items space-y-3',
        'filter_item' => 'mb-3',
        'filter_input_group' => 'input-group input-group-sm',
        'filter_input' => 'form-control border-primary border-opacity-25',
        'filter_select' => 'form-select border-primary border-opacity-25',
        'filter_delete_button' => 'btn btn-sm btn-link text-danger text-decoration-none mt-2 d-block w-100',
        'filter_actions' => 'd-flex justify-content-between gap-2',
        'filter_add_button' => 'btn btn-sm btn-primary w-100',
        'filter_reset_button' => 'btn btn-sm btn-outline-warning w-100',
        'filter_apply_button' => 'btn btn-sm btn-success w-100',

        // Header Controls
        'header_row' => 'row g-3 align-items-center',
        'search_col' => 'col-md-6 col-lg-5',
        'controls_col' => 'col-md-6 col-lg-7',
        'search_input_group' => 'input-group input-group-md',
        'search_input' => 'form-control border-secondary border-opacity-25',
        'search_icon' => 'input-group-text bg-white border-secondary border-opacity-25',

        // Controls
        'controls_flex' => 'd-flex gap-2 justify-content-end flex-wrap align-items-center',
        'per_page_group' => 'input-group input-group-sm',
        'per_page_select' => 'form-select form-select-md border-secondary border-opacity-25 w-25',
        'filter_button' => 'btn btn-md btn-outline-primary',
        'export_dropdown' => 'dropdown',
        'export_button' => 'btn btn-md btn-outline-info dropdown-toggle',
        'export_menu' => 'dropdown-menu dropdown-menu-end',
        'export_item' => 'dropdown-item',

        // Table
        'table_responsive' => 'table-responsive',
        'table' => 'table table-hover align-middle mb-0',
        'table_style' => 'font-size: 0.95rem;',
        'thead' => 'table-light',
        'thead_row' => 'fw-600',
        'th' => 'border-bottom-2 text-secondary fw-600 py-3 px-4',
        'th_button' => 'btn btn-link btn-sm text-dark text-decoration-none p-0 d-inline-flex align-items-center',
        'th_button_style' => 'font-weight: 600;',
        'th_sort_icon' => 'ms-2',
        'sort_icon_asc' => 'bi bi-sort-up',
        'sort_icon_desc' => 'bi bi-sort-down',
        'sort_icon_neutral' => 'bi bi-arrow-up-down',
        'sort_icon_color_active' => 'color: #0d6efd; font-size: 1rem;',
        'sort_icon_color_inactive' => 'font-size: 0.9rem;',
        'sort_icon_inactive_class' => 'opacity-50',

        // Tbody
        'tbody' => 'tbody',
        'tr' => 'border-bottom',
        'td' => 'px-4 py-3',

        // Column-specific cell styling
        // 'td_id' => 'font-mono text-gray-500 dark:text-gray-400', // Example: ID column styling
        // 'td_created_at' => 'text-xs', // Example: Date column styling
        // 'td_status' => 'text-center', // Example: Status column styling
        // 'td_email' => 'font-medium', // Example: Email column styling
        // 'td_actions' => 'text-right space-x-2', // Example: Actions column styling

        // Empty State
        'empty_wrapper' => 'text-center py-5',
        'empty_content' => 'text-secondary',
        'empty_icon' => 'bi bi-inbox',
        'empty_icon_style' => 'font-size: 2.5rem;',

        // Pagination
        'pagination_wrapper' => 'd-flex justify-content-between align-items-center',
        'pagination_info' => 'text-muted',
        'pagination_controls' => 'd-flex gap-2',

        // Date filter
        'date_filter_button' => 'btn btn-sm btn-outline-secondary',
        'date_filter_button_icon' => '',
        'date_filter_modal_backdrop' => 'modal-backdrop fade show',
        'date_filter_modal_wrapper' => 'modal d-block',
        'date_filter_modal_dialog' => 'modal-dialog modal-dialog-centered',
        'date_filter_modal_content' => 'modal-content border shadow',
        'date_filter_header' => 'modal-header',
        'date_filter_title' => 'modal-title',
        'date_filter_close' => 'btn-close',
        'date_filter_body' => 'modal-body',
        'date_filter_column_label' => 'form-label small text-secondary mb-1',
        'date_filter_column_select' => 'form-select',
        'date_filter_date_row' => 'd-flex align-items-end gap-3',
        'date_filter_date_group' => 'd-flex flex-column flex-fill',
        'date_filter_date_input' => 'form-control',
        'date_filter_date_separator' => 'text-secondary pb-1 small',
        'date_filter_footer' => 'modal-footer',
        'date_filter_apply' => 'btn btn-sm btn-primary',
        'date_filter_reset' => 'btn btn-sm btn-outline-secondary',
        'date_filter_cancel' => 'btn btn-sm btn-secondary',
        'date_filter_badge' => 'badge bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center gap-1 text-white',
        'date_filter_badge_remove' => 'btn-close btn-close-sm',

        // Custom Export Modal
        'custom_export_backdrop' => 'modal-backdrop fade show',
        'custom_export_wrapper' => 'modal d-block',
        'custom_export_dialog' => 'modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable',
        'custom_export_content' => 'modal-content border shadow',
        'custom_export_header' => 'modal-header',
        'custom_export_title' => 'modal-title',
        'custom_export_close' => 'btn-close',
        'custom_export_body' => 'modal-body',
        'custom_export_select_all' => 'd-flex gap-2 mb-3',
        'custom_export_select_all_btn' => 'btn btn-sm btn-outline-primary',
        'custom_export_deselect_all_btn' => 'btn btn-sm btn-outline-secondary',
        'custom_export_group_title' => 'text-muted text-uppercase small fw-bold mb-2',
        'custom_export_group_columns' => 'row g-2',
        'custom_export_checkbox_col' => 'col-6 col-md-4',
        'custom_export_checkbox_wrapper' => 'form-check',
        'custom_export_checkbox' => 'form-check-input',
        'custom_export_label' => 'form-check-label',
        'custom_export_footer' => 'modal-footer d-flex justify-content-between',
        'custom_export_type_wrapper' => 'd-flex align-items-center gap-2 flex-wrap',
        'custom_export_type_label' => 'form-label mb-0 text-nowrap',
        'custom_export_type_select' => 'form-select form-select-sm w-auto',
        'custom_export_actions' => 'd-flex gap-2',
        'custom_export_cancel' => 'btn btn-sm btn-secondary',
        'custom_export_submit' => 'btn btn-sm btn-primary',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Pagination Options
    |--------------------------------------------------------------------------
    |
    | This option controls the default pagination links that are shown
    | in the datatable. You can modify these to any values you want.
    | default use paginate, you can change to simplePaginate
    |
    */
    'default_pagination' => 'paginate',

    /*
    |--------------------------------------------------------------------------
    | Default Sort Direction
    |--------------------------------------------------------------------------
    |
    | This option controls the default sort direction for all columns
    | in the datatable.
    |
    */
    'default_sort_direction' => 'asc',

    /*
    |--------------------------------------------------------------------------
    | Debounce Time (ms)
    |--------------------------------------------------------------------------
    |
    | This option controls the debounce time for the search input in milliseconds.
    |
    */
    'search_debounce' => 300,

    /*
    |--------------------------------------------------------------------------
    | Advanced Filter
    |--------------------------------------------------------------------------
    |
    | This option controls the visibility of the advanced filter.
    |
    */
    'advanced_filter' => true,
];
