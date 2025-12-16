<?php

namespace App\Filament\Admin\Resources\Products;

use App\Filament\Admin\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use BackedEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $recordTitleAttribute = 'name';

    // Form method sesuai Filament 3.x
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->required()
                ->label('Nama Produk'),

            TextInput::make('price')
                ->required()
                ->numeric()
                ->label('Harga'),

            TextInput::make('stock')
                ->required()
                ->numeric()
                ->label('Stok'),

            TextInput::make('weight')
                ->numeric()
                ->label('Berat (kg)'),

            FileUpload::make('image')
                ->image()
                ->directory('products')
                ->disk('public')
                ->required()
                ->label('Gambar Produk'),
        ]);
    }

    // Table method harus menggunakan Filament\Tables\Table
    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
