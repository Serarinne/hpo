<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\BunnyStorage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CharacterController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $ratings = $request->input('rating', []);
        $seos = $request->input('seo', []);
        $workflows = $request->input('workflow', []);
        $hasWallpapers = $request->input('has_wallpaper', []);
        $hasSeries = $request->input('has_series', []);

        $characters = Character::query()
            ->when($search !== '', fn ($q) => $q->search($search))
            ->when(!empty($ratings), fn ($q) => $q->whereIn('rating', $ratings))
            ->when(count($seos) === 1, function (Builder $q) use ($seos) {
                if (in_array('filled', $seos)) {
                    $q->whereNotNull('seo_title')->where('seo_title', '!=', '');
                } elseif (in_array('empty', $seos)) {
                    $q->where(function (Builder $sub) {
                        $sub->whereNull('seo_title')->orWhere('seo_title', '');
                    });
                }
            })
            ->when(!empty($workflows), fn ($q) => $q->whereIn('debug', $workflows))
            ->when(count($hasWallpapers) === 1, function (Builder $q) use ($hasWallpapers) {
                if (in_array('yes', $hasWallpapers)) {
                    $q->has('wallpapers');
                } elseif (in_array('no', $hasWallpapers)) {
                    $q->doesntHave('wallpapers');
                }
            })
            ->when(count($hasSeries) === 1, function (Builder $q) use ($hasSeries) {
                if (in_array('yes', $hasSeries)) {
                    $q->has('series');
                } elseif (in_array('no', $hasSeries)) {
                    $q->doesntHave('series');
                }
            })
            ->latest('characters.created_at')
            ->paginate(32)
            ->withQueryString();

        return view('characters.index', compact('characters', 'search'));
    }

    public function edit($id)
    {
        $character = Character::with(['series', 'parents', 'apiTags'])->findOrFail($id);
        
        $preloadSeries = $character->series->map(fn($s) => ['id' => $s->id, 'text' => $s->name]);
        $preloadParents = $character->parents->map(fn($p) => ['id' => $p->id, 'text' => $p->name]);

        return view('characters.edit', compact('character', 'preloadSeries', 'preloadParents'));
    }

    public function update(Request $request, $id)
    {
        $character = Character::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'slug' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('characters')->ignore($character->id),
            ],
            'keywords' => 'nullable|string',
            'description' => 'nullable|string',
            'series' => 'array',
            'relationships' => 'nullable|array',
            'relationships.*.parent_id' => 'required|exists:characters,id',
            'relationships.*.relation_type' => 'nullable|string|max:255',
            'api_tags' => 'nullable|array',
            'api_tags.*.source_api' => 'required_with:api_tags|string',
            'api_tags.*.tag_name' => 'required_with:api_tags|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'rating' => 'required|in:general,sensitive,questionable,explicit,unknown',
            'debug' => 'nullable|boolean',
        ]);

        $bunny = $this->getBunnyStorage();
        if (!$bunny) {
            return response()->json([
                'success' => false,
                'message' => 'System configuration error (BunnyCDN).'
            ], 500);
        }

        DB::beginTransaction();

        $newImagePath = null;
        $oldImagePath = $character->image;

        try {
            if ($request->hasFile('image')) {
                if (!$request->file('image')->isValid()) {
                    throw new \Exception('Invalid image file or exceeds maximum server upload size.');
                }
                
                $newImagePath = $this->handleImageUpload($character->id, $request->file('image'), $bunny);
                $character->image = $newImagePath;
            }

            $character->fill([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['slug'] ?? $validated['name']),
                'keywords' => $validated['keywords'] ?? null,
                'description' => $validated['description'] ?? null,
                'seo_keywords' => $validated['meta_keywords'] ?? null,
                'rating' => $validated['rating'],
                'debug' => $request->boolean('debug'),
                'seo_title' => $validated['meta_title'] ?? null,
                'seo_description' => $validated['meta_description'] ?? null,
            ]);
            
            $character->save();

            $character->series()->sync($request->input('series', []));
            
            $syncData = [];
            if ($request->has('relationships')) {
                foreach ($request->relationships as $rel) {
                    if (!empty($rel['parent_id'])) {
                        $syncData[$rel['parent_id']] = [
                            'relation_type' => $rel['relation_type'] ?? 'variant'
                        ];
                    }
                }
            }
            $character->parents()->sync($syncData);

            $character->apiTags()->delete();
            if (!empty($validated['api_tags'])) {
                $filteredTags = array_filter($validated['api_tags'], function ($tag) {
                    return !empty($tag['source_api']) && !empty($tag['tag_name']);
                });
                if (!empty($filteredTags)) {
                    $character->apiTags()->createMany(array_values($filteredTags));
                }
            }

            DB::commit();

            if ($request->hasFile('image') && $oldImagePath && $bunny) {
                try {
                    if (is_array($oldImagePath)) {
                        $oldWebp = $oldImagePath['webp'] ?? null;
                        $oldJpg = $oldImagePath['jpg'] ?? null;

                        if ($oldWebp) {
                            $oldWebpClean = str_replace('/storage/', '', $oldWebp);
                            $bunny->delete($oldWebpClean);
                            
                            if (!$oldJpg) {
                                $oldJpgFallback = preg_replace('/\.(webp)$/i', '.jpg', $oldWebpClean);
                                if ($oldJpgFallback !== $oldWebpClean) {
                                    try { $bunny->delete($oldJpgFallback); } catch (\Exception $ex) {}
                                }
                            }
                        }

                        if ($oldJpg) {
                            $oldJpgClean = str_replace('/storage/', '', $oldJpg);
                            $bunny->delete($oldJpgClean);
                        }
                    } else {
                        $bunny->delete($oldImagePath . '.webp');
                        $bunny->delete($oldImagePath . '.jpg');
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old character image from BunnyCDN: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Character updated successfully.',
                'new_image' => $character->image_url, 
                'updated_at' => $character->updated_at->format('d M Y'),
                'slug' => $character->slug
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($newImagePath && $bunny) {
                try {
                    $bunny->delete($newImagePath . '.webp');
                    $bunny->delete($newImagePath . '.jpg');
                } catch (\Exception $deleteEx) {
                    Log::critical('Failed to rollback orphaned new character image in BunnyCDN: ' . $deleteEx->getMessage());
                }
            }

            Log::error('Character Update Error: ' . $e->getMessage(), [
                'character_id' => $character->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        $character = Character::findOrFail($id);
        $characterName = $character->name;
        $character->delete();

        return redirect()->route('characters.index')
            ->with('success', 'Character ' . $characterName . ' and its relationships have been permanently deleted.');
    }

    public function toggleDebug(Request $request, $id)
    {
        $character = Character::findOrFail($id);
        $character->debug = $request->boolean('debug');
        $character->save();

        return response()->json([
            'success' => true,
            'message' => 'Debug status updated successfully.',
            'rating' => $character->rating
        ]);
    }

    public function updateRating(Request $request, $id)
    {
        $character = Character::findOrFail($id);
        $character->rating = $request->rating;
        $character->save();

        return response()->json([
            'success' => true,
            'message' => 'Rating updated successfully.',
            'rating' => $character->rating
        ]);
    }

    public function list(Request $request)
    {
        $search = $request->get('q');

        $data = Character::query()
            ->where('name', 'like', "%{$search}%")
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->name,
                ];
            });

        return response()->json($data);
    }

    private function getBunnyStorage()
    {
        $bunnyConfig = config("services.bunny");

        if (!$bunnyConfig || empty($bunnyConfig['api_key'])) {
            Log::error("BunnyCDN API Key not found for app");
            return null;
        }

        return new BunnyStorage(
            $bunnyConfig['storage_name'] ?? $bunnyConfig['storage_zone'], 
            $bunnyConfig['api_key'],
            $bunnyConfig['region'] ?? null
        );
    }

    private function handleImageUpload($characterId, $file, BunnyStorage $bunny)
    {
        $paddedId = str_pad($characterId, 9, '0', STR_PAD_LEFT);
        $shardPath = implode('/', str_split($paddedId, 3)); 
        $basePath = "characters/{$shardPath}/{$characterId}";

        $targetSize = 600;

        $webpContent = $this->resizeAndCropToSquare($file, $targetSize, 'webp');
        $bunny->upload($basePath . '.webp', $webpContent);

        $jpgContent = $this->resizeAndCropToSquare($file, $targetSize, 'jpg');
        $bunny->upload($basePath . '.jpg', $jpgContent);

        return $basePath;
    }

    private function resizeAndCropToSquare($file, $targetSize, $format = 'webp')
    {
        $filePath = $file->getPathname();
        
        if (empty($filePath)) {
            throw new \Exception("Image file path is empty. Ensure the file uploaded correctly.");
        }

        list($origWidth, $origHeight, $type) = getimagesize($filePath);
        
        switch ($type) {
            case IMAGETYPE_JPEG: 
                $source = imagecreatefromjpeg($filePath); 
                break;
            case IMAGETYPE_PNG: 
                $source = imagecreatefrompng($filePath); 
                break;
            case IMAGETYPE_WEBP: 
                $source = imagecreatefromwebp($filePath); 
                break;
            default: 
                throw new \Exception("Unsupported image type.");
        }

        $minDim = min($origWidth, $origHeight);
        
        $srcX = ($origWidth - $minDim) / 2;
        $srcY = ($origHeight - $minDim) / 2;

        $destination = imagecreatetruecolor($targetSize, $targetSize);

        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $targetSize, $targetSize, $transparent);

        imagecopyresampled(
            $destination, $source,
            0, 0, $srcX, $srcY,
            $targetSize, $targetSize,
            $minDim, $minDim
        );

        ob_start();
        if ($format === 'jpg' || $format === 'jpeg') {
            $whiteBg = imagecreatetruecolor($targetSize, $targetSize);
            imagefill($whiteBg, 0, 0, imagecolorallocate($whiteBg, 255, 255, 255));
            imagecopy($whiteBg, $destination, 0, 0, 0, 0, $targetSize, $targetSize);
            imagejpeg($whiteBg, null, 90); 
            imagedestroy($whiteBg);
        } else {
            imagewebp($destination, null, 90); 
        }
        $content = ob_get_clean();

        imagedestroy($source);
        imagedestroy($destination);

        return $content;
    }
}