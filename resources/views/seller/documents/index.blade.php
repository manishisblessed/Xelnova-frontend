<x-seller.layout>
    @section('title', 'Documents')

    <div x-data="documentsManager()">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Document Management</h2>
                <p class="text-gray-600 mt-1">Upload and manage your business documents</p>
            </div>
            <button @click="showUploadModal = true" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Upload Document
            </button>
        </div>

        <!-- Document Types Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-medium text-blue-900 mb-2">Required Documents:</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• PAN Card - Required for tax purposes</li>
                <li>• GST Certificate - If you have GST registration</li>
                <li>• Business Registration - Company/Partnership registration proof</li>
                <li>• Address Proof - Utility bill or rental agreement</li>
                <li>• Bank Statement - Last 3 months statement</li>
            </ul>
        </div>

        <!-- Documents List -->
        @if($documents->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Documents Uploaded</h3>
                <p class="text-gray-600 mb-4">Upload your business documents to complete verification</p>
                <button @click="showUploadModal = true" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Upload Your First Document
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($documents as $document)
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200 hover:shadow-md transition">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 text-sm">{{ ucwords(str_replace('_', ' ', $document->document_type)) }}</h4>
                                    <p class="text-xs text-gray-500">{{ $document->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($document->verification_status === 'verified') bg-green-100 text-green-800
                                @elseif($document->verification_status === 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($document->verification_status) }}
                            </span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-3 truncate">{{ $document->original_filename }}</p>
                        
                        @if($document->rejection_reason)
                            <div class="bg-red-50 border border-red-200 rounded p-2 mb-3">
                                <p class="text-xs text-red-800"><strong>Reason:</strong> {{ $document->rejection_reason }}</p>
                            </div>
                        @endif
                        
                        <div class="flex gap-2">
                            <a href="{{ route('seller.documents.download', $document) }}" 
                                class="flex-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition text-center">
                                Download
                            </a>
                            <form method="POST" action="{{ route('seller.documents.destroy', $document) }}" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this document?')"
                                    class="w-full px-3 py-2 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Upload Modal -->
        <div x-show="showUploadModal" x-cloak 
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showUploadModal = false">
            <div class="bg-white rounded-lg max-w-md w-full p-6" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900">Upload Document</h3>
                    <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form method="POST" action="{{ route('seller.documents.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Document Type *</label>
                        <select name="document_type" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <option value="">Select document type</option>
                            <option value="pan_card">PAN Card</option>
                            <option value="gst_certificate">GST Certificate</option>
                            <option value="business_registration">Business Registration</option>
                            <option value="address_proof">Address Proof</option>
                            <option value="bank_statement">Bank Statement</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload File *</label>
                        <input type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png"
                            @change="fileName = $event.target.files[0]?.name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <p class="mt-1 text-xs text-gray-500">Accepted: PDF, JPG, PNG (Max 5MB)</p>
                        <p x-show="fileName" class="mt-1 text-xs text-green-600" x-text="'Selected: ' + fileName"></p>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showUploadModal = false" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function documentsManager() {
            return {
                showUploadModal: false,
                fileName: ''
            }
        }
    </script>
</x-seller.layout>
