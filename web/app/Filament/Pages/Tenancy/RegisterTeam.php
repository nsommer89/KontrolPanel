<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register team';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                // ...
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        $user = auth()->user();
        $team = Team::create(array_merge([
            'owner_id' => $user->id
        ], $data));

        $team->members()->attach($user);

        return $team;
    }
}