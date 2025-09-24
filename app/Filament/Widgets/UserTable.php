<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserTable extends TableWidget
{

    protected static ?string $heading = 'Utilisateurs';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => User::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                ToggleColumn::make('admin'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema([
                         TextInput::make('name')
                             ->label('Nom')
                             ->required()
                             ->maxLength(255),

                         TextInput::make('password')
                             ->label('Password')
                             ->password()
                             ->required()
                             ->maxLength(255)
                             ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),

                         TextInput::make('email')
                             ->label('Email')
                             ->email()
                             ->required()
                             ->maxLength(255),
                     ])->label("Nouvel utilisateur")
            ])
            ->recordActions([
                EditAction::make()
                    ->schema([
                         TextInput::make('name')
                             ->label('Nom')
                             ->required()
                             ->maxLength(255),

                         TextInput::make('password')
                             ->label('Password')
                             ->password()
                             ->required()
                             ->maxLength(255)
                             ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),

                         TextInput::make('email')
                             ->label('Email')
                             ->email()
                             ->required()
                             ->maxLength(255),
                     ])->label("Modifier"),
                DeleteAction::make()->label("Supprimer"),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
