<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\TransactionStoreRequest;
use App\Http\Requests\TransactionUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $month = request('month') ?: now()->format('Y-m');
        $transactions = Transaction::query()
            ->with([
                'category'
            ])
            ->when(!is_null(request('category_id')), function (Builder $query) {
                $query->where('category_id', request('category_id'));
            })
            ->when(!is_null($month), function (Builder $query) use ($month) {
                $datePieces = explode("-", $month);
                if (!isset($datePieces[0]) && !isset($datePieces[1])) return;
                $query->whereYear('date', $datePieces[0])
                    ->whereMonth('date', $datePieces[1]);
            })
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends([
                'category_id' => request('category_id'),
                'month' => $month
            ]);

        return view('transactions.index', [
            'transactions' => $transactions,
            'categories' => Category::all(),
            'month' => $month
        ]);
    }

    public function create(): View
    {
        return view('transactions.create', [
            'categories' => Category::all()
        ]);
    }

    public function store(TransactionStoreRequest $request): RedirectResponse
    {
        auth()->user()->transactions()->create($request->validated());
        session()->flash('success-toast', 'Transaction created successfully.');
        return redirect()->route('transactions.index');
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorize('edit', $transaction);
        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => Category::all()
        ]);
    }

    public function update(TransactionUpdateRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);
        $transaction->update($request->validated());
        session()->flash('success-toast', 'Transaction updated successfully.');
        return redirect()->route('transactions.index');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('destroy', $transaction);
        $transaction->delete();
        session()->flash('success-toast', 'Tranasction deleted successfully.');
        return redirect()->back();
    }
}