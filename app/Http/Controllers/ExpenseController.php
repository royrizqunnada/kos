<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Src\Expense\Domain\Enums\ExpenseCategory;
use Src\Expense\Domain\Models\Expense;
use Src\Expense\Domain\Repositories\ExpenseRepositoryInterface;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseRepositoryInterface $expenses) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Expense::class);

        return Inertia::render('Expenses/Index', [
            'expenses' => $this->expenses->paginate($request->only('category')),
            'filters' => $request->only('category'),
            'categories' => array_map(fn ($c) => ['value' => $c->value, 'label' => $c->label()], ExpenseCategory::cases()),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);
        $data = $request->validated();
        $data['recorded_by'] = $request->user()->id;
        $this->expenses->create($data);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);
        $this->expenses->update($expense, $request->validated());

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);
        $this->expenses->delete($expense);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
