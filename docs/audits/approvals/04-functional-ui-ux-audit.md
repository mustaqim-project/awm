# Functional UI/UX Audit - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Kepatuhan Desain Apple Human Interface Guidelines (HIG) & Bento Grid

### 1. Desain Bento & Kontainer Card
- Tata letak card menggunakan radius lengkung khas Apple (`rounded-[20px]`, `rounded-[22px]`, `rounded-[28px]`).
- Efek kaca akrilik menggunakan backdrop blur (`backdrop-blur-xl`, `backdrop-blur-2xl`) dengan border semitransparan elegan (`border-black/[0.06] dark:border-white/[0.08]`).

### 2. Modal Teleportation & Stacking Context
- Seluruh dialog modal (`Approve Modal`, `Reject Modal`, `Create Rule Modal`, `Edit Rule Modal`) dibungkus dalam `<template x-teleport="body">`.
- Indeks lapisan menggunakan z-index tinggi (`z-[200]` hingga `z-[220]`) untuk memastikan modal tidak terpotong oleh overflow parent card atau tertutup oleh header navigasi.
- Didukung tombol grab-bar pada tampilan layar sentuh ponsel (`w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto sm:hidden`) untuk pengalaman native bottom-sheet.

### 3. Target Sentuh Ramah Ponsel (Touch Targets)
- Seluruh elemen interaktif memenuhi batas minimal area sentuh ibu jari Apple HIG (minimal 44px):
  - Tombol aksi: `min-h-[44px]`
  - Tombol tutup dialog: `w-10 h-10 min-w-[44px] min-h-[44px]`
  - Tab navigasi filter: `px-4 py-2.5 rounded-full`

### 4. Kebijakan Anti-Pill Abuse
- Badge pill (`rounded-full`) hanya digunakan secara ketat untuk status transaksi riil:
  - `Menunggu Level :current dari :total` (Kuning oranye: `bg-[#FF9500]/15 text-[#FF9500]`)
  - `Disetujui` (Hijau: `bg-[#34C759]/15 text-[#34C759]`)
  - `Ditolak` (Merah: `bg-[#FF3B30]/15 text-[#FF3B30]`)
  - `Aktif` / `Nonaktif`
- Nominal transaksi disajikan secara elegan dengan font monospaced tabular numerals (`tabular-nums font-bold`) tanpa dibungkus pil warna-warni yang mencolok.

### 5. Kebijakan Bebas Emoji & Ikonografi
- Modul ini 100% bebas dari emotikon teks/emoji kartun.
- Seluruh penanda visual menggunakan ikon SVG Lucide (`inbox`, `history`, `sliders`, `file-text`, `receipt`, `building-2`, `check-circle-2`, `alert-triangle`, `x`).
