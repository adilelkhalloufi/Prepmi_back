<?php

namespace App\Http\Controllers;

use App\Services\TeamPartnershipService;
use Illuminate\Http\Request;

class TeamPartnershipController extends Controller
{
    protected $service;

    public function __construct(TeamPartnershipService $service)
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
            'job_title' => 'nullable|string',
            'email' => 'required|email',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'company_name' => 'required|string',
            'company_website' => 'nullable|string',
            'partnership_type' => 'required|string',
            'team_members_per_week' => 'required|string',
            'products_interested' => 'required|array',
            'heard_about_us' => 'nullable|string',
            'accept_terms' => 'required|boolean',
            'accept_communications' => 'required|boolean',
        ]);
        $partnership = $this->service->create($data);
        return response()->json($partnership, 201);
    }

    public function show($id)
    {
        $partnership = $this->service->find($id);
        if (!$partnership) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($partnership);
    }
}
