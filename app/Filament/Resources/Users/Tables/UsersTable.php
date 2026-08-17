<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_no')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->formatStateUsing(function (string $state): string {
                        $parts = explode('@', $state);
                        if (count($parts) !== 2) {
                            return $state;
                        }
                        $username = $parts[0];
                        $domainParts = explode('.', $parts[1]);
                        $tld = array_pop($domainParts); // Get the TLD (com, net, org, etc.)
                        $domain = implode('.', $domainParts);
                        $maskedUsername = Str::mask($username, '*', 1, -1);
                        $maskedDomain = $domain ? Str::mask($domain, '*', 1, -1).'.'.$tld : $tld;

                        return $maskedUsername.'@'.$maskedDomain;
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->formatStateUsing(function (string $state): string {
                        $cleanNumber = preg_replace('/[^0-9]/', '', $state);
                        if (str_starts_with($cleanNumber, '254')) {
                            return '254'.Str::mask(substr($cleanNumber, 3), '*', 1, 5);
                        } elseif (str_starts_with($cleanNumber, '0')) {
                            return '0'.Str::mask(substr($cleanNumber, 2), '*', 1, 4);
                        }

                        return Str::mask($state, '*', 3, 4);
                    })
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles.name')
                    ->badge()
                    ->label('Role')
                    ->searchable(),
                IconColumn::make('linked_id')
                    ->label('Kadi API Linked')
                    ->icon(fn ($state): string => $state === null ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($state): string => $state === null ? 'danger' : 'success')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('email_verified_at')
                    ->icon(fn ($state): string => $state === null ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($state): string => $state === null ? 'danger' : 'success')
                    ->label('Verified')
                    ->sortable(),
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
                SelectFilter::make('roles.name')
                    ->relationship('roles', 'name')
                    ->label('Role')
                    ->native(false)
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()->iconButton()->icon(Heroicon::OutlinedPencilSquare)->color('warning')->tooltip('Edit User'),
                ViewAction::make()->iconButton()->icon(Heroicon::OutlinedEye)->color('primary')->tooltip('View User'),
                DeleteAction::make()->iconButton()->icon(Heroicon::OutlinedTrash)->color('danger')->tooltip('Delete User'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
