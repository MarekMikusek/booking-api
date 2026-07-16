<?php

namespace App\Http\Controllers;

use App\Actions\CreateHolidayAction;
use App\DTOs\HolidayDTO;
use App\Http\Requests\StoreHolidayRequest;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHolidayRequest $request, CreateHolidayAction $createHolidayAction)
    {
        $data = HolidayDTO::fromRequest($request);

        $holiday = $createHolidayAction->execute($data);

        return response()->json([
            'message' => 'Dzień wolny został pomyślnie dodany.',
            'data' => $holiday
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Holiday $holiday)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Holiday $holiday)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Holiday $holiday)
    {
        //
    }
}
