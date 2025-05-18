<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhpVersionResource\Pages;
use App\Filament\Resources\PhpVersionResource\RelationManagers;
use App\Models\PhpVersion;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PhpVersionResource extends Resource
{
    protected static ?string $model = PhpVersion::class;

    protected static ?string $navigationLabel = 'PHP Versions';

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static bool $isScopedToTenant = false;

    protected static ?string $recordTitleAttribute = 'version';

    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('PHP Version'))
                    ->schema([

                        Forms\Components\TextInput::make('version')
                            ->mask('9.99')
                            ->placeholder('8.x')
                            ->columnSpan(1)
                            ->disabled(function ($record) {
                                return !empty($record);
                            })
                            ->unique(table: PhpVersion::class, ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'PHP :attribute is already installed.',
                            ])
                            ->helperText(__('This can\'t be changed. The PHP version can only be uninstalled.'))
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),
                Section::make(__('Advanced'))
                    ->schema([
                        Forms\Components\Toggle::make('default')
                            ->columnSpan(2)
                            ->helperText(__('If this is checked, /usr/bin/php will be symlinked to this version.'))
                            ->required(),
                        Forms\Components\Toggle::make('change_php_paths')->label('Change PHP paths')->translateLabel()
                            ->columnSpan(2)
                            ->helperText('Check this, if you want to modify the PHP paths.')
                            ->live(),
                        Forms\Components\TextInput::make('binary_path')
                            ->hint('Expected to point to something like /usr/bin/php8.x')
                            ->disabled(function (Get $get) {
                                return !$get('change_php_paths');
                            })
                            ->maxLength(255),
                        Forms\Components\TextInput::make('fpm_path')
                            ->hint('Expected to point to something like /etc/php/8.x/fpm')
                            ->rules(['nullable', 'string', 'starts_with:/etc/'])
                            ->disabled(function (Get $get) {
                                return !$get('change_php_paths');
                            })
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version')
                    ->getStateUsing(function ($record) {
                        return "PHP " . $record->version;
                    })
                    ->searchable(),
                Tables\Columns\IconColumn::make('installed')
                    ->getStateUsing(function () {
                        return true;
                    })
                    ->boolean(),
                Tables\Columns\IconColumn::make('default')
                    ->boolean(),
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
                Tables\Actions\BulkActionGroup::make([]),
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
            'index' => Pages\ListPhpVersions::route('/'),
            'create' => Pages\CreatePhpVersion::route('/create'),
            'edit' => Pages\EditPhpVersion::route('/{record}/edit'),
        ];
    }
}
