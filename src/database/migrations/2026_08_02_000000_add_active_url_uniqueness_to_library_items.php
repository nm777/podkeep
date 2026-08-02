<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("update library_items set is_duplicate = true, duplicate_detected_at = current_timestamp where source_url is not null and is_duplicate = false and processing_status <> 'failed' and id not in (select id from (select min(id) as id from library_items where source_url is not null and is_duplicate = false and processing_status <> 'failed' group by user_id, source_url) as kept_items)");

        DB::statement("create unique index library_items_active_user_source_url_unique on library_items (user_id, source_url) where source_url is not null and is_duplicate = false and processing_status <> 'failed'");
    }

    public function down(): void
    {
        DB::statement('drop index library_items_active_user_source_url_unique');
    }
};
