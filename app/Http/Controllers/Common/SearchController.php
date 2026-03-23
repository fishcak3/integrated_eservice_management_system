<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Resident;
use App\Models\Household;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function users(Request $request)
    {
        $search = trim($request->get('search', ''));
        $isInitialLoad = $request->get('initial') === 'true';

        // ALWAYS eager load the resident to prevent N+1 during the map() phase
        $query = User::with('resident')->orderBy('created_at', 'desc');

        if ($isInitialLoad && empty($search)) {
            $accounts = $query->limit(5)->get();
        } elseif (strlen($search) >= 2) {
            // Search email OR resident first/last name (mirroring your index logic)
            $accounts = $query->where('email', 'like', "%{$search}%")
                ->orWhereHas('resident', function ($q) use ($search) {
                    $q->where('fname', 'like', "%{$search}%")
                    ->orWhere('lname', 'like', "%{$search}%");
                })
                ->limit(5)
                ->get();
        } else {
            return response()->json([]);
        }

        return response()->json($accounts->map(function ($account) {
            return [
                'id'    => $account->id,
                // Assuming $account->resident exists, gracefully fallback if not
                'name'  => $account->resident ? trim($account->resident->fname . ' ' . $account->resident->lname) : 'Unknown',
                'email' => $account->email,
            ];
        }));
    }

    public function residents(Request $request)
    {
        $search = trim($request->get('search', ''));
        $isInitialLoad = $request->get('initial') === 'true';

        // ✅ FIX: Eager load the 'user' relationship to prevent N+1 queries
        $query = Resident::with('user'); 

        // NEW: If the user just clicked the input, give them 5 default residents
        if ($isInitialLoad && empty($search)) {
            $residents = $query
                ->orderBy('created_at', 'desc') // Shows the 5 most recently added residents
                ->limit(5)
                ->get()
                ->map(function ($resident) {
                    return [
                        'id'          => $resident->id,
                        'full_name'   => $resident->full_name,
                        'purok'       => $resident->purok,
                        'birthdate'   => optional($resident->birthdate)->format('M d, Y'),
                        // ✅ FIX: Pass the user_id or account status to the frontend
                        'has_account' => $resident->user !== null,
                        'user_id'     => $resident->user ? $resident->user->id : null,
                    ];
                });

            return response()->json($residents);
        }

        // EXISTING LOGIC: Only search if they typed at least 2 characters
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $residents = $query // ✅ FIX: Apply the eager-loaded query here as well
            ->where('fname', 'like', "%{$search}%")
            ->orWhere('lname', 'like', "%{$search}%")
            ->orWhereRaw("CONCAT(fname, ' ', lname) LIKE ?", ["%{$search}%"])
            ->orderBy('lname')
            ->orderBy('fname')
            ->limit(5)
            ->get()
            ->map(function ($resident) {
                return [
                    'id'          => $resident->id,
                    'full_name'   => $resident->full_name,
                    'purok'       => $resident->purok,
                    'birthdate'   => optional($resident->birthdate)->format('M d, Y'),
                    // ✅ FIX: Pass the user_id or account status to the frontend
                    'has_account' => $resident->user !== null,
                    'user_id'     => $resident->user ? $resident->user->id : null,
                ];
            });

        return response()->json($residents);
    }

    public function households(Request $request)
    {
        $search = trim($request->get('search', ''));
        $isInitialLoad = $request->get('initial') === 'true';

        // NEW: Load initial households (most recent 5)
        if ($isInitialLoad && empty($search)) {
            $households = Household::query()
                ->with('head') // Eager load the family head if you want to display their name
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($household) {
                    return [
                        'id'               => $household->id,
                        'household_number' => $household->household_number,
                        'sitio'            => $household->sitio,
                        'head_name'        => $household->head ? ($household->head->fname . ' ' . $household->head->lname) : 'No Head Assigned',
                    ];
                });

            return response()->json($households);
        }

        // Search logic: trigger if at least 1 character is typed (since house numbers can be short)
        if (strlen($search) < 1) {
            return response()->json([]);
        }

        $households = Household::query()
            ->with('head')
            ->where('household_number', 'like', "%{$search}%")
            ->orWhere('sitio', 'like', "%{$search}%") // Allow searching by sitio as well
            ->orderBy('household_number', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($household) {
                return [
                    'id'               => $household->id,
                    'household_number' => $household->household_number,
                    'sitio'            => $household->sitio,
                    'head_name'        => $household->head ? ($household->head->fname . ' ' . $household->head->lname) : 'No Head Assigned',
                ];
            });

        return response()->json($households);
    }
}