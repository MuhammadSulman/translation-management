<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationsTableSeeder extends Seeder
{
    public function run(): void
    {
        if (Translation::first()) {
            return;
        }

        $languages = Language::all();
        $tags = Tag::all();
        $batchSize = 1000; // Insert 1000 records at a time
        $totalRecords = 100000;
        $currentBatch = [];
        $tagRelations = [];
        $usedKeysPerLanguage = []; // Track used keys per language to ensure uniqueness

        $this->command->info('Starting translation seeding...');
        $this->command->getOutput()->progressStart($totalRecords);

        for ($i = 0; $i < $totalRecords; $i++) {
            $language = $languages->random();
            $languageId = $language->id;

            // Generate a unique key for this language
            $baseKey = 'key_' . floor($i / count($languages));
            $key = $baseKey;
            $counter = 1;

            // Ensure uniqueness for this language_id
            while (isset($usedKeysPerLanguage[$languageId][$key])) {
                $key = $baseKey . '_' . $counter++;
            }
            $usedKeysPerLanguage[$languageId][$key] = true;

            $now = now()->toDateTimeString();

            $currentBatch[] = [
                'key' => $key,
                'value' => "Translation value {$key} in {$language->code}",
                'language_id' => $languageId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Insert batch when it reaches the batch size or it's the last record
            if (count($currentBatch) === $batchSize || $i === $totalRecords - 1) {
                try {
                    // Insert the batch
                    DB::table(app(Translation::class)->getTable())->insert($currentBatch);

                    // Get the inserted IDs
                    $insertedIds = DB::table(app(Translation::class)->getTable())
                        ->whereIn('created_at', array_column($currentBatch, 'created_at'))
                        ->whereIn('key', array_column($currentBatch, 'key'))
                        ->pluck('id')
                        ->toArray();

                    if (count($insertedIds) !== count($currentBatch)) {
                        $this->command->warn('Some records may not have been inserted due to duplicates or errors.');
                    }

                    // Create tag relations for the batch
                    foreach ($insertedIds as $translationId) {
                        $randomTags = $tags->random(rand(1, 3));
                        foreach ($randomTags as $tag) {
                            $tagRelations[] = [
                                'translation_id' => $translationId,
                                'tag_id' => $tag->id,
                            ];
                        }
                    }

                    // Insert tag relations in batches
                    if (count($tagRelations) >= $batchSize) {
                        DB::table('translation_tag')->insert($tagRelations);
                        $tagRelations = [];
                    }

                    $this->command->getOutput()->progressAdvance(count($currentBatch));
                    $currentBatch = [];
                } catch (\Illuminate\Database\QueryException $e) {
                    $this->command->error("Batch insertion failed: " . $e->getMessage());
                    // Optionally, log the failed batch for debugging
                    \Log::error('Failed batch:', $currentBatch);
                    $currentBatch = [];
                }
            }
        }

        // Insert any remaining tag relations
        if (!blank($tagRelations)) {
            DB::table('translation_tag')->insert($tagRelations);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('Translations seeded successfully!');
    }
}
