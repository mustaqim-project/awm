# scripts/create_100_definitions.py
import json, re

# Load blog_links
blog_data = json.load(open('blog_links.json', encoding='utf-8'))
all_allowed_links = [l['url'] for l in blog_data['links']]

# Categorized link pools
link_pools = {
    'pos': [u for u in all_allowed_links if any(k in u for k in ['kasir', 'pos', 'printer', 'barcode', 'qris', 'struk'])],
    'inv': [u for u in all_allowed_links if any(k in u for k in ['stok', 'gudang', 'opname', 'reorder', 'fifo', 'sku', 'retur', 'kurir'])],
    'keu': [u for u in all_allowed_links if any(k in u for k in ['laba', 'rugi', 'neraca', 'pembukuan', 'sak-emkm', 'rekening', 'kas', 'modal', 'escrow'])],
    'hpp': [u for u in all_allowed_links if any(k in u for k in ['hpp', 'margin', 'markup', 'diskon', 'overhead', 'costing', 'harga-jual', 'bep'])],
    'b2b': [u for u in all_allowed_links if any(k in u for k in ['invoice', 'purchase-order', 'po', 'surat-jalan', 'bast', 'quotation', 'tempo'])],
    'pjk': [u for u in all_allowed_links if any(k in u for k in ['pajak', 'pph', 'nib', 'coretax', 'halal', 'bpom', 'spt', 'faktur-pajak'])],
    'wa':  [u for u in all_allowed_links if any(k in u for k in ['whatsapp', 'wa', 'broadcast', 'pelanggan', 'crm', 'loyalitas'])],
    'ops': [u for u in all_allowed_links if any(k in u for k in ['shift', 'lembur', 'thr', 'karyawan', 'sewa', 'listrik', 'operasional'])]
}

print("Link pools initialized.")
