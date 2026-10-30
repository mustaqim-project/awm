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
    'customer_seo_title' => 'Unified Customer Database & Omnichannel CRM | COOCA',
    'customer_seo_desc' => 'Unify customer data from physical stores, WhatsApp, and marketplaces into a single profile. Track purchase history and loyalty effortlessly.',
    'marketplace_seo_title' => 'Marketplace Integration & Store Stock Sync | COOCA',
    'marketplace_seo_desc' => 'Connect Shopee, Tokopedia, & TikTok Shop with your retail store: automatic stock synchronization, eliminate overselling, and manage orders in one hub.',
    'orders_seo_title' => 'Omnichannel Order Management & Online Store | COOCA',
    'orders_seo_desc' => 'Unify retail cashier, WhatsApp, and marketplace orders in a single queue. Process faster with picking slips and bulk airwaybill printing.',
    'social_seo_title' => 'Manage Store Multi-Account Social Media | COOCA',
    'social_seo_desc' => 'Manage Instagram, Facebook, and TikTok from one dashboard: automated scheduling, chat-to-order, and link store product catalogs.',
    'wa_seo_title' => 'Send Digital POS Receipts & Invoices via WhatsApp | COOCA',
    'wa_seo_desc' => 'Send automated digital receipts, order status updates, and invoice reminders directly to customer WhatsApp without saving contacts.',
    'mod_pos' => 'Point of Sale (POS)',
    'mod_crm' => 'CRM & Customer Database',
    'mod_orders' => 'Order Management',
    'mod_inventory' => 'Central Warehouse Stock',
    'mod_inventory_multibranch' => 'Multi-Branch Warehouse Stock',
    'mod_whatsapp' => 'WhatsApp Status Notifications',
    'mod_catalog_stock' => 'Store Catalog & Stock',
    'btn_view_all_erp' => 'View All ERP Modules',
    'btn_open_pipeline' => 'Open Full Pipeline',
    'btn_view_message_format' => 'View Message Format',
    'cust_pain_1_title' => 'VIP Customers Treated as Regular Buyers',
    'cust_pain_2_title' => 'Duplicate Data & Multiple Contacts',
    'cust_pain_3_title' => 'Misdirected Promotions',
    'cust_feature_kicker' => 'COOCA Single Customer View Features',
    'cust_feat_1_title' => 'Smart Identity Merge Profile Unification',
    'cust_feat_2_title' => 'Store Service Notes & Preferences',
    'cust_feat_3_title' => 'Complete Transaction History',
    'cust_flow_kicker' => 'How Customer Data Unifies in COOCA',
    'cust_step_1_title' => 'Interactions Across Multiple Channels',
    'cust_step_2_title' => 'Updated 360° Profile',
    'cust_step_3_title' => 'More Personalized Service',
    'cust_bottom_cta_title' => 'Start Truly Knowing Your Customers Today',
    'mp_pain_1_title' => 'Juggling Multiple Seller Center Tabs',
    'mp_feat_sync_title' => 'Zero-Delay Automatic Stock Synchronization',
    'mp_feat_price_title' => 'Flexible Price Updates',
    'mp_feat_scan_title' => 'Barcode Scan Before Dispatch',
    'mp_flow_kicker' => 'How Marketplace Transactions Flow in COOCA',
    'mp_step_pack_title' => 'Warehouse Picking & Packing',
    'mp_mod_orders_title' => 'Unified Order Management',
    'ord_pain_kicker' => 'Order Fulfillment Challenges',
    'ord_pain_1_title' => 'Missed Orders & Expired Deadlines',
    'ord_pain_2_title' => 'Wrong Shipments & High Returns',
    'ord_pain_3_title' => 'Expensive Shipping from Wrong Routes',
    'ord_feat_1_title' => 'Single Order Gateway for All Sales',
    'ord_feat_sla_title' => 'Shipping SLA Monitoring',
    'ord_flow_kicker' => 'How Orders Are Processed Fast in COOCA',
    'ord_step_verified_title' => 'Order Verified',
    'ord_step_courier_title' => 'Handover to Courier',
    'ord_step_done_title' => 'Order Completed',
    'ord_bottom_cta_title' => 'Accelerate Your Store Order Processing Today',
    'soc_pain_1_title' => 'Risks of Sharing Account Passwords',
    'soc_pain_2_title' => 'Content Disconnected from Real Stock',
    'soc_feature_kicker' => 'COOCA Social Media Features',
    'soc_feat_link_title' => 'Link Store Products & Live Inventory',
    'soc_flow_kicker' => 'Connecting Social Media with POS & Warehouse',
    'soc_step_pick_title' => 'Select Products Ready to Sell',
    'soc_step_schedule_title' => 'Automated Publishing Schedule',
    'soc_step_order_title' => 'Centralized Inbound Orders',
    'soc_step_report_title' => 'Stock Deducted & Neat Reporting',
    'soc_bottom_cta_title' => 'Start Publishing Business Content That Sells',
    'wa_pain_kicker' => 'Customer Communication Challenges',
    'wa_pain_1_title' => 'Wasteful Thermal Receipt Paper Costs',
    'wa_pain_2_title' => 'Mistyped Numbers & Slow Manual Chats',
    'wa_pain_3_title' => 'Customers Complaining About Order Status',
    'wa_feat_receipt_title' => 'Instant Comprehensive Digital Receipt',
    'wa_flow_kicker' => 'From Operational Events into Friendly Messages',
    'wa_step_finish_title' => 'Cashier Completes Order',
    'wa_step_arrive_title' => 'Message Arrives on WhatsApp',
];
