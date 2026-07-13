<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'        => 'required|numeric|min:500',
            'title'         => 'required|string|max:150',
            'description'   => 'nullable|string',
            'category'      => 'required|in:medical,food,shelter_maintenance,logistics,other',
            'invoice_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $invoiceUrl = null;
            if ($request->hasFile('invoice_image')) {
                $path = $request->file('invoice_image')->store('expense_invoices', 'public');
                $invoiceUrl = Storage::url($path);
            }

            $expense = Expense::create([
                'admin_id'           => Auth::id(),
                'amount'             => $request->amount,
                'title'              => $request->title,
                'description'        => $request->description,
                'category'           => $request->category,
                'invoice_image_path' => $invoiceUrl,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'The financial invoice has been successfully recorded and added to the general expenses log.',
                'data'    => $expense
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while processing the expense registration.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
