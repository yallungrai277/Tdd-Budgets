<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Http\Requests\Api\TransactionStoreRequest;
use App\Http\Requests\Api\TransactionUpdateRequest;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = Transaction::query()
            ->with([
                'category'
            ])
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return TransactionResource::collection($transactions);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('show', $transaction);
        return response()->json(new TransactionResource($transaction));
    }

    public function store(TransactionStoreRequest $request)
    {
        $transaction = $request->user()->transactions()->create($request->validated());
        return response()->json(new TransactionResource($transaction));
    }

    public function update(TransactionUpdateRequest $request, Transaction $transaction)
    {
        $transaction->update($request->validated());
        return response()->json(new TransactionResource($transaction->refresh()));
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('destroy', $transaction);
        $transaction->delete();
        return response()->noContent();
    }
}