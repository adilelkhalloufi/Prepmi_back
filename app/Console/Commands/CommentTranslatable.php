<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CommentTranslatable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translatable:toggle 
                            {action : "comment" to comment or "decomment" to uncomment the translatable property} 
                            {model? : The specific model to target (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comments or decomments the $translatable property in one or all models';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the action and optional model parameters
        $action = $this->argument('action');
        $model = $this->argument('model'); // e.g., "Product"

        // Validate the action
        if (! in_array($action, ['comment', 'decomment'])) {
            $this->error('Invalid action. Use "comment" or "decomment".');

            return 1;
        }

        // Determine the files to process
        $files = [];
        if ($model) {
            // Target a specific model
            $filePath = app_path("Models/{$model}.php");
            if (File::exists($filePath)) {
                $files[] = $filePath;
            } else {
                $this->error("Model file '{$model}.php' not found in app/Models.");

                return 1;
            }
        } else {
            // Target all models
            $files = File::allFiles(app_path('Models'));
        }

        foreach ($files as $file) {
            $filePath = is_string($file) ? $file : $file->getPathname();
            $content = File::get($filePath);

            if ($action === 'comment') {
                // Comment out the $translatable property with /* */
                $updatedContent = preg_replace(
                    '/^\s*public\s+\$translatable\s*=\s*\[.*\];$/m',
                    '/*$0*/',
                    $content
                );

                if ($updatedContent !== $content) {
                    File::put($filePath, $updatedContent);
                    $this->info("Commented out the translatable property in $filePath");
                }
            } elseif ($action === 'decomment') {
                // Decomment the $translatable property by removing /* */
                $updatedContent = preg_replace(
                    '/\/\*(\s*public\s+\$translatable\s*=\s*\[.*\];)\*\//m',
                    '$1',
                    $content
                );

                if ($updatedContent !== $content) {
                    File::put($filePath, $updatedContent);
                    $this->info("Decommented the translatable property in $filePath");
                }
            }
        }

        $this->info('Command completed.');

        return 0;
    }
}
