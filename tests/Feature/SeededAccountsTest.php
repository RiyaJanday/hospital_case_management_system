<?php

namespace Tests\Feature;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeededAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_documented_admin_and_viewer(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@gmail.com')->first();
        $viewer = User::where('email', 'viewer@gmail.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('Admin@123', $admin->password));

        $this->assertNotNull($viewer);
        $this->assertFalse($viewer->isAdmin());
        $this->assertTrue(Hash::check('Viewer@123', $viewer->password));
    }

    public function test_seeder_creates_at_least_twenty_cases(): void
    {
        $this->seed();

        $this->assertGreaterThanOrEqual(20, MedicalCase::count());
        $this->assertSame(2, User::count());
    }

    public function test_documented_logins_work_on_the_login_page(): void
    {
        $this->seed();

        $this->post('/login', ['email' => 'admin@gmail.com', 'password' => 'Admin@123']);
        $this->assertAuthenticated();

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', ['email' => 'viewer@gmail.com', 'password' => 'Viewer@123']);
        $this->assertAuthenticated();
    }

    public function test_new_registrations_get_the_viewer_role(): void
    {
        $this->post('/register', [
            'name' => 'New Person',
            'email' => 'new.person@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'new.person@gmail.com', 'role' => 'viewer']);
    }
}
