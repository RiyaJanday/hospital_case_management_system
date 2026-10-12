<?php

namespace Tests\Feature;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicalCaseTest extends TestCase
{
    // Rebuilds the (test) database before each test so tests do not affect each other.
    use RefreshDatabase;

    // Helper: valid data for a new case.
    private function validCaseData(string $caseNumber = 'CASE-9001'): array
    {
        return [
            'case_number' => $caseNumber,
            'patient_reference' => 'PAT-00001',
            'surgeon_name' => 'Dr. Test',
            'implant_type' => 'Knee Implant',
            'status' => 'Draft',
            'priority' => 'Low',
            'surgery_date' => '2026-12-01',
        ];
    }

    // 1. Valid login succeeds.
    public function test_valid_login_succeeds(): void
    {
        $user = User::factory()->create(); // factory password is "password"

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

    // 2. Invalid login is rejected.
    public function test_invalid_login_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    // 3. A guest cannot open case pages or APIs.
    public function test_guest_cannot_open_case_pages_or_api(): void
    {
        $this->get('/cases')->assertRedirect('/login');

        $this->getJson('/api/cases')->assertStatus(401);
    }

    // 4. Admin can create a case.
    public function test_admin_can_create_a_case(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/cases', $this->validCaseData());

        $response->assertRedirect(route('cases.index'));
        $this->assertDatabaseHas('cases', [
            'case_number' => 'CASE-9001',
            'created_by' => $admin->id,
        ]);
    }

    // 5. Duplicate Case Number fails with 422.
    public function test_duplicate_case_number_fails_with_422(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        MedicalCase::factory()->create([
            'case_number' => 'CASE-9001',
            'created_by' => $admin->id,
        ]);

        Sanctum::actingAs($admin); // pretends the admin sent a valid API token

        $response = $this->postJson('/api/cases', $this->validCaseData('CASE-9001'));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('case_number');
    }

    // 6. Viewer cannot delete a case (403) on the web and in the API.
    public function test_viewer_cannot_delete_a_case(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $viewer = User::factory()->create(['role' => 'viewer']);
        $case = MedicalCase::factory()->create(['created_by' => $admin->id]);

        // Web delete
        $this->actingAs($viewer)->delete('/cases/' . $case->id)->assertStatus(403);

        // API delete
        Sanctum::actingAs($viewer);
        $this->deleteJson('/api/cases/' . $case->id)->assertStatus(403);

        // The case must still exist.
        $this->assertDatabaseHas('cases', ['id' => $case->id]);
    }
}
