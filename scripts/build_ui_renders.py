from render_all_features_master_ui import CSS_BASE
import os
import base64
import asyncio
from playwright.async_api import async_playwright

BASE_DIR = r"C:\laragon\www\cooca_core"
OUTPUT_DIR = os.path.join(BASE_DIR, r"storage\app\public\features_ui_render")
os.makedirs(OUTPUT_DIR, exist_ok=True)

def get_base64_img(rel_path):
    full_path = os.path.join(BASE_DIR, rel_path)
    if not os.path.exists(full_path):
        return ""
    ext = os.path.splitext(full_path)[1].replace(".", "") or "png"
    with open(full_path, "rb") as f:
        data = f.read()
    return f"data:image/{ext};base64,{base64.b64encode(data).decode('utf-8')}"

logo_light = get_base64_img("public/assets/image/1785229034_logo_light.png")
if not logo_light:
    logo_light = get_base64_img("public/assets/image/cooca-logo-landscape.png")

# Common Header template for windows
def make_window_header(module_letter, module_name, path_url):
    return f"""
    <div class="app-topbar">
        <div class="window-controls">
            <span class="w-dot w-red"></span>
            <span class="w-dot w-yellow"></span>
            <span class="w-dot w-green"></span>
            <span style="font-size: 11px; color: #64748B; font-weight: 600; margin-left: 8px;">COOCA ERP CLOUD v4.8</span>
        </div>
        <div class="topbar-center">
            <span class="module-badge">MODUL {module_letter}: {module_name}</span>
            <span style="font-size: 12px; color: #94A3B8; font-family: monospace; background: rgba(0,0,0,0.3); padding: 3px 10px; border-radius: 6px;">https://app.cooca.id{path_url}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #10B981; font-weight: 600;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span> Online Sinkron
            </span>
            <span style="font-size: 12px; color: #E2E8F0; font-weight: 600;">Toko Kamu (Pusat)</span>
        </div>
    </div>
    """

# Module A: Kasir & POS
def get_module_a_html():
    return f"""
    <div class="app-window" id="modul-a">
        {make_window_header('A', 'KASIR & OPERASIONAL POS', '/pos')}
        <div class="app-body" style="background: #080E24;">
            <!-- POS Left: Product Catalog -->
            <div style="flex: 1; padding: 20px; display: flex; flex-direction: column; gap: 16px; border-right: 1px solid rgba(255,255,255,0.08);">
                <!-- Search & Filters -->
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div style="flex: 1; position: relative;">
                        <input type="text" placeholder="Scan Barcode / Cari Produk [F1]..." value="Ayam" style="width: 100%; background: #0E1A3D; border: 1px solid rgba(56,189,248,0.4); border-radius: 8px; padding: 10px 14px; color: #fff; font-size: 13px;" />
                        <span style="position: absolute; right: 12px; top: 10px; font-size: 11px; background: rgba(56,189,248,0.2); color: #38BDF8; padding: 2px 6px; border-radius: 4px;">SCANNER READY</span>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <span class="btn btn-primary" style="padding: 6px 14px; font-size: 12px;">Semua</span>
                        <span class="btn btn-secondary" style="padding: 6px 14px; font-size: 12px;">Makanan</span>
                        <span class="btn btn-secondary" style="padding: 6px 14px; font-size: 12px;">Minuman</span>
                        <span class="btn btn-secondary" style="padding: 6px 14px; font-size: 12px;">Paket Hemat</span>
                    </div>
                </div>

                <!-- Product Cards Grid -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                    <div style="background: #0F1F45; border: 2px solid #0284C7; border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 6px; position: relative;">
                        <span style="position: absolute; top: 8px; right: 8px; font-size: 10px; background: #0284C7; color: #fff; padding: 2px 6px; border-radius: 4px;">TERLARIS</span>
                        <div style="height: 70px; background: #16285A; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 28px;">🍗</div>
                        <div style="font-weight: 700; font-size: 13px; color: #fff;">Ayam Geprek Sambal Korek</div>
                        <div style="font-size: 11px; color: #94A3B8;">Stok Dapur: 48 porsi</div>
                        <div style="font-weight: 800; font-size: 14px; color: #38BDF8;">Rp 28.000</div>
                    </div>
                    <div style="background: #0F1F45; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 6px;">
                        <div style="height: 70px; background: #16285A; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 28px;">🍖</div>
                        <div style="font-weight: 700; font-size: 13px; color: #fff;">Ayam Bakar Madu Spesial</div>
                        <div style="font-size: 11px; color: #94A3B8;">Stok Dapur: 25 porsi</div>
                        <div style="font-weight: 800; font-size: 14px; color: #38BDF8;">Rp 32.000</div>
                    </div>
                    <div style="background: #0F1F45; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 6px;">
                        <div style="height: 70px; background: #16285A; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 28px;">☕</div>
                        <div style="font-weight: 700; font-size: 13px; color: #fff;">Es Kopi Susu Gula Aren</div>
                        <div style="font-size: 11px; color: #94A3B8;">Stok: 85 cup</div>
                        <div style="font-weight: 800; font-size: 14px; color: #38BDF8;">Rp 18.000</div>
                    </div>
                    <div style="background: #0F1F45; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 12px; display: flex; flex-direction: column; gap: 6px;">
                        <div style="height: 70px; background: #16285A; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 28px;">🍹</div>
                        <div style="font-weight: 700; font-size: 13px; color: #fff;">Lemon Tea Ice Jumbo</div>
                        <div style="font-size: 11px; color: #94A3B8;">Stok: 120 cup</div>
                        <div style="font-weight: 800; font-size: 14px; color: #38BDF8;">Rp 12.000</div>
                    </div>
                </div>

                <!-- Shift, Table, KDS Status Ribbon -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: auto;">
                    <div class="ui-card" style="padding: 12px; background: #0A1430;">
                        <div style="font-size: 11px; color: #94A3B8; font-weight: 600;">SHIFT KASIR & LACI</div>
                        <div style="font-size: 13px; font-weight: 700; color: #10B981; margin-top: 4px;">Shift 1 (Siti Rahmawati)</div>
                        <div style="font-size: 11px; color: #CBD5E1;">Modal Awal: Rp 500.000</div>
                    </div>
                    <div class="ui-card" style="padding: 12px; background: #0A1430;">
                        <div style="font-size: 11px; color: #94A3B8; font-weight: 600;">DINE-IN & MEJA</div>
                        <div style="font-size: 13px; font-weight: 700; color: #F59E0B; margin-top: 4px;">Meja 04 (Terisi - 4 Orang)</div>
                        <div style="font-size: 11px; color: #CBD5E1;">Denah: 8 Meja Kosong / 12 Total</div>
                    </div>
                    <div class="ui-card" style="padding: 12px; background: #0A1430;">
                        <div style="font-size: 11px; color: #94A3B8; font-weight: 600;">KITCHEN DISPLAY (KDS)</div>
                        <div style="font-size: 13px; font-weight: 700; color: #38BDF8; margin-top: 4px;">3 Pesanan di Dapur</div>
                        <div style="font-size: 11px; color: #CBD5E1;">Alur: Masak → Siap Diantar</div>
                    </div>
                </div>
            </div>

            <!-- POS Right: Active Transaction Cart & Pay -->
            <div style="width: 380px; padding: 20px; display: flex; flex-direction: column; gap: 16px; background: #0C1636;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 12px;">
                    <div>
                        <div style="font-size: 14px; font-weight: 800; color: #fff;">Order #TRX-202611-0842</div>
                        <div style="font-size: 11px; color: #94A3B8;">Pelanggan: Budi Santoso (Dine-In)</div>
                    </div>
                    <span class="badge badge-warning">Hold: 2 Nota</span>
                </div>

                <!-- Order Items list -->
                <div style="display: flex; flex-direction: column; gap: 10px; flex: 1; overflow-y: auto;">
                    <div style="background: #111F48; border-radius: 8px; padding: 10px; border-left: 3px solid #38BDF8;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 12px;">
                            <span>2x Ayam Geprek Sambal Korek</span>
                            <span>Rp 56.000</span>
                        </div>
                        <div style="font-size: 11px; color: #F59E0B; margin-top: 2px;">• Modifiers: Level 3 Pedas, Nasi Uduk</div>
                    </div>
                    <div style="background: #111F48; border-radius: 8px; padding: 10px; border-left: 3px solid #38BDF8;">
                        <div style="display: flex; justify-content: space-between; font-weight: 600; font-size: 12px;">
                            <span>2x Es Kopi Susu Gula Aren</span>
                            <span>Rp 36.000</span>
                        </div>
                        <div style="font-size: 11px; color: #10B981; margin-top: 2px;">• Modifiers: Less Sugar, Extra Jelly</div>
                    </div>
                </div>

                <!-- Calculation Summary -->
                <div style="background: #091129; border-radius: 8px; padding: 14px; display: flex; flex-direction: column; gap: 6px; font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; color: #94A3B8;"><span>Subtotal</span><span>Rp 92.000</span></div>
                    <div style="display: flex; justify-content: space-between; color: #94A3B8;"><span>Pajak Restoran (PB1 10%)</span><span>Rp 9.200</span></div>
                    <div style="display: flex; justify-content: space-between; color: #94A3B8;"><span>Pembulatan</span><span>Rp 0</span></div>
                    <div style="height: 1px; background: rgba(255,255,255,0.08); margin: 4px 0;"></div>
                    <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 16px; color: #38BDF8;">
                        <span>TOTAL TAGIHAN</span>
                        <span>Rp 101.200</span>
                    </div>
                </div>

                <!-- Payment Methods Grid -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                    <button class="btn btn-secondary" style="font-size: 12px; padding: 10px;">💵 Tunai (Cash)</button>
                    <button class="btn btn-primary" style="font-size: 12px; padding: 10px; background: #0284C7;">📱 QRIS Dinamis</button>
                    <button class="btn btn-secondary" style="font-size: 12px; padding: 10px;">💳 Kartu Debit/CC</button>
                    <button class="btn btn-secondary" style="font-size: 12px; padding: 10px;">🏦 Transfer Bank</button>
                </div>

                <!-- Actions: Thermal Print & WA Receipt -->
                <div style="display: flex; gap: 8px;">
                    <button class="btn btn-secondary" style="flex: 1; font-size: 11px;">🖨️ Cetak Thermal</button>
                    <button class="btn btn-success" style="flex: 1; font-size: 11px; background: #128C7E;">💬 Struk WhatsApp</button>
                </div>
            </div>
        </div>
    </div>
    """

# Module B: Anti-Fraud, Otorisasi PIN & Audit Trail
def get_module_b_html():
    return f"""
    <div class="app-window" id="modul-b">
        {make_window_header('B', 'ANTI-FRAUD, OTORISASI & AUDIT TRAIL', '/security/authorizations')}
        <div class="app-body">
            <!-- Sidebar -->
            <div class="app-sidebar">
                <div class="nav-item active">🛡️ Pusat Otorisasi & PIN</div>
                <div class="nav-item">⚙️ Approval Rules Engine</div>
                <div class="nav-item">📜 Audit Trail Real-Time</div>
                <div class="nav-item">👥 Granular RBAC Peran</div>
            </div>

            <!-- Content -->
            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Pusat Otorisasi Kasir & Mesin Kebijakan Anti-Fraud</div>
                        <div class="content-subtitle">Mencegah kebocoran pendapatan, manipulasi diskon, dan void sepihak</div>
                    </div>
                    <span class="badge badge-success">Sistem Keamanan Aktif: 0 Pelanggaran</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- PIN Supervisor Modal Simulation -->
                    <div class="ui-card" style="border: 2px solid #EF4444; background: #141129;">
                        <div class="ui-card-title">
                            <span style="color: #EF4444; display: flex; align-items: center; gap: 8px;">
                                🔒 Otorisasi Supervisor Dibutuhkan
                            </span>
                            <span class="badge badge-danger">High Security</span>
                        </div>
                        <div style="background: rgba(239,68,68,0.1); border-left: 3px solid #EF4444; padding: 12px; border-radius: 6px; margin-bottom: 14px; font-size: 12px; color: #FCA5A5;">
                            Permintaan Pembatalan Transaksi (Void) Nota #TRX-0839 senilai Rp 245.000 oleh Kasir Siti Rahmawati.
                        </div>
                        <div style="text-align: center; padding: 14px 0;">
                            <div style="font-size: 12px; color: #94A3B8; margin-bottom: 8px;">Masukkan 6-Digit PIN Supervisor / Owner:</div>
                            <div style="display: flex; justify-content: center; gap: 10px;">
                                <span style="width: 36px; height: 42px; background: #080D21; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px;">•</span>
                                <span style="width: 36px; height: 42px; background: #080D21; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px;">•</span>
                                <span style="width: 36px; height: 42px; background: #080D21; border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px;">•</span>
                                <span style="width: 36px; height: 42px; background: #080D21; border: 1px solid #38BDF8; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 20px;">|</span>
                                <span style="width: 36px; height: 42px; background: #080D21; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;"></span>
                                <span style="width: 36px; height: 42px; background: #080D21; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px;"></span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <button class="btn btn-secondary" style="flex: 1;">Tolak Void</button>
                            <button class="btn btn-primary" style="flex: 1; background: #EF4444;">Setujui dengan PIN</button>
                        </div>
                    </div>

                    <!-- Approval Rules Engine -->
                    <div class="ui-card">
                        <div class="ui-card-title">Aturan Persetujuan Bertingkat (Approval Rules Engine)</div>
                        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 12px;">
                            <div style="background: #09132E; padding: 10px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 700; color: #fff;">Batas Wewenang Diskon Kasir</div>
                                    <div style="color: #94A3B8; font-size: 11px;">Maksimal 5% tanpa approval. Di atas 5% wajib persetujuan remote owner.</div>
                                </div>
                                <span class="badge badge-success">Maks 5%</span>
                            </div>
                            <div style="background: #09132E; padding: 10px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 700; color: #fff;">Persetujuan PO Bernilai Besar</div>
                                    <div style="color: #94A3B8; font-size: 11px;">Nilai PO &gt; Rp 10.000.000 wajib multi-approval Manajer & Owner.</div>
                                </div>
                                <span class="badge badge-warning">Multi-Tier</span>
                            </div>
                            <div style="background: #09132E; padding: 10px 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 700; color: #fff;">Toleransi Selisih Stock Opname</div>
                                    <div style="color: #94A3B8; font-size: 11px;">Selisih &gt; Rp 200.000 otomatis membekukan penyesuaian hingga owner ACC.</div>
                                </div>
                                <span class="badge badge-danger">Strict Lock</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-Time Audit Trail -->
                <div class="ui-card">
                    <div class="ui-card-title">
                        <span>Catatan Audit Trail Real-Time (Log Aktivitas Tak Terbantahkan)</span>
                        <span style="font-size: 11px; color: #94A3B8;">Otomatis merekam IP, Timestamp & Nilai Data</span>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Karyawan</th>
                                <th>Peran</th>
                                <th>Aktivitas</th>
                                <th>Perangkat & IP</th>
                                <th>Perubahan Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono">12:38:14 WIB</td>
                                <td style="font-weight: 700;">Siti Rahmawati</td>
                                <td>Kasir</td>
                                <td>Buka Laci Kasir Manual</td>
                                <td>POS-01 (192.168.1.15)</td>
                                <td>Modal awal fisik verified</td>
                                <td><span class="badge badge-success">Authorized</span></td>
                            </tr>
                            <tr>
                                <td class="mono">12:15:02 WIB</td>
                                <td style="font-weight: 700;">Ahmad Faisal</td>
                                <td>Staf Gudang</td>
                                <td>Mutasi Stok Keluar</td>
                                <td>Mobile App (182.1.24.8)</td>
                                <td>Daging Ayam: 20kg → 15kg</td>
                                <td><span class="badge badge-info">Verified</span></td>
                            </tr>
                            <tr>
                                <td class="mono">11:42:50 WIB</td>
                                <td style="font-weight: 700;">Budi Santoso</td>
                                <td>Supervisor</td>
                                <td>Approval Diskon 10%</td>
                                <td>POS-01 (192.168.1.15)</td>
                                <td>Nota #0812: Rp 150rb → Rp 135rb</td>
                                <td><span class="badge badge-warning">PIN Approved</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    """

# Module C: Produksi, BOM Resep & Kontrol HPP
def get_module_c_html():
    return f"""
    <div class="app-window" id="modul-c">
        {make_window_header('C', 'PRODUKSI, RESEP FORMULA (BOM) & KONTROL HPP', '/production/bom')}
        <div class="app-body">
            <!-- Sidebar -->
            <div class="app-sidebar">
                <div class="nav-item active">🧪 Resep Formula (BOM)</div>
                <div class="nav-item">⚖️ Susut & Rendemen</div>
                <div class="nav-item">👷 Biaya Tenaga Kerja</div>
                <div class="nav-item">⚙️ Depresiasi Mesin</div>
                <div class="nav-item">📊 Simulator BEP & Margin</div>
            </div>

            <!-- Content -->
            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Bill of Materials (BOM) & Kalkulasi HPP Presisi</div>
                        <div class="content-subtitle">Formula baku memotong bahan otomatis per porsi terjual di kasir</div>
                    </div>
                    <button class="btn btn-primary">+ Tambah Resep Baru</button>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                    <!-- BOM Breakdown -->
                    <div class="ui-card">
                        <div class="ui-card-title">
                            <span>Resep Baku: 1 Porsi Ayam Geprek Sambal Korek</span>
                            <span class="badge badge-info">Kategori: Makanan Utama</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Komponen Bahan Baku</th>
                                    <th>Takaran Standar</th>
                                    <th>Harga Pokok Satuan</th>
                                    <th>Subtotal Biaya</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Daging Ayam Fillet Segar</td><td>200 gram</td><td>Rp 37.000 / kg</td><td class="mono">Rp 7.400</td></tr>
                                <tr><td>Tepung Bumbu Crispy Premium</td><td>40 gram</td><td>Rp 24.000 / kg</td><td class="mono">Rp 960</td></tr>
                                <tr><td>Minyak Goreng Sawit Curah</td><td>50 ml</td><td>Rp 17.000 / liter</td><td class="mono">Rp 850</td></tr>
                                <tr><td>Cabai Rawit Merah & Bawang</td><td>15 gram</td><td>Rp 50.000 / kg</td><td class="mono">Rp 750</td></tr>
                                <tr><td>Kemasan Kotak Kraft Eco</td><td>1 pcs</td><td>Rp 1.200 / pcs</td><td class="mono">Rp 1.200</td></tr>
                            </tbody>
                        </table>

                        <!-- Additional Manufacturing Costs -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px;">
                            <div style="background: #09132E; padding: 10px; border-radius: 8px; font-size: 11px;">
                                <div style="color: #94A3B8;">Susut Bahan (Wastage 8%)</div>
                                <div style="font-weight: 700; color: #F59E0B; font-size: 13px;">+Rp 796</div>
                            </div>
                            <div style="background: #09132E; padding: 10px; border-radius: 8px; font-size: 11px;">
                                <div style="color: #94A3B8;">Biaya Tenaga Kerja (Labor)</div>
                                <div style="font-weight: 700; color: #38BDF8; font-size: 13px;">+Rp 2.500 / porsi</div>
                            </div>
                            <div style="background: #09132E; padding: 10px; border-radius: 8px; font-size: 11px;">
                                <div style="color: #94A3B8;">Tarif Mesin & Listrik</div>
                                <div style="font-weight: 700; color: #C084FC; font-size: 13px;">+Rp 600 / jam</div>
                            </div>
                        </div>
                    </div>

                    <!-- Margin & BEP Simulator Card -->
                    <div class="ui-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                        <div class="ui-card-title">Ringkasan Margin & HPP</div>
                        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #94A3B8;">Total HPP Presisi:</span>
                                <span class="mono" style="font-weight: 800; color: #F87171;">Rp 15.056</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #94A3B8;">Harga Jual di Kasir:</span>
                                <span class="mono" style="font-weight: 800; color: #38BDF8;">Rp 28.000</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #94A3B8;">Laba Kotor (Gross Profit):</span>
                                <span class="mono" style="font-weight: 800; color: #34D399;">Rp 12.944 (46.2%)</span>
                            </div>
                            <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); border-radius: 8px; padding: 12px;">
                                <div style="font-size: 11px; color: #34D399; font-weight: 700;">SIMULATOR TITIK IMPAS (BEP)</div>
                                <div style="font-size: 12px; color: #E2E8F0; margin-top: 4px;">
                                    Dibutuhkan penjualan <b>54 porsi/hari</b> (1.610 porsi/bulan) untuk menutup modal sewa & operasional.
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-secondary" style="width: 100%; margin-top: 14px;">Simulasi Kenaikan Harga Bahan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Module D: Penjualan B2B, Grosir & Piutang
def get_module_d_html():
    return f"""
    <div class="app-window" id="modul-d">
        {make_window_header('D', 'PENJUALAN B2B, GROSIR & PIUTANG', '/sales/b2b')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">📄 Surat Penawaran (Quotation)</div>
                <div class="nav-item">📦 Sales Orders (Grosir)</div>
                <div class="nav-item">🧾 Faktur & Invoice Bertahap</div>
                <div class="nav-item">⏳ Pelacakan Piutang (AR)</div>
                <div class="nav-item">🔄 Retur Penjualan & Credit Note</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Manajemen Penjualan Grosir (B2B) & Piutang Usaha</div>
                        <div class="content-subtitle">Quotation resmi PDF, pengiriman bertahap, dan pengingat jatuh tempo</div>
                    </div>
                    <button class="btn btn-primary">+ Buat Surat Penawaran</button>
                </div>

                <!-- Aging Receivables & Metrics -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;">
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">TOTAL PIUTANG B2B</div>
                        <div style="font-size: 18px; font-weight: 800; color: #fff; margin-top: 4px;">Rp 142.800.000</div>
                        <div style="font-size: 11px; color: #34D399; margin-top: 2px;">18 Invoice Aktif</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">LANCAR (BELUM TEMPO)</div>
                        <div style="font-size: 18px; font-weight: 800; color: #34D399; margin-top: 4px;">Rp 98.000.000</div>
                        <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">68.6% dari total</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">TEMPO 1 - 30 HARI</div>
                        <div style="font-size: 18px; font-weight: 800; color: #FBBF24; margin-top: 4px;">Rp 32.500.000</div>
                        <div style="font-size: 11px; color: #FBBF24; margin-top: 2px;">5 Invoice Perlu Diingatkan</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">MACET &gt; 30 HARI</div>
                        <div style="font-size: 18px; font-weight: 800; color: #F87171; margin-top: 4px;">Rp 12.300.000</div>
                        <div style="font-size: 11px; color: #F87171; margin-top: 2px;">2 Klien Limit Beku</div>
                    </div>
                </div>

                <!-- Quotations & Orders Table -->
                <div class="ui-card">
                    <div class="ui-card-title">
                        <span>Daftar Pesanan Grosir & Status Faktur</span>
                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 10px;">Filter Status</span>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Dokumen</th>
                                <th>Nama Klien B2B</th>
                                <th>Nilai Kontrak</th>
                                <th>Skema Termin</th>
                                <th>Jatuh Tempo</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi Cepat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono font-bold">SO-2026-089</td>
                                <td>PT Boga Mandiri Sejahtera</td>
                                <td class="mono">Rp 48.500.000</td>
                                <td>DP 30% + Net 30</td>
                                <td>24 Nov 2026</td>
                                <td><span class="badge badge-warning">DP Lunas (Sisa Rp 33.950.000)</span></td>
                                <td><span class="btn btn-secondary" style="font-size: 10px; padding: 3px 8px;">Kirim WA Tagihan</span></td>
                            </tr>
                            <tr>
                                <td class="mono font-bold">QUO-2026-112</td>
                                <td>CV Cita Rasa Nusantara</td>
                                <td class="mono">Rp 22.000.000</td>
                                <td>Tempo Net 14</td>
                                <td>30 Nov 2026</td>
                                <td><span class="badge badge-info">Quotation Disetujui</span></td>
                                <td><span class="btn btn-primary" style="font-size: 10px; padding: 3px 8px;">Konversi ke SO</span></td>
                            </tr>
                            <tr>
                                <td class="mono font-bold">INV-2026-064</td>
                                <td>Hotel Grand Pasundan</td>
                                <td class="mono">Rp 72.300.000</td>
                                <td>Tempo Net 30</td>
                                <td>10 Nov 2026</td>
                                <td><span class="badge badge-danger">Lewat 7 Hari</span></td>
                                <td><span class="btn btn-secondary" style="font-size: 10px; padding: 3px 8px; color: #F87171;">Tagih Ulang</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    """

# Module E: Gudang, Inventori & Pengadaan
def get_module_e_html():
    return f"""
    <div class="app-window" id="modul-e">
        {make_window_header('E', 'GUDANG, INVENTORI & PENGADAAN', '/inventory/stocks')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">📦 Kartu Stok Real-Time</div>
                <div class="nav-item">⚠️ Peringatan Stok Minimum</div>
                <div class="nav-item">🚚 Surat Jalan Antar Cabang</div>
                <div class="nav-item">📋 Stock Opname Fisik</div>
                <div class="nav-item">🏢 Database Rekanan Vendor</div>
                <div class="nav-item">🛒 Purchase Orders (PO)</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Manajemen Stok Multi-Gudang & Pengadaan Supplier</div>
                        <div class="content-subtitle">Sinkronisasi otomatis mutasi bahan, peringatan batas aman & pesanan PO</div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-secondary">Transfer Stok Cabang</button>
                        <button class="btn btn-primary">+ Buat Purchase Order</button>
                    </div>
                </div>

                <!-- Alert Low Stock Banner -->
                <div style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); border-radius: 10px; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 20px;">🚨</span>
                        <div>
                            <div style="font-weight: 700; color: #FCA5A5; font-size: 13px;">3 Bahan Baku Di Bawah Batas Minimum Stok!</div>
                            <div style="font-size: 11px; color: #E2E8F0;">Daging Ayam Fillet (Sisa 4.2 kg, Min 10 kg), Kemasan Kotak (Sisa 45 pcs, Min 200 pcs).</div>
                        </div>
                    </div>
                    <button class="btn btn-primary" style="background: #EF4444; font-size: 11px;">Generate PO Otomatis ke Supplier</button>
                </div>

                <!-- Multi-Branch Warehouse Inventory Table -->
                <div class="ui-card">
                    <div class="ui-card-title">
                        <span>Kartu Persediaan Multi-Cabang & Status Pengadaan</span>
                        <div style="display: flex; gap: 6px;">
                            <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">Gudang Pusat</span>
                            <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">Cabang Dago</span>
                            <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">Cabang Sudirman</span>
                        </div>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kode SKU</th>
                                <th>Nama Bahan / Barang</th>
                                <th>Lokasi Gudang</th>
                                <th>Stok Fisik</th>
                                <th>Batas Min.</th>
                                <th>Status Stok</th>
                                <th>Histori Mutasi Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="mono font-bold">MAT-AYM-01</td>
                                <td>Daging Ayam Fillet Segar</td>
                                <td>Cabang Dago (Dapur)</td>
                                <td class="mono font-bold" style="color: #F87171;">4.2 kg</td>
                                <td class="mono">10.0 kg</td>
                                <td><span class="badge badge-danger">Kritis (Segera PO)</span></td>
                                <td style="font-size: 11px; color: #94A3B8;">-12.8 kg (Penjualan Kasir Shift 1)</td>
                            </tr>
                            <tr>
                                <td class="mono font-bold">MAT-TPG-02</td>
                                <td>Tepung Crispy Formula</td>
                                <td>Gudang Pusat (Central)</td>
                                <td class="mono font-bold">145.0 kg</td>
                                <td class="mono">50.0 kg</td>
                                <td><span class="badge badge-success">Aman Melimpah</span></td>
                                <td style="font-size: 11px; color: #94A3B8;">+50 kg (Penerimaan PO #042)</td>
                            </tr>
                            <tr>
                                <td class="mono font-bold">MAT-BOX-09</td>
                                <td>Kemasan Kraft Eco 16x16</td>
                                <td>Cabang Sudirman</td>
                                <td class="mono font-bold" style="color: #FBBF24;">75 pcs</td>
                                <td class="mono">100 pcs</td>
                                <td><span class="badge badge-warning">Mendekati Batas</span></td>
                                <td style="font-size: 11px; color: #94A3B8;">Surat Jalan In-Transit (+500 pcs)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    """

# Module F: Keuangan SAK EMKM
def get_module_f_html():
    return f"""
    <div class="app-window" id="modul-f">
        {make_window_header('F', 'KEUANGAN & PEMBUKUAN STANDAR SAK EMKM', '/finance/reports')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">📊 Laba Rugi Komprehensif</div>
                <div class="nav-item">⚖️ Neraca Posisi Keuangan</div>
                <div class="nav-item">🏦 Kas & Multi-Rekening Bank</div>
                <div class="nav-item">📑 Bagan Akun (COA) Baku</div>
                <div class="nav-item">📖 Buku Besar & Jurnal Otomatis</div>
                <div class="nav-item">🔄 Rekonsiliasi Bank & EDC/QRIS</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Pembukuan Otomatis & Laporan Finansial Standar SAK EMKM</div>
                        <div class="content-subtitle">Jurnal Double-Entry otomatis tanpa ribet debit kredit manual</div>
                    </div>
                    <button class="btn btn-secondary">Ekspor Laporan Pajak (PPN/PB1)</button>
                </div>

                <!-- Financial Performance Snapshot -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;">
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">PENDAPATAN USAHA (OMZET)</div>
                        <div style="font-size: 18px; font-weight: 800; color: #38BDF8; margin-top: 4px;">Rp 184.200.000</div>
                        <div style="font-size: 11px; color: #34D399; margin-top: 2px;">+14.8% vs bulan lalu</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">HARGA POKOK PENJUALAN (HPP)</div>
                        <div style="font-size: 18px; font-weight: 800; color: #F87171; margin-top: 4px;">Rp 98.400.000</div>
                        <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">53.4% dari omzet</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">BEBAN OPERASIONAL (EXPENSE)</div>
                        <div style="font-size: 18px; font-weight: 800; color: #FBBF24; margin-top: 4px;">Rp 38.600.000</div>
                        <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Gaji, Listrik, Sewa</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">LABA BERSIH RIIL (NET PROFIT)</div>
                        <div style="font-size: 18px; font-weight: 800; color: #34D399; margin-top: 4px;">Rp 47.200.000</div>
                        <div style="font-size: 11px; color: #34D399; margin-top: 2px;">Net Margin 25.6%</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Double-entry Auto Journal Stream -->
                    <div class="ui-card">
                        <div class="ui-card-title">Aliran Jurnal Otomatis (Double-Entry Live)</div>
                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12px;">
                            <div style="background: #09132E; padding: 10px; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; font-weight: 700; color: #fff;">
                                    <span>Penjualan Kasir POS #0842</span>
                                    <span class="mono">12:38 WIB</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #34D399; margin-top: 4px;">
                                    <span>(D) 1010 Kas Kasir Laci</span>
                                    <span class="mono">Rp 101.200</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #94A3B8;">
                                    <span>(K) 4010 Pendapatan Penjualan Makanan</span>
                                    <span class="mono">Rp 92.000</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; color: #94A3B8;">
                                    <span>(K) 2030 Utang Pajak Restoran (PB1)</span>
                                    <span class="mono">Rp 9.200</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cash & Multi-bank Accounts -->
                    <div class="ui-card">
                        <div class="ui-card-title">Saldo Kas & Multi-Rekening Bank Operasional</div>
                        <table class="data-table">
                            <thead><tr><th>Akun Bank / Kas</th><th>Nomor Rekening</th><th>Saldo Efektif</th></tr></thead>
                            <tbody>
                                <tr><td>Bank BCA Bisnis</td><td class="mono">882-019-2811</td><td class="mono font-bold" style="color: #38BDF8;">Rp 84.500.000</td></tr>
                                <tr><td>Bank Mandiri Operasional</td><td class="mono">132-00-19283-1</td><td class="mono font-bold" style="color: #38BDF8;">Rp 42.100.000</td></tr>
                                <tr><td>Kas Kasir (Petty Cash Outlet)</td><td class="mono">Kas Laci Fisik</td><td class="mono font-bold" style="color: #34D399;">Rp 3.850.000</td></tr>
                                <tr><td>Dana Settlement QRIS & EDC</td><td class="mono">Tripay / Midtrans</td><td class="mono font-bold" style="color: #FBBF24;">Rp 8.750.000</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Module G: Toko Online Storefront
def get_module_g_html():
    return f"""
    <div class="app-window" id="modul-g">
        {make_window_header('G', 'TOKO ONLINE (STOREFRONT) & OMNICHANNEL', '/storefront/settings')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">🌐 Web Toko Online (0% Fee)</div>
                <div class="nav-item">🎨 Theme & Branding Editor</div>
                <div class="nav-item">🛒 Alur Checkout Mandiri</div>
                <div class="nav-item">🏷️ Label Resi Pengiriman</div>
                <div class="nav-item">📅 Reservasi & Booking Online</div>
                <div class="nav-item">🔗 Hub Marketplace (Shopee/Tokped)</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Toko Online Mandiri 0% Komisi Transaksi & Omnichannel Hub</div>
                        <div class="content-subtitle">Alamat mandiri: tokokamu.cooca.id dengan sinkronisasi inventori otomatis</div>
                    </div>
                    <span class="badge badge-success">0% Komisi Pihak Ketiga</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Storefront Live Mockup Preview -->
                    <div class="ui-card" style="background: #080D21;">
                        <div class="ui-card-title">
                            <span>Live Mobile Web Preview (tokokamu.cooca.id)</span>
                            <span class="badge badge-info">Responsive Web</span>
                        </div>
                        <div style="background: #0F172A; border-radius: 12px; padding: 16px; border: 1px solid rgba(255,255,255,0.1);">
                            <!-- Mobile Header -->
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px;">
                                <div style="font-weight: 800; font-size: 14px; color: #38BDF8;">TOKO KAMU ARTISAN</div>
                                <span style="font-size: 12px;">🛒 2 item (Rp 46.000)</span>
                            </div>
                            <!-- Banner -->
                            <div style="background: linear-gradient(135deg, #0284C7, #1E3A8A); border-radius: 8px; padding: 14px; margin-top: 10px; color: #fff;">
                                <div style="font-weight: 800; font-size: 14px;">PROMO MENU BARU!</div>
                                <div style="font-size: 11px;">Gratis Ongkir se-Kota Bandung Min Belanja Rp 50rb</div>
                            </div>
                            <!-- Products -->
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; background: #1E293B; padding: 8px 12px; border-radius: 6px;">
                                    <div>
                                        <div style="font-size: 12px; font-weight: 700;">Ayam Geprek Sambal Korek</div>
                                        <div style="font-size: 11px; color: #38BDF8;">Rp 28.000</div>
                                    </div>
                                    <button class="btn btn-primary" style="font-size: 10px; padding: 4px 10px;">+ Pesan</button>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: #1E293B; padding: 8px 12px; border-radius: 6px;">
                                    <div>
                                        <div style="font-size: 12px; font-weight: 700;">Es Kopi Susu Gula Aren</div>
                                        <div style="font-size: 11px; color: #38BDF8;">Rp 18.000</div>
                                    </div>
                                    <button class="btn btn-primary" style="font-size: 10px; padding: 4px 10px;">+ Pesan</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Marketplace Hub & Shipping Settings -->
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="ui-card">
                            <div class="ui-card-title">Hub Marketplace Terintegrasi (Multi-Channel Sync)</div>
                            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; background: #09132E; padding: 10px; border-radius: 8px;">
                                    <span style="font-weight: 700; color: #FF5722;">Shopee Indonesia</span>
                                    <span class="badge badge-success">Terhubung (14 Pesanan Masuk)</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: #09132E; padding: 10px; border-radius: 8px;">
                                    <span style="font-weight: 700; color: #00B14F;">Tokopedia Store</span>
                                    <span class="badge badge-success">Terhubung (9 Pesanan Masuk)</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: #09132E; padding: 10px; border-radius: 8px;">
                                    <span style="font-weight: 700; color: #FE2C55;">TikTok Shop</span>
                                    <span class="badge badge-success">Terhubung (21 Pesanan Masuk)</span>
                                </div>
                            </div>
                        </div>

                        <div class="ui-card">
                            <div class="ui-card-title">Aturan Ongkos Kirim Fleksibel</div>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 11px;">
                                <div style="background: #09132E; padding: 10px; border-radius: 8px;">
                                    <div style="font-weight: 700; color: #fff;">Ambil di Toko (Self-Pickup)</div>
                                    <div style="color: #34D399; margin-top: 2px;">Gratis Ongkir Rp 0</div>
                                </div>
                                <div style="background: #09132E; padding: 10px; border-radius: 8px;">
                                    <div style="font-weight: 700; color: #fff;">Tarif Radius Jarak (KM)</div>
                                    <div style="color: #38BDF8; margin-top: 2px;">Rp 2.500 / km pertama</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Module H: WhatsApp Gateway
def get_module_h_html():
    return f"""
    <div class="app-window" id="modul-h">
        {make_window_header('H', 'WHATSAPP GATEWAY & PEMASARAN DIGITAL', '/marketing/whatsapp')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">💬 Struk Digital WhatsApp</div>
                <div class="nav-item">📢 WhatsApp Broadcast Promo</div>
                <div class="nav-item">📅 Kalender Konten Medsos</div>
                <div class="nav-item">📥 Social Inbox Terpadu</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Pusat Notifikasi WhatsApp & Kampanye Pemasaran</div>
                        <div class="content-subtitle">Kirim struk kasir otomatis via WhatsApp & broadcast promosi ke pelanggan</div>
                    </div>
                    <span class="badge badge-success">WhatsApp Business API Terhubung</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Real WhatsApp Digital Receipt Chat Preview -->
                    <div class="ui-card" style="background: #0B1917; border-color: rgba(37,211,102,0.2);">
                        <div class="ui-card-title">
                            <span style="color: #25D366; display: flex; align-items: center; gap: 8px;">
                                📱 Tampilan Struk Digital WhatsApp Pelanggan
                            </span>
                            <span class="badge badge-success">Delivered</span>
                        </div>
                        <div style="background: #075E54; border-radius: 10px; padding: 14px; color: #fff; font-size: 12px; border-left: 4px solid #25D366;">
                            <div style="font-weight: 800; font-size: 14px;">🧾 STRUK BELANJA TOKO KAMU</div>
                            <div style="font-size: 11px; opacity: 0.8; margin-top: 2px;">Terima kasih telah berbelanja, Kak Hendra!</div>
                            <div style="height: 1px; background: rgba(255,255,255,0.2); margin: 8px 0;"></div>
                            <div>• 2x Ayam Geprek Sambal Korek : Rp 56.000</div>
                            <div>• 2x Es Kopi Susu Gula Aren : Rp 36.000</div>
                            <div>• PB1 (10%) : Rp 9.200</div>
                            <div style="font-weight: 800; margin-top: 6px; font-size: 13px;">TOTAL LUNAS (QRIS): Rp 101.200</div>
                            <div style="height: 1px; background: rgba(255,255,255,0.2); margin: 8px 0;"></div>
                            <div style="font-size: 11px;">Poin Loyalitas bertambah: +101 Poin (Total: 2.450 Poin)</div>
                            <div style="font-size: 10px; opacity: 0.7; margin-top: 6px; text-align: right;">12:38 WIB ✓✓</div>
                        </div>
                    </div>

                    <!-- WhatsApp Broadcast Campaign Center -->
                    <div class="ui-card">
                        <div class="ui-card-title">Kampanye Broadcast Promo WhatsApp</div>
                        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 12px;">
                            <div style="background: #09132E; padding: 12px; border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; font-weight: 700;">
                                    <span>Promo Gajian Diskon 20%</span>
                                    <span class="badge badge-success">Selesai Terkirim</span>
                                </div>
                                <div style="color: #94A3B8; font-size: 11px; margin-top: 4px;">Penerima: 1.250 Pelanggan VIP &amp; Reguler</div>
                                <div style="display: flex; gap: 16px; margin-top: 8px; font-size: 11px;">
                                    <span style="color: #34D399;">✓ Delivery: 99.2% (1.240)</span>
                                    <span style="color: #38BDF8;">👁️ Read Rate: 84.6%</span>
                                    <span style="color: #FBBF24;">🎟️ Voucher Terpakai: 184</span>
                                </div>
                            </div>
                            <button class="btn btn-primary" style="background: #128C7E;">+ Buat Broadcast Promosi Baru</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Module I: CRM & Program Loyalitas
def get_module_i_html():
    return f"""
    <div class="app-window" id="modul-i">
        {make_window_header('I', 'CRM & PROGRAM LOYALITAS PELANGGAN', '/crm/customers')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">👤 Database Pelanggan</div>
                <div class="nav-item">⭐ Tingkatan Member (Tiering)</div>
                <div class="nav-item">🎁 Poin & Reward Belanja</div>
                <div class="nav-item">🎟️ Kupon & Voucher Diskon</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Pusat Data Pelanggan (CRM) & Sistem Loyalitas Poin</div>
                        <div class="content-subtitle">Meningkatkan repeat order dan nilai belanja seumur hidup (CLV)</div>
                    </div>
                    <button class="btn btn-primary">+ Tambah Member Baru</button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Customer Detailed Profile Card -->
                    <div class="ui-card" style="border-left: 4px solid #C084FC;">
                        <div class="ui-card-title">
                            <span>Profil Pelanggan Unggulan (VIP)</span>
                            <span class="badge badge-purple">Platinum Member</span>
                        </div>
                        <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 14px;">
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: #1E293B; display: flex; align-items: center; justify-content: center; font-size: 22px;">👔</div>
                            <div>
                                <div style="font-weight: 800; font-size: 15px; color: #fff;">Hendra Wijaya, S.T.</div>
                                <div style="font-size: 12px; color: #94A3B8;">+62 812-9876-5432 • hendra.w@gmail.com</div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 11px;">
                            <div style="background: #09132E; padding: 10px; border-radius: 8px;">
                                <div style="color: #94A3B8;">Total Belanja (CLV)</div>
                                <div style="font-weight: 800; color: #38BDF8; font-size: 13px;">Rp 18.650.000</div>
                            </div>
                            <div style="background: #09132E; padding: 10px; border-radius: 8px;">
                                <div style="color: #94A3B8;">Jumlah Kunjungan</div>
                                <div style="font-weight: 800; color: #34D399; font-size: 13px;">42x Transaksi</div>
                            </div>
                            <div style="background: #09132E; padding: 10px; border-radius: 8px;">
                                <div style="color: #94A3B8;">Poin Belanja Aktif</div>
                                <div style="font-weight: 800; color: #FBBF24; font-size: 13px;">2.450 Poin</div>
                            </div>
                        </div>
                    </div>

                    <!-- Member Tiering Rules -->
                    <div class="ui-card">
                        <div class="ui-card-title">Tingkatan Keanggotaan (Membership Tiering)</div>
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                            <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #09132E; border-radius: 6px;">
                                <span style="font-weight: 700; color: #CBD5E1;">🥉 Bronze Member</span>
                                <span style="color: #94A3B8;">Belanja Rp 0 - Rp 2.000.000 (Poin 1x)</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #09132E; border-radius: 6px;">
                                <span style="font-weight: 700; color: #38BDF8;">🥈 Silver Member</span>
                                <span style="color: #94A3B8;">Belanja Rp 2 Jt - Rp 5 Jt (Poin 1.2x)</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #09132E; border-radius: 6px;">
                                <span style="font-weight: 700; color: #FBBF24;">🥇 Gold Member</span>
                                <span style="color: #94A3B8;">Belanja Rp 5 Jt - Rp 15 Jt (Diskon 5%)</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 8px 12px; background: #09132E; border-radius: 6px;">
                                <span style="font-weight: 700; color: #C084FC;">💎 Platinum VIP</span>
                                <span style="color: #94A3B8;">Belanja &gt; Rp 15 Jt (Diskon 10% + Gift)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Module J: HRM & Payroll
def get_module_j_html():
    return f"""
    <div class="app-window" id="modul-j">
        {make_window_header('J', 'MANAJEMEN KARYAWAN & PENGGAJIAN (HRM & PAYROLL)', '/hrm/payroll')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">👥 Database Staf Multi-Outlet</div>
                <div class="nav-item">📅 Roster Shift & Jadwal Tim</div>
                <div class="nav-item">📍 Presensi GPS (Geofencing)</div>
                <div class="nav-item">💳 Pencatatan Kasbon Staf</div>
                <div class="nav-item">💰 Kalkulator Gaji (Payroll)</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Manajemen Karyawan, Presensi Geofencing & Slip Gaji Digital</div>
                        <div class="content-subtitle">Penggajian otomatis: Gaji Pokok + Uang Hadir + Lembur - Kasbon</div>
                    </div>
                    <button class="btn btn-primary">+ Generate Slip Gaji Bulan Ini</button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- GPS Geofencing Attendance Card -->
                    <div class="ui-card">
                        <div class="ui-card-title">
                            <span>Presensi Geofencing GPS Real-Time</span>
                            <span class="badge badge-success">Radius 50m Valid</span>
                        </div>
                        <div style="background: #09132E; border-radius: 8px; padding: 12px; font-size: 12px; display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #94A3B8;">Lokasi Terkunci:</span>
                                <span style="font-weight: 700; color: #fff;">Outlet Dago Flagship</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #94A3B8;">Koordinat Toko:</span>
                                <span class="mono" style="color: #38BDF8;">-6.914744, 107.609810</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #94A3B8;">Status Staf Hari Ini:</span>
                                <span style="color: #34D399; font-weight: 700;">8 Hadir Tepat Waktu / 0 Terlambat</span>
                            </div>
                        </div>
                    </div>

                    <!-- Digital Pay Slip Simulator -->
                    <div class="ui-card" style="border: 1px solid rgba(16,185,129,0.3);">
                        <div class="ui-card-title">
                            <span>Slip Gaji Digital (Siti Rahmawati - Kasir Senior)</span>
                            <span class="btn btn-success" style="font-size: 10px; padding: 2px 8px; background: #128C7E;">Kirim WA</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 6px; font-size: 12px;">
                            <div style="display: flex; justify-content: space-between; color: #94A3B8;"><span>Gaji Pokok (UMR Bandung)</span><span class="mono">Rp 4.500.000</span></div>
                            <div style="display: flex; justify-content: space-between; color: #94A3B8;"><span>Uang Kehadiran (26 Hari)</span><span class="mono">Rp 650.000</span></div>
                            <div style="display: flex; justify-content: space-between; color: #94A3B8;"><span>Upah Lembur (12 Jam)</span><span class="mono">Rp 360.000</span></div>
                            <div style="display: flex; justify-content: space-between; color: #94A3B8;"><span>Komisi Target Penjualan Kasir</span><span class="mono">Rp 420.000</span></div>
                            <div style="display: flex; justify-content: space-between; color: #F87171;"><span>Potongan Cicilan Kasbon Staf</span><span class="mono">(Rp 250.000)</span></div>
                            <div style="height: 1px; background: rgba(255,255,255,0.08); margin: 4px 0;"></div>
                            <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 14px; color: #34D399;">
                                <span>TOTAL TAKE HOME PAY:</span>
                                <span class="mono">Rp 5.680.000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Module K: Konektivitas MCP (Model Context Protocol)
def get_module_k_html():
    return f"""
    <div class="app-window" id="modul-k">
        {make_window_header('K', 'KONEKTIVITAS AI BEBAS VIA MODEL CONTEXT PROTOCOL (MCP)', '/settings/mcp')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">🤖 Protokol Terbuka MCP</div>
                <div class="nav-item">💬 Tanya Jawab Data Real-Time</div>
                <div class="nav-item">⚡ Automasi Perintah Bisnis</div>
                <div class="nav-item">🔑 Manajemen API Key &amp; Token</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Konektor Model Context Protocol (MCP) Bebas Vendor Lock-in</div>
                        <div class="content-subtitle">Hubungkan COOCA ke Claude, ChatGPT, Gemini, Cursor, atau Local LLM</div>
                    </div>
                    <span class="badge badge-info">Open Protocol MCP v1.0</span>
                </div>

                <!-- Connected Platforms Badges -->
                <div style="display: flex; gap: 12px; align-items: center;">
                    <span style="font-size: 12px; color: #94A3B8; font-weight: 600;">Platform AI Terhubung:</span>
                    <span class="badge" style="background: rgba(217,119,6,0.15); color: #F59E0B; border: 1px solid rgba(217,119,6,0.3);">Anthropic Claude 3.7</span>
                    <span class="badge" style="background: rgba(16,185,129,0.15); color: #34D399; border: 1px solid rgba(16,185,129,0.3);">OpenAI ChatGPT-4o</span>
                    <span class="badge" style="background: rgba(56,189,248,0.15); color: #38BDF8; border: 1px solid rgba(56,189,248,0.3);">Google Gemini 2.5</span>
                    <span class="badge" style="background: rgba(168,85,247,0.15); color: #C084FC; border: 1px solid rgba(168,85,247,0.3);">Ollama Local AI</span>
                </div>

                <!-- Interactive AI Chat Box with ERP live tools -->
                <div class="ui-card" style="background: #080E24; border-color: rgba(56,189,248,0.3);">
                    <div class="ui-card-title">
                        <span>Konsol Tanya Jawab Data Riil Bisnis via Asisten AI</span>
                        <span class="badge badge-success">Terhubung ke Database ERP</span>
                    </div>

                    <!-- Chat stream -->
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                        <!-- User prompt -->
                        <div style="align-self: flex-end; background: #0284C7; color: #fff; padding: 10px 14px; border-radius: 12px 12px 2px 12px; max-width: 70%;">
                            Berapa laba bersih Cabang Dago minggu ini dan bahan baku apa yang harus segera saya pesan ulang?
                        </div>

                        <!-- AI Response with Tool calls -->
                        <div style="align-self: flex-start; background: #0F1D3D; border: 1px solid rgba(255,255,255,0.1); color: #E2E8F0; padding: 14px; border-radius: 12px 12px 12px 2px; max-width: 80%;">
                            <div style="font-size: 11px; color: #38BDF8; font-weight: 700; margin-bottom: 6px;">
                                ⚡ Claude 3.7 via COOCA MCP (Model Context Protocol):
                            </div>
                            <div>
                                Berdasarkan data keuangan riil tanggal <b>9 - 15 November 2026</b> di Cabang Dago:
                            </div>
                            <div style="margin-top: 6px; padding-left: 12px; border-left: 2px solid #34D399;">
                                <div>• <b>Laba Bersih Cabang Dago:</b> <span class="mono" style="color: #34D399; font-weight: 700;">Rp 14.850.000</span> (Naik 12% dibanding minggu lalu).</div>
                                <div>• <b>Bahan Baku Kritis:</b> Daging Ayam Fillet Segar sisa <b>4.2 kg</b> (batas minimum 10 kg).</div>
                            </div>
                            <div style="margin-top: 10px; display: flex; gap: 8px;">
                                <button class="btn btn-primary" style="font-size: 11px; padding: 4px 10px;">+ Buatkan Draf PO Supplier Sekarang</button>
                                <button class="btn btn-secondary" style="font-size: 11px; padding: 4px 10px;">Lihat Rincian Pengeluaran</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Module L: Laporan Eksekutif & Analitik
def get_module_l_html():
    return f"""
    <div class="app-window" id="modul-l">
        {make_window_header('L', 'LAPORAN EKSEKUTIF, ANALITIK & MULTI-OUTLET', '/analytics/executive')}
        <div class="app-body">
            <div class="app-sidebar">
                <div class="nav-item active">📈 Dashboard Eksekutif</div>
                <div class="nav-item">⏰ Jam Paling Ramai (Peak Hours)</div>
                <div class="nav-item">🛒 Analitik Keranjang Belanja</div>
                <div class="nav-item">🏬 Konsolidasi Multi-Cabang</div>
            </div>

            <div class="app-content">
                <div class="content-header">
                    <div>
                        <div class="content-title">Dashboard Analitik Eksekutif & Konsolidasi Multi-Cabang</div>
                        <div class="content-subtitle">Pantau performa penjualan, margin laba, dan tren cabang secara real-time</div>
                    </div>
                    <span class="badge badge-success">Data Terkonsolidasi 3 Cabang</span>
                </div>

                <!-- Executive Metric Cards -->
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;">
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">TOTAL OMZET HARI INI</div>
                        <div style="font-size: 20px; font-weight: 800; color: #38BDF8; margin-top: 4px;">Rp 28.450.000</div>
                        <div style="font-size: 11px; color: #34D399; margin-top: 2px;">+18.4% vs kemarin</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">ESTIMASI LABA KOTOR</div>
                        <div style="font-size: 20px; font-weight: 800; color: #34D399; margin-top: 4px;">Rp 12.180.000</div>
                        <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Margin: 42.8%</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">TOTAL TRANSAKSI (TRX)</div>
                        <div style="font-size: 20px; font-weight: 800; color: #fff; margin-top: 4px;">412 Struk</div>
                        <div style="font-size: 11px; color: #38BDF8; margin-top: 2px;">Rata-rata 34 TRX/jam</div>
                    </div>
                    <div class="ui-card" style="padding: 14px;">
                        <div style="font-size: 11px; color: #94A3B8;">BASKET SIZE (RATA-RATA)</div>
                        <div style="font-size: 20px; font-weight: 800; color: #FBBF24; margin-top: 4px;">Rp 69.050</div>
                        <div style="font-size: 11px; color: #34D399; margin-top: 2px;">+5.2% kenaikan belanja</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Peak Hours Heatmap Simulation -->
                    <div class="ui-card">
                        <div class="ui-card-title">Analitik Jam Puncak Transaksi (Peak Hours)</div>
                        <div style="display: flex; flex-direction: column; gap: 8px; font-size: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="width: 80px; color: #94A3B8;">11:00 - 12:00</span>
                                <div style="flex: 1; height: 12px; background: #1E293B; border-radius: 6px; overflow: hidden;">
                                    <div style="width: 45%; height: 100%; background: #38BDF8;"></div>
                                </div>
                                <span class="mono" style="font-size: 11px;">42 TRX</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="width: 80px; color: #94A3B8; font-weight: 700; color: #F87171;">12:00 - 13:30</span>
                                <div style="flex: 1; height: 12px; background: #1E293B; border-radius: 6px; overflow: hidden;">
                                    <div style="width: 95%; height: 100%; background: #EF4444;"></div>
                                </div>
                                <span class="mono font-bold" style="font-size: 11px; color: #F87171;">128 TRX (PUNCAK)</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="width: 80px; color: #94A3B8;">14:00 - 17:00</span>
                                <div style="flex: 1; height: 12px; background: #1E293B; border-radius: 6px; overflow: hidden;">
                                    <div style="width: 30%; height: 100%; background: #38BDF8;"></div>
                                </div>
                                <span class="mono" style="font-size: 11px;">28 TRX</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="width: 80px; color: #94A3B8; font-weight: 700; color: #FBBF24;">18:30 - 20:30</span>
                                <div style="flex: 1; height: 12px; background: #1E293B; border-radius: 6px; overflow: hidden;">
                                    <div style="width: 85%; height: 100%; background: #F59E0B;"></div>
                                </div>
                                <span class="mono font-bold" style="font-size: 11px; color: #FBBF24;">94 TRX (RAMAI)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Multi-Outlet Consolidation -->
                    <div class="ui-card">
                        <div class="ui-card-title">Perbandingan Performa Antar Cabang</div>
                        <table class="data-table">
                            <thead><tr><th>Nama Cabang</th><th>Omzet Bulan Ini</th><th>Kontribusi</th><th>Kinerja</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight: 700;">Cabang Dago Flagship</td>
                                    <td class="mono font-bold" style="color: #38BDF8;">Rp 425.000.000</td>
                                    <td><span class="badge badge-info">45%</span></td>
                                    <td><span class="badge badge-success">Target Tercapai</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">Cabang Sudirman</td>
                                    <td class="mono font-bold" style="color: #38BDF8;">Rp 310.000.000</td>
                                    <td><span class="badge badge-info">33%</span></td>
                                    <td><span class="badge badge-success">Target Tercapai</span></td>
                                </tr>
                                <tr>
                                    <td style="font-weight: 700;">Cabang Tebet</td>
                                    <td class="mono font-bold" style="color: #38BDF8;">Rp 215.000.000</td>
                                    <td><span class="badge badge-info">22%</span></td>
                                    <td><span class="badge badge-warning">Evaluasi Staf</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    """

# Build Complete Master Showcase HTML
def build_master_html():
    modules = [
        get_module_a_html(),
        get_module_b_html(),
        get_module_c_html(),
        get_module_d_html(),
        get_module_e_html(),
        get_module_f_html(),
        get_module_g_html(),
        get_module_h_html(),
        get_module_i_html(),
        get_module_j_html(),
        get_module_k_html(),
        get_module_l_html(),
    ]
    
    html = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>COOCA - All Features UI Ecosystem</title>
<style>
{CSS_BASE}
</style>
</head>
<body style="background: #030712; padding: 40px; display: flex; flex-direction: column; gap: 40px;">
    <!-- Master Header -->
    <div style="background: linear-gradient(135deg, #0A1433, #04091A); border: 1px solid rgba(56,189,248,0.25); border-radius: 20px; padding: 32px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 20px 50px rgba(0,0,0,0.8);">
        <div>
            <div style="display: flex; align-items: center; gap: 14px;">
                <img src="{logo_light}" alt="COOCA Logo" style="height: 38px;" />
                <span style="font-size: 12px; background: rgba(56,189,248,0.2); color: #38BDF8; font-weight: 800; padding: 4px 12px; border-radius: 9999px; letter-spacing: 0.05em;">ENTERPRISE ECOSYSTEM</span>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #FFFFFF; margin-top: 10px;">
                Suite Antarmuka Lengkap Sistem Operasi Bisnis &amp; ERP Omnichannel
            </div>
            <div style="font-size: 14px; color: #94A3B8; margin-top: 4px;">
                Tampilan Utuh Seluruh 12 Modul Inti (A - L): Kasir POS, Keamanan, BOM/HPP, B2B, Inventori, SAK EMKM, Storefront, WhatsApp, CRM, HRM, MCP AI &amp; Analitik
            </div>
        </div>
        <div style="text-align: right; display: flex; flex-direction: column; gap: 6px;">
            <span style="font-size: 12px; color: #64748B;">Platform Resmi</span>
            <span style="font-size: 16px; font-weight: 800; color: #38BDF8;">https://cooca.id</span>
            <span class="badge badge-success" style="align-self: flex-end;">100% Fitur Komersial Aktif</span>
        </div>
    </div>

    <!-- 12 Modules Full Layouts -->
    {''.join(modules)}

    <!-- Master Footer -->
    <div style="text-align: center; padding: 30px 0; color: #64748B; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.08);">
        COOCA Enterprise Operating System &bull; Dokumen Antarmuka Komersial &bull; Hak Cipta &copy; 2026 PT Cooca Solusi Indonesia
    </div>
</body>
</html>
"""
    return html

# Build Individual Module HTML
def build_single_module_html(module_html, title):
    return f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{title}</title>
<style>
{CSS_BASE}
body {{
    background: #030712;
    padding: 30px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}}
.app-window {{
    width: 100%;
    max-width: 1560px;
    margin-bottom: 0;
}}
</style>
</head>
<body>
{module_html}
</body>
</html>
"""

async def run_render():
    print("Writing HTML files...")
    master_html = build_master_html()
    master_file = os.path.join(OUTPUT_DIR, "master_all_features_ui.html")
    with open(master_file, "w", encoding="utf-8") as f:
        f.write(master_html)

    modules_dict = {
        "01_pos_operasional": (get_module_a_html(), "COOCA POS & Operasional Kasir"),
        "02_antifraud_otorisasi": (get_module_b_html(), "COOCA Anti-Fraud & Otorisasi PIN"),
        "03_produksi_bom_hpp": (get_module_c_html(), "COOCA Produksi BOM & HPP Presisi"),
        "04_penjualan_b2b_piutang": (get_module_d_html(), "COOCA Penjualan B2B & Piutang"),
        "05_gudang_inventori_pengadaan": (get_module_e_html(), "COOCA Inventori & Pengadaan Multi-Gudang"),
        "06_keuangan_sak_emkm": (get_module_f_html(), "COOCA Keuangan & Pembukuan SAK EMKM"),
        "07_storefront_omnichannel": (get_module_g_html(), "COOCA Toko Online Storefront"),
        "08_whatsapp_pemasaran_digital": (get_module_h_html(), "COOCA WhatsApp Gateway & Pemasaran"),
        "09_crm_loyalitas_pelanggan": (get_module_i_html(), "COOCA CRM & Program Loyalitas"),
        "10_hrm_penggajian_payroll": (get_module_j_html(), "COOCA HRM, Presensi & Payroll"),
        "11_konektivitas_ai_mcp": (get_module_k_html(), "COOCA Konektivitas Terbuka MCP AI"),
        "12_laporan_eksekutif_analitik": (get_module_l_html(), "COOCA Dashboard Eksekutif & Analitik"),
    }

    for name, (mod_html, title) in modules_dict.items():
        file_path = os.path.join(OUTPUT_DIR, f"{name}.html")
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(build_single_module_html(mod_html, title))

    print("Launching Playwright...")
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)

        # 1. Render Master Panoramic Image
        print("Rendering Master Panorama (All 12 Modules into 1 image)...")
        context_master = await browser.new_context(viewport={"width": 1920, "height": 1080}, device_scale_factor=2)
        page_master = await context_master.new_page()
        await page_master.goto(f"file:///{master_file.replace(os.sep, '/')}", wait_until="networkidle")
        await page_master.wait_for_timeout(2000)
        master_img_path = os.path.join(OUTPUT_DIR, "cooca_master_all_features_ui.png")
        await page_master.screenshot(path=master_img_path, full_page=True)
        print(f"Master image rendered to: {master_img_path}")
        await context_master.close()

        # 2. Render each of the 12 individual module images
        for name in modules_dict.keys():
            print(f"Rendering individual module: {name}...")
            context_mod = await browser.new_context(viewport={"width": 1600, "height": 950}, device_scale_factor=2)
            page_mod = await context_mod.new_page()
            mod_file = os.path.join(OUTPUT_DIR, f"{name}.html")
            await page_mod.goto(f"file:///{mod_file.replace(os.sep, '/')}", wait_until="networkidle")
            await page_mod.wait_for_timeout(1000)
            img_out = os.path.join(OUTPUT_DIR, f"{name}.png")
            await page_mod.screenshot(path=img_out, full_page=True)
            print(f"Rendered {img_out}")
            await context_mod.close()

        await browser.close()
    print("ALL RENDERS COMPLETED SUCCESSFULLY!")

if __name__ == "__main__":
    asyncio.run(run_render())
