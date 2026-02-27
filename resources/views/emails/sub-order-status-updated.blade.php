<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
        }
        .status-shipped { background: #ddd6fe; color: #5b21b6; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-out_for_delivery { background: #dbeafe; color: #1e40af; }
        .tracking-box {
            background: white;
            border: 2px solid #059669;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .items-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin: 20px 0;
        }
        .items-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .items-table td {
            padding: 12px;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background: #059669;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>XELNOVA</h1>
        <p>Order Status Update</p>
    </div>

    <div class="content">
        <h2>Hello {{ $subOrder->order->user->name }},</h2>
        
        <p>Your order has been updated:</p>
        
        <div style="text-align: center; margin: 20px 0;">
            <span class="status-badge status-{{ $subOrder->status }}">
                {{ ucwords(str_replace('_', ' ', $subOrder->status)) }}
            </span>
        </div>

        <p><strong>Order Number:</strong> {{ $subOrder->sub_order_number }}</p>
        <p><strong>Seller:</strong> {{ $subOrder->seller->seller?->business_name ?? $subOrder->seller->name ?? 'Seller' }}</p>

        @if(in_array($subOrder->status, ['shipped', 'out_for_delivery', 'delivered']) && $subOrder->tracking_number)
            <div class="tracking-box">
                <h3 style="margin-top: 0;">Tracking Information</h3>
                <p><strong>Tracking Number:</strong> {{ $subOrder->tracking_number }}</p>
                @if($subOrder->courier)
                    <p><strong>Courier:</strong> {{ $subOrder->courier }}</p>
                @endif
                @if($subOrder->shipped_at)
                    <p><strong>Shipped On:</strong> {{ $subOrder->shipped_at->format('M d, Y h:i A') }}</p>
                @endif
            </div>
        @endif

        <h3>Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subOrder->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total Amount:</strong> ₹{{ number_format($subOrder->total, 2) }}</p>

        @if($subOrder->status === 'cancelled')
            <p style="color: #991b1b; font-weight: bold;">
                This order has been cancelled. If you have already been charged, a refund will be processed within 5-7 business days.
            </p>
        @elseif($subOrder->status === 'delivered')
            <p style="color: #065f46; font-weight: bold;">
                Your order has been delivered! We hope you enjoy your purchase.
            </p>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('account.order.detail', $subOrder->order->order_number) }}" class="button">
                View Order Details
            </a>
        </div>

        <p style="font-size: 14px; color: #6b7280;">
            If you have any questions about your order, please contact our support team.
        </p>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} XELNOVA. All rights reserved.</p>
        <p>This is an automated email. Please do not reply to this message.</p>
    </div>
</body>
</html>
