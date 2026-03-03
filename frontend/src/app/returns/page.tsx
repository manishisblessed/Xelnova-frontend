export default function ReturnsPage() {
  return (
    <div className="min-h-screen bg-surface-raised py-12">
      <div className="container mx-auto px-4">
        <div className="bg-white rounded-2xl border border-border/60 shadow-sm max-w-3xl mx-auto p-8 md:p-12">
          <h1 className="text-3xl font-extrabold text-text-primary mb-6">Return Policy</h1>
          <div className="prose prose-sm max-w-none text-text-secondary leading-relaxed space-y-4">
            <p>We want you to be completely happy with your purchase. If something isn&apos;t right, our return policy makes it easy to get a refund or replacement.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">Return Window</h2>
            <p>Most products can be returned within <strong>30 days</strong> of delivery. Electronics have a 10-day replacement window for manufacturing defects.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">How to Initiate a Return</h2>
            <ol className="list-decimal pl-5 space-y-1">
              <li>Go to My Orders and select the item you want to return</li>
              <li>Choose your reason for return</li>
              <li>Schedule a pickup or drop the item at a collection center</li>
              <li>Refund will be processed within 5-7 business days</li>
            </ol>
            <h2 className="text-lg font-bold text-text-primary !mt-8">Non-Returnable Items</h2>
            <p>Certain categories like grocery, innerwear, customized products, and digital downloads are non-returnable. Check the product page for specific return eligibility.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">Refund Methods</h2>
            <p>Refunds are processed to the original payment method. For COD orders, refunds are credited to your Xelnova wallet or bank account.</p>
          </div>
        </div>
      </div>
    </div>
  );
}
