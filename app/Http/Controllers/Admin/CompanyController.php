<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    /**
     * Show all companies.
     */
    public function index()
    {
        $companies = Company::paginate(15);
        return Inertia::render('admin.companies.index', compact('companies'));
    }

    /**
     * Show create company form.
     */
    public function create()
    {
        return Inertia::render('admin.companies.create');
    }

    /**
     * Store a new company.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies', 'public');
        }

        Company::create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'logo' => $logoPath,
        ]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'تم إنشاء الشركة بنجاح.');
    }

    /**
     * Show edit company form.
     */
    public function edit($companyId)
    {
        $company = Company::findOrFail($companyId);
        return Inertia::render('admin.companies.edit', compact('company'));
    }

    /**
     * Show company details.
     */
    public function show($companyId)
    {
        $company = Company::with('products')->findOrFail($companyId);
                $products = $company->products()->paginate(15);
        return Inertia::render('admin.companies.show', compact('company', 'products'));
    }

    /**
     * Update company.
     */
    public function update(Request $request, $companyId)
    {
        $company = Company::findOrFail($companyId);

        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $logoPath = $company->logo;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies', 'public');
        }

        $company->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'logo' => $logoPath,
        ]);

        return redirect()->route('admin.companies.index')
            ->with('success', 'تم تحديث بيانات الشركة بنجاح.');
    }

    /**
     * Delete company.
     */
    public function destroy($companyId)
    {
        $company = Company::findOrFail($companyId);
        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'تم حذف الشركة بنجاح.');
    }
}



