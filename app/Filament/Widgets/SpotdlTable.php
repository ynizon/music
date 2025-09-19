<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Spotdls\SpotdlResource;
use App\Models\Spotdl;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SpotdlTable extends TableWidget
{
    protected static ?string $heading = 'Téléchargements';
    protected static ?int $sort = 10;
    protected static bool $isDefaultDashboardWidget = false;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return env("SPOTIFY_SH")!='' && env("LIDARR_API")!='';
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(100)
            ->paginationPageOptions([10, 25, 50, 100])
            ->query(fn (): Builder => Spotdl::query())
            ->columns([
                  TextColumn::make('artist')
                      ->searchable()
                      ->label('Artiste'),
                  TextColumn::make('album')
                    ->searchable()
                    ->url(fn ($record): string => $record->spotifyurl)
                    ->openUrlInNewTab(),
                  ToggleColumn::make("todo")
                    ->label('A faire'),
                  ToggleColumn::make("done")
                    ->label('Téléchargé'),
                  ToggleColumn::make("avoid")
                    ->label('A éviter'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                DeleteAction::make()->label("Supprimer"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markAsDone')
                        ->label('A faire')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['todo' => 1]);
                            });
                        }),
                    BulkAction::make('markAsNoDone')
                        ->label('A ne pas faire')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['todo' => 0]);
                            });
                        }),
                    BulkAction::make('remove')
                        ->label('Supprimer')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->delete();
                            });
                        })
                ])->label('Actions globales'),
            ]);
    }
}
