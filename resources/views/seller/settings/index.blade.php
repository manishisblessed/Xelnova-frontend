<x-seller.layout>
    @section('title', 'Settings')

    <div class="space-y-6">
        <!-- Pincode Settings -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="p-6 border-b">
                <h2 class="text-lg font-bold text-gray-800">Delivery Settings</h2>
                <p class="text-sm text-gray-500">Configure your base location to calculate delivery estimates for customers.</p>
            </div>
            
            <form action="{{ route('seller.settings.update') }}" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label for="postal_code" class="block text-sm font-medium text-gray-700">Pickup Pincode <span class="text-red-500">*</span></label>
                        <input type="text" name="postal_code" id="postal_code" 
                               value="{{ old('postal_code', $seller->postal_code) }}" 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-xelnova-green-500 focus:ring-xelnova-green-500" 
                               placeholder="e.g. 560001" required maxlength="10">
                        @error('postal_code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">This pincode will be used as the starting point for calculating delivery times.</p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Location Status</h3>
                        @if($seller->latitude && $seller->longitude)
                            <div class="flex items-center gap-2 text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium">Location coordinates synced</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                Latitude: {{ number_format($seller->latitude, 6) }}<br>
                                Longitude: {{ number_format($seller->longitude, 6) }}
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-amber-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium">Pending Sync</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Coordinates will be updated automatically when you save.</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4">
                    <button type="submit" class="bg-xelnova-green-600 hover:bg-xelnova-green-700 text-white font-bold py-2 px-6 rounded shadow-sm transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-seller.layout>
