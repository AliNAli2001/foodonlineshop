<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Client\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Get all companies.
     */
    public function index(Request $request)
    {
        $companies = Company::withCount('products')->paginate(20);
        

        return CompanyResource::collection($companies);
    }

    /**
     * Get company details with products.
     */
    public function show(Request $request, $company)
    {
       
        $company = Company::with(['products' => function ($query) {
            $query->with(['primaryImage', 'category', 'tags']);
        }])->findOrFail($company);

        return new CompanyResource($company);
    }
}
