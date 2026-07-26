<?php

namespace Database\Factories;

use App\Models\Chapter;
use App\Models\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'media_file_id' => MediaFile::factory(),
            'start_time' => $this->faker->numberBetween(0, 3600),
            'title' => $this->faker->sentence(3),
        ];
    }
}
