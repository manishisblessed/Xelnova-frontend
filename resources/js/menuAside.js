import {
    mdiAccountSupervisor,
    mdiViewDashboard,
    mdiAccountBoxMultiple,
    mdiAccountGroup,
    mdiMonitorEye,
    mdiAccountEye,
    mdiArchiveEye,
    mdiStorefront,
    mdiPackageVariant,
    mdiShape,
    mdiCube,
    mdiTag,
    mdiCart,
    mdiClipboardList,
    mdiAlertCircle,
    mdiCashRefund,
    mdiCurrencyUsd,
    mdiPercent,
    mdiCashMultiple,
    mdiSale,
    mdiTicketPercent,
    mdiFlash,
    mdiStar,
    mdiImage,
    mdiFileDocument,
    mdiFilePlus,
    mdiChartLine,
    mdiChartBar,
    mdiAccountStar,
    mdiCashRegister,
    mdiShieldAccount,
} from "@mdi/js";

export default [
    {
        route: "dashboard",
        icon: mdiViewDashboard,
        label: "Dashboard",
    },
    {
        label: "User Management",
        icon: mdiAccountSupervisor,
        menu: [
            {
                route: "user.index",
                label: "Users",
                icon: mdiAccountBoxMultiple,
                resource: "user",
            },
            {
                route: "seller.index",
                label: "Sellers",
                icon: mdiStorefront,
                resource: "seller",
            },
            {
                route: "customer.index",
                label: "Customers",
                icon: mdiAccountGroup,
                resource: "customer",
            },
            {
                route: "role.index",
                label: "Roles",
                icon: mdiShieldAccount,
                resource: "role",
            },
        ],
    },

    {
        label: "Catalog",
        icon: mdiPackageVariant,
        menu: [
            {
                route: "category.index",
                label: "Categories",
                icon: mdiShape,
                resource: "category",
            },
            {
                route: "product.index",
                label: "Products",
                icon: mdiCube,
                resource: "product",
            },
           /* {
                route: "brand.index",
                label: "Brands",
                icon: mdiTag,
                resource: "brand",
            },*/
            {
                route: "sellerBrand.index",
                label: "Seller Brands",
                icon: mdiTag,
                resource: "sellerBrand",
            },
            {
                route: "variantType.index",
                label: "Variant Types",
                icon: mdiShape,
                resource: "variantType",
            },
        ],
    },
    {
        label: "Orders",
        icon: mdiCart,
        menu: [
            {
                route: "order.index",
                label: "All Orders",
                icon: mdiClipboardList,
                resource: "order",
            },
            {
                route: "dispute.index",
                label: "Disputes",
                icon: mdiAlertCircle,
                resource: "dispute",
            },
            {
                route: "refund.index",
                label: "Refunds",
                icon: mdiCashRefund,
                resource: "refund",
            },
        ],
    },
    {
        label: "Payments & Payouts",
        icon: mdiCurrencyUsd,
        menu: [
            {
                route: "commission.index",
                label: "Commission",
                icon: mdiPercent,
                resource: "commission",
            },
            {
                route: "payout.index",
                label: "Payouts",
                icon: mdiCashMultiple,
                resource: "payout",
            },
        ],
    },
    {
        label: "Promotions",
        icon: mdiSale,
        menu: [
            {
                route: "coupon.index",
                label: "Coupons",
                icon: mdiTicketPercent,
                resource: "coupon",
            },
            {
                route: "flashDeal.index",
                label: "Flash Deals",
                icon: mdiFlash,
                resource: "flashDeal",
            },
            {
                route: "featuredProduct.index",
                label: "Featured",
                icon: mdiStar,
                resource: "featuredProduct",
            },
            {
                route: "banner.index",
                label: "Banners",
                icon: mdiImage,
                resource: "banner",
            },
        ],
    },
    {
        label: "Content",
        icon: mdiFileDocument,
        menu: [
            {
                route: "page.index",
                label: "Pages",
                icon: mdiFilePlus,
                resource: "page",
            },
        ],
    },
    {
        label: "Reports",
        icon: mdiChartLine,
        menu: [
            {
                route: "salesReport.index",
                label: "Sales",
                icon: mdiChartBar,
                resource: "salesReport",
            },
            {
                route: "sellerPerformance.index",
                label: "Seller Performance",
                icon: mdiAccountStar,
                resource: "sellerPerformance",
            },
            {
                route: "revenueReport.index",
                label: "Revenue",
                icon: mdiCashRegister,
                resource: "revenueReport",
            },
        ],
    },
    {
        label: "Logs",
        icon: mdiMonitorEye,
        menu: [
            {
                route: "signinLog.index",
                label: "Signin Logs",
                icon: mdiAccountEye,
                resource: "signinLog",
            },
            {
                route: "activityLog.index",
                label: "Activity Logs",
                icon: mdiArchiveEye,
                resource: "activityLog",
            },
        ],
    },
];