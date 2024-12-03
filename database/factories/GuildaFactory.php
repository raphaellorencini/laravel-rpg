<?php

namespace Database\Factories;

use App\Models\Guilda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guilda>
 */
class GuildaFactory extends Factory
{
    protected $model = Guilda::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition() {
        return [
            'nome' => 'Test '.rand(1000, 9999),
        ];
    }
}
