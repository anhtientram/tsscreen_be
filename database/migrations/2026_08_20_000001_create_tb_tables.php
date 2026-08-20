<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_users', function (Blueprint $table) {
            $table->id('customer_id');
            $table->string('customer_name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->unique();
            $table->string('date_of_birth')->nullable();
            $table->string('sex')->nullable();
            $table->string('chu_tk')->nullable();
            $table->string('stk')->nullable();
            $table->string('nganhang')->nullable();
            $table->string('chinhanh')->nullable();
            $table->string('password');
            $table->string('customer_token', 64)->unique();
            $table->text('fcm_token')->nullable();
            $table->string('login_with', 20)->default('email');
            $table->string('status', 1)->default('y');
            $table->string('deleted', 1)->default('n');
            $table->string('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->string('last_MDF_by')->nullable();
            $table->timestamp('last_MDF_date')->nullable();
        });

        Schema::create('tb_accounts', function (Blueprint $table) {
            $table->id('account_id');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('user_type', 8)->default('2');
            $table->text('fcm_token')->nullable();
            $table->string('deleted', 1)->default('n');
            $table->timestamp('created_date')->nullable();
            $table->timestamp('last_MDF_date')->nullable();
        });

        Schema::create('tb_otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('code_authen', 16);
            $table->string('purpose', 32);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->index(['email', 'purpose']);
        });

        Schema::create('tb_configs', function (Blueprint $table) {
            $table->id();
            $table->string('config_key')->unique();
            $table->text('config_value')->nullable();
        });

        Schema::create('tb_packets', function (Blueprint $table) {
            $table->id('packet_id');
            $table->string('name_packet')->nullable();
            $table->string('price')->nullable();
            $table->string('price_6_month')->nullable();
            $table->string('price_12_month')->nullable();
            $table->string('day_qty')->nullable();
            $table->string('month_qty')->nullable();
            $table->string('year_qty')->nullable();
            $table->string('is_trial', 8)->default('0');
            $table->string('is_business', 8)->default('0');
            $table->text('detail')->nullable();
            $table->text('description')->nullable();
            $table->string('picture')->nullable();
            $table->string('expire_date')->nullable();
            $table->string('limit_capacity')->default('0');
            $table->string('limit_qty')->default('0');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('deleted', 1)->default('n');
            $table->timestamp('created_date')->nullable();
            $table->timestamp('last_MDF_date')->nullable();
        });

        Schema::create('tb_orders', function (Blueprint $table) {
            $table->id('paid_id');
            $table->unsignedBigInteger('packet_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('packet_code')->nullable();
            $table->string('reg_number')->nullable();
            $table->string('name_packet')->nullable();
            $table->string('price')->nullable();
            $table->string('price_6_month')->nullable();
            $table->string('price_12_month')->nullable();
            $table->string('day_qty')->nullable();
            $table->string('month_qty')->nullable();
            $table->string('year_qty')->nullable();
            $table->string('pay_month')->nullable();
            $table->string('is_trial', 8)->default('0');
            $table->string('is_business', 8)->default('0');
            $table->text('detail')->nullable();
            $table->text('description')->nullable();
            $table->string('picture')->nullable();
            $table->string('pay', 8)->default('0');
            $table->string('type')->nullable();
            $table->string('type_pay')->nullable();
            $table->string('register_date')->nullable();
            $table->string('payment_date')->nullable();
            $table->string('valid_date')->nullable();
            $table->string('expire_date')->nullable();
            $table->string('payment_due_date')->nullable();
            $table->string('limit_capacity')->nullable();
            $table->string('limit_qty')->nullable();
            $table->string('deleted', 1)->default('n');
            $table->string('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->string('last_MDF_by')->nullable();
            $table->timestamp('last_MDF_date')->nullable();
        });

        Schema::create('tb_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->unsignedBigInteger('paid_id')->nullable()->index();
            $table->unsignedBigInteger('packet_id')->nullable();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('reg_number')->nullable();
            $table->string('name_packet')->nullable();
            $table->string('amount')->nullable();
            $table->string('payment_date')->nullable();
            $table->string('ref_transaction_id')->nullable();
            $table->timestamp('created_date')->nullable();
        });

        Schema::create('tb_dirs', function (Blueprint $table) {
            $table->id('id_dir');
            $table->string('name_dir')->nullable();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('type_dir')->nullable();
            $table->string('turnon_time')->nullable();
            $table->string('turnoff_time')->nullable();
            $table->string('deleted', 1)->default('n');
            $table->string('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->string('last_MDF_by')->nullable();
            $table->timestamp('last_MDF_date')->nullable();
        });

        Schema::create('tb_dir_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_dir')->index();
            $table->unsignedBigInteger('customer_idfrom')->index();
            $table->unsignedBigInteger('customer_idto')->index();
            $table->string('checkOwner', 8)->default('0');
            $table->timestamp('created_date')->nullable();
        });

        Schema::create('tb_devices', function (Blueprint $table) {
            $table->id('computer_id');
            $table->string('computer_name')->nullable();
            $table->string('seri_computer')->index();
            $table->string('ip_address')->nullable();
            $table->string('status')->default('1');
            $table->string('provinces')->nullable();
            $table->string('district')->nullable();
            $table->string('wards')->nullable();
            $table->string('center_id')->nullable();
            $table->string('location')->nullable();
            $table->string('actived_date')->nullable();
            $table->string('ultraviewPW')->nullable();
            $table->string('ultraviewID')->nullable();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('customer_name')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('id_dir')->nullable()->index();
            $table->string('time_end')->nullable();
            $table->string('turn_on')->default('0');
            $table->string('turn_off')->default('0');
            $table->string('user')->nullable();
            $table->string('pass')->nullable();
            $table->string('isCheckOnProjector')->default('0');
            $table->string('isCheckOffProjector')->default('0');
            $table->text('computer_token')->nullable();
            $table->string('rom_memory_total')->nullable();
            $table->string('rom_memory_used')->nullable();
            $table->timestamp('lasted_alive_time')->nullable();
            $table->string('deleted', 1)->default('n');
            $table->string('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->string('last_MDF_by')->nullable();
            $table->timestamp('last_MDF_date')->nullable();
        });

        Schema::create('tb_device_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('computer_id')->index();
            $table->unsignedBigInteger('id_dir')->nullable();
            $table->unsignedBigInteger('customer_idfrom')->index();
            $table->unsignedBigInteger('customer_idto')->index();
            $table->string('checkOwner', 8)->default('0');
            $table->timestamp('created_date')->nullable();
        });

        Schema::create('tb_campaigns', function (Blueprint $table) {
            $table->id('campaign_id');
            $table->string('campaign_name')->nullable();
            $table->string('status')->nullable();
            $table->string('video_id')->nullable();
            $table->string('from_date')->nullable();
            $table->string('to_date')->nullable();
            $table->string('from_time')->nullable();
            $table->string('to_time')->nullable();
            $table->string('days_of_week')->nullable();
            $table->string('video_type')->nullable();
            $table->text('url_youtobe')->nullable();
            $table->text('url_usp')->nullable();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('computer_id')->nullable()->index();
            $table->unsignedBigInteger('id_dir')->nullable()->index();
            $table->string('id_computer')->nullable();
            $table->string('video_duration')->nullable();
            $table->string('approved_yn', 8)->default('0');
            $table->string('default_yn', 8)->default('0');
            $table->string('run_by_default_yn', 8)->default('0');
            $table->unsignedBigInteger('default_campaign_id')->nullable();
            $table->string('accept_count')->nullable();
            $table->text('accept_customers')->nullable();
            $table->string('deleted', 1)->default('n');
            $table->timestamp('created_date')->nullable();
            $table->timestamp('last_MDF_date')->nullable();
        });

        Schema::create('tb_campaign_time_runs', function (Blueprint $table) {
            $table->id('id_run');
            $table->unsignedBigInteger('campaign_id')->index();
            $table->string('from_time')->nullable();
            $table->string('to_time')->nullable();
        });

        Schema::create('tb_campaign_run_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->string('campaign_name')->nullable();
            $table->text('url')->nullable();
            $table->unsignedBigInteger('computer_id')->nullable()->index();
            $table->string('seri_computer')->nullable();
            $table->string('computer_name')->nullable();
            $table->string('run_time')->nullable();
            $table->timestamp('run_time_server')->nullable();
        });

        Schema::create('tb_notifications', function (Blueprint $table) {
            $table->id('id_notify');
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('title')->nullable();
            $table->text('descript')->nullable();
            $table->text('detail')->nullable();
            $table->string('picture')->nullable();
            $table->string('seen', 8)->default('0');
            $table->timestamp('created_date')->nullable();
        });

        Schema::create('tb_account_notifications', function (Blueprint $table) {
            $table->id('id_notify');
            $table->unsignedBigInteger('account_id')->index();
            $table->string('title')->nullable();
            $table->text('descript')->nullable();
            $table->text('detail')->nullable();
            $table->string('picture')->nullable();
            $table->string('seen', 8)->default('0');
            $table->timestamp('created_date')->nullable();
        });

        Schema::create('tb_resources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('name_dir')->index();
            $table->string('name')->nullable();
            $table->string('path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('file_type')->nullable();
            $table->timestamp('creation_time')->nullable();
            $table->timestamp('modification_time')->nullable();
            $table->string('deleted', 1)->default('n');
        });

        Schema::create('tb_upload_chunks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('name_dir')->nullable();
            $table->string('filename')->nullable();
            $table->unsignedInteger('chunk_index')->nullable();
            $table->unsignedInteger('total_chunks')->nullable();
            $table->string('part_path')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['name_dir', 'filename']);
        });

        Schema::create('tb_commands', function (Blueprint $table) {
            $table->id('cmd_id');
            $table->string('sn')->index();
            $table->string('cmd_code')->nullable();
            $table->text('content')->nullable();
            $table->string('is_imme', 8)->default('0');
            $table->unsignedInteger('second_wait')->default(10);
            $table->timestamp('commit_time')->nullable();
            $table->timestamp('return_time')->nullable();
            $table->text('return_value')->nullable();
            $table->string('sync')->nullable();
            $table->string('done', 16)->default('0');
        });
    }

    public function down(): void
    {
        $tables = [
            'tb_commands',
            'tb_upload_chunks',
            'tb_resources',
            'tb_account_notifications',
            'tb_notifications',
            'tb_campaign_run_profiles',
            'tb_campaign_time_runs',
            'tb_campaigns',
            'tb_device_shares',
            'tb_devices',
            'tb_dir_shares',
            'tb_dirs',
            'tb_transactions',
            'tb_orders',
            'tb_packets',
            'tb_configs',
            'tb_otp_codes',
            'tb_accounts',
            'tb_users',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
