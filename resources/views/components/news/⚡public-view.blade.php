<?php

use AcMarche\News\Filament\Resources\News\Schemas\NewsInfolist;
use AcMarche\News\Models\News;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

new class extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    /**
     * The component is reachable without authentication, so the file upload RPC
     * methods InteractsWithSchemas exposes must stay closed.
     */
    use RestrictsFileUploadsToSchemaComponents;

    public News $news;

    /**
     * The panel infolist, reused as is so a guest sees the same article, media
     * gallery and metadata as a signed in user.
     */
    public function newsInfolist(Schema $schema): Schema
    {
        return NewsInfolist::configure($schema->record($this->news));
    }
};
?>

<div>
    {{ $this->newsInfolist }}
</div>
