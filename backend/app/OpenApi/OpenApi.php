<?php

namespace App\OpenApi;

/**
 * @OA\OpenApi(
 *   @OA\Info(title="Singre API", version="1.0.0"),
 *   @OA\Server(url="/", description="App server")
 * )
 * @OA\Tag(name="Backups", description="Backup operations")
 *
 * @OA\Post(
 *   path="/api/backups/run",
 *   tags={"Backups"},
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(name="only_db", in="query", @OA\Schema(type="boolean")),
 *   @OA\Response(response=200, description="Backup executed")
 * )
 *
 * @OA\Get(
 *   path="/api/backups",
 *   tags={"Backups"},
 *   security={{"bearerAuth":{}}},
 *   @OA\Response(response=200, description="List backups")
 * )
 *
 * @OA\Get(
 *   path="/api/backups/{filename}/download",
 *   tags={"Backups"},
 *   security={{"bearerAuth":{}}},
 *   @OA\Parameter(name="filename", in="path", required=true, @OA\Schema(type="string")),
 *   @OA\Response(response=200, description="Download backup")
 * )
 */
class OpenApi {}
