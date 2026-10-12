<?php

namespace Database\Factories;

use App\Models\MedicalCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalCaseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'case_number' => fake()->unique()->numerify('CASE-####'),
            'patient_reference' => 'PAT-' . fake()->numerify('#####'),
            'surgeon_name' => 'Dr. ' . fake()->lastName(),
            'implant_type' => fake()->randomElement([
                'Knee Implant', 'Hip Implant', 'Spinal Cage', 'Shoulder Implant', 'Dental Implant',
            ]),
            'status' => fake()->randomElement(MedicalCase::STATUSES),
            'priority' => fake()->randomElement(MedicalCase::PRIORITIES),
            'surgery_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'created_by' => User::factory(),
        ];
    }
}
