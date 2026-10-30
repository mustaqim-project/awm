<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Business Calculators Suite Translations (English)
    |--------------------------------------------------------------------------
    */

    // Common
    'calc_breadcrumb_home' => 'Home',
    'calc_breadcrumb_hub' => 'SMB Business Calculators',
    'calc_currency_symbol' => 'Rp',
    'calc_action_calculate' => 'Calculate Now',
    'calc_action_reset' => 'Reset Form',
    'calc_action_copy' => 'Copy Summary',
    'calc_action_print' => 'Print / Save PDF',
    'calc_copied_success' => 'Summary successfully copied to clipboard!',
    'calc_disclaimer' => 'This calculator serves as an estimation tool. Actual figures may vary depending on material price fluctuations and real operating expenses.',
    'calc_cta_erp_title' => 'Want Automatic Calculations Connected to POS & Inventory?',
    'calc_cta_erp_desc' => 'With COOCA, you no longer need manual math. Every POS transaction automatically deducts material stock and records real-time COGS and gross profits.',
    'calc_cta_erp_button' => 'Try COOCA for Free',

    // Index / Hub
    'hub_kicker' => 'SMB Financial Decision Tools',
    'hub_title' => 'Precision Financial & COGS Calculators',
    'hub_subtitle' => 'Make confident pricing and capital decisions. Calculate ingredient costs per serving, determine break-even points (BEP), and project true net profits instantly without registration.',
    'hub_badge_free' => '100% Free, No Sign-up Required',
    'hub_badge_privacy' => 'Data Stored Locally in Your Browser',
    'hub_badge_standard' => 'Indonesian SAK EMKM Accounting Standard',

    // 1. HPP
    'hpp_kicker' => '3-Pillar SAK EMKM Standard Costing',
    'hpp_title' => 'COGS & Production Cost Calculator',
    'hpp_subtitle' => 'Uncover true production costs per serving, bottle, or item. Avoid underpricing that silently drains your capital and hurts business sustainability.',
    'hpp_label_raw_materials' => 'Direct Raw Material Costs',
    'hpp_label_labor' => 'Direct Labor Expenses',
    'hpp_label_overhead' => 'Operational Overhead (Electricity, Gas, Packaging)',
    'hpp_label_target_qty' => 'Batch Output Volume (Units/Portions)',
    'hpp_result_unit_cogs' => 'True Unit COGS',
    'hpp_result_total_batch' => 'Total Batch Production Cost',

    // 2. BEP (Break Even Point)
    'bep_kicker' => 'Operational Break-Even Analysis',
    'bep_title' => 'BEP (Break-Even Point) Calculator',
    'bep_subtitle' => 'Calculate exactly how many units you must sell each month to cover rent, payroll, utilities, and keep the business operating without taking a loss.',
    'bep_label_fixed_costs' => 'Monthly Fixed Costs (Rent, Base Salaries, Utilities)',
    'bep_label_price_per_unit' => 'Selling Price per Unit/Serving',
    'bep_label_variable_cost_per_unit' => 'Variable Cost per Unit (Raw Materials, Packaging)',
    'bep_result_units' => 'Break-Even Unit Sales Target',
    'bep_result_revenue' => 'Break-Even Revenue Target (IDR)',

    // 3. Selling Price & Margin
    'price_kicker' => 'Profitable Price Setting',
    'price_title' => 'Selling Price & Profit Margin Calculator',
    'price_subtitle' => 'Determine your optimal retail price based on profit margin targets or cost markup. Protect your business margins against online marketplace commissions.',
    'price_label_cost' => 'Unit Base Cost (COGS)',
    'price_label_method' => 'Pricing Methodology',
    'price_method_margin' => 'Based on Target Profit Margin (%)',
    'price_method_markup' => 'Based on Cost Markup (%)',
    'price_label_marketplace_fee' => 'Sales Channel / Platform Commission (%)',
    'price_result_selling_price' => 'Recommended Selling Price',
    'price_result_gross_profit' => 'Gross Profit per Unit',

    // 4. Net Profit
    'profit_kicker' => 'Net Profit Simulation',
    'profit_title' => 'True SMB Net Profit Calculator',
    'profit_subtitle' => 'Separate gross revenue from the money that actually stays in your bank account after deducting all operational overheads.',
    'profit_label_revenue' => 'Total Gross Monthly Revenue',
    'profit_label_cogs' => 'Total Cost of Goods Sold (COGS)',
    'profit_label_opex' => 'Monthly Operating & Administrative Expenses',
    'profit_label_tax' => 'Taxes & Other Charges',
    'profit_result_net' => 'Estimated Monthly Net Profit',
    'profit_result_margin' => 'Net Profit Margin (%)',

    // 5. Employee Payroll & BPJS
    'payroll_kicker' => 'Compensation & Employee Benefits Costing',
    'payroll_title' => 'Employee Payroll & BPJS Expense Calculator',
    'payroll_subtitle' => 'Calculate employee take-home pay and total employer labor expenses transparently in accordance with Indonesian manpower regulations.',
    'payroll_label_basic' => 'Monthly Base Salary',
    'payroll_label_allowance' => 'Fixed Allowances (Meal, Transport)',
    'payroll_label_overtime' => 'Overtime & Performance Incentives',
    'payroll_label_bpjs_tk' => 'BPJS Ketenagakerjaan (JKK, JKM, JHT, JP)',
    'payroll_label_bpjs_kes' => 'BPJS Kesehatan (4% Employer, 1% Employee)',
    'payroll_result_take_home' => 'Estimated Employee Take-Home Pay',
    'payroll_result_company_cost' => 'Total Employer Labor Cost',

    // 6. PPh Final 0.5%
    'tax_kicker' => 'Indonesian SMB Tax Law (PP 55 / UU HPP)',
    'tax_title' => 'SMB Final Income Tax Calculator (PPh Final 0.5%)',
    'tax_subtitle' => 'Calculate monthly 0.5% final income tax liability, taking into account the Rp500 million tax-free threshold for individual taxpayers.',
    'tax_label_taxpayer_type' => 'Taxpayer Category',
    'tax_type_individual' => 'Individual Entrepreneur (Exempt up to Rp500M Annual Gross Revenue)',
    'tax_type_corporate' => 'Corporate Entity / CV / PT (Flat 0.5% on Gross Revenue)',
    'tax_label_monthly_turnover' => 'Current Month Gross Revenue',
    'tax_label_cumulative_turnover' => 'Prior Cumulative Revenue for the Year',
    'tax_result_tax_payable' => 'Current Month Tax Due',

    // 7. Daily Revenue Target
    'rev_kicker' => 'Cash Flow Planning',
    'rev_title' => 'Daily & Weekly Sales Target Calculator',
    'rev_subtitle' => 'Break down monthly profit targets and operational overhead into actionable daily sales quotas for your store staff and cashiers.',
    'rev_label_target_monthly_net' => 'Target Monthly Net Profit',
    'rev_label_monthly_fixed_costs' => 'Estimated Monthly Fixed Expenses',
    'rev_label_gross_margin_pct' => 'Average Product Gross Margin (%)',
    'rev_label_operating_days' => 'Business Operating Days per Month',
    'rev_result_daily_target' => 'Daily Sales Target',

    // 8. Business Simulation What-If
    'sim_kicker' => 'Scenario Planning',
    'sim_title' => 'What-If Business Scenario Calculator',
    'sim_subtitle' => 'Simulate the impact of raw material price hikes, promotional discounts, or opening new branches on cash flow and net profitability before taking risks.',
    'sim_label_baseline_rev' => 'Current Baseline Revenue',
    'sim_label_price_change' => 'Planned Price Adjustment (%)',
    'sim_label_cost_change' => 'Projected Cost Increase (%)',
    'sim_label_volume_change' => 'Projected Volume Shift (%)',
    'sim_result_impact_profit' => 'Net Impact on Business Profit',
];

