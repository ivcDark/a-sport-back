<?php

namespace App\Service;

use App\Models\FilterTable;

class FilterTableService
{
    public function get($params): \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array
    {
        $filters = FilterTable::query();

        if (isset($params['type'])) {
            $filters = $filters->where('type', $params['type']);

            if (isset($params['table'])) {
                $filters = $filters->where('html_tables', 'LIKE', "%{$params['table']}%");
            }

            $filters = $filters->orderBy('sort');

            return $filters->get();
        }

        return collect([]);
    }
}
