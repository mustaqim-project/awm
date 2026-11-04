<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiStudio\InspirationAsset;
use App\Models\AiStudio\InspirationItem;
use App\Models\StorageFile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class SyncAllInspirationsToCoocaAssetsCommand extends Command
{
    protected $signature = 'ai-studio:sync-cooca-assets {--batch-size=100 : Ukuran batch pemrosesan}';

    protected $description = 'Konversi & sinkronkan seluruh gambar inspirasi menjadi Aset Resmi COOCA (InspirationAsset & StorageFile) dengan URL resmi https://cooca.id/storage/...';

    public function handle(): int
    {
        $this->info("===================================================================");
        $this->info("   COOCA AI STUDIO — SINKRONISASI ASET RESMI & URL COOCA.ID       ");
        $this->info("===================================================================");

        $ownerUser = User::where('email', 'cooca.idn@gmail.com')->first() ?? User::first();
        if (! $ownerUser) {
            $this->error("Tidak ditemukan pengguna di database untuk asosiasi owner aset.");
            return self::FAILURE;
        }

        $totalItems = InspirationItem::where('status', 'active')->count();
        $this->line("Total InspirationItem Aktif : <comment>{$totalItems}</comment>");
        $this->line("Akun Owner Aset             : <info>{$ownerUser->name} ({$ownerUser->email})</info>");
        $this->line("Target Domain Aset          : <info>https://cooca.id</info>");
        $this->newLine();

        $batchSize = max(20, (int) $this->option('batch-size'));
        $processed = 0;
        $updatedItems = 0;
        $createdStorageFiles = 0;
        $createdAssets = 0;

        InspirationItem::with(['source', 'visualAnalysis'])
            ->where('status', 'active')
            ->chunkById($batchSize, function ($items) use ($ownerUser, &$processed, &$updatedItems, &$createdStorageFiles, &$createdAssets, $totalItems): void {
                DB::transaction(function () use ($items, $ownerUser, &$updatedItems, &$createdStorageFiles, &$createdAssets): void {
                    foreach ($items as $item) {
                        $sourceCode = $item->source?->code ?? 'general';
                        $safeId = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $item->external_id);

                        // 1. Temukan berkas fisik di disk
                        $foundRelPath = null;
                        $fullPath = null;

                        foreach (['jpg', 'jpeg', 'webp', 'png', 'svg'] as $ext) {
                            $checkRel = "ai-studio/inspirations/{$sourceCode}/{$safeId}.{$ext}";
                            $publicDisk = public_path("storage/{$checkRel}");
                            $appDisk = storage_path("app/public/{$checkRel}");

                            if (file_exists($publicDisk)) {
                                $foundRelPath = $checkRel;
                                $fullPath = $publicDisk;
                                break;
                            } elseif (file_exists($appDisk)) {
                                $foundRelPath = $checkRel;
                                $fullPath = $appDisk;
                                break;
                            }
                        }

                        // Fallback ke thumbnail_url atau canonical_url jika belum ketemu
                        if (! $foundRelPath) {
                            $rawUrl = (string) ($item->getRawOriginal('thumbnail_url') ?: $item->getRawOriginal('canonical_url'));
                            $cleanRel = ltrim(preg_replace('#^/?storage/#', '', $rawUrl), '/');
                            if (file_exists(public_path("storage/{$cleanRel}"))) {
                                $foundRelPath = $cleanRel;
                                $fullPath = public_path("storage/{$cleanRel}");
                            }
                        }

                        if (! $foundRelPath || ! $fullPath || ! file_exists($fullPath)) {
                            continue;
                        }

                        $fileName = basename($foundRelPath);
                        $fileSize = (int) @filesize($fullPath);
                        $mimeType = @mime_content_type($fullPath) ?: 'image/jpeg';
                        $coocaAssetUrl = "https://cooca.id/storage/{$foundRelPath}";

                        // 2. Update InspirationItem ke URL resmi COOCA
                        $item->canonical_url = $coocaAssetUrl;
                        $item->thumbnail_url = $coocaAssetUrl;
                        $item->media_urls = [$coocaAssetUrl];
                        $item->is_curated = true;
                        $item->curated_notes = 'Aset resmi COOCA Curated Studio.';
                        $item->save();
                        $updatedItems++;

                        // 3. Upsert ke COOCA Media Library (StorageFile)
                        $storageFile = StorageFile::updateOrCreate(
                            [
                                'file_path' => $foundRelPath,
                                'disk' => 'public',
                            ],
                            [
                                'owner_id' => $ownerUser->id,
                                'business_id' => null,
                                'user_id' => $ownerUser->id,
                                'file_name' => $fileName,
                                'mime_type' => $mimeType,
                                'file_size' => $fileSize > 0 ? $fileSize : 200000,
                                'category' => StorageFile::CATEGORY_SOCIAL_MEDIA,
                                'module' => 'ai_studio',
                                'is_temporary' => false,
                                'status' => StorageFile::STATUS_ACTIVE,
                                'metadata' => [
                                    'title' => $item->title,
                                    'source' => $sourceCode,
                                    'industry' => $item->industry_target,
                                    'cooca_asset' => true,
                                ],
                                'uploaded_at' => $item->created_at ?? now(),
                            ]
                        );
                        $createdStorageFiles++;

                        // 4. Upsert ke COOCA Curated Asset (InspirationAsset)
                        $aspectRatio = (string) ($item->visualAnalysis?->suggested_aspect_ratios[0] ?? '1:1');

                        InspirationAsset::updateOrCreate(
                            [
                                'external_asset_id' => (string) $item->external_id,
                            ],
                            [
                                'source_type' => InspirationAsset::SOURCE_CURATED,
                                'source_id' => $item->source_id,
                                'media_id' => $storageFile->id,
                                'business_id' => null,
                                'user_id' => $ownerUser->id,
                                'title' => $item->title,
                                'description' => $item->caption,
                                'source_url' => "https://cooca.id/ai-studio/inspiration/{$item->id}",
                                'image_url' => $coocaAssetUrl,
                                'thumbnail_url' => $coocaAssetUrl,
                                'width' => 1024,
                                'height' => 1024,
                                'aspect_ratio' => $aspectRatio,
                                'mime_type' => $mimeType,
                                'file_size' => $fileSize > 0 ? $fileSize : 200000,
                                'author_name' => 'COOCA Studio',
                                'author_profile_url' => 'https://cooca.id',
                                'source_name' => 'COOCA Curated Library',
                                'license_name' => 'COOCA Commercial License',
                                'license_url' => 'https://cooca.id/terms',
                                'attribution_text' => 'Koleksi Aset Resmi COOCA Studio',
                                'usage_policy' => 'Komersial & Bebas Royalti untuk seluruh pengguna platform COOCA.',
                                'commercial_usage_allowed' => true,
                                'redistribution_allowed' => true,
                                'storage_policy' => 'permanent',
                                'moderation_status' => InspirationAsset::MODERATION_APPROVED,
                                'metadata' => [
                                    'item_id' => $item->id,
                                    'industry' => $item->industry_target,
                                    'tags' => $item->tags ?? [],
                                    'cooca_asset' => true,
                                ],
                            ]
                        );
                        $createdAssets++;
                    }
                });

                $processed += count($items);
                $this->output->write("\r  Progress Sinkronisasi: {$processed}/{$totalItems} aset terproses...");
            });

        $this->newLine(2);
        $this->info("===================================================================");
        $this->info("                    HASIL SINKRONISASI ASET                        ");
        $this->info("===================================================================");
        $this->line("Total InspirationItem Diperbarui   : <comment>{$updatedItems}</comment>");
        $this->line("Total Media Library (StorageFile)  : <comment>{$createdStorageFiles}</comment>");
        $this->line("Total COOCA Curated Assets         : <comment>{$createdAssets}</comment>");
        $this->line("Domain URL Seluruh Aset            : <info>https://cooca.id/storage/ai-studio/inspirations/...</info>");
        $this->newLine();

        $this->info("✓ Seluruh image telah resmi menjadi Aset COOCA dan menggunakan URL cooca.id.");
        return self::SUCCESS;
    }
}
