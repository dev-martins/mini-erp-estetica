<?php

namespace App\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Laravel\Passport\Console\InstallCommand as BaseInstallCommand;

class PassportInstallCommand extends BaseInstallCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:install
                            {--force : Overwrite keys they already exist}
                            {--length=4096 : The length of the private key}
                            {--publish-migrations : Force publishing Passport migration stubs even if they exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare Passport for use without duplicating migration stubs.';

    /**
     * Required Passport migration stub base names.
     *
     * @var array<int, string>
     */
    protected array $migrationStubs = [
        'create_oauth_auth_codes_table',
        'create_oauth_access_tokens_table',
        'create_oauth_refresh_tokens_table',
        'create_oauth_clients_table',
        'create_oauth_device_codes_table',
    ];

    public function __construct(protected Filesystem $filesystem)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('passport:keys', [
            '--force' => $this->option('force'),
            '--length' => $this->option('length'),
        ]);

        $this->call('vendor:publish', $this->buildPublishConfigOptions());

        if ($this->shouldPublishMigrations()) {
            $this->call('vendor:publish', ['--tag' => 'passport-migrations']);
        } else {
            $this->components->info('Passport migrations already exist; skipping publication.');
        }

        if (! $this->components->confirm('Would you like to run all pending database migrations?', true)) {
            return;
        }

        $this->call('migrate');

        if (! $this->components->confirm('Would you like to create the "personal access" grant client?', true)) {
            return;
        }

        $this->call('passport:client', [
            '--personal' => true,
            '--name' => config('app.name'),
        ]);
    }

    /**
     * Determine if migrations should be published.
     */
    protected function shouldPublishMigrations(): bool
    {
        if ($this->option('publish-migrations')) {
            return true;
        }

        return ! $this->passportMigrationsPublished();
    }

    /**
     * Build the publish options array for the config files.
     */
    protected function buildPublishConfigOptions(): array
    {
        $options = ['--tag' => 'passport-config'];

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        return $options;
    }

    /**
     * Check whether all Passport migrations are already present in database/migrations.
     */
    protected function passportMigrationsPublished(): bool
    {
        return $this->availableMigrationFiles()
            ->map(static fn (\SplFileInfo $file): string => $file->getFilename())
            ->pipe(function (Collection $filenames): bool {
                return collect($this->migrationStubs)->every(
                    fn (string $stub): bool => $filenames->contains(
                        fn (string $filename): bool => str_contains($filename, $stub)
                    )
                );
            });
    }

    /**
     * Get a collection of existing migration SplFileInfo objects.
     *
     * @return \Illuminate\Support\Collection<int, \SplFileInfo>
     */
    protected function availableMigrationFiles(): Collection
    {
        /** @var array<int, \SplFileInfo> $files */
        $files = $this->filesystem->files(database_path('migrations'));

        return collect($files);
    }
}
