<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DomainResource\Pages;
use App\Filament\Resources\DomainResource\RelationManagers;
use App\Models\Domain;
use App\Models\Webhotel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Webhosting';

    protected static ?string $navigationLabel = 'Domains';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('webhotel.id'),
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('primary')
                    ->required(),
                Forms\Components\Toggle::make('ssl_enabled')
                    ->required(),
                Forms\Components\TextInput::make('cert_path')
                    ->maxLength(255),
                Forms\Components\TextInput::make('key_path')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('valid_until'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('webhotel_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('domain')
                    ->searchable(),
                Tables\Columns\IconColumn::make('primary')
                    ->boolean(),
                Tables\Columns\IconColumn::make('ssl_enabled')
                    ->boolean(),
                Tables\Columns\TextColumn::make('cert_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('key_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->dateTime()
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
            'index' => Pages\ListDomains::route('/'),
            'create' => Pages\CreateDomain::route('/create'),
            'edit' => Pages\EditDomain::route('/{record}/edit'),
        ];
    }
}
