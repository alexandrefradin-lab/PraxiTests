<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = \App\Models\Profile::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'status'            => fake()->randomElement(['employee', 'entrepreneur', 'jobseeker', 'student']),
            'status_since'      => fake()->dateTimeBetween('-3 years', '-1 month'),
            'status_months'     => fake()->numberBetween(1, 36),
            'current_role'      => fake()->jobTitle(),
            'industry'          => fake()->randomElement(['Tech', 'Finance', 'Santé', 'Éducation', 'Commerce']),
            'cv_path'           => null,
            'cv_original_name'  => null,
            'consent_data'      => false,
            'consent_marketing' => false,
        ];
    }

    /**
     * Profil complet avec CV simulé (sans fichier réel — pour les tests feature).
     * Satisfait Profile::isComplete() : status + status_since + cv_path + consent_data.
     */
    public function cvUploaded(): static
    {
        return $this->state(fn () => [
            'cv_path'          => 'cvs/test/fake-cv.pdf',
            'cv_original_name' => 'mon-cv.pdf',
            'consent_data'     => true,
            'completed_at'     => now(),
        ]);
    }

    /** Profil incomplet (pas de CV, pas de consentement). */
    public function incomplete(): static
    {
        return $this->state(fn () => [
            'cv_path'      => null,
            'consent_data' => false,
            'completed_at' => null,
        ]);
    }
}
