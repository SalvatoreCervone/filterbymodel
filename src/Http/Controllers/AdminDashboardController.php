<?php

namespace SalvatoreCervone\FilterByModel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class AdminDashboardController extends Controller
{
    /**
     * Mostra la dashboard amministrativa del package FilterByModel.
     */
    public function index(): View
    {
        $apiConfig = config('filterbymodel.routes.api', config('filterbymodel.routes', []));
        $apiPrefix = $apiConfig['prefix'] ?? 'api';

        return view('filterbymodel::admin', [
            'apiPrefix'     => url($apiPrefix),
            'userModel'     => config('filterbymodel.user.model', 'App\Models\User'),
            'userTable'     => 'users',
            'userIdField'   => 'id',
            'userLabelField'=> 'name',
            'packageVersion'=> '1.1.0',
        ]);
    }
}
