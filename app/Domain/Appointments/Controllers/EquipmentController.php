<?php

namespace App\Domain\Appointments\Controllers;

use App\Domain\Appointments\Models\Equipment;
use App\Domain\Appointments\Resources\EquipmentResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Domain\Appointments\Models\Appointment::class);

        $equipments = Equipment::query()
            ->orderBy('name')
            ->get();

        return EquipmentResource::collection($equipments);
    }
}
