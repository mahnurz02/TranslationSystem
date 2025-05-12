<?php

namespace App\Http\Controllers\Api;

use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

/**
 * @OA\Tag(
 *     name="Translations",
 *     description="API Endpoints for managing translations"
 * )
 */
class TranslationController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/translations/{locale}",
     *     summary="Get paginated translations by locale",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="locale",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of translations"
     *     )
     * )
     */
    public function index(Request $request, string $locale)
    {
        $translations = cache()->remember(
            "translations_{$locale}_page_{$request->page}",
            60,
            function () use ($locale, $request) {
                return Translation::where('locale', $locale)
                    ->orderBy('id')
                    ->paginate(50, ['id', 'locale', 'key', 'context', 'value']);
            }
        );

        return response()->json($translations);
    }

    /**
     * @OA\Post(
     *     path="/api/translations",
     *     summary="Create or update a translation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key", "locale", "value", "context"},
     *             @OA\Property(property="key", type="string"),
     *             @OA\Property(property="locale", type="string"),
     *             @OA\Property(property="value", type="string"),
     *             @OA\Property(property="context", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation stored or updated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'locale' => 'required|string',
            'value' => 'required|string',
            'context' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $translation = Translation::updateOrCreate(
            ['key' => $validated['key'], 'locale' => $validated['locale']],
            ['value' => $validated['value'], 'context' => $validated['context']]
        );

        cache()->forget("translations_{$validated['locale']}");

        return response()->json([
            'message' => 'Success',
            'data' => $translation,
        ], 200);
    }

    
    /**
     * @OA\Delete(
     *     path="/api/translations/{id}",
     *     summary="Delete a translation",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},    
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation deleted"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $translation = Translation::findOrFail($id);
        $locale = $translation->locale;

        if ($translation->delete()) {
            cache()->forget("translations_{$locale}");
        }

        return response()->json(['message' => 'Deleted'], 200);
    }

    
    /**
     * @OA\Get(
     *     path="/api/translations/search",
     *     summary="Search translations by key, locale, or context",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},    
     *     @OA\Parameter(name="key", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="locale", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="context", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Search results"
     *     )
     * )
     */
    public function search(Request $request)
    {
        $query = Translation::query();

        $query->when($request->filled('key'), fn ($q) =>
            $q->where('key', 'like', '%' . $request->key . '%')
        );

        $query->when($request->filled('locale'), fn ($q) =>
            $q->where('locale', 'like', '%' . $request->locale . '%')
        );

        $query->when($request->filled('context'), fn ($q) =>
            $q->where('context', $request->context)
        );

        $translations = $query->orderBy('updated_at', 'desc')->paginate(20);

        return response()->json($translations);
    }

     /**
     * @OA\Get(
     *     path="/api/translations/export",
     *     summary="Export translations as structured JSON",
     *     tags={"Translations"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="locale", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Exported translation data"
     *     )
     * )
     */
    public function export(Request $request)
    {
        $translations = Translation::whereNull('deleted_at')
            ->when($request->filled('locale'), fn ($q) =>
                $q->where('locale', $request->locale)
            )
            ->orderBy('id')
            ->paginate(50);

        $output = [];

        foreach ($translations->items() as $t) {
            $output[$t->locale][$t->key] = $t->value;
        }

        return response()->json([
            'data' => $output,
            'pagination' => [
                'total' => $translations->total(),
                'per_page' => $translations->perPage(),
                'current_page' => $translations->currentPage(),
                'last_page' => $translations->lastPage(),
                'next_page_url' => $translations->nextPageUrl(),
                'prev_page_url' => $translations->previousPageUrl(),
            ],
        ]);
    }
}
