<?php

namespace App\Filament\Resources;

use App\Filament\Exports\CategoryExporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Категория';

    protected static ?string $pluralLabel = 'Категории';

    protected static ?string $navigationLabel = 'Список категорий';

    protected static ?string $navigationGroup = 'Блог';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('title')
                            ->required()
                            ->minLength(3)
                            ->live(true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state, string $operation) {
                                if ($operation === 'edit' && $get('slug')) return;
                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Гененирируется автоматически'),

                        RichEditor::make('content')->columnSpan(2),
                    ])->columns(2)
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make()->schema([
                        Toggle::make('is_feature')->default(false)->onColor('success')->offColor('danger'),
                        FileUpload::make('image')->image()->directory("preview/" . date('Y') . '/' . date('m') . '/' . date('d')),
                    ])
                ])
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                ImageColumn::make('image'),
                TextColumn::make('title')->sortable(),
                TextColumn::make('slug')->sortable(),
                IconColumn::make('is_featured')->boolean()->sortable(),
                // ToggleColumn::make('is_featured')
                //     ->afterStateUpdated(function () {
                //         Notification::make()->title('Категория обновлена')->success()->send();
                //     }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                ExportAction::make()->exporter(CategoryExporter::class),
                ImportAction::make()->importer(CategoryImporter::class),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\ReplicateAction::make()
                        ->excludeAttributes(['slug'])
                        ->successRedirectUrl(fn(Model $replica) => route('filament.admin.resources.categories.edit', $replica)),
                ])
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
