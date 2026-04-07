<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacancy>
 */
class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraph(3),
            'must_have_skills' => ['PHP', 'Laravel'],
            'nice_to_have_skills' => ['Vue.js', 'Docker'],
            'min_experience_years' => fake()->randomFloat(1, 0, 10),
            'language_requirements' => ['Русский', 'English'],
            'salary_min' => fake()->numberBetween(5000000, 10000000),
            'salary_max' => fake()->numberBetween(10000000, 20000000),
            'location' => fake()->city(),
            'employment_type' => EmploymentType::FullTime,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
