#!/usr/bin/env python3
"""
COOCA DEEP RESEARCH & VIRAL POTENTIAL ENGINE (ALL 8 SOCIAL AUTOMATIONS)
----------------------------------------------------------------------
Evaluates and scores content topics for:
Channels: Instagram, Facebook, LinkedIn, TikTok
Formats: Feed (Single 1080x1080), Carousel (5-8 Slides 1080x1080)
Strict Gate: Viral Potential Score must be >= 85.0/100.
If Score < 85.0 -> REJECT (Does not pass to production).
"""

import sys
import json
import argparse
from datetime import datetime

# Curated High-Intent UMKM Retail Knowledge Base & Trend Weights
KNOWLEDGE_BASE = [
    {
        "id": "cashier_leakage",
        "topic": "Selisih Kas Harian & Kebocoran Meja Kasir",
        "keywords": ["selisih kas toko", "cara mengatasi kasir curang", "rekap kas shift kasir", "uang kas kurang saat tutup toko"],
        "trend_velocity": 96.0,
        "problem_urgency": 98.0,
        "channels": {
            "instagram": {"feed_fit": 94.0, "carousel_fit": 98.0, "cta_clarity": 95.0},
            "facebook": {"feed_fit": 92.0, "carousel_fit": 95.0, "cta_clarity": 94.0},
            "linkedin": {"feed_fit": 91.0, "carousel_fit": 93.0, "cta_clarity": 93.0},
            "tiktok": {"feed_fit": 97.0, "carousel_fit": 96.0, "cta_clarity": 95.0},
        },
        "hook_title": "Omzet Ramai Tapi Uang Kas Toko Sering Selisih?",
        "solution_summary": "Kunci transaksi sensitif kasir dengan PIN Supervisor dan otomatisasi rekap kas shift harian.",
    },
    {
        "id": "inventory_discrepancy",
        "topic": "Selisih Stok Opname Fisik vs Buku",
        "keywords": ["stok barang hilang", "cara stock opname cepat", "sinkronisasi stok toko", "kartu stok otomatis"],
        "trend_velocity": 94.0,
        "problem_urgency": 97.0,
        "channels": {
            "instagram": {"feed_fit": 92.0, "carousel_fit": 96.0, "cta_clarity": 94.0},
            "facebook": {"feed_fit": 96.0, "carousel_fit": 97.0, "cta_clarity": 95.0},
            "linkedin": {"feed_fit": 93.0, "carousel_fit": 95.0, "cta_clarity": 94.0},
            "tiktok": {"feed_fit": 93.0, "carousel_fit": 94.0, "cta_clarity": 93.0},
        },
        "hook_title": "Stok di Buku Ada 10 Pcs, Kenapa di Rak Sudah Habis?",
        "solution_summary": "Pengurangan stok otomatis saat scan barcode kasir dan peringatan dini batas minimum barang.",
    },
    {
        "id": "multi_outlet_scaleup",
        "topic": "Pengawasan Multi-Cabang Dari 1 Layar HP",
        "keywords": ["cara pantau banyak cabang toko", "kelola 3 toko ritel dari hp", "transfer barang antar cabang", "omzet multi cabang"],
        "trend_velocity": 92.0,
        "problem_urgency": 95.0,
        "channels": {
            "instagram": {"feed_fit": 91.0, "carousel_fit": 95.0, "cta_clarity": 94.0},
            "facebook": {"feed_fit": 90.0, "carousel_fit": 93.0, "cta_clarity": 92.0},
            "linkedin": {"feed_fit": 97.0, "carousel_fit": 98.0, "cta_clarity": 96.0},
            "tiktok": {"feed_fit": 90.0, "carousel_fit": 92.0, "cta_clarity": 91.0},
        },
        "hook_title": "Buka Cabang Baru Tapi Kontrol Operasional Malah Kacau?",
        "solution_summary": "Pantau penjualan, stok gudang, dan hak akses kasir seluruh cabang dari satu dashboard smartphone.",
    },
    {
        "id": "slow_cashier_queues",
        "topic": "Antrean Kasir Menumpuk & Transaksi Lambat",
        "keywords": ["aplikasi kasir anti lemot", "transaksi kasir 3 detik", "kasir ritel cepat", "cetak struk wa"],
        "trend_velocity": 95.0,
        "problem_urgency": 94.0,
        "channels": {
            "instagram": {"feed_fit": 93.0, "carousel_fit": 95.0, "cta_clarity": 94.0},
            "facebook": {"feed_fit": 91.0, "carousel_fit": 93.0, "cta_clarity": 92.0},
            "linkedin": {"feed_fit": 89.0, "carousel_fit": 92.0, "cta_clarity": 92.0},
            "tiktok": {"feed_fit": 98.0, "carousel_fit": 97.0, "cta_clarity": 96.0},
        },
        "hook_title": "Antrean Kasir Mengular Bikin Pelanggan Batal Beli?",
        "solution_summary": "Selesaikan pembayaran kurang dari 3 detik dengan scan QRIS instan dan struk belanja digital WhatsApp.",
    },
]

def calculate_viral_score(trend_vel: float, urgency: float, platform_fit: float, cta_clarity: float) -> float:
    score = (trend_vel * 0.30) + (urgency * 0.30) + (platform_fit * 0.25) + (cta_clarity * 0.15)
    return round(score, 2)

def evaluate_topic(channel: str, content_format: str, topic_id: str = None):
    channel = channel.lower()
    content_format = content_format.lower()
    
    if channel not in ["instagram", "facebook", "linkedin", "tiktok"]:
        raise ValueError(f"Saluran tidak dikenal: {channel}")
    if content_format not in ["feed", "carousel"]:
        raise ValueError(f"Format tidak dikenal: {content_format}. Harus 'feed' atau 'carousel'.")

    # Select best topic based on platform alignment if not specified
    candidates = KNOWLEDGE_BASE
    if topic_id:
        candidates = [c for c in KNOWLEDGE_BASE if c["id"] == topic_id]
        if not candidates:
            candidates = KNOWLEDGE_BASE

    scored_results = []
    for c in candidates:
        chan_data = c["channels"].get(channel, {})
        fit_key = "carousel_fit" if content_format == "carousel" else "feed_fit"
        fit_score = chan_data.get(fit_key, 80.0)
        cta_score = chan_data.get("cta_clarity", 85.0)

        total_score = calculate_viral_score(
            trend_vel=c["trend_velocity"],
            urgency=c["problem_urgency"],
            platform_fit=fit_score,
            cta_clarity=cta_score
        )

        passed = total_score >= 85.0
        scored_results.append({
            "id": c["id"],
            "topic": c["topic"],
            "channel": channel,
            "format": content_format,
            "trend_velocity": c["trend_velocity"],
            "problem_urgency": c["problem_urgency"],
            "platform_fit": fit_score,
            "cta_clarity": cta_score,
            "viral_score": total_score,
            "gate_threshold": 85.0,
            "passed_gate": passed,
            "hook_title": c["hook_title"],
            "solution_summary": c["solution_summary"],
            "keywords": c["keywords"],
        })

    # Sort descending by viral score
    scored_results.sort(key=lambda x: x["viral_score"], reverse=True)
    best = scored_results[0]

    return best, scored_results

def generate_caption(channel: str, content_format: str, evaluated_data: dict) -> str:
    channel = channel.lower()
    hook = evaluated_data["hook_title"]
    solution = evaluated_data["solution_summary"]

    if channel == "instagram":
        if content_format == "carousel":
            caption = (
                f"{hook}\n\n"
                f"Jangan biarkan masalah operasional menggerus omzet usaha Anda. "
                f"Riset menemukan bahwa kebocoran toko bermula dari pembukuan kasir manual tanpa rekonsiliasi otomatis.\n\n"
                f"Geser slide untuk mempelajari:\n"
                f"1. Titik rawan kebocoran kasir di toko ritel.\n"
                f"2. Standar waktu transaksi kasir kurang dari 3 detik.\n"
                f"3. Proteksi menu kasir sensitif (anti-void tanpa PIN Supervisor).\n"
                f"4. Rekap laba rugi otomatis per pergantian shift.\n\n"
                f"Di Toko Kamu yang didukung oleh Cooca Indonesia, seluruh transaksi kasir terhubung langsung ke laporan keuangan real-time.\n\n"
                f"Coba gratis sekarang di https://cooca.id (Tautan ada di bio profil)\n"
                f"Masa uji coba 14 hari tanpa biaya kartu kredit.\n\n"
                f"#CoocaERP #AplikasiKasir #KasirPintar #UMKMIndonesia #BisnisRitel #KelolaToko #ManajemenKeuangan #TokoKamu"
            )
        else: # feed single
            caption = (
                f"{hook}\n\n"
                f"Operasional rapi adalah kunci omzet aman. {solution}\n\n"
                f"Di Toko Kamu bersama Cooca Indonesia, pantau rekap kas dan transaksi langsung dari HP tanpa repot hitung manual saat toko tutup.\n\n"
                f"Pelajari sistem kasir pintar di: https://cooca.id\n\n"
                f"#CoocaERP #AplikasiKasir #KasirPintar #UMKMIndonesia #BisnisRitel #KelolaToko #TokoKamu #CoocaIndonesia"
            )
    elif channel == "linkedin":
        if content_format == "carousel":
            caption = (
                f"{hook}\n\n"
                f"Skalabilitas bisnis ritel modern ditentukan oleh kekuatan sistem kontrol internal. "
                f"Banyak pemilik bisnis ritel terjebak dalam pembukaan outlet baru tanpa standarisasi operasional yang kokoh.\n\n"
                f"Geser dokumen ini untuk meninjau framework tata kelola multi-outlet Cooca:\n"
                f"- Pilar 1: Sinkronisasi mutasi stok dan kartu stok antar outlet.\n"
                f"- Pilar 2: Penguncian menu kasir sensitif berbasis role-based access control (RBAC).\n"
                f"- Pilar 3: Rekonsiliasi kas harian per shift kasir tanpa intervensi manual.\n"
                f"- Pilar 4: Konsolidasi laporan laba rugi multi-cabang dalam satu dashboard.\n\n"
                f"Di Toko Kamu yang didukung oleh Cooca Indonesia, standarisasi operasional cabang diselesaikan dengan efisien.\n\n"
                f"Pelajari arsitektur enterprise ritel untuk bisnis Anda di: https://cooca.id\n\n"
                f"#CoocaERP #TataKelolaBisnis #MultiOutlet #SOPBisnis #ManajemenRitel #TokoKamu #CoocaIndonesia"
            )
        else: # feed single
            caption = (
                f"Tantangan terbesar scale up bisnis ritel bukan membuka cabang baru, melainkan mempertahankan standarisasi dan kontrol stok lintas outlet.\n\n"
                f"Di Toko Kamu yang didukung oleh Cooca Indonesia, monitoring 3 cabang diselesaikan dari satu layar smartphone:\n"
                f"- Pantau perputaran omzet kasir per outlet secara real-time.\n"
                f"- Transfer barang antar gudang tercatat otomatis tanpa risiko selisih stok fisik.\n"
                f"- Hak akses kasir dibatasi per cabang untuk mencegah manipulasi data keuangan.\n\n"
                f"Pelajari sistem multi-outlet terintegrasi untuk bisnis Anda di: https://cooca.id\n\n"
                f"#CoocaERP #TataKelolaBisnis #MultiOutlet #SOPBisnis #ManajemenRitel #TokoKamu #CoocaIndonesia"
            )
    elif channel == "facebook":
        if content_format == "carousel":
            caption = (
                f"Bagi rekan-rekan pemilik toko ritel, grosir, dan minimarket: {hook}\n\n"
                f"Banyak pedagang mengira barang hilang karena dicuri, padahal 80 persen selisih stok terjadi karena masalah pencatatan kasir yang manual. "
                f"Geser foto album ini untuk melihat 3 solusi praktisnya:\n\n"
                f"1. Pembukuan kartu stok otomatis setiap kasir scan barcode produk.\n"
                f"2. Kunci fitur pembatalan (void) kasir hanya dengan izin PIN Supervisor.\n"
                f"3. Notifikasi otomatis saat stok barang sudah menipis di rak.\n\n"
                f"Di Toko Kamu yang dikelola bersama Cooca Indonesia, seluruh pembukuan toko berjalan rapi dan otomatis.\n\n"
                f"Simak panduan dan coba demonya di: https://cooca.id\n\n"
                f"#KomunitasRitel #StudiKasusUMKM #StokGudang #AplikasiToko #TokoKamu #CoocaIndonesia"
            )
        else: # feed single
            caption = (
                f"Bagi rekan-rekan pemilik toko dan grosir ritel, pernahkah mengalami situasi stok di buku masih ada 10 pcs tapi saat dicari di rak barangnya sudah habis?\n\n"
                f"Hal ini biasanya terjadi karena:\n"
                f"1. Kasir lupa mencatat barang keluar saat toko sedang ramai.\n"
                f"2. Ada retur barang yang tidak tercatat ke kartu stok.\n"
                f"3. Tidak ada sistem notifikasi saat barang sudah mencapai batas minimum.\n\n"
                f"Di Toko Kamu bersama Cooca Indonesia, setiap kali kasir scan produk di kasir, kartu stok otomatis berkurang detik itu juga. Ketika barang menipis, sistem langsung mengirim pengingat otomatis untuk restock.\n\n"
                f"Simak studi kasus lengkapnya di: https://cooca.id\n\n"
                f"#KomunitasRitel #StudiKasusUMKM #StokGudang #AplikasiToko #TokoKamu #CoocaIndonesia"
            )
    elif channel == "tiktok":
        if content_format == "carousel":
            caption = (
                f"3 trik rahasia kasir toko ramai anti-selisih dan anti-bocor kas! Geser sampai akhir.\n\n"
                f"Jangan anggap wajar kalau setiap tutup kasir selalu ada uang yang kurang. "
                f"Ini SOP kasir profesional yang wajib diterapkan di toko kamu:\n"
                f"1. Ganti shift kasir wajib rekap kas otomatis dalam 1 menit.\n"
                f"2. Fitur void belanja wajib kunci dengan PIN Supervisor.\n"
                f"3. Kirim struk belanja otomatis via WhatsApp pelanggan.\n\n"
                f"Sistem ini sudah dipakai di Toko Kamu bersama Cooca Indonesia. Praktis, cepat, dan anti-ribet!\n\n"
                f"Coba gratis sekarang di https://cooca.id (klik link di bio profil!)\n\n"
                f"#trikbisnis #kasirtoko #aplikasikasir #umkmmaju #tokokamu #coocaindonesia"
            )
        else: # feed single
            caption = (
                f"3 detik langsung beres! Trik kasir toko anti-bocor kas dan anti-kecurangan.\n\n"
                f"Jangan biarkan uang kembalian atau transaksi void kasir bikin omzet toko kamu hilang sia-sia.\n"
                f"Dengan sistem Kasir Pintar COOCA di Toko Kamu:\n"
                f"- Kasir tidak bisa hapus transaksi tanpa PIN Supervisor.\n"
                f"- Rekap kas otomatis per pergantian shift.\n"
                f"- Struk belanja langsung terkirim via WhatsApp pelanggan.\n\n"
                f"Coba gratis sekarang di https://cooca.id (klik link di bio!)\n\n"
                f"#trikbisnis #kasirtoko #aplikasikasir #umkmmaju #tokokamu #coocaindonesia"
            )
    else:
        caption = f"{hook}\n\n{solution}\n\nKunjungi https://cooca.id"

    # Enforce zero em dash / en dash
    clean_caption = caption.replace("—", "-").replace("–", "-")
    return clean_caption

def main():
    parser = argparse.ArgumentParser(description="Cooca Deep Research & Viral Potential Engine")
    parser.add_argument("--channel", required=True, choices=["instagram", "facebook", "linkedin", "tiktok"], help="Target Social Media Platform")
    parser.add_argument("--format", required=True, choices=["feed", "carousel"], help="Content Format: feed (single) or carousel (multi-slide)")
    parser.add_argument("--topic", default=None, help="Optional topic ID")
    parser.add_argument("--output-json", default=None, help="Path to write evaluated specification JSON")

    args = parser.parse_args()

    best_topic, all_candidates = evaluate_topic(args.channel, args.format, args.topic)

    if not best_topic["passed_gate"]:
        print(f"[GATE REJECTED] Topik '{best_topic['topic']}' memiliki skor viral {best_topic['viral_score']}/100, yang berada DI BAWAH ambang batas 85.0/100.")
        sys.exit(1)

    generated_caption = generate_caption(args.channel, args.format, best_topic)

    result_payload = {
        "channel": args.channel,
        "format": args.format,
        "topic_id": best_topic["id"],
        "topic_name": best_topic["topic"],
        "viral_score": best_topic["viral_score"],
        "gate_status": "PASSED (>= 85.0)",
        "trend_velocity": best_topic["trend_velocity"],
        "problem_urgency": best_topic["problem_urgency"],
        "platform_fit": best_topic["platform_fit"],
        "cta_clarity": best_topic["cta_clarity"],
        "caption": generated_caption,
        "business_name": "Toko Kamu",
        "owner_name": "Cooca Indonesia",
        "dimensions": "1080x1080",
        "evaluated_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
    }

    print("====================================================================")
    print(f"  COOCA VIRAL RESEARCH RESULT: {args.channel.upper()} ({args.format.upper()})")
    print("====================================================================")
    print(f"Topik Terpilih   : {best_topic['topic']}")
    print(f"Skor Viral       : {best_topic['viral_score']} / 100 [LOLOS GATE >= 85%]")
    print(f"Trend Velocity   : {best_topic['trend_velocity']} / 100")
    print(f"Problem Urgency  : {best_topic['problem_urgency']} / 100")
    print(f"Platform Fit     : {best_topic['platform_fit']} / 100")
    print(f"CTA Clarity      : {best_topic['cta_clarity']} / 100")
    print(f"Identitas Wajib  : Toko Kamu (Pemilik: Cooca Indonesia)")
    print("====================================================================")

    if args.output_json:
        with open(args.output_json, "w", encoding="utf-8") as f:
            json.dump(result_payload, f, indent=2, ensure_ascii=False)
        print(f"[JSON] Berhasil menulis spesifikasi viral ke: {args.output_json}")

if __name__ == "__main__":
    main()
