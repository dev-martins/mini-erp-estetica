<?php

namespace App\Domain\Appointments\Controllers;

use App\Domain\Appointments\Models\Professional;
use App\Domain\Appointments\Resources\ProfessionalResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Domain\Appointments\Models\Appointment::class);

        $professionals = Professional::query()
            ->with('user')
            ->when($request->boolean('only_active', true), fn ($query) => $query->where('active', true))
            ->orderBy('display_name')
            ->get();

        return ProfessionalResource::collection($professionals);
    }
}
