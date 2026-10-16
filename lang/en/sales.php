<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Sales Orders
    |--------------------------------------------------------------------------
    */
    'sales_orders' => [
        'page_title' => 'Sales Orders - Cooca',
        'header_title' => 'Sales Orders',
        'header_subtitle' => 'Manage confirmed customer orders, shipment stock allocation, and issue formal billing Invoices.',
        'module_title' => 'Sales Orders (SO)',
        'module_subtitle' => 'Confirmed customer order pipeline, warehouse stock allocation, and invoice generation.',
        'btn_create' => 'Create Sales Order',

        // KPI Summary
        'kpi_total_orders' => 'Total Sales Orders',
        'kpi_recorded' => 'Recorded',
        'kpi_confirmed' => 'Confirmed',
        'kpi_ready_process' => 'Ready to process',
        'kpi_partially_fulfilled' => 'Partially Fulfilled',
        'kpi_partial' => 'Partial',
        'kpi_fulfilled' => 'Fulfilled',
        'kpi_completed' => 'Completed',

        // Controls & Search
        'search_placeholder' => 'Search SO number or customer name...',
        'filter_all' => 'All (:count)',
        'filter_confirmed' => 'Confirmed (:count)',
        'filter_partially_fulfilled' => 'Partial (:count)',
        'filter_fulfilled' => 'Fulfilled (:count)',

        // Table
        'col_so_number' => 'SO Number',
        'col_customer' => 'Customer',
        'col_order_date' => 'Order Date',
        'col_expected_delivery' => 'Delivery ETA',
        'col_tax' => 'Tax',
        'col_total_amount' => 'Order Value',
        'col_fulfillment_status' => 'Fulfillment Status',
        'col_actions' => 'Actions',
        'btn_detail' => 'Details',

        // Status Badges
        'status_fulfilled' => 'Fulfilled',
        'status_partially_fulfilled' => 'Partial',
        'status_confirmed' => 'Confirmed',

        // Empty States
        'empty_title' => 'No Sales Orders Yet',
        'empty_desc' => 'Create a new sales order to lock stock allocations and issue formal billing invoices.',
        'btn_create_first' => 'Create First Order',
        'empty_mobile' => 'No sales orders registered yet.',

        // Pagination
        'pagination_showing' => 'Showing',
        'pagination_of' => 'of',
        'pagination_orders' => 'orders',

        // Form Create SO
        'create_page_title' => 'Create Sales Order (SO) - Cooca',
        'create_header_title' => 'Create Sales Order',
        'create_header_subtitle' => 'Create a new confirmed sales order - directly or converted from a quotation.',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_sales_orders' => 'Sales Orders',
        'breadcrumb_create' => 'Create New',
        'form_title' => 'Sales Order Form',
        'form_subtitle' => 'Customer order agreement document and delivery schedule.',
        'btn_cancel' => 'Cancel',
        'btn_confirm_order' => 'Confirm Order',
        'validation_errors_title' => 'Please check the following form errors:',

        // Step 1: Quotation Reference
        'step1_title' => '1. Quotation Reference',
        'step1_desc' => 'Select an approved customer quotation to automatically populate items and details.',
        'quotation_ref_label' => 'Related Quotation Document',
        'optional_label' => '(optional)',
        'quotation_direct_order' => '- Direct Order (No Quotation) -',
        'quotation_selected_badge' => 'Selected Quotation',
        'quotation_auto_loaded' => 'Customer details, discount, and product items automatically loaded.',

        // Step 2: Customer & Shipping Info
        'step2_title' => '2. Customer & Shipping Information',
        'step2_desc' => 'Specify the buyer, document number, order date, and destination delivery address.',
        'customer_label' => 'Customer',
        'customer_select_placeholder' => '- Select Customer -',
        'so_number_label' => 'Sales Order No.',
        'order_date_label' => 'Order Date',
        'expected_delivery_label' => 'Delivery ETA',
        'shipping_address_label' => 'Shipping Address',
        'shipping_address_placeholder' => 'e.g.: 45 Sudirman Ave, Warehouse Complex Block A...',

        // Step 3: Line Items
        'step3_title' => '3. Order Line Items',
        'step3_desc' => 'Select products, set quantities, agreed unit pricing, and item discounts.',
        'btn_add_item' => 'Add Row',
        'col_item_product' => 'Product / Item',
        'col_item_unit_price' => 'Unit Price (IDR)',
        'col_item_qty' => 'Quantity',
        'col_item_discount' => 'Discount (IDR)',
        'col_item_subtotal' => 'Subtotal (IDR)',
        'select_product_placeholder' => '- Select Physical Product or Service -',
        'optgroup_goods' => 'Physical Goods / Products',
        'optgroup_services' => 'Services (No Inventory)',
        'badge_service_item' => 'Service (No Inventory)',
        'badge_goods_item' => 'Physical Product',
        'btn_remove_item_tooltip' => 'Remove row',

        // Notes & Financials
        'notes_label' => 'Order & Delivery Notes',
        'notes_placeholder' => 'Delivery instructions, recipient courier contact, or specific customer requests...',
        'subtotal_items_label' => 'Items Subtotal:',
        'order_discount_label' => 'Order Discount (IDR):',
        'tax_vat_label' => 'VAT Tax (%):',
        'total_order_value_label' => 'Total Order Value:',
        'btn_submit_create' => 'Confirm & Create Order',

        // Confirmation Dialog
        'dialog_confirm_title' => 'Create Sales Order?',
        'dialog_confirm_prompt' => 'Confirm issuance of this sales order? The order data will be immediately saved.',
        'dialog_btn_cancel' => 'Cancel',
        'dialog_btn_confirm' => 'Confirm',
        'alert_select_customer' => 'Please select a customer first.',

        // Detail / Show View
        'show_page_title' => 'Sales Order :number - Cooca',
        'show_header_title' => 'Sales Order Details',
        'show_header_subtitle' => 'Manage order fulfillment status, verify goods breakdown, and issue invoices.',
        'btn_generate_invoice' => 'Issue Invoice',
        'btn_print' => 'Print',
        'btn_back' => 'Back',
        'summary_customer_label' => 'Ordering Customer',
        'summary_issued_on' => 'Issued on :date',
        'summary_eta' => 'Delivery ETA: :date',
        'summary_eta_default' => 'As agreed',
        'origin_quotation_label' => 'Derived from Quotation:',
        'btn_view_quotation' => 'View Quotation',
        'invoice_issued_label' => 'Official Invoice Issued:',
        'btn_open_invoice' => 'Open Invoice',

        // Printable Document Card
        'doc_default_address' => 'Business operational address',
        'doc_heading_title' => 'SALES ORDER',
        'doc_buyer_label' => 'BUYER / CUSTOMER:',
        'doc_shipping_address_label' => 'SHIPPING ADDRESS:',
        'doc_same_as_customer' => 'Same as primary customer address',
        'doc_col_num' => 'No.',
        'doc_col_desc' => 'Product Description',
        'doc_col_unit_price' => 'Unit Price',
        'doc_col_qty' => 'Quantity',
        'doc_col_fulfilled' => 'Fulfilled',
        'doc_col_subtotal' => 'Subtotal',
        'doc_notes_label' => 'ORDER NOTES:',
        'doc_subtotal_label' => 'Subtotal:',
        'doc_discount_label' => 'Order Discount:',
        'doc_tax_label' => 'VAT Tax (:percent%):',
        'doc_grand_total_label' => 'Total Invoice Value:',

        // Signatures
        'sig_created_by' => 'Created By,',
        'sig_sales_dept' => 'Sales Department',
        'sig_approved_by' => 'Approved By,',
        'sig_supervisor' => 'Supervisor / Manager',
        'sig_received_by' => 'Received By,',

        // Invoice Modal
        'modal_invoice_title' => 'Issue Invoice?',
        'modal_invoice_prompt' => 'Issue official billing Invoice from this sales order?',
        'modal_btn_cancel' => 'Cancel',
        'modal_btn_issue' => 'Issue',
    ],

    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */
    'quotations' => [
        'page_title' => 'Quotations - Cooca',
        'header_title' => 'Quotations',
        'header_subtitle' => 'Create official commercial quotations for clients and convert to sales orders',
        'module_title' => 'Quotations',
        'module_subtitle' => 'Issue B2B commercial price quotations and convert to Sales Orders (SO) in 1 click.',
        'btn_create_new' => 'Create New Quotation',

        // KPI Summary
        'kpi_total_issued' => 'Total Quotations Issued',
        'kpi_docs_unit' => 'Documents',
        'kpi_pipeline' => 'Pipeline',
        'kpi_accumulated_value' => 'Accumulated Quotation Value',
        'kpi_est_revenue' => 'Estimated Revenue',
        'kpi_deal_accepted' => 'Accepted Deals',
        'kpi_accepted_unit' => 'Accepted',
        'kpi_ready_convert' => 'Ready to Convert',

        // Controls & Search
        'search_placeholder' => 'Search quotation number or customer name...',
        'filter_all_status' => 'All Statuses',
        'status_sent' => 'Sent',
        'status_accepted' => 'Accepted',
        'status_rejected' => 'Rejected',
        'status_expired' => 'Expired',
        'btn_reset' => 'Reset',

        // Table
        'col_quotation_number' => 'Quotation No.',
        'col_customer' => 'Customer',
        'col_date' => 'Issue Date',
        'col_expiry_date' => 'Expiry Date',
        'col_total_amount' => 'Quotation Value',
        'col_status' => 'Status',
        'col_actions' => 'Actions',
        'btn_detail' => 'Details',
        'empty_title' => 'No price quotations found.',

        // Form Create Quotation
        'create_page_title' => 'Create New Price Quotation',
        'create_header_title' => 'Create New Quotation',
        'create_header_subtitle' => 'Issue formal commercial quotation to prospective customers or B2B clients',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_quotations' => 'Quotations',
        'breadcrumb_create' => 'Create New',
        'btn_back_to_list' => 'Back to List',

        // Section 1: Client & Date
        'sec1_title' => '1. Client & Date Information',
        'customer_label' => 'Customer / Client',
        'customer_select_placeholder' => '-- Select Customer --',
        'quotation_number_label' => 'Quotation No.',
        'date_issued_label' => 'Issue Date',
        'expiry_date_label' => 'Expiry Date',

        // Section 2: Items
        'sec2_title' => '2. Quotation Line Items',
        'sec2_desc' => 'Specify commercial product list, pricing, and order quantities.',
        'btn_add_item' => 'Add Row',
        'col_item_product' => 'Product / Item',
        'col_item_price' => 'Price (IDR)',
        'col_item_qty' => 'Quantity',
        'col_item_total' => 'Total (IDR)',
        'select_product_placeholder' => '-- Select Physical Product or Service --',
        'optgroup_goods' => 'Physical Goods / Products',
        'optgroup_services' => 'Services (No Inventory)',
        'badge_service' => 'Service (No Inventory)',
        'badge_goods' => 'Physical Product',

        // Section 3: Notes & Summary
        'notes_terms_label' => 'Notes & Quotation Terms',
        'notes_terms_placeholder' => 'e.g.: Price includes shipping. 50% deposit required upon PO issuance...',
        'subtotal_label' => 'Subtotal:',
        'discount_label' => 'Discount (IDR):',
        'tax_vat_label' => 'VAT Tax (IDR):',
        'total_quotation_label' => 'Total Quotation:',
        'btn_cancel' => 'Cancel',
        'btn_publish' => 'Publish Quotation',

        // Detail / Show View
        'show_page_title' => 'Quotation :number',
        'show_header_title' => 'Quotation Document Details',
        'show_header_subtitle' => 'Official commercial price quotation document for customer',
        'client_meta' => 'Client: :name • Total: IDR :total',
        'btn_convert_to_so' => 'Convert to Sales Order',
        'btn_view_so' => 'View SO (:number)',
        'btn_print' => 'Print',
        'btn_whatsapp' => 'WhatsApp',
        'btn_close' => 'Close',

        // Printable Sheet
        'doc_business_fallback' => 'Business Operations',
        'doc_heading_title' => 'Price Quotation',
        'doc_date_label' => 'Date:',
        'doc_valid_until_label' => 'Valid Until:',
        'doc_to_customer_label' => 'ADDRESSED TO:',
        'doc_col_num' => 'No.',
        'doc_col_desc' => 'Item Description',
        'doc_col_unit_price' => 'Unit Price',
        'doc_col_qty' => 'Quantity',
        'doc_col_subtotal' => 'Subtotal',
        'doc_additional_notes' => 'Additional Notes:',
        'doc_digital_notice' => 'Issued digitally by COOCA ID.',
        'doc_subtotal_label' => 'Subtotal:',
        'doc_discount_label' => 'Discount:',
        'doc_tax_label' => 'Tax (VAT):',
        'doc_total_label' => 'Total Quotation:',

        // Convert Modal
        'modal_convert_title' => 'Convert to Sales Order?',
        'modal_convert_prompt' => 'This quotation will be converted into an official Sales Order and made available in the sales module.',
        'modal_btn_cancel' => 'Cancel',
        'modal_btn_convert' => 'Convert',
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------------
    */
    'invoices' => [
        'page_title' => 'Sales Invoices - Cooca',
        'header_title' => 'Invoice & Billing Management',
        'header_subtitle' => 'Manage commercial sales invoices, track receivables, and log incoming payments',
        'module_title' => 'Sales Invoices',
        'module_subtitle' => 'Manage commercial invoices, track receivables, and log payments',
        'btn_export_csv' => 'Export CSV',
        'btn_create_new' => 'Create New Invoice',

        // KPI Summary
        'kpi_total_invoiced' => 'Total Invoiced Value',
        'kpi_invoiced_sub' => 'Invoices Issued',
        'kpi_total_paid' => 'Total Paid',
        'kpi_paid_sub' => 'Cash Collected',
        'kpi_unpaid_receivables' => 'Outstanding Receivables',
        'kpi_unpaid_sub' => 'Unsettled',
        'kpi_overdue' => 'Overdue Invoices',
        'kpi_overdue_sub' => 'Requires Follow-up',

        // Controls & Search
        'search_placeholder' => 'Search invoice number or customer name...',
        'filter_all_status' => 'All Statuses',
        'status_draft' => 'Draft',
        'status_sent' => 'Sent',
        'status_partially_paid' => 'Partial',
        'status_paid' => 'Paid',
        'status_overdue' => 'Overdue',
        'status_cancelled' => 'Cancelled',

        // Table
        'col_invoice_number' => 'Invoice No.',
        'col_customer' => 'Customer',
        'col_date' => 'Invoice Date',
        'col_due_date' => 'Due Date',
        'col_total_amount' => 'Total Billed',
        'col_balance_due' => 'Balance Due',
        'col_status' => 'Status',
        'col_actions' => 'Actions',
        'btn_detail' => 'Details',
        'btn_delete' => 'Delete',
        'empty_title' => 'No sales invoices yet.',
        'empty_desc' => 'Create a new invoice directly or generate one from the Sales Orders module.',
        'btn_create_first' => 'Create First Invoice',

        // Delete Modal
        'delete_modal_title' => 'Delete Sales Invoice?',
        'delete_modal_prompt' => 'Are you sure you want to delete this invoice? This action cannot be undone.',
        'delete_btn_cancel' => 'Cancel',
        'delete_btn_confirm' => 'Delete Invoice',

        // Create / Edit Form
        'create_title' => 'Create New Sales Invoice',
        'create_subtitle' => 'Issue a new commercial billing invoice for customer',
        'edit_title' => 'Edit Sales Invoice :number',
        'edit_subtitle' => 'Update item breakdown, due date, and billing information',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_invoices' => 'Sales Invoices',
        'breadcrumb_create' => 'Create New',
        'breadcrumb_edit' => 'Edit',
        'btn_back' => 'Back',

        'sec_client_info' => '1. Customer & Billing Information',
        'customer_label' => 'Customer',
        'customer_select_placeholder' => '-- Select Customer --',
        'invoice_number_label' => 'Invoice No.',
        'invoice_date_label' => 'Invoice Date',
        'due_date_label' => 'Due Date',
        'so_reference_label' => 'Related Sales Order (Optional)',
        'so_reference_none' => '-- Direct Invoice (No SO) --',

        'sec_items_title' => '2. Invoice Line Items',
        'btn_add_item' => 'Add Row',
        'col_item' => 'Product / Service',
        'col_price' => 'Unit Price (IDR)',
        'col_qty' => 'Quantity',
        'col_subtotal' => 'Subtotal (IDR)',

        'sec_payment_info' => '3. Payment Terms & Bank Details',
        'payment_terms_label' => 'Payment Terms / Bank Account',
        'payment_terms_placeholder' => 'e.g.: Bank transfer to BCA 1234567890 a/n PT Toko Berkah. Due within 14 days...',
        'notes_label' => 'Additional Notes',
        'notes_placeholder' => 'Thank you for your business...',
        'subtotal_label' => 'Subtotal:',
        'discount_label' => 'Discount:',
        'tax_vat_label' => 'Tax (VAT):',
        'grand_total_label' => 'Invoice Total:',
        'btn_cancel' => 'Cancel',
        'btn_save_invoice' => 'Save & Issue Invoice',

        // Show View
        'show_title' => 'Invoice :number',
        'show_subtitle' => 'Official commercial sales billing document',
        'btn_print' => 'Print Invoice (PDF / Print)',
        'btn_record_payment' => 'Record Payment',
        'btn_edit_invoice' => 'Edit Invoice',
        'btn_send_whatsapp' => 'Send WhatsApp',
        'badge_paid' => 'Paid',
        'badge_partially_paid' => 'Partial',
        'badge_overdue' => 'Overdue',
        'badge_unpaid' => 'Unpaid',

        'card_billing_to' => 'BILLED TO:',
        'card_payment_info' => 'PAYMENT INFORMATION:',
        'card_bank_account' => 'Payment Account:',
        'card_terms' => 'Terms:',
        'card_remaining_balance' => 'Balance Due:',

        'history_payments_title' => 'Payment Receipts History',
        'col_payment_date' => 'Date',
        'col_payment_method' => 'Method',
        'col_payment_amount' => 'Amount Paid',
        'col_payment_notes' => 'Notes',
        'no_payments_yet' => 'No payments have been logged for this invoice yet.',

        // Payment Modal
        'modal_payment_title' => 'Record Invoice Payment',
        'modal_payment_subtitle' => 'Record customer cash receipt or bank transfer for this invoice',
        'payment_date_label' => 'Payment Date',
        'payment_amount_label' => 'Payment Amount (IDR)',
        'payment_method_label' => 'Payment Method',
        'method_bank_transfer' => 'Bank Transfer',
        'method_cash' => 'Cash',
        'method_qris' => 'QRIS',
        'method_credit_card' => 'Credit / Debit Card',
        'method_other' => 'Other',
        'payment_notes_label' => 'Payment Notes (Optional)',
        'payment_notes_placeholder' => 'e.g.: Transfer via BCA ref 987654321',
        'btn_save_payment' => 'Save Payment',

        // Printable Template
        'print_heading' => 'SALES INVOICE',
        'print_invoice_no' => 'Invoice No.:',
        'print_date' => 'Date:',
        'print_due_date' => 'Due Date:',
        'print_billed_to' => 'BILLED TO:',
        'print_col_no' => 'No.',
        'print_col_desc' => 'Item / Service Description',
        'print_col_unit_price' => 'Unit Price',
        'print_col_qty' => 'Quantity',
        'print_col_subtotal' => 'Amount',
        'print_subtotal' => 'Subtotal',
        'print_discount' => 'Discount',
        'print_tax' => 'Tax (VAT)',
        'print_total' => 'Invoice Total',
        'print_amount_paid' => 'Amount Paid',
        'print_balance_due' => 'Balance Due',
        'print_payment_info' => 'Payment Instructions & Bank Account:',
        'print_terms' => 'Terms & Conditions:',
        'print_authorized_signature' => 'Authorized Signature,',
        'print_authorized_name' => 'Finance & Billing Department',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sales Returns
    |--------------------------------------------------------------------------
    */
    'returns' => [
        'page_title' => 'Sales Returns - Cooca',
        'header_title' => 'Sales Returns & Product Restocking',
        'header_subtitle' => 'Manage customer returns against sales invoices or POS cashier, credit note issuance, and warehouse restocking.',
        'module_title' => 'Sales Returns & Compensation',
        'module_subtitle' => 'Customer returns, invoice credit notes, and stock restoration.',
        'btn_create' => 'Create Sales Return',

        // KPI Summary
        'kpi_page_amount' => 'Page Return Value',
        'kpi_refund' => 'Refund',
        'kpi_total_returns' => 'Total Return Documents',
        'kpi_recorded' => 'Recorded',
        'kpi_pending_approval' => 'Pending Approval',
        'kpi_pending' => 'Pending',
        'kpi_completed_returns' => 'Completed Returns (Restocked)',
        'kpi_restocked' => 'Restock Done',

        // Controls & Search
        'search_placeholder' => 'Search return number, invoice, or customer name...',
        'filter_all_status' => 'All Statuses (:count)',
        'filter_draft' => 'Draft / Pending (:count)',
        'filter_approved' => 'Approved (:count)',
        'filter_completed' => 'Completed (:count)',

        // Table
        'col_return_number' => 'Return No.',
        'col_customer' => 'Customer',
        'col_return_date' => 'Return Date',
        'col_invoice_ref' => 'Invoice Ref.',
        'col_compensation_type' => 'Compensation',
        'col_total_amount' => 'Return Value',
        'col_status' => 'Status',
        'col_actions' => 'Actions',
        'btn_detail' => 'Details',

        // Status Badges
        'status_completed' => 'Completed',
        'status_approved' => 'Approved',
        'status_draft' => 'Draft',
        'status_cancelled' => 'Cancelled',

        // Compensation Badges
        'comp_credit_note' => 'Credit Note',
        'comp_cash_refund' => 'Cash Refund',
        'comp_exchange' => 'Product Exchange',

        // Empty States
        'empty_title' => 'No Sales Return Documents Yet',
        'empty_desc' => 'Record damaged or returned items to replenish warehouse inventory and adjust receivables.',
        'btn_create_first' => 'Create First Return',
        'empty_mobile' => 'No return documents registered yet.',

        // Form Create Return
        'create_page_title' => 'Create New Sales Return - Cooca',
        'create_header_title' => 'Create Sales Return',
        'create_header_subtitle' => 'Form for processing customer goods returns and receivable/cash adjustments.',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_sales_returns' => 'Sales Returns',
        'breadcrumb_create' => 'Create New',
        'btn_back' => 'Back',

        'sec_ref_invoice' => '1. Sales Invoice & Customer Reference',
        'invoice_ref_label' => 'Select Source Sales Invoice',
        'invoice_ref_placeholder' => '-- Select Registered Sales Invoice --',
        'return_number_label' => 'Sales Return No.',
        'return_date_label' => 'Return Date',
        'customer_label' => 'Customer',
        'compensation_label' => 'Return Compensation Method',
        'opt_credit_note' => 'Invoice Deduction (Credit Note)',
        'opt_cash_refund' => 'Cash / Transfer Refund',
        'opt_exchange' => 'Replacement Product (Exchange)',

        'sec_items_title' => '2. Returned Items Breakdown',
        'sec_items_subtitle' => 'Select returned products along with customer return reasons.',
        'btn_add_item' => 'Add Row',
        'col_product' => 'Product / Item',
        'col_return_qty' => 'Return Quantity',
        'col_unit_price' => 'Unit Price (IDR)',
        'col_subtotal' => 'Return Subtotal (IDR)',
        'col_reason' => 'Return Reason',
        'reason_placeholder' => 'e.g.: Factory defect, damaged packaging, incorrect shipment...',

        'sec_notes_summary' => '3. Notes & Return Summary',
        'notes_label' => 'Additional Return Notes',
        'notes_placeholder' => 'Notes regarding physical condition of goods upon warehouse arrival...',
        'total_return_amount_label' => 'Total Return Compensation:',
        'btn_cancel' => 'Cancel',
        'btn_save_return' => 'Save Return Document',

        // Show View
        'show_page_title' => 'Sales Return :number - Cooca',
        'show_header_title' => 'Sales Return Details',
        'show_header_subtitle' => 'Verify returned physical items and authorize receivable deductions.',
        'btn_approve' => 'Approve Return',
        'btn_complete_restock' => 'Complete & Restock Warehouse',
        'btn_print' => 'Print Return Note',
        'btn_back_to_list' => 'Back',

        'doc_heading' => 'SALES RETURN NOTE',
        'doc_return_no' => 'Return No.:',
        'doc_date' => 'Return Date:',
        'doc_ref_invoice' => 'Invoice Ref.:',
        'doc_compensation' => 'Compensation Form:',
        'doc_customer' => 'Customer:',
        'doc_col_no' => 'No.',
        'doc_col_product' => 'Returned Item',
        'doc_col_qty' => 'Quantity',
        'doc_col_unit_price' => 'Unit Price',
        'doc_col_subtotal' => 'Subtotal',
        'doc_col_reason' => 'Return Reason',
        'doc_total_return' => 'Total Return Value:',
        'doc_notes' => 'Warehouse / Logistics Notes:',

        // Print View
        'print_page_title' => 'Print Return Note :number - Cooca',
        'print_heading' => 'RETURN NOTE & CREDIT NOTE',
        'print_meta_issued' => 'Issued:',
        'print_sig_warehouse' => 'Warehouse Receiver,',
        'print_sig_supervisor' => 'Manager Authorization,',
        'print_sig_customer' => 'Returned By Customer,',
    ],
];
