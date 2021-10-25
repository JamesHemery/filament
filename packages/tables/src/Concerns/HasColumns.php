<?php

namespace Filament\Tables\Concerns;

use Filament\Tables\Columns\Column;

trait HasColumns
{
    protected array $cachedTableColumns;

    public function cacheTableColumns(): void
    {
        $this->cachedTableColumns = collect($this->getTableColumns())
            ->mapWithKeys(function (Column $column): array {
                $column->table($this->getTable());

                return [$column->getName() => $column];
            })
            ->toArray();
    }

    public function callTableColumnAction(string $name, string $recordKey): void
    {
        $record = $this->resolveTableRecord($recordKey);

        if (! $record) {
            return;
        }

        $column = $this->getCachedTableColumn($name);

        if (! $column) {
            return;
        }

        $column->record($record)->callAction();
    }

    public function getCachedTableColumns(): array
    {
        return $this->cachedTableColumns;
    }

    public function getTableColumns(): array
    {
        return [];
    }

    protected function getCachedTableColumn(string $name): ?Column
    {
        return $this->getCachedTableColumns()[$name] ?? null;
    }
}