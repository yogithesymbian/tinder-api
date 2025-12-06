<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeStructure extends Command
{
    protected $signature = 'make:structure {name : The name of the module (e.g. Person)}';
    protected $description = 'Create Controller (V1), Service, Repository, and Interface';

    public function handle()
    {
        $name = $this->argument('name');

        // 1. Interface
        $this->generateFile(
            "app/Repositories/Contracts/{$name}RepositoryInterface.php",
            "App\\Repositories\\Contracts",
            "interface {$name}RepositoryInterface \n{\n    public function getAll(); \n}"
        );

        // 2. Repository
        $this->generateFile(
            "app/Repositories/Eloquent/{$name}Repository.php",
            "App\\Repositories\\Eloquent",
            "use App\\Repositories\\Contracts\\{$name}RepositoryInterface;\n\nclass {$name}Repository implements {$name}RepositoryInterface \n{\n    public function getAll() { return []; }\n}"
        );

        // 3. Service
        $this->generateFile(
            "app/Services/{$name}Service.php",
            "App\\Services",
            "use App\\Repositories\\Contracts\\{$name}RepositoryInterface;\n\nclass {$name}Service \n{\n    public function __construct(protected {$name}RepositoryInterface \$repo) {}\n}"
        );

        // 4. Controller (V1)
        $this->generateFile(
            "app/Http/Controllers/Api/V1/{$name}Controller.php",
            "App\\Http\\Controllers\\Api\\V1",
            "use App\\Http\\Controllers\\Controller;\nuse App\\Services\\{$name}Service;\n\nclass {$name}Controller extends Controller \n{\n    public function __construct(protected {$name}Service \$service) {}\n}"
        );

        $this->info("Structure for {$name} created successfully!");
        $this->warn("Don't forget to bind the Repository in your AppServiceProvider!");
    }

    protected function generateFile($path, $namespace, $content)
    {
        $fullPath = base_path($path);
        $dir = dirname($fullPath);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (File::exists($fullPath)) {
            $this->error("File already exists: {$path}");
            return;
        }

        $stub = "<?php\n\nnamespace {$namespace};\n\n{$content}\n";
        File::put($fullPath, $stub);
        $this->info("Created: {$path}");
    }
}