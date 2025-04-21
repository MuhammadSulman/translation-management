<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word, // Generating a unique key for each translation
            'value' => $this->faker->sentence, // Random sentence as translation value
            'language_id' => Language::factory(), // Associate a random language from the factory
        ];
    }

    /**
     * Indicate that the translation should have tags.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withTags(): Factory
    {
        return $this->afterCreating(function (Translation $translation) {
            $tags = Tag::factory()->count(2)->create(); // Create 2 tags and associate them with the translation
            $translation->tags()->sync($tags->pluck('id'));
        });
    }
}
