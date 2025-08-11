<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function run(Request $request): JsonResponse
    {
        $onlyDb = filter_var($request->query('only_db'), FILTER_VALIDATE_BOOLEAN);
        $command = $onlyDb ? 'backup:run --only-db' : 'backup:run';

        Artisan::call($command);
        $output = Artisan::output();

        return response()->json([
            'message' => 'Backup executed',
            'output' => $output,
        ]);
    }

    public function index(): JsonResponse
    {
        $disk = Storage::disk('local');
        $files = collect($disk->allFiles())
            ->filter(function (string $path): bool {
                return str_ends_with($path, '.zip') || str_ends_with($path, '.sql');
            })
            ->map(function (string $path) use ($disk) {
                return [
                    'filename' => basename($path),
                    'path' => $path,
                    'size' => $disk->size($path),
                    'last_modified' => date('c', $disk->lastModified($path)),
                ];
            })
            ->values();

        return response()->json(['data' => $files]);
    }

    public function download(string $filename)
    {
        $disk = Storage::disk('local');
        $path = collect($disk->allFiles())
            ->first(function (string $path) use ($filename): bool {
                return basename($path) === $filename;
            });

        abort_unless($path, 404, 'Backup file not found');
        return $disk->download($path);
    }
}
