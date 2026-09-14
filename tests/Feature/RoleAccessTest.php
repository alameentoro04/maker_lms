<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_student_cannot_access_admin_dashboard(): void
    {
        $student = User::factory()->role(Role::STUDENT)->create();

        $this->actingAs($student)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->role(Role::ADMIN)->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }

    public function test_dashboard_redirects_by_role(): void
    {
        $instructor = User::factory()->role(Role::INSTRUCTOR)->create();

        $this->actingAs($instructor)->get('/dashboard')
            ->assertRedirect(route('instructor.dashboard'));
    }
}
