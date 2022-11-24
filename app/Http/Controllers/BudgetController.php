<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetStoreRequest;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\BudgetUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(): View
    {
        $month = request('month') ?: now()->format('Y-m');
        $budgets = Budget::query()
            ->with([
                'category',
                'category.transactions'
            ])
            ->when(!is_null($month), function (Builder $query) use ($month) {
                $datePieces = explode("-", $month);
                if (!isset($datePieces[0]) && !isset($datePieces[1])) return;
                $query->where('year', $datePieces[0])
                    ->where('month', $datePieces[1]);
            })
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'DESC')
            ->paginate(10)
            ->appends([
                'month' => $month
            ]);

        return view('budgets.index', [
            'budgets' => $budgets,
            'month' => $month
        ]);
    }

    public function create(): View
    {
        return view('budgets.create', [
            'categories' => Category::all()
        ]);
    }

    public function store(BudgetStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $datePieces = explode("-", $data['date']);

        if (!$this->canCreateBudget(array_merge($data, [
            'year' => $datePieces[0],
            'month' => $datePieces[1]
        ]))) {
            throw ValidationException::withMessages([
                'budget' => 'The budget has already been created for this month and category.'
            ]);
        }

        $budget = auth()->user()->budgets()->create([
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'year' => $datePieces[0],
            'month' => $datePieces[1],
        ]);

        session()->flash('success-toast', 'Budget created sucessfully for the given month.');
        return redirect()->route('budgets.index', [
            'month' => "{$budget->year}-{$budget->month}"
        ]);
    }

    private function canCreateBudget(array $data): bool
    {
        if (auth()->user()
            ->budgets()
            ->where('year', $data['year'])
            ->where('month', $data['month'])
            ->where('category_id', $data['category_id'])
            ->first()
        ) {
            return false;
        }
        return true;
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('edit', $budget);
        $categories = Category::all();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(BudgetUpdateRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        $data = $request->validated();
        $datePieces = explode("-", $data['date']);

        if (
            $budget->category_id != $data['category_id'] &&
            !$this->canCreateBudget(array_merge($data, [
                'year' => $datePieces[0],
                'month' => $datePieces[1]
            ]))
        ) {
            throw ValidationException::withMessages([
                'budget' => 'The budget has already been updated for this month and category.'
            ]);
        }

        $budget->update([
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'year' => $datePieces[0],
            'month' => $datePieces[1],
        ]);

        $budget->fresh();

        session()->flash('success-toast', 'Budget updated sucessfully for the given month.');
        return redirect()->route('budgets.index', [
            'month' => "{$budget->year}-{$budget->month}"
        ]);
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('destroy', $budget);
        $budget->delete();
        session()->flash('success-toast', 'Deleted successfully.');
        return redirect()->back();
    }
}