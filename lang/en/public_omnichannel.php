<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Omnichannel Suite Translations (English)
    |--------------------------------------------------------------------------
    */

    // Common Omnichannel
    'omni_kicker' => 'Unified Omnichannel Architecture',
    'omni_title_lead' => 'Connect Physical Stores, Marketplaces & WhatsApp in One View',
    'omni_subtitle_lead' => 'Manage orders, inventory, and customer relationships across all sales channels without juggling multiple browser tabs. Stock levels synchronize automatically as items sell anywhere.',

    // Common Buttons
    'btn_dashboard' => 'Dashboard',
    'btn_try_demo' => 'Try WhatsApp Notification Demo',
    'btn_connect_pos' => 'Connect to POS Cashier',
    'btn_connect_marketplace' => 'Connect to Marketplaces',
    'btn_social_demo' => 'Try Social Media Demo',
    'btn_view_calendar' => 'View Content Calendar',
    'btn_orders_demo' => 'Try Order Management Demo',
    'btn_customer_demo' => 'Try Customer View Demo',
    'btn_consultation' => 'Store Consultation',
    'btn_free_account' => 'Start Free Trial',

    // ==========================================
    // 1. WhatsApp Integration (whatsapp.blade.php)
    // ==========================================
    'wa_kicker' => 'Official WhatsApp Business Integration',
    'wa_title' => 'Send Digital Receipts & Invoices via WhatsApp Without Saving Numbers',
    'wa_subtitle' => 'Deliver paperless digital POS receipts, notify customers when orders are ready for pickup, update service statuses, and send polite invoice reminders directly through official WhatsApp chats.',
    'wa_reassurance_receipt' => 'Official Paperless Digital Receipts',
    'wa_reassurance_no_save' => 'Auto-Sent Without Saving Numbers',
    'wa_reassurance_anti_spam' => 'Spam-Protected Transactional Messaging',
    'wa_meta_protocol' => 'Official & Secure Meta Cloud API Protocol',
    'wa_verified_business' => 'Verified Business Account',
    'wa_feat_1' => 'Interactive digital receipts with complete itemized breakdowns',
    'wa_feat_2' => 'Automated alerts when auto repair or laundry orders are completed',
    'wa_feat_3' => 'Official invoice due date reminders sent directly to clients',
    'wa_feat_4' => 'Customer phone numbers securely retained in company records, not cashier personal phones',
    'wa_status_update_title' => 'Real-Time Order Status Updates',
    'wa_status_update_desc' => 'Designed for laundry, auto repair shops, and maintenance services. When a technician marks an order as Completed, the system automatically sends a message alerting the customer that their item is ready for pickup.',
    'wa_cta_bottom_title' => 'Start Modern Transactional Messaging with Your Customers',
    'wa_cta_bottom_desc' => 'Increase cashier throughput, eliminate thermal paper expenses, and provide a professional, seamless checkout experience for your customers with COOCA WhatsApp integration.',
    'wa_faq_1_q' => 'How does digital receipt delivery via WhatsApp work in COOCA?',
    'wa_faq_1_a' => 'Upon completing checkout at the POS terminal, the cashier simply enters the customer\'s WhatsApp number and taps \'Send WhatsApp Receipt\'. The system generates a polite message with an official digital receipt link that customers can open instantly on their mobile phone.',
    'wa_faq_2_q' => 'Do retail store employees need to save customer phone numbers to their personal devices?',
    'wa_faq_2_a' => 'Not at all. Notifications are routed directly through COOCA\'s business channel. Customer contact records are stored securely within your business database, preventing data leaks to personal employee handsets.',
    'wa_faq_3_q' => 'Is this WhatsApp messaging service protected from bans?',
    'wa_faq_3_a' => 'COOCA focuses exclusively on legitimate transactional messages that customers actively anticipate such as purchase receipts, laundry completion notices, or vehicle repair work orders. We do not facilitate bulk unsolicited spam, safeguarding your business number\'s health score.',

    // ==========================================
    // 2. Marketplace Sync (marketplace.blade.php)
    // ==========================================
    'mp_kicker' => 'Multi-Marketplace Synchronization',
    'mp_title' => 'Automated Inventory Sync for Shopee, Tokopedia & TikTok Shop',
    'mp_subtitle' => 'Eliminate order cancellation penalties caused by stockouts (overselling). When an item sells at your brick-and-mortar POS, stock on Shopee and Tokopedia is deducted in real time.',
    'mp_reassurance_single_stock' => '1 Central Stock for In-Store & Online',
    'mp_reassurance_auto_sync' => 'Automatic Sync < 5 Seconds',
    'mp_reassurance_penalty_safe' => 'Protected from Order Cancellation Penalties',
    'mp_penalty_protection_desc' => 'COOCA\'s centralized inventory engine actively updates inventory counts across all online stores whenever a sale occurs at your retail counter or other channels. Eliminate overselling risks and protect your store from marketplace cancellation penalties.',
    'mp_feat_1' => 'Single centralized stock pool for offline stores and all marketplace storefronts',
    'mp_feat_2' => 'Instant simultaneous stock deduction as incoming orders arrive',
    'mp_feat_3' => 'Aggregate multi-store order queues into one unified fulfillment dashboard',
    'mp_feat_4' => 'Automatic platform fee and sales commission reconciliation per marketplace',

    // ==========================================
    // 3. Social Media Integration (social-media.blade.php)
    // ==========================================
    'soc_kicker' => 'Social Commerce & Chat-to-Invoice',
    'soc_title' => 'Manage All Business Social Media Channels in One Unified Workspace',
    'soc_subtitle' => 'Stop juggling multiple mobile accounts. Schedule promotional content across Instagram, Facebook, and TikTok, sync directly with your store catalog, and turn social media followers into paying customers.',
    'soc_reassurance_multi_acc' => 'Multi-Account: IG, FB, & TikTok',
    'soc_reassurance_pos_catalog' => 'Linked to POS Catalog',
    'soc_reassurance_no_pwd_share' => 'Secure Without Password Sharing',
    'soc_feat_1' => 'Generate instant digital invoices and payment links right from chat',
    'soc_feat_2' => 'Support instant QRIS and bank virtual account payments',
    'soc_feat_3' => 'Automated payment verification no need to request screenshot transfer receipts',
    'soc_feat_4' => 'Real-time inventory reserve locks as soon as the customer checks out',

    // ==========================================
    // 4. Orders Hub (orders.blade.php)
    // ==========================================
    'ord_kicker' => 'Multi-Channel Order Hub',
    'ord_title' => 'Unify All Business Order Streams Into One High-Speed Pipeline',
    'ord_subtitle' => 'Orders arrive from retail cashiers, WhatsApp chats, standalone online stores, and online marketplaces. COOCA consolidates everything into a neat visual pipeline, routes to the nearest outlet, and ensures timely order fulfillment.',
    'ord_reassurance_aggregation' => 'Aggregates POS, WA & Marketplaces',
    'ord_reassurance_smart_routing' => 'Smart Routing to Nearest Branch',
    'ord_reassurance_bulk_receipt' => 'Bulk Shipping Label & Picking Slip Print',
    'ord_feat_1' => 'Unified kanban order pipeline across every connected sales channel',
    'ord_feat_2' => 'Single-click shipping label and courier manifest printing',
    'ord_feat_3' => 'Automated courier tracking updates synced to customer order statuses',
    'ord_feat_4' => 'Clean separation between in-store pickup orders and courier deliveries',

    // ==========================================
    // 5. Customer Database (customer.blade.php)
    // ==========================================
    'crm_kicker' => 'Single Customer View',
    'crm_title' => 'Recognize Your Customers Across Offline and Online Touchpoints',
    'crm_subtitle' => 'Customers buy in physical stores, message via WhatsApp, and purchase on marketplaces. COOCA merges every touchpoint into a unified Single Customer View so you understand each shopper\'s true lifetime value.',
    'crm_reassurance_single_view' => 'Single Customer View Offline & Online',
    'crm_reassurance_loyalty' => 'Automated Loyalty Points & Discounts',
    'crm_reassurance_receivables' => 'Store Credit & Debt Ledger Limits',
    'crm_feat_1' => 'Unified customer profile across physical outlets and e-commerce stores',
    'crm_feat_2' => 'Store credit and receivables ledger with enforceable credit limits',
    'crm_feat_3' => 'Automated member discounts and loyalty point redemption at POS',
    'crm_feat_4' => 'Actionable segmentation: active shoppers, VIP loyalists, and at-risk churn accounts',
];
