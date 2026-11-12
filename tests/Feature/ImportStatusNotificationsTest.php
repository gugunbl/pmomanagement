<?php

namespace Tests\Feature;

use App\Livewire\ImportStatusNotifications;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ImportStatusNotificationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('daily_task_report_imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('status');
            $table->text('message')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_user_can_see_and_dismiss_their_completed_import(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'unused',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('daily_task_report_imports')->insert([
            'id' => 10,
            'user_id' => 1,
            'status' => 'completed',
            'message' => 'Import selesai.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::actingAs(User::findOrFail(1))
            ->test(ImportStatusNotifications::class)
            ->assertSee('Import selesai.')
            ->call('dismiss', 10)
            ->assertDontSee('Import selesai.');

        $this->assertNotNull(
            DB::table('daily_task_report_imports')->where('id', 10)->value('read_at')
        );
    }
}
