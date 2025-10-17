<?php

namespace App\Filament\Widgets;

use App\Actions\Cron;
use App\Filament\Actions\DeleteSpotAction;
use App\Models\Spotdl;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
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
        if (request()->route()?->getName() === 'filament.admin.pages.download') {
            return env("SPOTIFY_SH")!='' && env("LIDARR_API")!='';
        }
        return false;
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
                DeleteSpotAction::make()->label("Supprimer"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('markAsDone')
                        ->label('A faire')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['todo' => 1]);
                            });

                            $cron = new Cron();
                            $cron->makeCronFile();
                        }),
                    BulkAction::make('markAsNoDone')
                        ->label('A ne pas faire')
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->update(['todo' => 0]);
                            });

                            $cron = new Cron();
                            $cron->makeCronFile();
                        }),
                    BulkAction::make('remove')
                        ->label('Supprimer')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                $record->delete();
                            });

                            $cron = new Cron();
                            $cron->makeCronFile();
                        })
                ])->label('Actions globales'),
            ]);
    }
}
