<?php

namespace App\Services;

use Illuminate\Http\Request;

class ApplyFilters
{
    /**
     * Create a new class instance.
     */
    public function applyFilters(Request $request, $queryBuilder, $filterOptions, $filterCondition)
    {
        $reqOption   = $request->input('fetch_option');
        $reqCriteria = $request->input('fetch_criteria');
        $reqSearch   = $request->input('search_value');

        if (!$reqOption || !$reqCriteria || !$reqSearch) {
            return;
        }

        $filterOptions   = array_column($filterOptions, 'value');
        $filterCondition = array_column($filterCondition, 'value');

        if (!in_array($reqOption, $filterOptions, TRUE) || !in_array($reqCriteria, $filterCondition, TRUE)) {
            \Log::warning('Invalid filter option or criteria', ['options' => $reqOption, 'criteria' => $reqCriteria]);
            return;
        }

        switch ($reqCriteria) {
            case 'exact_match':
                $queryBuilder->where($reqOption, $reqSearch);
                break;

            case 'exists':
                $search = addcslashes($reqSearch, '%_');
                $queryBuilder->where($reqOption, 'LIKE', "%{$search}%");
                break;
        }

        return;
    }
}
