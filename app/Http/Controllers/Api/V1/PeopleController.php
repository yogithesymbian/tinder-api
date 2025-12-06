<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\People;
use App\Services\PeopleService;
use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function __construct(private PeopleService $service) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json($this->service->listRecommended($perPage));
    }

    public function like(Request $request, People $people)
    {
        $user = $request->user();
        $result = $this->service->like($people, $user);

        return response()->json($result);
    }

    public function dislike(Request $request, People $people)
    {
        $user = $request->user();
        $result = $this->service->dislike($people, $user);

        return response()->json($result);
    }

    public function likedList(Request $request)
    {
        $user = $request->user();

        return response()->json($this->service->likedList($user));
    }
}
