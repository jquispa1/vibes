<?php

namespace App\Console\Commands;

use Common\Localizations\Localization;
use Common\Localizations\LocalizationsRepository;
use Illuminate\Console\Command;

class SyncLocalizationLines extends Command
{
    protected $signature = 'translations:sync';

    protected $description = 'Merge default translation lines into all existing localizations.';

    public function __construct(protected LocalizationsRepository $localizations)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $defaultLines = $this->localizations->getDefaultTranslationLines();

        Localization::all()->each(function (Localization $localization) use ($defaultLines) {
            $this->localizations->storeLocalizationLines(
                $localization,
                array_merge(
                    $defaultLines,
                    $this->localizations->getLocalizationLines($localization),
                ),
            );
        });

        $this->info('Localization lines synchronized');

        return self::SUCCESS;
    }
}
