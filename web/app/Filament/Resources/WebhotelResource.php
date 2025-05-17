<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhotelResource\Pages;
use App\Filament\Resources\WebhotelResource\RelationManagers;
use App\Models\Webhotel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WebhotelResource extends Resource
{
    protected static ?string $model = Webhotel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Webhosting';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('php_version')
                    ->label('PHP Version')
                    ->required()
                    ->options(\App\Models\PhpVersion::pluck('version', 'version')),

                Forms\Components\TextInput::make('port')
                    ->numeric()
                    ->required(),

                Forms\Components\Toggle::make('enabled')->label('Enabled'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('php_version'),
                Tables\Columns\TextColumn::make('port'),
                Tables\Columns\IconColumn::make('enabled')->boolean(),
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
            'index' => Pages\ListWebhotels::route('/'),
            'create' => Pages\CreateWebhotel::route('/create'),
            'edit' => Pages\EditWebhotel::route('/{record}/edit'),
        ];
    }
}
