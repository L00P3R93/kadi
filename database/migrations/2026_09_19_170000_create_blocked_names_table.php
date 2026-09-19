<?php

use App\Support\NameGuard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Names people may not register or change to (see App\Support\NameGuard). The list is data, so
     * an operator can extend it without a deploy: `php artisan blocked-names:add ...`.
     *
     * The starter entries below stop impersonation of Kadi staff. Profanity is deliberately not
     * seeded: add the terms that matter for your community with the command.
     */
    public function up(): void
    {
        Schema::create('blocked_names', function (Blueprint $table) {
            $table->id();
            $table->string('term', 100)->unique();     // stored already normalised (see NameGuard::normalise)
            $table->string('match', 10)->default('word'); // word | contains
            $table->string('reason', 255)->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('blocked_names')->insert(array_map(fn (string $term) => [
            'term' => NameGuard::normalise($term), 'match' => 'word', 'reason' => 'Impersonation (starter list)', 'created_at' => $now, 'updated_at' => $now,
        ], ['admin', 'administrator', 'moderator', 'support', 'staff', 'official', 'kadi', 'kadionline', 'customer care', 'customer service', 'helpdesk', 'system', 'root', 'owner']));
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_names');
    }
};
