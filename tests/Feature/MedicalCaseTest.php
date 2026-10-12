<?php

namespace Tests\Feature;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicalCaseTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_valid_login_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

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

    public function test_guest_cannot_open_case_pages_or_api(): void
    {
        $this->get('/cases')->assertRedirect('/login');

        $this->getJson('/api/cases')->assertStatus(401);
    }

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

    public function test_duplicate_case_number_fails_with_422(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        MedicalCase::factory()->create([
            'case_number' => 'CASE-9001',
            'created_by' => $admin->id,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/cases', $this->validCaseData('CASE-9001'));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('case_number');
    }

    public function test_viewer_cannot_delete_a_case(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $viewer = User::factory()->create(['role' => 'viewer']);
        $case = MedicalCase::factory()->create(['created_by' => $admin->id]);

        $this->actingAs($viewer)->delete('/cases/' . $case->id)->assertStatus(403);

        Sanctum::actingAs($viewer);
        $this->deleteJson('/api/cases/' . $case->id)->assertStatus(403);

        $this->assertDatabaseHas('cases', ['id' => $case->id]);
    }
}
