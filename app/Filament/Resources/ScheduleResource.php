<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Auth;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-s-calendar-days';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Toggle::make('is_banned'),
                        Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('shift_id')
                        ->relationship('shift', 'name')
                        ->required(),
                    Forms\Components\Select::make('office_id')
                        ->relationship('office', 'name')
                        ->required(),
                        Forms\Components\Toggle::make('is_wfa')
                    ])
                ])
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (Builder $query){
            $is_super_admin = Auth::user()->hasRole('super_admin');
            if(!$is_super_admin){
                $query->where('user_id',Auth::user()->id);
            }
        })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('is_wfa')
                ->label('WFA'),
                Tables\Columns\ToggleColumn::make('is_banned')
                ->label('Banned')
                ->hidden(fn () => !Auth::user()->hasRole('super_admin')),
                Tables\Columns\TextColumn::make('shift.name')
                ->description(fn (Schedule $record): string => $record->shift->start_time.'-'.$record->shift->end_time)
                    ->sortable(),
                Tables\Columns\TextColumn::make('office.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
