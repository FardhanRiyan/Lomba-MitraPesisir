<?php

namespace App\Http\Controllers;

use App\Services\TideSimulator;
use App\Support\ChartBuilder;
use App\Support\CurrentLocation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Prediksi pasang surut 24/48 jam + riwayat 7 hari. */
class PredictionController extends Controller
{
    public function __construct(
        private readonly TideSimulator $tide,
        private readonly CurrentLocation $current,
    ) {
    }

    public function index(Request $request): View
    {
        $location = $this->current->get();
        $hours = $request->query('jam') === '48' ? 48 : 24;

        $predictionChart = null;
        $historyChart = null;
        $history = null;
        if ($location) {
            $prediction = $this->tide->prediction($location->id, $hours);
            $history = $this->tide->history($location->id);
            $predictionChart = ChartBuilder::prediction($prediction['series']);
            $historyChart = ChartBuilder::history($history['series']);
        }

        return view('prediksi.index', [
            'location' => $location,
            'hours' => $hours,
            'predictionChart' => $predictionChart,
            'historyChart' => $historyChart,
            'history' => $history,
        ]);
    }
}
