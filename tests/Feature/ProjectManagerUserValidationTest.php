<?php

namespace Tests\Feature;

use App\Mprojectmanager;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProjectManagerUserValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('m_project_manager')) {
            Schema::create('m_project_manager', function (Blueprint $table) {
                $table->increments('id_pm');
                $table->string('nama_pm');
                $table->tinyInteger('status')->default(1);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_pm_user_id_must_be_a_valid_existing_user(): void
    {
        $this->expectException(ValidationException::class);

        Mprojectmanager::create([
            'nama_pm' => 'PM Invalid',
            'status' => 1,
            'user_id' => 999999,
        ]);
    }

    public function test_pm_user_id_must_be_unique_across_pm_records(): void
    {
        $uniqueEmail = 'pm-unique-' . uniqid() . '@example.com';

        $user = User::create([
            'name' => 'PM Unique',
            'email' => $uniqueEmail,
            'password' => 'secret123',
            'status' => 1,
        ]);

        Mprojectmanager::create([
            'nama_pm' => 'PM 1',
            'status' => 1,
            'user_id' => $user->id,
        ]);

        $this->expectException(ValidationException::class);

        Mprojectmanager::create([
            'nama_pm' => 'PM 2',
            'status' => 1,
            'user_id' => $user->id,
        ]);
    }
}
