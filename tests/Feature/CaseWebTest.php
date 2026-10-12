<?php

namespace Tests\Feature;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseWebTest extends TestCase
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

    public function test_guest_is_redirected_to_login_from_every_case_page(): void
    {
        $case = $this->makeCase($this->makeAdmin());

        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/cases')->assertRedirect('/login');
        $this->get('/cases/create')->assertRedirect('/login');
        $this->get('/cases/' . $case->id)->assertRedirect('/login');
        $this->get('/cases/' . $case->id . '/edit')->assertRedirect('/login');
        $this->post('/cases', $this->validCaseData())->assertRedirect('/login');
        $this->put('/cases/' . $case->id, $this->validCaseData())->assertRedirect('/login');
        $this->delete('/cases/' . $case->id)->assertRedirect('/login');

        $this->assertDatabaseHas('cases', ['id' => $case->id]);
        $this->assertDatabaseMissing('cases', ['case_number' => 'CASE-9001']);
    }

    public function test_viewer_can_see_list_and_details_without_action_buttons(): void
    {
        $case = $this->makeCase($this->makeAdmin(), ['case_number' => 'CASE-7001']);
        $viewer = $this->makeViewer();

        $this->actingAs($viewer)->get('/cases')
            ->assertOk()
            ->assertSee('CASE-7001')
            ->assertDontSee('New Case')
            ->assertDontSee('>Edit</a>', false)
            ->assertDontSee('>Delete</button>', false);

        $this->actingAs($viewer)->get('/cases/' . $case->id)
            ->assertOk()
            ->assertSee('CASE-7001')
            ->assertDontSee('>Edit</a>', false);
    }

    public function test_admin_sees_all_action_buttons(): void
    {
        $admin = $this->makeAdmin();
        $this->makeCase($admin);

        $this->actingAs($admin)->get('/cases')
            ->assertOk()
            ->assertSee('New Case')
            ->assertSee('>Edit</a>', false)
            ->assertSee('>Delete</button>', false);
    }

    public function test_viewer_cannot_create_or_edit_a_case(): void
    {
        $case = $this->makeCase($this->makeAdmin(), ['surgeon_name' => 'Dr. Original']);
        $viewer = $this->makeViewer();

        $this->actingAs($viewer)->get('/cases/create')->assertStatus(403);
        $this->actingAs($viewer)->post('/cases', $this->validCaseData())->assertStatus(403);
        $this->actingAs($viewer)->get('/cases/' . $case->id . '/edit')->assertStatus(403);
        $this->actingAs($viewer)
            ->put('/cases/' . $case->id, $this->validCaseData(['surgeon_name' => 'Dr. Hacker']))
            ->assertStatus(403);

        $this->assertDatabaseMissing('cases', ['case_number' => 'CASE-9001']);
        $this->assertDatabaseHas('cases', ['id' => $case->id, 'surgeon_name' => 'Dr. Original']);
    }

    public function test_admin_can_open_the_edit_form(): void
    {
        $admin = $this->makeAdmin();
        $case = $this->makeCase($admin, ['case_number' => 'CASE-7001']);

        $this->actingAs($admin)->get('/cases/' . $case->id . '/edit')
            ->assertOk()
            ->assertSee('CASE-7001');
    }

    public function test_admin_can_update_a_case_and_change_its_status(): void
    {
        $admin = $this->makeAdmin();
        $case = $this->makeCase($admin, [
            'case_number' => 'CASE-7001',
            'status' => 'Draft',
            'surgeon_name' => 'Dr. Old',
        ]);

        $response = $this->actingAs($admin)->put('/cases/' . $case->id, $this->validCaseData([
            'case_number' => 'CASE-7001',
            'status' => 'Approved',
            'surgeon_name' => 'Dr. New',
        ]));

        $response->assertRedirect(route('cases.show', $case));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'case_number' => 'CASE-7001',
            'status' => 'Approved',
            'surgeon_name' => 'Dr. New',
        ]);
    }

    public function test_editing_a_case_can_keep_its_own_case_number(): void
    {
        $admin = $this->makeAdmin();
        $case = $this->makeCase($admin, ['case_number' => 'CASE-7001']);

        $this->actingAs($admin)
            ->put('/cases/' . $case->id, $this->validCaseData(['case_number' => 'CASE-7001', 'priority' => 'High']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cases', ['id' => $case->id, 'priority' => 'High']);
    }

    public function test_editing_a_case_to_another_cases_number_is_rejected(): void
    {
        $admin = $this->makeAdmin();
        $first = $this->makeCase($admin, ['case_number' => 'CASE-7001']);
        $second = $this->makeCase($admin, ['case_number' => 'CASE-7002']);

        $this->actingAs($admin)
            ->put('/cases/' . $second->id, $this->validCaseData(['case_number' => $first->case_number]))
            ->assertSessionHasErrors('case_number');

        $this->assertDatabaseHas('cases', ['id' => $second->id, 'case_number' => 'CASE-7002']);
    }

    public function test_admin_can_delete_a_case(): void
    {
        $admin = $this->makeAdmin();
        $case = $this->makeCase($admin);

        $this->actingAs($admin)->delete('/cases/' . $case->id)->assertRedirect(route('cases.index'));

        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
    }

    public function test_deleting_a_case_that_does_not_exist_returns_404(): void
    {
        $this->actingAs($this->makeAdmin())->delete('/cases/99999')->assertNotFound();
    }

    public function test_creating_a_case_without_required_fields_shows_an_error_for_each_field(): void
    {
        $this->actingAs($this->makeAdmin())->post('/cases', [])->assertSessionHasErrors([
            'case_number',
            'patient_reference',
            'surgeon_name',
            'implant_type',
            'status',
            'priority',
        ]);

        $this->assertDatabaseCount('cases', 0);
    }

    public function test_status_priority_and_date_must_be_valid(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post('/cases', $this->validCaseData([
                'status' => 'Finished',
                'priority' => 'Urgent',
                'surgery_date' => 'not-a-date',
            ]))
            ->assertSessionHasErrors(['status', 'priority', 'surgery_date']);

        $this->assertDatabaseCount('cases', 0);
    }

    public function test_surgery_date_is_optional(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post('/cases', $this->validCaseData(['surgery_date' => null]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cases', ['case_number' => 'CASE-9001', 'surgery_date' => null]);
    }

    public function test_case_details_page_shows_who_created_the_case(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Alice Admin']);
        $case = $this->makeCase($admin, ['patient_reference' => 'PAT-12345']);

        $this->actingAs($this->makeViewer())->get('/cases/' . $case->id)
            ->assertOk()
            ->assertSee('PAT-12345')
            ->assertSee('Alice Admin');
    }

    public function test_case_list_can_be_searched_by_case_number_or_surgeon(): void
    {
        $admin = $this->makeAdmin();
        $this->makeCase($admin, ['case_number' => 'CASE-1111', 'surgeon_name' => 'Dr. Alpha']);
        $this->makeCase($admin, ['case_number' => 'CASE-2222', 'surgeon_name' => 'Dr. Beta']);

        $this->actingAs($admin)->get('/cases?search=1111')
            ->assertSee('CASE-1111')
            ->assertDontSee('CASE-2222');

        $this->actingAs($admin)->get('/cases?search=Beta')
            ->assertSee('CASE-2222')
            ->assertDontSee('CASE-1111');
    }

    public function test_case_list_can_be_filtered_by_status(): void
    {
        $admin = $this->makeAdmin();
        $this->makeCase($admin, ['case_number' => 'CASE-3001', 'status' => 'Approved']);
        $this->makeCase($admin, ['case_number' => 'CASE-3002', 'status' => 'Draft']);

        $this->actingAs($admin)->get('/cases?status=Approved')
            ->assertSee('CASE-3001')
            ->assertDontSee('CASE-3002');
    }

    public function test_case_list_shows_ten_cases_per_page(): void
    {
        $admin = $this->makeAdmin();
        MedicalCase::factory()->count(12)->create(['created_by' => $admin->id]);

        $this->actingAs($admin)->get('/cases')->assertViewHas('cases', function ($cases) {
            return $cases->count() === 10 && $cases->total() === 12;
        });

        $this->actingAs($admin)->get('/cases?page=2')->assertViewHas('cases', function ($cases) {
            return $cases->count() === 2;
        });
    }

    public function test_dashboard_shows_the_correct_counts(): void
    {
        $admin = $this->makeAdmin();
        $statusCounts = [
            'Draft' => 1,
            'Planning' => 2,
            'In Review' => 1,
            'Approved' => 3,
            'Completed' => 1,
            'Cancelled' => 1,
        ];
        foreach ($statusCounts as $status => $count) {
            MedicalCase::factory()->count($count)->create(['created_by' => $admin->id, 'status' => $status]);
        }

        $this->actingAs($this->makeViewer())->get('/dashboard')
            ->assertOk()
            ->assertViewHas('totalCases', 9)
            ->assertViewHas('planningCases', 2)
            ->assertViewHas('inReviewCases', 1)
            ->assertViewHas('approvedCases', 3)
            ->assertViewHas('completedCases', 1);
    }
}
