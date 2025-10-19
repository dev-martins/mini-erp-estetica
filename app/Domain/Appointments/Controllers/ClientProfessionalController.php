<?php

namespace App\Domain\Appointments\Controllers;

use App\Domain\Appointments\Models\Professional;
use App\Domain\Appointments\Resources\ClientProfessionalResource;
use App\Http\Controllers\Controller;

class ClientProfessionalController extends Controller
{
    public function index()
    {
        $professionals = Professional::query()
            ->where('active', true)
            ->orderBy('display_name')
            ->get(['id', 'display_name', 'specialty', 'work_hours']);

        return ClientProfessionalResource::collection($professionals);
    }
}
