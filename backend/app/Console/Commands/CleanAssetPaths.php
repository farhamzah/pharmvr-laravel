<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;
use App\Models\EducationContent;
use App\Models\TrainingModule;
use App\Models\UserProfile;
use App\Models\AiAvatarProfile;
use App\Services\AssetUrlService;
use Illuminate\Support\Facades\DB;

class CleanAssetPaths extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Normalize absolute asset URLs into clean, relative paths for database portability.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Asset Path Normalization...');

        DB::transaction(function () {
            // 1. News - Correct column: image_url
            $this->processModel(News::class, 'image_url', 'News');

            // 2. EducationContent - Correct column: thumbnail_url
            $this->processModel(EducationContent::class, 'thumbnail_url', 'EducationContent');

            // 3. TrainingModule - Correct column: cover_image_path
            $this->processModel(TrainingModule::class, 'cover_image_path', 'TrainingModule');

            // 4. UserProfile - Correct column: avatar_url
            $this->processModel(UserProfile::class, 'avatar_url', 'UserProfile');

            // 5. AiAvatarProfile - Correct column: avatar_model_path
            $this->processModel(AiAvatarProfile::class, 'avatar_model_path', 'AiAvatarProfile');
        });

        $this->info('Successfully normalized all asset paths!');
        return self::SUCCESS;
    }

    /**
     * Process each model and normalize its asset field.
     */
    private function processModel($modelClass, $field, $label)
    {
        $items = $modelClass::whereNotNull($field)->get();
        $count = 0;

        foreach ($items as $item) {
            $currentValue = $item->{$field};
            $normalizedValue = AssetUrlService::normalize($currentValue);

            if ($currentValue !== $normalizedValue) {
                $item->{$field} = $normalizedValue;
                $item->save();
                $count++;
            }
        }

        if ($count > 0) {
            $this->line(" - Normalized $count records in $label.");
        } else {
            $this->line(" - $label is already clean.");
        }
    }
}
