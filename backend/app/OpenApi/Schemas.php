<?php

namespace App\OpenApi;

/**
 * @OA\Schema(
 *   schema="Client",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="email", type="string"),
 *   @OA\Property(property="phone", type="string"),
 *   @OA\Property(property="document", type="string"),
 *   @OA\Property(property="address", type="string"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 * )
 *
 * @OA\Schema(
 *   schema="Product",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="sku", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="price", type="number", format="float"),
 *   @OA\Property(property="stock", type="integer"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 * )
 *
 * @OA\Schema(
 *   schema="Service",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="price", type="number", format="float"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 * )
 *
 * @OA\Schema(
 *   schema="ServiceOrder",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="client_id", type="integer"),
 *   @OA\Property(property="status", type="string"),
 *   @OA\Property(property="total", type="number", format="float"),
 *   @OA\Property(property="notes", type="string"),
 *   @OA\Property(property="opened_at", type="string", format="date-time"),
 *   @OA\Property(property="closed_at", type="string", format="date-time", nullable=true),
 * )
 *
 * @OA\Schema(
 *   schema="Setting",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="key", type="string"),
 *   @OA\Property(property="value", type="string"),
 * )
 */
class Schemas {}
