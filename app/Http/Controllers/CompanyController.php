<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use App\Models\Website;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        // Get request
        $searchTerm = $request->input('search');

        // Get all websites with company
        $query = Website::with('company');

        // If there are text in searchbar filter the result
        if ($searchTerm) {
            $query->whereHas('company', function ($query) use ($searchTerm) {
                $query->where('URL', 'like', '%' . $searchTerm . '%');
            });
        }

        // Get result
        $websites = $query->get();
        // Return data
        return response()->json($websites);
    }
    public function create(Request $request)
    {
        // Validate incoming data from the form
        $validatedData = $request->validate([
            'Company_name' => 'required|string|max:255',
            'URL' => 'required|url|max:255',
            'Scan' => 'nullable|boolean', // New field for Scan
            'DNS' => 'nullable|boolean', // New field for DNS
            'SSL' => 'nullable|boolean', // New field for SSL
        ]);
        $sanitizedUrl = filter_var($validatedData['URL'], FILTER_SANITIZE_URL);
        // Check if the company name already exists in the database
        $company = Company::where('Company_name', $validatedData['Company_name'])->first();

        // If the company already exists
        if ($company) {
            // Check if the URL already exists for the current company
            $existingWebsite = Website::where('URL', $sanitizedUrl)->where('company_id', $company->id)->first();

            // If the URL doesn't already exist
            if (!$existingWebsite) {
                // Create a new website with the existing company's ID
                $website = Website::create([
                    'URL' => $sanitizedUrl,
                    'company_id' => $company->id,
                    'Scan' => $validatedData['Scan'],
                    'DNS' => $validatedData['DNS'],
                    'SSL' => $validatedData['SSL'],
                ]);

            } else {
                // If the URL already exists, return an error message
                return response()->json([
                    'errorMessage' => 'The URL already exists for this company.',
                    'message' => 'Oops! This URL is already associated with the company.',
                    'status' => 'error'
                ], 400);
            }
        } else {
            // Check if the URL already exists
            $existingWebsite = Website::where('URL', $sanitizedUrl)->first();
            if ($existingWebsite) {
                // If the URL already exists, return an error message
                return response()->json([
                    'error' => [
                        'message' => 'The URL already exists for this company.',
                        'comment' => 'Oops! This URL is already associated with the company.'
                    ],
                    'status' => 'error'
                ], 400);
            } else {
                // If the company doesn't exist, create a new company and then the website
                $company = Company::create([
                    'Company_name' => $validatedData['Company_name'],
                ]);

                $website = Website::create([
                    'URL' => $sanitizedUrl,
                    'company_id' => $company->id,
                    'Scan' => $validatedData['Scan'],
                    'DNS' => $validatedData['DNS'],
                    'SSL' => $validatedData['SSL'],
                ]);
            }
        }

        // Redirect the user back to the dashboard
        return redirect()->route('dashboard')->with('success', 'Website updated successfully');
    }

        public function update(Request $request, $id)
        {

            $website = Website::findOrFail($id);

            $validatedData = $request->validate([
                'URL' => 'required|url',
                'Company_name' => 'required|string',
                'Scan' => 'boolean',
                'DNS' => 'boolean',
                'SSL' => 'boolean',
            ]);

            $sanitizedUrl = filter_var($validatedData['URL'], FILTER_SANITIZE_URL);
            $validatedData['URL'] = $sanitizedUrl;
            $website->update($validatedData);

            // Find the corresponding company and update its name
            $company = Company::findOrFail($website->company_id);
            $company->update(['Company_name' => $validatedData['Company_name']]);

            // Redirect the user back to the dashboard
            return redirect()->route('dashboard')->with('success', 'Website updated successfully');
        }

    public function delete($id)
    {
        // Hitta webbplatsen med den angivna id
        $website = Website::findOrFail($id);

        // Radera webbplatsen
        $website->delete();


    }
    public function getCompanyName($id)
    {
        $website = Website::with('company')->findOrFail($id);
        $company_name = $website->company->Company_name;

        return Inertia::render('EditWebsite', ['company_name' => $company_name]);
    }

}
