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
use Src\Expense\Domain\Models\RecurringExpense;
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
            'recurring' => RecurringExpense::query()->orderByDesc('is_active')->orderBy('day_of_month')->get()
                ->map(fn (RecurringExpense $r) => [
                    'id' => $r->id,
                    'category' => $r->category->value,
                    'category_label' => $r->category->label(),
                    'description' => $r->description,
                    'amount' => (float) $r->amount,
                    'day_of_month' => $r->day_of_month,
                    'is_active' => $r->is_active,
                ]),
        ]);
    }

    public function recurringStore(Request $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $data = $request->validate([
            'category' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'day_of_month' => ['required', 'integer', 'min:1', 'max:28'],
        ]);
        RecurringExpense::query()->create([...$data, 'is_active' => true]);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berulang ditambahkan.');
    }

    public function recurringToggle(RecurringExpense $recurring): RedirectResponse
    {
        $this->authorize('update', Expense::class);
        $recurring->update(['is_active' => ! $recurring->is_active]);

        return redirect()->route('expenses.index')->with('success', 'Status pengeluaran berulang diperbarui.');
    }

    public function recurringDestroy(RecurringExpense $recurring): RedirectResponse
    {
        $this->authorize('delete', Expense::class);
        $recurring->delete();

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berulang dihapus.');
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
