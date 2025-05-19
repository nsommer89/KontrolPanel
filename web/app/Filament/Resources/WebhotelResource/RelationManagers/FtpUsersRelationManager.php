<?php

namespace App\Filament\Resources\WebhotelResource\RelationManagers;

use App\Models\FtpUser;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class FtpUsersRelationManager extends RelationManager
{
    protected static string $relationship = 'ftpUsers';

    // public static function getTitle(): string
    // {
    //     return __('message.translation');
    // }

    protected static ?string $title = 'FTP users';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->unique(table: FtpUser::class, column: 'username', ignoreRecord: true)
                    ->maxLength(32),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->maxLength(64)
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('generate')
                            ->icon('heroicon-o-sparkles')
                            ->tooltip('Generate secure password')
                            ->action(fn(Forms\Set $set) => $set('password', Str::random(16)))
                    ),

                Forms\Components\TextInput::make('homedir')
                    ->required()
                    ->default(fn(RelationManager $livewire) => '/var/www/' . $livewire->ownerRecord->id),


                Forms\Components\TextInput::make('quota')
                    ->label('Quota (MB)')
                    ->numeric()
                    ->minValue(0)
                    ->helperText('Optional disk limit per user'),

                Forms\Components\Hidden::make('webhotel_id')
                    ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->id),

                Forms\Components\Hidden::make('team_id')
                    ->default(fn(RelationManager $livewire) => $livewire->ownerRecord->tenant_id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->columns([
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('homedir'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New FTP user')
                    ->translateLabel()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = Filament::getTenant()->id;
                        $data['password'] = crypt($data['password'], base64_encode(random_bytes(6)));
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->recordTitle(fn (FtpUser $record): string => "FTP user: {$record->username}")
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['password'])) {
                            $data['password'] = crypt($data['password'], base64_encode(random_bytes(6)));
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
