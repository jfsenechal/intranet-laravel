<div
    x-data="{
        previewUrl: null,
        contentType: null,
        fileName: null,
    }"
    x-init="
        document.addEventListener('FilePond:addfile', (e) => {
            const file = e.detail.file.file;
            fileName = file.name;
            contentType = file.type;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            const isPreviewable = contentType.startsWith('image/') || contentType === 'application/pdf';
            previewUrl = isPreviewable ? URL.createObjectURL(file) : null;
        });
        document.addEventListener('FilePond:removefile', () => {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            contentType = null;
            fileName = null;
        });
    "
>
    <template x-if="previewUrl">
        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <template x-if="contentType === 'application/pdf'">
                <iframe
                    :src="previewUrl"
                    class="h-[600px] w-full rounded-lg border-0"
                    :title="fileName"
                ></iframe>
            </template>
            <template x-if="contentType && contentType.startsWith('image/')">
                <img
                    :src="previewUrl"
                    :alt="fileName"
                    class="mx-auto max-h-[600px] rounded-lg object-contain"
                />
            </template>
        </div>
    </template>

    <template x-if="fileName && !previewUrl">
        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-col items-center justify-center py-8">
                <x-filament::icon
                    icon="tabler-file"
                    class="mb-2 h-12 w-12 text-gray-400"
                />
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="fileName"></p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500" x-text="contentType"></p>
            </div>
        </div>
    </template>
</div>
