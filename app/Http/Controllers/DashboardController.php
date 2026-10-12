<?php

namespace App\Http\Controllers;

use App\Models\MedicalCase;

class DashboardController extends Controller
{
    public function index()
    {
        // Each count() asks MySQL to count the matching rows.
        $totalCases = MedicalCase::count();
        $planningCases = MedicalCase::where('status', 'Planning')->count();
        $inReviewCases = MedicalCase::where('status', 'In Review')->count();
        $approvedCases = MedicalCase::where('status', 'Approved')->count();
        $completedCases = MedicalCase::where('status', 'Completed')->count();

        return view('dashboard', [
            'totalCases' => $totalCases,
            'planningCases' => $planningCases,
            'inReviewCases' => $inReviewCases,
            'approvedCases' => $approvedCases,
            'completedCases' => $completedCases,
        ]);
    }
}
