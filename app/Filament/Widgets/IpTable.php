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

    protected static ?string $heading = "Autorisations d'IP";

    public static function canView(): bool
    {
        return env("SPOTIFY_SH")!='' && env("LIDARR_API")!='';
    }

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
                             ])->label("Nouvelle IP")
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                         TextInput::make('ip')
                             ->label('Ip')
                             ->required()
                             ->maxLength(255),
                     ])->label("Modifier"),
                DeleteAction::make()->label("Supprimer"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
