{{-- Image Upload Component --}}
<div class="row" x-data="imageUploadPreview()">
    {{-- Main Thumbnail Image --}}
    <div class="col-md-6 mb-3">
        <label for="itemThumbnail" class="form-label font-hold fw-bold">
            {{ __('items.main_image') }}
            <span class="text-muted">({{ __('items.optional') }})</span>
        </label>
        
        <div class="border rounded p-3">
            {{-- Preview Existing Thumbnail --}}
            @if($creating === false && $existingThumbnail)
                <div class="mb-3">
                    <p class="text-muted small mb-2">{{ __('items.current_image') }}:</p>
                    <div class="position-relative d-inline-block">
                        <img src="{{ $existingThumbnail->getUrl('preview') }}" 
                             alt="Current thumbnail" 
                             class="img-thumbnail"
                             style="max-width: 200px; max-height: 200px;">
                        <button type="button" 
                                wire:click="deleteExistingImage({{ $existingThumbnail->id }}, 'thumbnail')"
                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                style="z-index: 10;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Upload New Thumbnail --}}
            <div class="mb-2">
                <input type="file" 
                       wire:model="itemThumbnail" 
                       class="form-control @error('itemThumbnail') is-invalid @enderror"
                       id="itemThumbnail"
                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                       @change="previewThumbnail($event)">
                @error('itemThumbnail')
                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                @enderror
            </div>

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="itemThumbnail" class="text-primary">
                <i class="fas fa-spinner fa-spin"></i> {{ __('items.uploading') }}...
            </div>

            {{-- Preview New Thumbnail (Client-side) --}}
            <div x-show="thumbnailPreview" class="mt-2" style="display: none;">
                <p class="text-muted small mb-2">{{ __('items.preview') }}:</p>
                <div class="position-relative d-inline-block">
                    <img :src="thumbnailPreview" 
                         alt="Preview" 
                         class="img-thumbnail"
                         style="max-width: 200px; max-height: 200px; object-fit: cover;">
                    <button type="button" 
                            @click="clearThumbnail()"
                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                            style="z-index: 10;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <small class="text-muted d-block mt-2">
                {{ __('items.allowed_formats') }}: JPG, PNG, GIF, WEBP. {{ __('items.max_size') }}: 2MB
            </small>
        </div>
    </div>

    {{-- Additional Images --}}
    <div class="col-md-6 mb-3">
        <label for="itemImages" class="form-label font-hold fw-bold">
            {{ __('items.additional_images') }}
            <span class="text-muted">({{ __('items.optional') }})</span>
        </label>
        
        <div class="border rounded p-3">
            {{-- Display Existing Images --}}
            @if($creating === false && !empty($existingImages) && count($existingImages) > 0)
                <div class="mb-3">
                    <p class="text-muted small mb-2">{{ __('items.current_images') }}:</p>
                    <div class="row g-2">
                        @foreach($existingImages as $image)
                            <div class="col-4">
                                <div class="position-relative">
                                    <img src="{{ $image->getUrl('thumb') }}" 
                                         alt="Item image" 
                                         class="img-thumbnail w-100"
                                         style="height: 100px; object-fit: cover; cursor: pointer;"
                                         onclick="window.open('{{ $image->getUrl('large') }}', '_blank')">
                                    <button type="button" 
                                            wire:click="deleteExistingImage({{ $image->id }}, 'gallery')"
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                            style="z-index: 10; padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Upload New Images --}}
            <div class="mb-2">
                <input type="file" 
                       wire:model="itemImages" 
                       class="form-control @error('itemImages.*') is-invalid @enderror"
                       id="itemImages"
                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                       multiple
                       @change="previewMultipleImages($event)">
                @error('itemImages.*')
                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                @enderror
            </div>

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="itemImages" class="text-primary">
                <i class="fas fa-spinner fa-spin"></i> {{ __('items.uploading') }}...
            </div>

            {{-- Preview New Images (Client-side) --}}
            <div x-show="imagesPreviews.length > 0" class="mt-2" style="display: none;">
                <p class="text-muted small mb-2">{{ __('items.preview') }}:</p>
                <div class="row g-2">
                    <template x-for="(preview, index) in imagesPreviews" :key="index">
                        <div class="col-4">
                            <div class="position-relative">
                                <img :src="preview" 
                                     alt="Preview" 
                                     class="img-thumbnail w-100"
                                     style="height: 100px; object-fit: cover;">
                                <button type="button" 
                                        @click="removeImagePreview(index)"
                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                        style="z-index: 10; padding: 0.25rem 0.5rem;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <small class="text-muted d-block mt-2">
                {{ __('items.multiple_images_note') }}. {{ __('items.max_size') }}: 2MB {{ __('items.per_image') }}
            </small>
        </div>
    </div>
</div>

{{-- Image Guidelines --}}
{{-- <div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>{{ __('items.image_tips') }}:</strong>
            <ul class="mb-0 mt-2">
                <li>{{ __('items.tip_1') }}</li>
                <li>{{ __('items.tip_2') }}</li>
                <li>{{ __('items.tip_3') }}</li>
                <li>{{ __('items.tip_4') }}</li>
            </ul>
        </div>
    </div>
</div> --}}

{{-- Alpine.js Component for Client-side Image Preview --}}
<script>
function imageUploadPreview() {
    return {
        thumbnailPreview: null,
        imagesPreviews: [],
        
        previewThumbnail(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('{{ __("items.invalid_image_format") }}');
                    event.target.value = '';
                    this.thumbnailPreview = null;
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('{{ __("items.image_too_large") }}');
                    event.target.value = '';
                    this.thumbnailPreview = null;
                    return;
                }
                
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.thumbnailPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.thumbnailPreview = null;
            }
        },
        
        clearThumbnail() {
            this.thumbnailPreview = null;
            document.getElementById('itemThumbnail').value = '';
            // Trigger Livewire to clear the model
            @this.set('itemThumbnail', null);
        },
        
        previewMultipleImages(event) {
            const files = Array.from(event.target.files);
            this.imagesPreviews = [];
            
            if (files.length === 0) return;
            
            files.forEach((file, index) => {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    console.warn(`File ${file.name} is not a valid image format`);
                    return;
                }
                
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    console.warn(`File ${file.name} is too large (max 2MB)`);
                    return;
                }
                
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagesPreviews.push(e.target.result);
                };
                reader.readAsDataURL(file);
            });
        },
        
        removeImagePreview(index) {
            this.imagesPreviews.splice(index, 1);
            
            // Clear the file input if no previews left
            if (this.imagesPreviews.length === 0) {
                document.getElementById('itemImages').value = '';
                @this.set('itemImages', []);
            }
        }
    }
}
</script>

