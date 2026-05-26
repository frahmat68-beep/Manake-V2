<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductAvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Get availability details for a specific equipment item.
     *
     * @param Equipment $equipment
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Equipment $equipment, Request $request): JsonResponse
    {
        $startDate = $request->query('rental_start_date');
        $endDate = $request->query('rental_end_date');
        $qty = (int) $request->query('qty', 1);

        if (!$startDate || !$endDate) {
            return response()->json([
                'ok' => false,
                'status' => 'not_available',
                'message' => 'Tanggal mulai sewa dan tanggal akhir sewa harus diisi.',
            ], 422);
        }

        $summary = $this->availabilityService->getAvailabilitySummary($equipment, $startDate, $endDate, $qty);

        return response()->json($summary);
    }
}
