import os, re

fixes = {
    'admin.php': {
        'ubah_penawaran': 'Edit Quotation',
        'daftar_surat_penawaran_harga_sales_quotations': 'Sales Quotations List',
        'contoh_workshop_optimasi_omset_kasir_strategi': 'Example: Cashier Revenue Optimization & MSME Margin Strategy 2026',
        'jelaskan_detail_materi_narasumber_manfaat_bagi': 'Explain details of topics, speakers, benefits for business owners, and...',
        'kosongkan_jika_kuota_tidak_dibatasi': 'Leave blank if quota is unlimited',
        'daftar_peserta_terdaftar': 'Registered Participants List',
        'tidak_ada_data_feedback_ditemukan': 'No feedback data found',
        'request_fitur': 'Feature Request',
        'tuliskan_catatan_perbaikan_atau_progres_yang': 'Enter notes on fixes or progress made...',
        'catatan_arsitektur_atau_pertimbangan_rilis': 'Architectural notes or release considerations...',
        'biaya_perpanjangan_server_tahunan_kuota_token': 'Annual server renewal fee / additional token quota...',
        'catatan_baris_khusus_opsional': 'Special line notes (optional)...',
        'catatan_nomor_invoice_persetujuan_direksi_atau': 'Invoice number notes, board approval, or reference number...',
        'bukti_transaksi': 'Transaction Proof',
        'lihat_faktur_tagihan': 'View Invoice / Bill',
        'bukti_transfer_keluar': 'Outgoing Transfer Proof',
        'bukti_transfer_2': 'Transfer Proof',
        'klik_untuk_melihat_bukti_gambar_kuitansi': 'Click to view receipt / proof image',
        'lihat_bukti': 'View Proof',
        'title_kotak_masuk_inbox_cooca_admin': '@title: Inbox - COOCA Admin',
        'muat_seluruh_39_berkas_panduan_dari': 'Load all 39 guide files from docs/user-guides directly to',
        'pengaturan_kredensial_meta_webhook': 'Meta & Webhook Credentials Settings',
        'simpan_kredensial': 'Save Credentials',
        'ringkasan_1_2_kalimat_untuk_kartu': '1-2 sentence summary for preview card on blog page...',
        'cari_judul_artikel_topik_atau_kata': 'Search article title, topic, or keyword...',
        'operasional_dan_stok': 'operations-and-stock',
        'belum_ada_batch_pada_program_ini': 'No batches in this program yet. Click "Add Batch" to set quota and',
        'belum_ada_riwayat_perubahan_harga_khusus': 'No special price change history recorded.',
        'tambah_batch_kuota_harga_khusus': 'Add Quota Batch & Special Pricing',
        'profil_ganti_kata_sandi_administrator_admin': 'Administrator Profile & Change Password - Admin Console',
        'cooca_software_kasir_aplikasi_pembukuan_pos': 'Cooca, cashier software, bookkeeping application, store POS',
        'software_kasir_pembukuan_kalkulator_bisnis_ai': 'Cashier software, bookkeeping, business calculator & AI Assistant free forever',
        'buka_halaman': 'Open Page',
        'testingsocial_menguji_kredensial': "testingSocial ? 'Testing Credentials...' :",
        'owner_bisnis_kasir': 'Business Owner & Cashier',
        'pencairan_saldo_merchant_upload_bukti_bayar': 'Merchant Balance Payout & Upload Payment Proof',
        'preview_bukti_bayar': 'Preview Payment Proof',
        'bukti_transfer_struk_bank': 'Bank Transfer Receipt / Proof',
        'contoh_nomor_rekening_tidak_valid_atau': 'Example: Account number is invalid or owner name does not match...',
        'tidak_ada_data_pengajuan_pencairan_saldo': 'No balance payout requests match the filter.',
        'transfer_upload_bukti': 'Transfer & Upload Proof',
        'konfirmasi_selesai_kirim_bukti': 'Confirm Completion & Send Proof',
        'pencairan_saldo_bukti_transfer': 'Balance Payout & Transfer Proof',
        'upload_bukti_transfer_bank': 'Upload Bank Transfer Proof',
        'bukti_transfer_pencairan': 'Payout Transfer Proof',
        'setujui_kirim_bukti': 'Approve & Send Proof',
        'unduh_bukti': 'Download Proof',
        'bukti_transfer_bank_resmi': 'Official Bank Transfer Proof',
        'lihat_detail_postingan': 'View Post Details',
        'edit_postingan': 'Edit Post',
        'belum_ada_postingan_resmi_platform': 'No Official Platform Posts Found',
        'belum_ada_komentar_yang_masuk_pada': 'No incoming comments on',
        'kotak_masuk_interaksi': 'Interaction Inbox',
        'buka_kotak_masuk_baru': 'Open New Inbox',
        'riwayat_postingan_resmi_platform': 'Platform Official Posts History',
        'buat_postingan_resmi_cooca': 'Create Official Cooca Post',
        'edit_konten_postingan': 'Edit Post Content',
        'detail_postingan_platform': 'Platform Post Details',
        'transaksi_bukti_pembayaran': 'Transactions & Payment Proof',
        'contoh_mutasi_bca_terkonfirmasi_masuk': 'Example: BCA bank transfer confirmed received',
        'contoh_nominal_transfer_kurang_rp_200': 'Example: Transfer amount is short by Rp 200, or receipt is blurry/unreadable',
        'konfirmasi_pengirim_bukti': 'Confirm Sender & Proof',
        'laporan_arus_kas_bulanan': 'monthly-cash-flow-report',
        'satu_poin_per_baris_contoh_10': 'One point per line, example:&#10;Automatic calculation of cash inflow and outflow',
        'tidak_ada_data': 'No data available',
        'belum_ada_catatan_log_pengingat': 'No reminder log entries recorded.',
        'tindakan_diperlukan_verifikasi_kepemilikan_nomor_di': 'Action Required: Verify Phone Number Ownership in Meta WhatsApp Manager',
    },
    'ai.php': {
        'keluar_dari_kontrol_agen_esc': 'Exit Agent Control [ESC]',
        'keluar_esc': 'Exit [ESC]',
        'pilih_di_galeri_inspirasi_197_template': 'Choose from Inspiration Gallery (197 Templates)',
        'buka_katalog_inspirasi': 'Open Inspiration Catalog',
        'cari_gaya_visual_mood_pencahayaan_atau': 'Search visual style, mood, lighting, or keywords...',
        'lihat_semua_inspirasi': 'View All Inspirations',
        'tidak_ada_inspirasi_ditemukan': 'No Inspirations Found',
        'kunjungi_halaman_asli': 'Visit Original Page',
        'larr_ganti_paket_metode': '&larr; Change Plan / Method',
        'buka_halaman_tagihan_lengkap': 'Open Full Billing Page',
    },
    'community.php': {
        'belum_ada_postingan_komunitas': 'No community posts yet',
        'batas_kuota': 'Limits & Quota',
    },
    'dashboard.php': {
        'alert_low_stock_title': 'Low Stock Alert',
    },
    'finance.php': {
        'bukti_transfer_settlement': 'Settlement Transfer Proof',
    },
    'inventory.php': {
        'inventori_stok_multi_gudang': 'Inventory & Multi-Warehouse Stock',
        'belum_ada_data_stok_tercatat_pada': 'No stock data recorded for this filter.',
        'belum_ada_data_stok_tercatat': 'No stock data recorded.',
        'belum_ada_transfer_stok_antar_outlet': 'No inter-outlet stock transfers recorded.',
    },
    'landing_page.php': {
        'preview_halaman_publik_bisnis': 'Preview Business Public Page',
        'contoh_suasana_ruang_tunggu_dan_kasir': 'Example: Waiting Room and Cashier Atmosphere',
        'pengaturan_halaman_navigasi_dinamis_auto_hide': 'Page Settings & Dynamic Navigation (Auto-Hide)',
        '5_sinkronisasi_produk_kasir_pos': '5. POS Cashier Product Synchronization',
        '10_tata_letak_konten_footer': '10. Footer Layout & Content',
        'tuliskan_detail_penawaran_kode_voucher_belanja': 'Enter offer details, shopping voucher code, promo operational hours...',
        'halaman_reservasi': 'Reservation Page',
    },
    'marketplace.php': {
        'pesanan_masuk': 'Incoming Orders',
    },
    'master_data.php': {
        'belum_ada_konversi_satuan_bisnis_terdaftar': 'No business unit conversions registered.',
        'belum_ada_konversi_satuan_terdaftar': 'No unit conversions registered.',
    },
    'pos.php': {
        'cetak_salinan_ke_o_print_count': 'Print Copy (Copy :count)',
        'belum_ada_transaksi_pos_yang_tercatat': 'No POS transactions recorded yet.',
        'buka_terminal_kasir': 'Open Cashier Terminal',
        'belum_ada_riwayat_shift_kasir_tercatat': 'No cashier shift history recorded.',
        'kas_masuk_kas_keluar': 'Cash In / Cash Out',
        'belum_ada_riwayat_servis_tercatat_untuk': 'No service history recorded for this vehicle.',
        'contoh_kasir_utama_printer_dapur_barista': 'Example: Main Cashier, Kitchen Printer, Barista',
        'contoh_penukaran_uang_kembalian_pecahan_kecil': 'Example: Exchanging change for small denominations with the adjacent cashier...',
    },
    'products.php': {
        'belum_ada_produk_aktif_yang_terdaftar': 'No active products registered.',
    },
    'sales.php': {
        'edit_surat_penawaran': 'Edit Quotation Letter:',
        'ketentuan_garansi_sla_pengiriman_atau_batas': 'Warranty terms, delivery SLA, or down payment limit...',
        'edit_penawaran': 'Edit Quotation:',
        'edit_penawaran_2': 'Edit Quotation',
        'edit_penawaran_3': 'Edit Quotation',
        'hapus_penawaran': 'Delete Quotation',
        'edit_penawaran_4': 'Edit Quotation',
    },
    'settings.php': {
        'profil_ganti_kata_sandi': 'Profile & Change Password',
        'ganti_nomor_whatsapp': 'Change WhatsApp Number',
        'ganti_alamat_email': 'Change Email Address',
        'cari_parameter_fitur': 'Search feature parameters...',
    },
    'social_media.php': {
        'atur_provider_ai_untuk_membuka_fitur': 'Configure AI Provider to unlock this feature',
        'belum_ada_percakapan_masuk_hubungkan_akun': 'No incoming conversations yet. Connect your Facebook Page or Instagram account to',
    },
    'storefront.php': {
        'stok_berkelanjutan': 'Sustainable Stock',
    }
}

for fname, mapping in fixes.items():
    path = os.path.join('lang/en', fname)
    if not os.path.exists(path):
        print(f"File not found: {path}")
        continue
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()

    replaced_count = 0
    for k, new_val in mapping.items():
        escaped_k = re.escape(k)
        # Match single or double quoted keys and their string values
        pattern = re.compile(r"^(\s*['\"]" + escaped_k + r"['\"]\s*=>\s*)['\"].*?['\"](,\s*)$", re.MULTILINE)
        escaped_val = new_val.replace("'", "\\'")
        replacement = r"\g<1>'" + escaped_val + r"'\g<2>"
        new_content, count = pattern.subn(replacement, content)
        if count > 0:
            content = new_content
            replaced_count += count
        else:
            print(f"  Warning: key '{k}' not matched in {fname}")

    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Updated {fname}: {replaced_count}/{len(mapping)} replaced")

print("All replacements done.")
