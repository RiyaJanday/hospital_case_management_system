<?php

namespace Tests\Feature;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CaseApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeViewer(): User
    {
        return User::factory()->create(['role' => 'viewer']);
    }

    private function makeCase(User $creator, array $overrides = []): MedicalCase
    {
        return MedicalCase::factory()->create(array_merge(['created_by' => $creator->id], $overrides));
    }

    private function validCaseData(array $overrides = []): array
    {
        return array_merge([
            'case_number' => 'CASE-9001',
            'patient_reference' => 'PAT-00001',
            'surgeon_name' => 'Dr. Test',
            'implant_type' => 'Knee Implant',
            'status' => 'Draft',
            'priority' => 'Low',
            'surgery_date' => '2026-12-01',
        ], $overrides);
    }

    public function test_login_returns_a_token_and_the_role(): void
    {
        $user = $this->makeAdmin();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['token', 'role'], 'message'])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $user = $this->makeAdmin();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertStatus(401)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJsonPath('success', false);
    }

    public function test_login_with_unknown_email_returns_401(): void
    {
        $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'password'])
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_login_without_email_and_password_returns_422(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_token_from_login_works_and_logout_revokes_it(): void
    {
        $user = $this->makeViewer();
        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->json('data.token');

        $this->withToken($token)->getJson('/api/cases')->assertOk();

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_logout_requires_a_token(): void
    {
        $this->postJson('/api/logout')->assertStatus(401);
    }

    public function test_guest_gets_401_on_every_case_endpoint(): void
    {
        $case = $this->makeCase($this->makeAdmin());

        $this->getJson('/api/cases')->assertStatus(401)->assertJsonPath('success', false);
        $this->postJson('/api/cases', $this->validCaseData())->assertStatus(401);
        $this->getJson('/api/cases/' . $case->id)->assertStatus(401);
        $this->putJson('/api/cases/' . $case->id, $this->validCaseData())->assertStatus(401);
        $this->deleteJson('/api/cases/' . $case->id)->assertStatus(401);

        $this->assertDatabaseHas('cases', ['id' => $case->id]);
        $this->assertDatabaseMissing('cases', ['case_number' => 'CASE-9001']);
    }

    public function test_admin_and_viewer_can_list_cases_with_pagination(): void
    {
        $admin = $this->makeAdmin();
        MedicalCase::factory()->count(12)->create(['created_by' => $admin->id]);

        foreach ([$admin, $this->makeViewer()] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/cases')
                ->assertOk()
                ->assertJsonStructure(['success', 'data' => ['data', 'current_page', 'total'], 'message'])
                ->assertJsonCount(10, 'data.data')
                ->assertJsonPath('data.total', 12);

            $this->getJson('/api/cases?page=2')->assertOk()->assertJsonCount(2, 'data.data');
        }
    }

    public function test_admin_and_viewer_can_get_one_case(): void
    {
        $admin = $this->makeAdmin();
        $case = $this->makeCase($admin, ['case_number' => 'CASE-7001']);

        foreach ([$admin, $this->makeViewer()] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/cases/' . $case->id)
                ->assertOk()
                ->assertJsonStructure(['success', 'data', 'message'])
                ->assertJsonPath('data.case_number', 'CASE-7001');
        }
    }

    public function test_getting_a_missing_case_returns_404(): void
    {
        Sanctum::actingAs($this->makeViewer());

        $this->getJson('/api/cases/99999')
            ->assertStatus(404)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_create_a_case_through_the_api(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin);

        $this->postJson('/api/cases', $this->validCaseData())
            ->assertStatus(201)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.case_number', 'CASE-9001');

        $this->assertDatabaseHas('cases', ['case_number' => 'CASE-9001', 'created_by' => $admin->id]);
    }

    public function test_viewer_cannot_create_a_case_through_the_api(): void
    {
        Sanctum::actingAs($this->makeViewer());

        $this->postJson('/api/cases', $this->validCaseData())
            ->assertStatus(403)
            ->assertJsonStructure(['success', 'data', 'message'])
            ->assertJsonPath('success', false);

        $this->postJson('/api/cases', [])->assertStatus(403);

        $this->assertDatabaseCount('cases', 0);
    }

    public function test_creating_a_case_with_missing_fields_returns_field_errors(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $this->postJson('/api/cases', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors([
                'case_number',
                'patient_reference',
                'surgeon_name',
                'implant_type',
                'status',
                'priority',
            ]);
    }

    public function test_api_rejects_invalid_status_priority_and_date(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $this->postJson('/api/cases', $this->validCaseData([
            'status' => 'Finished',
            'priority' => 'Urgent',
            'surgery_date' => 'not-a-date',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'priority', 'surgery_date']);

        $this->assertDatabaseCount('cases', 0);
    }

    public function test_admin_can_update_a_case_through_the_api(): void
    {
        $admin = $this->makeAdmin();
        $case = $this->makeCase($admin, ['case_number' => 'CASE-7001', 'status' => 'Draft']);
        Sanctum::actingAs($admin);

        $this->putJson('/api/cases/' . $case->id, $this->validCaseData([
            'case_number' => 'CASE-7001',
            'status' => 'Completed',
        ]))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'Completed');

        $this->assertDatabaseHas('cases', ['id' => $case->id, 'status' => 'Completed']);
    }

    public function test_api_update_to_another_cases_number_returns_422(): void
    {
        $admin = $this->makeAdmin();
        $first = $this->makeCase($admin, ['case_number' => 'CASE-7001']);
        $second = $this->makeCase($admin, ['case_number' => 'CASE-7002']);
        Sanctum::actingAs($admin);

        $this->putJson('/api/cases/' . $second->id, $this->validCaseData(['case_number' => $first->case_number]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('case_number');
    }

    public function test_viewer_cannot_update_a_case_through_the_api(): void
    {
        $case = $this->makeCase($this->makeAdmin(), ['surgeon_name' => 'Dr. Original']);
        Sanctum::actingAs($this->makeViewer());

        $this->putJson('/api/cases/' . $case->id, $this->validCaseData(['surgeon_name' => 'Dr. Hacker']))
            ->assertStatus(403);

        $this->assertDatabaseHas('cases', ['id' => $case->id, 'surgeon_name' => 'Dr. Original']);
    }

    public function test_updating_a_missing_case_returns_404(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $this->putJson('/api/cases/99999', $this->validCaseData())
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_delete_a_case_through_the_api(): void
    {
        $admin = $this->makeAdmin();
        $case = $this->makeCase($admin);
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/cases/' . $case->id)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
    }

    public function test_deleting_a_missing_case_returns_404(): void
    {
        Sanctum::actingAs($this->makeAdmin());

        $this->deleteJson('/api/cases/99999')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
