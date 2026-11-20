import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const CAPTURES_DIR = path.resolve(__dirname, '../documentation/ui-captures/web');

const EXPECTED_FILES = [
  '01_auth_login.png',
  '02_auth_register.png',
  '03_auth_forgot_password.png',
  '04_dashboard_overview.png',
  '05_dashboard_owner_pulse.png',
  '06_business_switcher.png',
  '07_pos_terminal.png',
  '08_pos_cart_active.png',
  '09_pos_payment_modal.png',
  '10_sales_orders_list.png',
  '11_purchasing_orders_list.png',
  '12_purchase_order_detail.png',
  '13_goods_receipt.png',
  '14_inventory_products_list.png',
  '15_inventory_stock_levels.png',
  '16_stock_card_movement.png',
  '17_customers_directory.png',
  '18_suppliers_directory.png',
  '19_accounting_general_ledger.png',
  '20_accounting_journal_entries.png',
  '21_accounting_profit_loss.png',
  '22_accounting_balance_sheet.png',
  '23_reports_sales_analytics.png',
  '24_reports_inventory_valuation.png',
  '25_retail_wholesale_catalog.png',
  '26_retail_pos_fmcg.png',
  '27_fnb_table_layout.png',
  '28_fnb_menu_modifiers.png',
  '29_fnb_kitchen_display.png',
  '30_mfg_bom_formula.png',
  '31_mfg_production_order.png',
  '32_mfg_work_center.png',
  '33_workshop_vehicle_queue.png',
  '34_workshop_spk_order.png',
  '35_workshop_service_history.png'
];

console.log('====================================================');
console.log('   COOCA WEB SCREENSHOT SUITE INTEGRITY VALIDATOR   ');
console.log(`   Inspecting directory: ${CAPTURES_DIR}            `);
console.log('====================================================\n');

let passCount = 0;
let failCount = 0;
const results = [];

for (const filename of EXPECTED_FILES) {
  const filePath = path.join(CAPTURES_DIR, filename);
  if (!fs.existsSync(filePath)) {
    console.error(`[FAIL] MISSING: ${filename}`);
    failCount++;
    continue;
  }

  const stat = fs.statSync(filePath);
  if (stat.size === 0) {
    console.error(`[FAIL] EMPTY FILE: ${filename} (0 bytes)`);
    failCount++;
    continue;
  }

  const buf = fs.readFileSync(filePath);
  // PNG Magic Header: 89 50 4E 47 0D 0A 1A 0A
  const isPng = buf[0] === 0x89 && buf[1] === 0x50 && buf[2] === 0x4e && buf[3] === 0x47 &&
                buf[4] === 0x0d && buf[5] === 0x0a && buf[6] === 0x1a && buf[7] === 0x0a;
  if (!isPng) {
    console.error(`[FAIL] INVALID PNG HEADER: ${filename}`);
    failCount++;
    continue;
  }

  // IHDR width at offset 16, height at offset 20
  const width = buf.readUInt32BE(16);
  const height = buf.readUInt32BE(20);

  if (width !== 1440 || height !== 900) {
    console.error(`[FAIL] INVALID RESOLUTION: ${filename} is ${width}x${height}, expected 1440x900`);
    failCount++;
    continue;
  }

  passCount++;
  results.push({ filename, width, height, size: stat.size });
  console.log(`[PASS] ${filename} | ${width}x${height} | ${stat.size.toLocaleString()} bytes`);
}

console.log('\n----------------------------------------------------');
console.log(`VALIDATION SUMMARY: ${passCount} PASSED / ${failCount} FAILED out of ${EXPECTED_FILES.length} expected`);
console.log('----------------------------------------------------');

if (failCount > 0) {
  process.exit(1);
} else {
  console.log('ALL 35 SCREENSHOTS MET ALL GEOMETRIC & INTEGRITY REQUIREMENTS!\n');
}
