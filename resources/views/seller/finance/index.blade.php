<x-seller.layout>
    @section('title', 'Finance & Payments')

    @php
        $formatMoney = fn($value) => '₹' . number_format((float) $value, 2);
        $canRequest = $eligibility['has_verified_bank'] && !$eligibility['has_pending_request'] && ($summary['available_balance'] ?? 0) >= ($eligibility['min_amount'] ?? 500);
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Total Earned</h3>
            <div class="text-2xl font-bold text-gray-900">{{ $formatMoney($summary['total_earned'] ?? 0) }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Commission Paid</h3>
            <div class="text-2xl font-bold text-red-600">{{ $formatMoney($summary['commission_paid'] ?? 0) }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Available Balance</h3>
            <div class="text-2xl font-bold text-green-600">{{ $formatMoney($summary['available_balance'] ?? 0) }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Paid Out</h3>
            <div class="text-2xl font-bold text-gray-900">{{ $formatMoney($summary['paid_out'] ?? 0) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Request Payout</h2>
                <p class="text-sm text-gray-500">Commission Rate: {{ number_format((float) ($eligibility['commission_rate'] ?? 0), 2) }}% | Minimum Request: ₹500.00</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded border border-red-200 bg-red-50 text-red-700 text-sm">
                {{ collect($errors->all())->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('seller.finance.payout-request') }}" class="space-y-3">
            @csrf
            <textarea name="seller_notes" class="w-full border rounded-lg px-3 py-2 text-sm" rows="2" placeholder="Notes for admin (optional)">{{ old('seller_notes') }}</textarea>
            <button
                type="submit"
                class="px-4 py-2 rounded-lg text-sm font-medium {{ $canRequest ? 'bg-xelnova-green-600 text-white hover:bg-xelnova-green-700' : 'bg-gray-200 text-gray-500 cursor-not-allowed' }}"
                {{ $canRequest ? '' : 'disabled' }}
            >
                Request Full Available Balance
            </button>
        </form>

        @unless($canRequest)
            <div class="mt-3 text-sm text-gray-600">
                @if(!$eligibility['has_verified_bank'])
                    Verified bank account is required.
                @elseif($eligibility['has_pending_request'])
                    You already have a pending payout request.
                @elseif(($summary['available_balance'] ?? 0) < ($eligibility['min_amount'] ?? 500))
                    Available balance must be at least ₹500.00.
                @endif
            </div>
        @endunless
    </div>

    <div class="bg-white rounded-lg shadow-sm mb-8">
        <div class="p-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">Payout Request History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Request #</th>
                        <th class="px-6 py-3">Requested</th>
                        <th class="px-6 py-3">Approved</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Requested At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payoutRequests as $request)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $request->request_number }}</td>
                            <td class="px-6 py-4">{{ $formatMoney($request->requested_amount) }}</td>
                            <td class="px-6 py-4">{{ $formatMoney($request->approved_amount ?? 0) }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded
                                    {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $request->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $request->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $request->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                ">{{ ucfirst($request->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ optional($request->requested_at)->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">No payout requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $payoutRequests->links() }}</div>
    </div>

    <div class="bg-white rounded-lg shadow-sm">
        <div class="p-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">Ledger History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Reference</th>
                        <th class="px-6 py-3 text-right">Amount</th>
                        <th class="px-6 py-3 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledgerEntries as $entry)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-500">{{ $entry->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ str_replace('_', ' ', ucfirst($entry->entry_type)) }}</td>
                            <td class="px-6 py-4 text-gray-500">
                                @if($entry->subOrder)
                                    {{ $entry->subOrder->sub_order_number }}
                                @elseif($entry->payoutRequest)
                                    {{ $entry->payoutRequest->request_number }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right {{ $entry->direction === 'credit' ? 'text-green-600' : 'text-red-600' }} font-medium">
                                {{ $entry->direction === 'credit' ? '+' : '-' }}{{ $formatMoney($entry->amount) }}
                            </td>
                            <td class="px-6 py-4 text-right font-medium">{{ $formatMoney($entry->balance_after) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">No ledger entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $ledgerEntries->links() }}</div>
    </div>
</x-seller.layout>
