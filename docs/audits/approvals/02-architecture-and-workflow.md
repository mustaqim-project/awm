# Architecture and Workflow - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Pola Arsitektur MAR (Maker, Approver, Releaser)

```
[ Maker (Draf Transaksi) ]
  PO / Expense / Supplier Bill / Stock Opname
            |
            v
[ Evaluasi Plafon Aturan (ApprovalWorkflowService) ]
            |
   +--------+--------+
   |                 |
[ Di bawah plafon / ]  [ Melebihi Plafon Aturan ]
[ UMKM no rules   ]          |
   |                         v
[ Disetujui Langsung ] [ ApprovalRequest: Pending (Level 1) ]
   |                         |
   |                         v
   |                 [ Penyetuju Level 1 (Supervisor) ]
   |                   /                    \
   |           [ Tolak ]                    [ Setujui ]
   |              |                              |
   |              v                              +---> Jika Level < Total:
   |      [ Rejection Log ]                            Naik ke Level 2 (Manager)
   |      [ Status: REJECTED ]                         /                     \
   |              |                            [ Tolak ]                     [ Setujui ]
   |              v                               |                               |
   |      [ Maker Revisi / Batal ]         [ Status: REJECTED ]         +---> Jika Level < Total:
   |                                                                          Naik ke Level 3 (Owner)
   |                                                                          /                 \
   |                                                                  [ Tolak ]                 [ Setujui ]
   |                                                                      |                          |
   +----------------------------------------------------------------------+--------------------------+
                                                                                                     |
                                                                                                     v
                                                                                       [ Status: APPROVED ]
                                                                                       [ Siap Dieksekusi/Cair ]
```

## Komponen Kunci

### 1. State Machine ApprovalRequest
Status siklus hidup dokumen:
- `pending`: Menunggu tindakan pengguna dengan peran yang diwajibkan pada `current_level`.
- `approved`: Telah disetujui hingga `current_level === total_levels`. Kolom `approved_at` diisi timestamp. Dokumen asal (PO/Expense) dapat dieksekusi atau dikonfirmasi.
- `rejected`: Ditolak oleh salah satu penyetuju. Kolom `rejection_reason` dan `rejected_at` diisi. Alur berhenti seketika.

### 2. Aturan Plafon (ApprovalRule)
- Kolom: `document_type`, `min_amount`, `max_amount`, `required_levels` (1-3).
- Role per level: `approver_role_level_1`, `approver_role_level_2`, `approver_role_level_3`.
- Scope: `business_id` (Isolasi multi-tenant).

### 3. Segregation of Duties (SoD)
- Staf pembuat transaksi (`requester_id`) dilarang keras menyetujui dokumen miliknya sendiri (`canUserApprove` mengembalikan `false`), kecuali jika user bersangkutan adalah Owner toko (karena Owner adalah otoritas tertinggi dalam entitas bisnis).
- Admin / Owner memiliki hak darurat untuk mengeksekusi approval jika terjadi kekosongan staf supervisor/manager.
