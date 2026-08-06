<?php

use AcMarche\Document\Filament\Resources\Documents\Schemas\DocumentInfolist;
use AcMarche\Document\Models\Document;
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

    public Document $document;

    /**
     * The panel infolist, reused as is so a guest sees the same category,
     * description and file preview as a signed in user.
     */
    public function documentInfolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema->record($this->document));
    }
};
?>

<div>
    {{ $this->documentInfolist }}
</div>
