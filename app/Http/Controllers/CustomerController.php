<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index()
    {
        $customer = Customer::query()
            ->select([
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.country'
            ])
            ->latest()
            ->get();

        return response()
            ->json([
                'customers' => $customer
            ]);
    }

    public function import(Request $request)
    {
        $payload = $request->json()->all();

        $imported = 0;
        $skipped = [];

        if (!isset($payload['results']) || !is_array($payload['results'])) {
            return response()
                ->json([
                    'message' => 'Invalid structure: "results" key missing or not an array'
                ], 400);
        }

        foreach ($payload as $index => $user) {
            if ($user['location']['country'] !== 'Australia') {
                $skipped[] = [
                    'index' => $index,
                    'reason' => 'User not from Australia'
                ];
                continue;
            }

            $data = [
                'uuid' => $user['login']['uuid'],
                'username' => $user['login']['username'],
                'password' => Hash::make($user['login']['password']),
                'first_name' => $user['name']['first'],
                'last_name' => $user['name']['last'],
                'email' => $user['email'],
                'gender' => $user['gender'],
                'country' => $user['location']['country'],
                'city' => $user['location']['city'],
                'phone' => $user['phone'],
            ];

            Customer::create($data);
            $imported++;
        }

        return response()->json([
            'message' => 'Import completed.',
            'customers_imported' => $imported,
            'skipped' => count($skipped),
            'skipped_details' => $skipped
        ]);
    }

    public function show($id)
    {
        try {
            $customer = Customer::query()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'message' => 'Customer not found',
                    'tech_details' => $e->getMessage()
                ], 404);
        }

        return response()
            ->json([
                'customer' => $customer
            ]);
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|uuid|unique:users,uuid',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required|string',
            'country' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()
                ->json([
                    'message' => 'Invalid data provided',
                    'errors' => $validator->errors()
                ], 422);
        }

        try {
            $customer = Customer::query()->findOrFail($id);
            $customer->update($request->all());

            return response()
                ->json([
                    'message' => 'Customer updated successfully',
                    'customer' => $customer
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'message' => 'Customer not found',
                    'tech_details' => $e->getMessage()
                ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::query()->findOrFail($id);
            $customer->delete();

            return response()
                ->json([
                    'message' => 'Customer deleted successfully'
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'message' => 'Customer not found',
                    'tech_details' => $e->getMessage()
                ], 404);
        }
    }
}
