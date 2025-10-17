<?php

namespace App\Domain\Appointments\Controllers;

use App\Domain\Appointments\Models\Room;
use App\Domain\Appointments\Resources\RoomResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Domain\Appointments\Models\Appointment::class);

        $rooms = Room::query()
            ->when($request->boolean('only_active', true), fn ($query) => $query->where('active', true))
            ->orderBy('name')
            ->get();

        return RoomResource::collection($rooms);
    }
}
