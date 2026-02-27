import {
  mdiMenu,
  mdiClockOutline,
  mdiCloud,
  mdiCrop,
  mdiAccount,
  mdiCogOutline,
  mdiEmail,
  mdiLogout,
  mdiThemeLightDark,
  mdiGithub,
  mdiReact,
  mdiCog,
} from "@mdi/js";

export default [
  /*
  {
    icon: mdiMenu,
    label: "Sample menu",
    menu: [
      {
        icon: mdiClockOutline,
        label: "Item One",
      },
      {
        icon: mdiCloud,
        label: "Item Two",
      },
      {
        isDivider: true,
      },
      {
        icon: mdiCrop,
        label: "Item Last",
      },
    ],
  },*/
  {
    isCurrentUser: true,
    menu: [
      {
        icon: mdiAccount,
        label: "My Profile",
        route: "profile.profile",
      },
      {
        isDivider: true,
      },
      {
        icon: mdiLogout,
        label: "Log Out",
        isLogout: true,
      },
    ],
  },
  {
    icon: mdiThemeLightDark,
    label: "Light/Dark",
    isDesktopNoLabel: true,
    isToggleLightDark: true,
  },
  {
    icon: mdiCog,
    label: "Settings",
    isDesktopNoLabel: true,
    menu: [
      {
        icon: mdiCogOutline,
        label: "General Settings",
        route: "setting.list",
        permission: "setting_list",
      },
      {
        icon: mdiReact, // or any appropriate icon like mdiCurrencyUsd or mdiPercent
        label: "Tax Rates",
        route: "tax-rate.index",
        // permission: "tax_rate_list", // Add if/when permissions are implemented
      },
    ],
  },
  {
    icon: mdiLogout,
    label: "Log out",
    isDesktopNoLabel: true,
    isLogout: true,
  },
];
