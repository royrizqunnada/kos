<?php

declare(strict_types=1);

namespace Src\Expense\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Src\Expense\Domain\Enums\ExpenseCategory;

class RecurringExpense extends Model
{
    protected $fillable = ['category', 'description', 'amount', 'day_of_month', 'is_active'];

    protected function casts(): array
    {
        return [
            'category' => ExpenseCategory::class,
            'amount' => 'decimal:2',
            'day_of_month' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Expense, $this> */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
