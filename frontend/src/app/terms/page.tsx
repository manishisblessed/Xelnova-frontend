export default function TermsPage() {
  return (
    <div className="min-h-screen bg-surface-raised py-12">
      <div className="container mx-auto px-4">
        <div className="bg-white rounded-2xl border border-border/60 shadow-sm max-w-3xl mx-auto p-8 md:p-12">
          <h1 className="text-3xl font-extrabold text-text-primary mb-6">Terms of Use</h1>
          <div className="prose prose-sm max-w-none text-text-secondary leading-relaxed space-y-4">
            <p>Welcome to Xelnova. By accessing or using our platform, you agree to be bound by these Terms of Use. Please read them carefully before using our services.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">1. Use of Platform</h2>
            <p>You must be at least 18 years old to use our services. You agree to provide accurate information during registration and keep your account credentials secure.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">2. Orders & Payments</h2>
            <p>All orders are subject to product availability and confirmation. Prices are inclusive of applicable taxes. We reserve the right to cancel orders if fraud or unauthorized activity is detected.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">3. Intellectual Property</h2>
            <p>All content on Xelnova including logos, images, and text is the property of Xelnova Private Limited and protected by intellectual property laws.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">4. Limitation of Liability</h2>
            <p>Xelnova acts as a marketplace facilitator. We are not responsible for the quality, safety, or legality of products listed by sellers. Disputes should be raised through our resolution center.</p>
            <h2 className="text-lg font-bold text-text-primary !mt-8">5. Governing Law</h2>
            <p>These terms are governed by the laws of India. Any disputes shall be subject to the jurisdiction of courts in New Delhi.</p>
          </div>
        </div>
      </div>
    </div>
  );
}
