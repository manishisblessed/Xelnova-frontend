import { cn } from "@/lib/utils";

type Variant = "default" | "success" | "warning" | "danger" | "info";

interface BadgeProps {
  children: React.ReactNode;
  variant?: Variant;
  className?: string;
}

const variants: Record<Variant, string> = {
  default: "bg-gray-100 text-gray-700",
  success: "bg-primary-100 text-primary-800",
  warning: "bg-accent-100 text-accent-800",
  danger: "bg-red-100 text-red-700",
  info: "bg-blue-100 text-blue-700",
};

export function Badge({ children, variant = "default", className }: BadgeProps) {
  return (
    <span
      className={cn(
        "inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-semibold rounded-full",
        variants[variant],
        className
      )}
    >
      {children}
    </span>
  );
}
