<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FieldResource\Pages;
use App\Models\Field;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class FieldResource extends Resource
{
    protected static ?string $model = Field::class;

    // Menggunakan icon field/lapangan yang sesuai
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';

    // Sesuaikan label navigasi dalam Bahasa Indonesia
    protected static ?string $navigationLabel = 'Lapangan';

    // Opsional: Tambahkan grup navigasi
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Lapangan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Tipe Lapangan')
                    ->options([
                        'Sintetis' => 'Sintetis',
                        'Vynil' => 'Vynil',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Harga (Rp)')
                    ->numeric()
                    ->minValue(0)
                    ->required(),

                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Lapangan')
                    ->image()
                    ->required()
                    ->disk('public')
                    ->directory('fields') // disimpan di: storage/app/public/fields
                    ->visibility('public') // penting agar bisa ditampilkan ke publik
                    ->imageEditor()
                    ->imageEditorMode(2)
                    ->imageResizeMode('contain')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('400')
                    ->imageResizeTargetHeight('400'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lapangan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Lapangan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga (Rp)')
                    ->sortable()
                    ->money('IDR', true),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->disk('public')
                    ->height(80)
                    ->width(80)
                    ->square()
                    ->defaultImageUrl(asset('images/placeholder.png'))
                    ->visibility('public'),
            ])
            ->filters([
                // Tambah filter kalau perlu
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFields::route('/'),
            'create' => Pages\CreateField::route('/create'),
            'edit' => Pages\EditField::route('/{record}/edit'),
        ];
    }

    public static function getActions(): array
    {
        return [
            Action::make('generateSchedules')
                ->label('Generate Jadwal')
                ->action(function () {
                    Artisan::call('schedule:generate');
                    Notification::make()
                        ->title('Jadwal berhasil dibuat!')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Generate Jadwal Otomatis')
                ->modalDescription('Ini akan membuat jadwal otomatis untuk semua lapangan selama 1 minggu. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Generate!')
                ->color('success')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
