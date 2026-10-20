<?php

declare(strict_types=1);

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permanentToken = 'EAAUrMrnYomIBStJFCuws2rmo8pP7zNPYcwp3qo4ODjBBh1hDnZAu2atx2mU1It3RGFOM1e0KoZA2TxEOTUDaoyfILeoRBmqCvw18IZBS9ZBSDOgw1CFZBljDOLuWqZA5HDorLXcuMhG4ZAolWWuIP9Vv0gUNMaZCTTFgzxqsQZAZBbxaNgtZAIZANbOusLSBkeZB7tfHjaAZDZD';
        $phoneId = '1304859399385592';
        $wabaId = '37944837988498077';
        $appId = '1454871749894754';
        $appSecret = '73b4d4fcdb8e5178c27ed47eeedd83d8';

        // 1. WhatsApp Cloud API & Omnichannel credentials
        SystemSetting::set('meta_wa_phone_number_id', $phoneId, 'whatsapp');
        SystemSetting::set('whatsapp_phone_number_id', $phoneId, 'meta_omnichannel');
        SystemSetting::set('meta_wa_token', $permanentToken, 'whatsapp', true);
        SystemSetting::set('meta_system_user_token', $permanentToken, 'meta_omnichannel', true);
        SystemSetting::set('meta_wa_waba_id', $wabaId, 'whatsapp');
        SystemSetting::set('meta_wa_app_id', $appId, 'whatsapp');
        SystemSetting::set('meta_wa_app_secret', $appSecret, 'whatsapp', true);
        SystemSetting::set('meta_wa_graph_version', 'v21.0', 'whatsapp');
        SystemSetting::set('meta_wa_graph_url', 'https://graph.facebook.com', 'whatsapp');
        SystemSetting::set('meta_wa_webhook_verify_token', 'cooca_meta_wa_webhook_secret', 'whatsapp');
        SystemSetting::set('meta_wa_otp_template', 'cooca_otp', 'whatsapp');
        SystemSetting::set('wa_otp_active', '1', 'whatsapp');
        SystemSetting::set('wa_blast_active', '1', 'whatsapp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
