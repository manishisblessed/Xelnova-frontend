<x-seller.layout>
    @section('title', 'My Brands')

    <div x-data="brandsManager()">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Brand Management</h2>
                <p class="text-gray-600 mt-1">Create and manage your brands for product listings</p>
            </div>
            <button @click="openAddModal()" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Brand
            </button>
        </div>

        <!-- Info Banner -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">Important Information:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Submit your brand with proof document (trademark certificate, registration, etc.)</li>
                        <li>Brands must be approved by admin before use in product listings</li>
                        <li>Only approved brands will appear in your product creation form</li>
                        <li>Provide accurate information to avoid rejection</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Brands List -->
        @if($brands->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Brands Added</h3>
                <p class="text-gray-600 mb-4">Create your first brand to start listing products</p>
                <button @click="openAddModal()" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Add Your First Brand
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($brands as $brand)
                    <div class="bg-white rounded-lg shadow-sm p-6 border-2 transition
                        {{ $brand->isApproved() ? 'border-green-500' : ($brand->isRejected() ? 'border-red-500' : 'border-gray-200 hover:border-gray-300') }}">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                @if($brand->logo_path)
                                    <img src="{{ Storage::url($brand->logo_path) }}" 
                                        alt="{{ $brand->brand_name }}" 
                                        class="w-12 h-12 object-cover rounded-lg">
                                @else
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $brand->brand_name }}</h4>
                                    <p class="text-xs text-gray-500">Created {{ $brand->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                @if($brand->isApproved()) bg-green-100 text-green-800
                                @elseif($brand->isRejected()) bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($brand->approval_status) }}
                            </span>
                        </div>
                        
                        @if($brand->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $brand->description }}</p>
                        @endif

                        @if($brand->isRejected() && $brand->rejection_reason)
                            <div class="bg-red-50 border border-red-200 rounded p-3 mb-4">
                                <p class="text-xs font-medium text-red-800 mb-1">Rejection Reason:</p>
                                <p class="text-xs text-red-700">{{ $brand->rejection_reason }}</p>
                            </div>
                        @endif

                        @if($brand->isApproved())
                            <div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
                                <p class="text-xs text-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Approved on {{ $brand->approved_at->format('M d, Y') }}
                                </p>
                            </div>
                        @endif
                        
                        <div class="flex gap-2 pt-4 border-t">
                            @if($brand->proof_document_path)
                                <a href="{{ route('seller.brands.download-proof', $brand) }}" 
                                    class="flex-1 px-3 py-2 text-sm bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition text-center">
                                    View Proof
                                </a>
                            @endif
                            
                            @if(!$brand->isApproved())
                                <button @click="openEditModal({{ $brand->id }}, '{{ $brand->brand_name }}', '{{ addslashes($brand->description ?? '') }}', {{ $brand->logo_path ? 'true' : 'false' }}, {{ $brand->proof_document_path ? 'true' : 'false' }})" 
                                    class="flex-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('seller.brands.destroy', $brand) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this brand?')"
                                        class="px-3 py-2 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 transition">
                                        Delete
                                    </button>
                                </form>
                            @else
                                <div class="flex-1 px-3 py-2 text-sm bg-gray-50 text-gray-500 rounded text-center cursor-not-allowed">
                                    Cannot Edit (Approved)
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak 
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showModal = false">
            <div class="bg-white rounded-lg max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900" x-text="editMode ? 'Edit Brand' : 'Add Brand'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form :action="editMode ? '{{ url('seller/brands') }}/' + editId : '{{ route('seller.brands.store') }}'" 
                      method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" x-model="editMode ? 'PUT' : 'POST'">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand Name *</label>
                        <input type="text" name="brand_name" x-model="formData.brand_name" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" x-model="formData.description" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Brief description of your brand..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand Logo (Optional)</label>
                        <input type="file" name="logo" accept="image/*" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF up to 2MB</p>
                        <p x-show="formData.hasLogo && editMode" class="text-xs text-green-600 mt-1">✓ Logo already uploaded</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Proof Document <span x-show="!editMode || !formData.hasProof">*</span>
                        </label>
                        <input type="file" name="proof_document" accept=".pdf,.jpg,.jpeg,.png" 
                            :required="!editMode && !formData.hasProof"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Upload trademark certificate, registration document, or authorization letter (PDF, JPG, PNG up to 5MB)</p>
                        <p x-show="formData.hasProof && editMode" class="text-xs text-green-600 mt-1">✓ Proof document already uploaded</p>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <p class="text-xs text-yellow-800">
                            <strong>Note:</strong> Your brand will be reviewed by our admin team. Only approved brands can be used in product listings.
                        </p>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showModal = false" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <span x-text="editMode ? 'Update' : 'Submit'"></span> Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function brandsManager() {
            return {
                showModal: false,
                editMode: false,
                editId: null,
                formData: {
                    brand_name: '',
                    description: '',
                    hasLogo: false,
                    hasProof: false
                },
                
                openAddModal() {
                    this.editMode = false;
                    this.editId = null;
                    this.formData = {
                        brand_name: '',
                        description: '',
                        hasLogo: false,
                        hasProof: false
                    };
                    this.showModal = true;
                },
                
                openEditModal(id, brandName, description, hasLogo, hasProof) {
                    this.editMode = true;
                    this.editId = id;
                    this.formData = {
                        brand_name: brandName,
                        description: description,
                        hasLogo: hasLogo,
                        hasProof: hasProof
                    };
                    this.showModal = true;
                }
            }
        }
    </script>
</x-seller.layout>
