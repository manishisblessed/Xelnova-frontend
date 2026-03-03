export default function PrivacyPage() {
  return (
    <div className="min-h-screen bg-surface-raised py-12">
      <div className="container mx-auto px-4">
        <div className="bg-white rounded-2xl border border-border/60 shadow-sm max-w-3xl mx-auto p-8 md:p-12">
          <h1 className="text-3xl font-extrabold text-text-primary mb-6">Privacy Policy</h1>
          <div className="prose prose-sm max-w-none text-text-secondary leading-relaxed space-y-4">
            <p>At Xelnova, we are committed to protecting your privacy. This policy explains how we collect, use, and safeguard your personal information.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">Information We Collect</h2>
            <p>We collect information you provide during registration, orders, and browsing — including name, email, phone number, address, and payment details.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">How We Use Your Data</h2>
            <ul className="list-disc pl-5 space-y-1">
              <li>To process and deliver your orders</li>
              <li>To personalize your shopping experience</li>
              <li>To send order updates and promotional offers</li>
              <li>To improve our platform and services</li>
              <li>To prevent fraud and ensure security</li>
            </ul>
            <h2 className="text-lg font-bold text-text-primary !mt-8">Data Security</h2>
            <p>We use industry-standard SSL encryption and secure servers to protect your data. Payment information is processed through PCI-DSS compliant payment gateways.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">Your Rights</h2>
            <p>You can access, update, or delete your personal data from your account settings. To request data deletion, contact us at privacy@xelnova.in.</p>
          </div>
        </div>
      </div>
    </div>
  );
}
