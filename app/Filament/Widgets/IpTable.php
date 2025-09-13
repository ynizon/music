<?php

namespace App\Filament\Widgets;

use App\Models\Ip;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class IpTable extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Ip::query())
            ->columns([
                  TextColumn::make('ip')
                      ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema([
                         TextInput::make('ip')
                             ->label('Ip')
                             ->required()
                             ->maxLength(255),
                             ])
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                         TextInput::make('ip')
                             ->label('Ip')
                             ->required()
                             ->maxLength(255),
                     ]),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
