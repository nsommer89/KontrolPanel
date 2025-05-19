<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhotelResource\Pages;
use App\Filament\Resources\WebhotelResource\RelationManagers;
use App\Models\Webhotel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
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
                Forms\Components\TextInput::make('system_user')
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
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('domains_count')
                    ->badge()
                    ->color(function ($state) {
                        return $state > 0 ? 'success' : 'info';
                    })
                    ->counts('domains'),
                Tables\Columns\TextColumn::make('php_version')
                    ->badge()
                    ->color('primary')
                    ->label('PHP Version'),
                Tables\Columns\TextColumn::make('port')
                    ->badge()
                    ->color('primary')
                    ->label('PHP-FPM port'),
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
            RelationManagers\FtpUsersRelationManager::class,
            RelationManagers\DomainsRelationManager::class,
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
