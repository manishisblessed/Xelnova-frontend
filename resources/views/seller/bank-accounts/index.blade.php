<x-seller.layout>
    @section('title', 'Bank Accounts')

    <div x-data="bankAccountsManager()">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bank Account Management</h2>
                <p class="text-gray-600 mt-1">Manage your payout bank accounts</p>
            </div>
            <button @click="openAddModal()" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Add Bank Account
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
                        <li>Add at least one bank account to receive payouts</li>
                        <li>Set one account as primary for automatic payouts</li>
                        <li>Bank accounts will be verified by our team</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bank Accounts List -->
        @if($bankAccounts->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Bank Accounts Added</h3>
                <p class="text-gray-600 mb-4">Add your bank account to receive payouts</p>
                <button @click="openAddModal()" 
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    Add Your First Bank Account
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($bankAccounts as $account)
                    <div class="bg-white rounded-lg shadow-sm p-6 border-2 transition
                        {{ $account->is_primary ? 'border-green-500' : 'border-gray-200 hover:border-gray-300' }}">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $account->bank_name }}</h4>
                                    <p class="text-sm text-gray-600">{{ $account->account_holder_name }}</p>
                                </div>
                            </div>
                            @if($account->is_primary)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                    Primary
                                </span>
                            @endif
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Account Number:</span>
                                <span class="font-mono font-medium">****{{ substr($account->account_number, -4) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">IFSC Code:</span>
                                <span class="font-mono font-medium">{{ $account->ifsc_code }}</span>
                            </div>
                            @if($account->branch_name)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Branch:</span>
                                    <span class="font-medium">{{ $account->branch_name }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    @if($account->verification_status === 'verified') bg-green-100 text-green-800
                                    @elseif($account->verification_status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($account->verification_status) }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 pt-4 border-t">
                            @if(!$account->is_primary)
                                <form method="POST" action="{{ route('seller.bank-accounts.set-primary', $account) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                        class="w-full px-3 py-2 text-sm bg-green-50 text-green-700 rounded hover:bg-green-100 transition">
                                        Set as Primary
                                    </button>
                                </form>
                            @endif
                            <button @click="openEditModal({{ $account->id }}, '{{ $account->account_holder_name }}', '{{ $account->account_number }}', '{{ $account->bank_name }}', '{{ $account->ifsc_code }}', '{{ $account->branch_name }}', {{ $account->is_primary ? 'true' : 'false' }})" 
                                class="flex-1 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('seller.bank-accounts.destroy', $account) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this bank account?')"
                                    class="px-3 py-2 text-sm bg-red-50 text-red-600 rounded hover:bg-red-100 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Add/Edit Modal -->
        <div x-show="showModal" x-cloak 
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showModal = false">
            <div class="bg-white rounded-lg max-w-lg w-full p-6" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-900" x-text="editMode ? 'Edit Bank Account' : 'Add Bank Account'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form :action="editMode ? '{{ url('seller/bank-accounts') }}/' + editId : '{{ route('seller.bank-accounts.store') }}'" 
                      method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" x-model="editMode ? 'PUT' : 'POST'">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Holder Name *</label>
                        <input type="text" name="account_holder_name" x-model="formData.account_holder_name" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Number *</label>
                        <input type="text" name="account_number" x-model="formData.account_number" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name *</label>
                        <input type="text" name="bank_name" x-model="formData.bank_name" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">IFSC Code *</label>
                            <input type="text" name="ifsc_code" x-model="formData.ifsc_code" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="SBIN0001234">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name</label>
                            <input type="text" name="branch_name" x-model="formData.branch_name" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_primary" x-model="formData.is_primary" value="1"
                                class="text-green-600 focus:ring-green-500 rounded">
                            <span class="text-sm text-gray-700">Set as primary account</span>
                        </label>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showModal = false" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            <span x-text="editMode ? 'Update' : 'Add'"></span> Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bankAccountsManager() {
            return {
                showModal: false,
                editMode: false,
                editId: null,
                formData: {
                    account_holder_name: '',
                    account_number: '',
                    bank_name: '',
                    ifsc_code: '',
                    branch_name: '',
                    is_primary: false
                },
                
                openAddModal() {
                    this.editMode = false;
                    this.editId = null;
                    this.formData = {
                        account_holder_name: '',
                        account_number: '',
                        bank_name: '',
                        ifsc_code: '',
                        branch_name: '',
                        is_primary: false
                    };
                    this.showModal = true;
                },
                
                openEditModal(id, holderName, accountNumber, bankName, ifscCode, branchName, isPrimary) {
                    this.editMode = true;
                    this.editId = id;
                    this.formData = {
                        account_holder_name: holderName,
                        account_number: accountNumber,
                        bank_name: bankName,
                        ifsc_code: ifscCode,
                        branch_name: branchName,
                        is_primary: isPrimary
                    };
                    this.showModal = true;
                }
            }
        }
    </script>
</x-seller.layout>
