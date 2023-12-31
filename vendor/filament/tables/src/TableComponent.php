<?php

namespace Filament\Tables;

use Filament\Forms;
use Livewire\Component;

abstract class TableComponent extends Component implements Forms\Contracts\HasForms, Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Concerns\InteractsWithTable;
}
