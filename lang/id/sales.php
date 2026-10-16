<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Sales Orders (Pesanan Penjualan)
    |--------------------------------------------------------------------------
    */
    'sales_orders' => [
        'page_title' => 'Pesanan Penjualan (Sales Orders) - Cooca',
        'header_title' => 'Pesanan Penjualan (Sales Orders)',
        'header_subtitle' => 'Kelola pesanan pelanggan terkonfirmasi, alokasi stok pengiriman, dan terbitkan faktur penagihan (Invoice).',
        'module_title' => 'Pesanan Penjualan (SO)',
        'module_subtitle' => 'Pipeline pesanan pelanggan terkonfirmasi, alokasi stok gudang, dan penerbitan faktur.',
        'btn_create' => 'Buat Sales Order',

        // KPI Summary
        'kpi_total_orders' => 'Total Sales Order',
        'kpi_recorded' => 'Tercatat',
        'kpi_confirmed' => 'Terkonfirmasi',
        'kpi_ready_process' => 'Siap proses',
        'kpi_partially_fulfilled' => 'Sebagian Terpenuhi',
        'kpi_partial' => 'Parsial',
        'kpi_fulfilled' => 'Selesai (Fulfilled)',
        'kpi_completed' => 'Tuntas',

        // Controls & Search
        'search_placeholder' => 'Cari nomor SO atau nama pelanggan...',
        'filter_all' => 'Semua (:count)',
        'filter_confirmed' => 'Terkonfirmasi (:count)',
        'filter_partially_fulfilled' => 'Sebagian (:count)',
        'filter_fulfilled' => 'Selesai (:count)',

        // Table
        'col_so_number' => 'No. Sales Order',
        'col_customer' => 'Pelanggan',
        'col_order_date' => 'Tanggal Pesanan',
        'col_expected_delivery' => 'Estimasi Kirim',
        'col_tax' => 'Pajak',
        'col_total_amount' => 'Nilai Pesanan',
        'col_fulfillment_status' => 'Status Pemenuhan',
        'col_actions' => 'Aksi',
        'btn_detail' => 'Detail',

        // Status Badges
        'status_fulfilled' => 'Selesai',
        'status_partially_fulfilled' => 'Sebagian',
        'status_confirmed' => 'Terkonfirmasi',

        // Empty States
        'empty_title' => 'Belum Ada Pesanan Penjualan',
        'empty_desc' => 'Mulai buat sales order baru untuk mengunci alokasi stok dan menerbitkan faktur tagihan resmi.',
        'btn_create_first' => 'Buat Pesanan Pertama',
        'empty_mobile' => 'Belum ada pesanan penjualan terdaftar.',

        // Pagination
        'pagination_showing' => 'Menampilkan',
        'pagination_of' => 'dari',
        'pagination_orders' => 'pesanan',

        // Form Create SO
        'create_page_title' => 'Buat Pesanan Penjualan (SO) - Cooca',
        'create_header_title' => 'Buat Pesanan Penjualan',
        'create_header_subtitle' => 'Buat pesanan penjualan terkonfirmasi baru - langsung atau dari konversi penawaran harga (Quotation).',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_sales_orders' => 'Sales Orders',
        'breadcrumb_create' => 'Buat Baru',
        'form_title' => 'Formulir Pesanan Penjualan',
        'form_subtitle' => 'Dokumen kesepakatan pemesanan barang pelanggan dan jadwal pengiriman.',
        'btn_cancel' => 'Batal',
        'btn_confirm_order' => 'Konfirmasi Pesanan',
        'validation_errors_title' => 'Harap periksa isian formulir berikut:',

        // Step 1: Quotation Reference
        'step1_title' => '1. Referensi Penawaran Harga (Quotation)',
        'step1_desc' => 'Pilih penawaran yang telah disetujui pelanggan untuk mengisi item dan rincian secara otomatis.',
        'quotation_ref_label' => 'Dokumen Quotation Terkait',
        'optional_label' => '(opsional)',
        'quotation_direct_order' => '- Tanpa Quotation (Pesanan Langsung) -',
        'quotation_selected_badge' => 'Quotation Terpilih',
        'quotation_auto_loaded' => 'Data pelanggan, diskon, dan item produk otomatis dimuat ke formulir.',

        // Step 2: Customer & Shipping Info
        'step2_title' => '2. Informasi Pelanggan & Pengiriman',
        'step2_desc' => 'Tentukan pembeli, nomor dokumen, tanggal pemesanan, dan alamat tujuan barang.',
        'customer_label' => 'Pelanggan',
        'customer_select_placeholder' => '- Pilih Pelanggan -',
        'so_number_label' => 'No. Sales Order',
        'order_date_label' => 'Tanggal Pesanan',
        'expected_delivery_label' => 'Estimasi Kirim',
        'shipping_address_label' => 'Alamat Pengiriman',
        'shipping_address_placeholder' => 'Contoh: Jl. Sudirman No. 45, Kompleks Pergudangan Blok A...',

        // Step 3: Line Items
        'step3_title' => '3. Rincian Item Pesanan (Line Items)',
        'step3_desc' => 'Pilih produk, tentukan kuantitas, harga satuan kesepakatan, dan diskon per item.',
        'btn_add_item' => 'Tambah Baris',
        'col_item_product' => 'Produk / Item',
        'col_item_unit_price' => 'Harga Satuan (Rp)',
        'col_item_qty' => 'Kuantitas',
        'col_item_discount' => 'Diskon (Rp)',
        'col_item_subtotal' => 'Subtotal (Rp)',
        'select_product_placeholder' => '- Pilih Produk Fisik atau Layanan -',
        'optgroup_goods' => 'Produk / Barang Fisik',
        'optgroup_services' => 'Jasa & Layanan (Bebas Stok)',
        'badge_service_item' => 'Jasa / Layanan (Bebas Stok)',
        'badge_goods_item' => 'Produk Fisik',
        'btn_remove_item_tooltip' => 'Hapus baris',

        // Notes & Financials
        'notes_label' => 'Catatan Pesanan & Pengiriman',
        'notes_placeholder' => 'Instruksi pengiriman, nama kontak penerima ekspedisi, atau catatan khusus pelanggan...',
        'subtotal_items_label' => 'Subtotal Item:',
        'order_discount_label' => 'Diskon Pesanan (Rp):',
        'tax_vat_label' => 'Pajak PPN (%):',
        'total_order_value_label' => 'Total Nilai Pesanan:',
        'btn_submit_create' => 'Konfirmasi & Buat Pesanan',

        // Confirmation Dialog
        'dialog_confirm_title' => 'Buat Sales Order?',
        'dialog_confirm_prompt' => 'Konfirmasi penerbitan pesanan penjualan ini? Data pesanan akan langsung tercatat di sistem.',
        'dialog_btn_cancel' => 'Batal',
        'dialog_btn_confirm' => 'Konfirmasi',
        'alert_select_customer' => 'Harap pilih pelanggan terlebih dahulu.',

        // Detail / Show View
        'show_page_title' => 'Pesanan Penjualan :number - Cooca',
        'show_header_title' => 'Detail Pesanan Penjualan',
        'show_header_subtitle' => 'Kelola status pemenuhan pesanan, verifikasi rincian barang, dan terbitkan faktur penagihan.',
        'btn_generate_invoice' => 'Terbitkan Faktur',
        'btn_print' => 'Cetak',
        'btn_back' => 'Kembali',
        'summary_customer_label' => 'Pelanggan Pemesan',
        'summary_issued_on' => 'Diterbitkan pada :date',
        'summary_eta' => 'Estimasi Kirim: :date',
        'summary_eta_default' => 'Sesuai kesepakatan',
        'origin_quotation_label' => 'Berasal dari Penawaran (Quotation):',
        'btn_view_quotation' => 'Lihat Penawaran',
        'invoice_issued_label' => 'Faktur Tagihan Resmi Terbit:',
        'btn_open_invoice' => 'Buka Faktur',

        // Printable Document Card
        'doc_default_address' => 'Alamat operasional bisnis',
        'doc_heading_title' => 'PESANAN PENJUALAN',
        'doc_buyer_label' => 'PEMBELI / PELANGGAN:',
        'doc_shipping_address_label' => 'ALAMAT PENGIRIMAN:',
        'doc_same_as_customer' => 'Sesuai alamat pelanggan utama',
        'doc_col_num' => 'No.',
        'doc_col_desc' => 'Deskripsi Produk',
        'doc_col_unit_price' => 'Harga Satuan',
        'doc_col_qty' => 'Kuantitas',
        'doc_col_fulfilled' => 'Terpenuhi',
        'doc_col_subtotal' => 'Subtotal',
        'doc_notes_label' => 'CATATAN PESANAN:',
        'doc_subtotal_label' => 'Subtotal:',
        'doc_discount_label' => 'Diskon Pesanan:',
        'doc_tax_label' => 'Pajak PPN (:percent%):',
        'doc_grand_total_label' => 'Total Nilai Tagihan:',

        // Signatures
        'sig_created_by' => 'Dibuat Oleh,',
        'sig_sales_dept' => 'Bagian Penjualan',
        'sig_approved_by' => 'Disetujui Oleh,',
        'sig_supervisor' => 'Supervisor / Manajer',
        'sig_received_by' => 'Diterima Pelanggan,',

        // Invoice Modal
        'modal_invoice_title' => 'Terbitkan Faktur?',
        'modal_invoice_prompt' => 'Terbitkan Faktur Tagihan (Invoice) resmi dari pesanan penjualan ini?',
        'modal_btn_cancel' => 'Batal',
        'modal_btn_issue' => 'Terbitkan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Quotations (Surat Penawaran Harga)
    |--------------------------------------------------------------------------
    */
    'quotations' => [
        'page_title' => 'Penawaran Harga (Quotations)',
        'header_title' => 'Penawaran Harga',
        'header_subtitle' => 'Buat surat penawaran harga komersial resmi ke klien dan konversi ke pesanan penjualan',
        'module_title' => 'Penawaran Harga (Quotations)',
        'module_subtitle' => 'Terbitkan penawaran harga komersial B2B dan konversi ke Pesanan Penjualan (SO) dalam 1 klik.',
        'btn_create_new' => 'Buat Penawaran Baru',

        // KPI Summary
        'kpi_total_issued' => 'Total Penawaran Terbit',
        'kpi_docs_unit' => 'Dokumen',
        'kpi_pipeline' => 'Pipeline',
        'kpi_accumulated_value' => 'Nilai Akumulasi Penawaran',
        'kpi_est_revenue' => 'Estimasi Omzet',
        'kpi_deal_accepted' => 'Penawaran Deal / Disetujui',
        'kpi_accepted_unit' => 'Disetujui',
        'kpi_ready_convert' => 'Siap Konversi',

        // Controls & Search
        'search_placeholder' => 'Cari nomor penawaran atau nama pelanggan...',
        'filter_all_status' => 'Semua Status',
        'status_sent' => 'Terkirim',
        'status_accepted' => 'Disetujui',
        'status_rejected' => 'Ditolak',
        'status_expired' => 'Kadaluwarsa',
        'btn_reset' => 'Reset',

        // Table
        'col_quotation_number' => 'No. Penawaran',
        'col_customer' => 'Pelanggan',
        'col_date' => 'Tanggal Terbit',
        'col_expiry_date' => 'Batas Berlaku',
        'col_total_amount' => 'Nilai Penawaran',
        'col_status' => 'Status',
        'col_actions' => 'Aksi',
        'btn_detail' => 'Detail',
        'empty_title' => 'Belum ada surat penawaran harga.',

        // Form Create Quotation
        'create_page_title' => 'Buat Penawaran Harga Baru',
        'create_header_title' => 'Buat Penawaran Baru',
        'create_header_subtitle' => 'Terbitkan dokumen penawaran harga resmi ke calon pelanggan atau klien B2B',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_quotations' => 'Penawaran Harga',
        'breadcrumb_create' => 'Buat Baru',
        'btn_back_to_list' => 'Kembali ke Daftar',

        // Section 1: Client & Date
        'sec1_title' => '1. Informasi Klien & Tanggal',
        'customer_label' => 'Pelanggan / Klien',
        'customer_select_placeholder' => '-- Pilih Pelanggan --',
        'quotation_number_label' => 'No. Penawaran',
        'date_issued_label' => 'Tanggal Terbit',
        'expiry_date_label' => 'Batas Berlaku',

        // Section 2: Items
        'sec2_title' => '2. Rincian Item Penawaran',
        'sec2_desc' => 'Tentukan daftar produk komersial, harga, dan kuantitas pesanan.',
        'btn_add_item' => 'Tambah Baris',
        'col_item_product' => 'Produk / Item',
        'col_item_price' => 'Harga (Rp)',
        'col_item_qty' => 'Jumlah',
        'col_item_total' => 'Total (Rp)',
        'select_product_placeholder' => '-- Pilih Produk Fisik atau Layanan --',
        'optgroup_goods' => 'Produk / Barang Fisik',
        'optgroup_services' => 'Jasa & Layanan (Bebas Stok)',
        'badge_service' => 'Jasa / Layanan (Bebas Stok)',
        'badge_goods' => 'Produk Fisik',

        // Section 3: Notes & Summary
        'notes_terms_label' => 'Catatan & Syarat Penawaran',
        'notes_terms_placeholder' => 'Contoh: Harga sudah termasuk ongkos kirim. Pembayaran DP 50% saat PO diterbitkan...',
        'subtotal_label' => 'Subtotal:',
        'discount_label' => 'Diskon (Rp):',
        'tax_vat_label' => 'Pajak PPN (Rp):',
        'total_quotation_label' => 'Total Penawaran:',
        'btn_cancel' => 'Batal',
        'btn_publish' => 'Terbitkan Penawaran',

        // Detail / Show View
        'show_page_title' => 'Penawaran :number',
        'show_header_title' => 'Detail Surat Penawaran',
        'show_header_subtitle' => 'Dokumen resmi penawaran harga komersial ke pelanggan',
        'client_meta' => 'Klien: :name • Total: Rp :total',
        'btn_convert_to_so' => 'Ubah ke Sales Order',
        'btn_view_so' => 'Lihat SO (:number)',
        'btn_print' => 'Cetak',
        'btn_whatsapp' => 'WhatsApp',
        'btn_close' => 'Tutup',

        // Printable Sheet
        'doc_business_fallback' => 'Operasional Bisnis',
        'doc_heading_title' => 'Surat Penawaran Harga',
        'doc_date_label' => 'Tanggal:',
        'doc_valid_until_label' => 'Berlaku Sampai:',
        'doc_to_customer_label' => 'DITUJUKAN KEPADA:',
        'doc_col_num' => 'No.',
        'doc_col_desc' => 'Deskripsi Item',
        'doc_col_unit_price' => 'Harga Satuan',
        'doc_col_qty' => 'Jumlah',
        'doc_col_subtotal' => 'Subtotal',
        'doc_additional_notes' => 'Catatan Tambahan:',
        'doc_digital_notice' => 'Diterbitkan secara digital oleh COOCA ID.',
        'doc_subtotal_label' => 'Subtotal:',
        'doc_discount_label' => 'Diskon:',
        'doc_tax_label' => 'Pajak (PPN):',
        'doc_total_label' => 'Total Penawaran:',

        // Convert Modal
        'modal_convert_title' => 'Ubah ke Sales Order?',
        'modal_convert_prompt' => 'Surat penawaran ini akan dikonversi menjadi Pesanan Penjualan resmi dan siap diproses di modul penjualan.',
        'modal_btn_cancel' => 'Batal',
        'modal_btn_convert' => 'Konversi',
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoices (Faktur Penjualan & Penagihan)
    |--------------------------------------------------------------------------
    */
    'invoices' => [
        'page_title' => 'Faktur Penjualan (Invoices)',
        'header_title' => 'Manajemen Faktur & Penagihan',
        'header_subtitle' => 'Kelola faktur penjualan komersial, pelacakan piutang, dan pencatatan pembayaran',
        'module_title' => 'Faktur Penjualan',
        'module_subtitle' => 'Kelola faktur komersial, pelacakan piutang, dan pencatatan pembayaran',
        'btn_export_csv' => 'Export CSV',
        'btn_create_new' => 'Buat Faktur Baru',

        // KPI Summary
        'kpi_total_invoiced' => 'Total Nilai Faktur',
        'kpi_invoiced_sub' => 'Tagihan Terbit',
        'kpi_total_paid' => 'Sudah Terbayar',
        'kpi_paid_sub' => 'Kas Masuk',
        'kpi_unpaid_receivables' => 'Sisa Piutang Berjalan',
        'kpi_unpaid_sub' => 'Belum Lunas',
        'kpi_overdue' => 'Faktur Jatuh Tempo',
        'kpi_overdue_sub' => 'Perlu Ditagih',

        // Controls & Search
        'search_placeholder' => 'Cari nomor faktur atau nama pelanggan...',
        'filter_all_status' => 'Semua Status',
        'status_draft' => 'Draft',
        'status_sent' => 'Terkirim',
        'status_partially_paid' => 'Sebagian',
        'status_paid' => 'Lunas',
        'status_overdue' => 'Jatuh Tempo',
        'status_cancelled' => 'Dibatalkan',

        // Table
        'col_invoice_number' => 'No. Faktur',
        'col_customer' => 'Pelanggan',
        'col_date' => 'Tanggal Faktur',
        'col_due_date' => 'Jatuh Tempo',
        'col_total_amount' => 'Total Tagihan',
        'col_balance_due' => 'Sisa Tagihan',
        'col_status' => 'Status',
        'col_actions' => 'Aksi',
        'btn_detail' => 'Detail',
        'btn_delete' => 'Hapus',
        'empty_title' => 'Belum ada data faktur penjualan.',
        'empty_desc' => 'Buat faktur baru langsung atau terbitkan otomatis dari modul Pesanan Penjualan (Sales Orders).',
        'btn_create_first' => 'Buat Faktur Pertama',

        // Delete Modal
        'delete_modal_title' => 'Hapus Faktur Penjualan?',
        'delete_modal_prompt' => 'Apakah Anda yakin ingin menghapus faktur ini? Tindakan ini tidak dapat dibatalkan.',
        'delete_btn_cancel' => 'Batal',
        'delete_btn_confirm' => 'Hapus Faktur',

        // Create / Edit Form
        'create_title' => 'Buat Faktur Penjualan Baru',
        'create_subtitle' => 'Terbitkan faktur tagihan komersial baru untuk pelanggan',
        'edit_title' => 'Ubah Faktur Penjualan :number',
        'edit_subtitle' => 'Perbarui rincian item, tanggal jatuh tempo, dan informasi penagihan',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_invoices' => 'Faktur Penjualan',
        'breadcrumb_create' => 'Buat Baru',
        'breadcrumb_edit' => 'Ubah',
        'btn_back' => 'Kembali',

        'sec_client_info' => '1. Informasi Pelanggan & Penagihan',
        'customer_label' => 'Pelanggan',
        'customer_select_placeholder' => '-- Pilih Pelanggan --',
        'invoice_number_label' => 'No. Faktur',
        'invoice_date_label' => 'Tanggal Faktur',
        'due_date_label' => 'Jatuh Tempo',
        'so_reference_label' => 'Terkait Sales Order (Opsional)',
        'so_reference_none' => '-- Tanpa Sales Order --',

        'sec_items_title' => '2. Rincian Item Tagihan',
        'btn_add_item' => 'Tambah Baris',
        'col_item' => 'Produk / Jasa',
        'col_price' => 'Harga Satuan (Rp)',
        'col_qty' => 'Kuantitas',
        'col_subtotal' => 'Subtotal (Rp)',

        'sec_payment_info' => '3. Ketentuan Pembayaran & Catatan',
        'payment_terms_label' => 'Ketentuan Pembayaran / Rekening Bank',
        'payment_terms_placeholder' => 'Contoh: Pembayaran ditransfer ke Rekening BCA 1234567890 a/n PT Toko Berkah. Jatuh tempo 14 hari...',
        'notes_label' => 'Catatan Tambahan',
        'notes_placeholder' => 'Terima kasih atas kerja sama Anda...',
        'subtotal_label' => 'Subtotal:',
        'discount_label' => 'Diskon:',
        'tax_vat_label' => 'Pajak (PPN):',
        'grand_total_label' => 'Total Faktur:',
        'btn_cancel' => 'Batal',
        'btn_save_invoice' => 'Simpan & Terbitkan Faktur',

        // Show View
        'show_title' => 'Faktur Tagihan :number',
        'show_subtitle' => 'Dokumen resmi penagihan penjualan komersial',
        'btn_print' => 'Cetak Faktur (PDF / Cetak)',
        'btn_record_payment' => 'Catat Pembayaran',
        'btn_edit_invoice' => 'Ubah Faktur',
        'btn_send_whatsapp' => 'Kirim WA',
        'badge_paid' => 'Lunas',
        'badge_partially_paid' => 'Sebagian',
        'badge_overdue' => 'Jatuh Tempo',
        'badge_unpaid' => 'Belum Lunas',

        'card_billing_to' => 'TAGIHAN KEPADA:',
        'card_payment_info' => 'INFORMASI PEMBAYARAN:',
        'card_bank_account' => 'Rekening Pembayaran:',
        'card_terms' => 'Ketentuan:',
        'card_remaining_balance' => 'Sisa Tagihan:',

        'history_payments_title' => 'Riwayat Pembayaran Diterima',
        'col_payment_date' => 'Tanggal',
        'col_payment_method' => 'Metode',
        'col_payment_amount' => 'Nominal Bayar',
        'col_payment_notes' => 'Catatan',
        'no_payments_yet' => 'Belum ada pembayaran yang dicatat untuk faktur ini.',

        // Payment Modal
        'modal_payment_title' => 'Catat Pembayaran Faktur',
        'modal_payment_subtitle' => 'Input penerimaan dana tunai atau transfer dari pelanggan untuk faktur ini',
        'payment_date_label' => 'Tanggal Pembayaran',
        'payment_amount_label' => 'Nominal Pembayaran (Rp)',
        'payment_method_label' => 'Metode Pembayaran',
        'method_bank_transfer' => 'Transfer Bank',
        'method_cash' => 'Tunai (Cash)',
        'method_qris' => 'QRIS',
        'method_credit_card' => 'Kartu Kredit / Debit',
        'method_other' => 'Lainnya',
        'payment_notes_label' => 'Catatan Pembayaran (Opsional)',
        'payment_notes_placeholder' => 'Contoh: Transfer via BCA ref 987654321',
        'btn_save_payment' => 'Simpan Pembayaran',

        // Printable Template
        'print_heading' => 'FAKTUR PENJUALAN',
        'print_invoice_no' => 'No. Faktur:',
        'print_date' => 'Tanggal:',
        'print_due_date' => 'Jatuh Tempo:',
        'print_billed_to' => 'KEPADA YTH:',
        'print_col_no' => 'No.',
        'print_col_desc' => 'Deskripsi Item / Layanan',
        'print_col_unit_price' => 'Harga Satuan',
        'print_col_qty' => 'Kuantitas',
        'print_col_subtotal' => 'Jumlah',
        'print_subtotal' => 'Subtotal',
        'print_discount' => 'Diskon',
        'print_tax' => 'Pajak (PPN)',
        'print_total' => 'Total Tagihan',
        'print_amount_paid' => 'Telah Dibayar',
        'print_balance_due' => 'Sisa Pembayaran',
        'print_payment_info' => 'Petunjuk Pembayaran & Rekening:',
        'print_terms' => 'Syarat & Ketentuan:',
        'print_authorized_signature' => 'Tanda Tangan Resmi,',
        'print_authorized_name' => 'Bagian Keuangan & Penagihan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sales Returns (Retur Penjualan & Kompensasi)
    |--------------------------------------------------------------------------
    */
    'returns' => [
        'page_title' => 'Retur Penjualan - Cooca',
        'header_title' => 'Retur Penjualan & Pengembalian Barang',
        'header_subtitle' => 'Kelola pengembalian barang pelanggan atas faktur penjualan atau kasir POS, penerbitan credit note, dan pengembalian stok gudang.',
        'module_title' => 'Retur Penjualan & Kompensasi',
        'module_subtitle' => 'Pengembalian barang pelanggan, pemotongan faktur (Credit Note), dan pemulihan stok.',
        'btn_create' => 'Buat Retur Penjualan',

        // KPI Summary
        'kpi_page_amount' => 'Nilai Retur Halaman',
        'kpi_refund' => 'Refund',
        'kpi_total_returns' => 'Total Dokumen Retur',
        'kpi_recorded' => 'Tercatat',
        'kpi_pending_approval' => 'Menunggu Persetujuan',
        'kpi_pending' => 'Pending',
        'kpi_completed_returns' => 'Retur Selesai (Restok)',
        'kpi_restocked' => 'Restok Selesai',

        // Controls & Search
        'search_placeholder' => 'Cari nomor retur, faktur, atau nama pelanggan...',
        'filter_all_status' => 'Semua Status (:count)',
        'filter_draft' => 'Draft / Menunggu (:count)',
        'filter_approved' => 'Disetujui (:count)',
        'filter_completed' => 'Selesai (:count)',

        // Table
        'col_return_number' => 'No. Retur',
        'col_customer' => 'Pelanggan',
        'col_return_date' => 'Tanggal Retur',
        'col_invoice_ref' => 'Ref. Faktur',
        'col_compensation_type' => 'Kompensasi',
        'col_total_amount' => 'Nilai Retur',
        'col_status' => 'Status',
        'col_actions' => 'Aksi',
        'btn_detail' => 'Detail',

        // Status Badges
        'status_completed' => 'Selesai',
        'status_approved' => 'Disetujui',
        'status_draft' => 'Draft',
        'status_cancelled' => 'Dibatalkan',

        // Compensation Badges
        'comp_credit_note' => 'Credit Note',
        'comp_cash_refund' => 'Refund Kas',
        'comp_exchange' => 'Ganti Barang',

        // Empty States
        'empty_title' => 'Belum Ada Dokumen Retur Penjualan',
        'empty_desc' => 'Catat pengembalian barang rusak atau pesanan batal untuk memulihkan stok dan menyesuaikan tagihan piutang.',
        'btn_create_first' => 'Buat Dokumen Retur Pertama',
        'empty_mobile' => 'Belum ada dokumen retur terdaftar.',

        // Form Create Return
        'create_page_title' => 'Buat Retur Penjualan Baru - Cooca',
        'create_header_title' => 'Buat Retur Penjualan',
        'create_header_subtitle' => 'Formulir pengembalian barang pelanggan dan penyesuaian nilai piutang/kas.',
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_sales_returns' => 'Retur Penjualan',
        'breadcrumb_create' => 'Buat Baru',
        'btn_back' => 'Kembali',

        'sec_ref_invoice' => '1. Referensi Faktur Tagihan & Pelanggan',
        'invoice_ref_label' => 'Pilih Faktur Penjualan Asal',
        'invoice_ref_placeholder' => '-- Pilih Faktur Penjualan Terdaftar --',
        'return_number_label' => 'No. Retur Penjualan',
        'return_date_label' => 'Tanggal Retur',
        'customer_label' => 'Pelanggan',
        'compensation_label' => 'Bentuk Kompensasi Pengembalian',
        'opt_credit_note' => 'Potong Tagihan Faktur (Credit Note)',
        'opt_cash_refund' => 'Pengembalian Dana Kas / Transfer (Refund)',
        'opt_exchange' => 'Penggantian Barang Baru (Exchange)',

        'sec_items_title' => '2. Rincian Barang yang Diretur',
        'sec_items_subtitle' => 'Pilih item barang yang dikembalikan pelanggan beserta alasannya.',
        'btn_add_item' => 'Tambah Baris',
        'col_product' => 'Produk / Barang',
        'col_return_qty' => 'Kuantitas Retur',
        'col_unit_price' => 'Harga Satuan (Rp)',
        'col_subtotal' => 'Subtotal Retur (Rp)',
        'col_reason' => 'Alasan Pengembalian',
        'reason_placeholder' => 'Contoh: Cacat pabrik, kemasan rusak, barang salah kirim...',

        'sec_notes_summary' => '3. Catatan & Ringkasan Nilai Retur',
        'notes_label' => 'Catatan Tambahan Retur',
        'notes_placeholder' => 'Keterangan kondisi fisik barang saat diterima kembali di gudang...',
        'total_return_amount_label' => 'Total Nilai Kompensasi Retur:',
        'btn_cancel' => 'Batal',
        'btn_save_return' => 'Simpan Dokumen Retur',

        // Show View
        'show_page_title' => 'Retur Penjualan :number - Cooca',
        'show_header_title' => 'Detail Dokumen Retur Penjualan',
        'show_header_subtitle' => 'Verifikasi pengembalian barang fisik dan otorisasi pemotongan piutang.',
        'btn_approve' => 'Setujui Retur',
        'btn_complete_restock' => 'Selesaikan & Restok Gudang',
        'btn_print' => 'Cetak Nota Retur',
        'btn_back_to_list' => 'Kembali',

        'doc_heading' => 'NOTA RETUR PENJUALAN',
        'doc_return_no' => 'No. Retur:',
        'doc_date' => 'Tanggal Retur:',
        'doc_ref_invoice' => 'Ref. Faktur:',
        'doc_compensation' => 'Bentuk Kompensasi:',
        'doc_customer' => 'Pelanggan:',
        'doc_col_no' => 'No.',
        'doc_col_product' => 'Barang Retur',
        'doc_col_qty' => 'Kuantitas',
        'doc_col_unit_price' => 'Harga Satuan',
        'doc_col_subtotal' => 'Subtotal',
        'doc_col_reason' => 'Alasan Retur',
        'doc_total_return' => 'Total Nilai Retur:',
        'doc_notes' => 'Catatan Gudang / Logistik:',

        // Print View
        'print_page_title' => 'Cetak Nota Retur :number - Cooca',
        'print_heading' => 'NOTA RETUR & CREDIT NOTE',
        'print_meta_issued' => 'Diterbitkan:',
        'print_sig_warehouse' => 'Penerima Gudang,',
        'print_sig_supervisor' => 'Otorisasi Manajer,',
        'print_sig_customer' => 'Diserahkan Pelanggan,',
    ],
];
