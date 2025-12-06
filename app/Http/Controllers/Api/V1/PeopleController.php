<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\People;
use App\Services\PeopleService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="People",
 *     description="Operations related to people (recommendations, like/unlike, liked list)"
 * )
 */
class PeopleController extends Controller
{
    public function __construct(private PeopleService $service) {}

    /**
     * List recommended people.
     *
     * @OA\Get(
     *     path="/api/v1/people",
     *     summary="List recommended people",
     *     tags={"People"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of recommended people",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="likes_count", type="integer")
     *             )),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json($this->service->listRecommended($perPage));
    }

    /**
     * Like a person.
     *
     * @OA\Post(
     *     path="/api/v1/people/{people}/like",
     *     summary="Like a person",
     *     tags={"People"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="people",
     *         in="path",
     *         description="People ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Like result",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="liked", type="boolean"),
     *             @OA\Property(property="likes_count", type="integer")
     *         )
     *     ),
     * )
     */
    public function like(Request $request, People $people)
    {
        $user = $request->user();
        $result = $this->service->like($people, $user);

        return response()->json($result);
    }

    /**
     * Dislike / remove like from a person.
     *
     * @OA\Post(
     *     path="/api/v1/people/{people}/dislike",
     *     summary="Dislike a person (remove like)",
     *     tags={"People"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="people",
     *         in="path",
     *         description="People ID",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dislike result",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="liked", type="boolean"),
     *             @OA\Property(property="likes_count", type="integer")
     *         )
     *     ),
     * )
     */
    public function dislike(Request $request, People $people)
    {
        $user = $request->user();
        $result = $this->service->dislike($people, $user);

        return response()->json($result);
    }

    /**
     * Get list of people liked by the authenticated user.
     *
     * @OA\Get(
     *     path="/api/v1/people/liked",
     *     summary="List of people liked by the authenticated user",
     *     tags={"People"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of liked people",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="likes_count", type="integer")
     *             )),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     * )
     */
    public function likedList(Request $request)
    {
        $user = $request->user();

        return response()->json($this->service->likedList($user));
    }
}
