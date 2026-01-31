<?php

namespace App\Http\Controllers;

use App\Services\CollaborationService;
use Illuminate\Http\Request;

class CollaborationController extends Controller
{
    protected $service;

    public function __construct(CollaborationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string',
            'social_url_1' => 'required|string',
            'social_url_2' => 'nullable|string',
            'social_url_3' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'country' => 'required|string',
        ]);
        $collab = $this->service->create($data);
        return response()->json($collab, 201);
    }

    public function show($id)
    {
        $collab = $this->service->find($id);
        if (!$collab) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($collab);
    }
}
