<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('instructions', function ($table) {
            $table->text('behavior')->nullable()->change();
            $table->text('commands')->nullable()->after('behavior');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
