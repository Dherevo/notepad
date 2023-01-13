<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Note;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */

class NoteFactory extends Factory {

    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */

    public function definition() {

        return [
            'title' => $this->faker->words(5, true),
            'body' => $this->faker->sentence(20),
        ];

    }
}
