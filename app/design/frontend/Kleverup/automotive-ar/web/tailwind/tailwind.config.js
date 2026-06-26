const {
  spacing
} = require('tailwindcss/defaultTheme');

const colors = require('tailwindcss/colors');

const hyvaModules = require('@hyva-themes/hyva-modules');

module.exports = hyvaModules.mergeTailwindConfig({
  theme: {
    extend: {
      screens: {
        'sm': '640px',
        'md': '768px',
        'lg': '1024px',
        'xl': '1280px',
         'xl2': '1440px',
        '2xl': '1536px'
      },
      fontFamily: {
        icon: ["fontello"],
      },
      colors: {
        primary: {
          lighter: colors.blue['300'],
          "DEFAULT": colors.blue['800'],
          darker: colors.blue['900']
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        background: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        green: colors.emerald,
        yellow: colors.amber,
        purple: colors.violet,
       "theme-blue": "#00843D",
        "theme-yellow": "#FFC300",
        "theme-dark": "#000",
        "theme-light": "#DBDBDB",
        "theme-white": "#fff",
        "border-color": "#D9D9D9",
      },
      textColor: {
        orange: colors.orange,
        red: { ...colors.red,
          "DEFAULT": colors.red['500']
        },
        primary: {
          lighter: colors.gray['700'],
          "DEFAULT": colors.gray['800'],
          darker: colors.gray['900']
        },
        secondary: {
          lighter: colors.gray['400'],
          "DEFAULT": colors.gray['600'],
          darker: colors.gray['800']
        },
        "theme-blue": "#00843D",
        "theme-yellow": "#FFC300",
        "theme-dark": "#000",
        "theme-light": "#DBDBDB",
      },
      backgroundColor: {
        primary: {
          lighter: colors.blue['600'],
          "DEFAULT": colors.blue['700'],
          darker: colors.blue['800']
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        container: {
          lighter: colors.white,
          "DEFAULT": colors.neutral['50'],
          darker: colors.neutral['100']
        },
        "theme-blue": "#00843D",
        "theme-yellow": "#FFC300",
        "theme-dark": "#000",
        "theme-light": "#DBDBDB",
      },
      borderColor: {
        primary: {
          lighter: colors.blue['600'],
          "DEFAULT": colors.blue['700'],
          darker: colors.blue['800']
        },
        secondary: {
          lighter: colors.blue['100'],
          "DEFAULT": colors.blue['200'],
          darker: colors.blue['300']
        },
        container: {
          lighter: colors.neutral['100'],
          "DEFAULT": '#e7e7e7',
          darker: '#b6b6b6'
        },
        "theme-blue": "#00843D",
        "theme-yellow": "#FFC300",
        "theme-dark": "#000",
        "theme-light": "#DBDBDB",
      },
      minHeight: {
        a11y: spacing["11"],
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      maxHeight: {
        'screen-25': '25vh',
        'screen-50': '50vh',
        'screen-75': '75vh'
      },
      container: {
        center: true,
        padding: spacing["6"]
      },
      fontSize: {
        xsm: "0.625rem",
        f106: "6.625rem",
        f85: "5.313rem",
        f60: "3.75rem",
        f59: "3.688rem",
        f58: "3.625rem",
        f57: "3.563rem",
        f56: "3.5rem",
        f55: "3.438rem",
        f54: "3.375rem",
        f53: "3.313rem",
        f52: "3.25rem",
        f51: "3.188rem",
        f50: "3.125rem",
        f49: "3.063rem",
        f48: "3rem",
        f47: "2.938rem",
        f46: "2.875rem",
        f45: "2.813rem",
        f44: "2.75rem",
        f40: "2.5rem",
        f39: "2.438rem",
        f38: "2.375rem",
        f37: "2.313rem",
        f36: "2.25rem",
        f35: "2.188rem",
        f34: "2.125rem",
        f33: "2.063rem",
        f32: "2rem",
        f31: "1.938rem",
        f30: "1.875rem",
        f29: "1.813rem",
        f28: "1.75rem",
        f27: "1.688rem",
        f26: "1.625rem",
        f25: "1.563rem",
        f24: "1.5rem",
        f23: "1.438rem",
        f22: "1.375rem",
        f21: "1.313rem",
        f20: "1.25rem",
        f19: "1.188rem",
        f18: "1.125rem",
        f17: "1.063rem",
        f16: "1rem",
        f15: "0.938rem",
        f14: "0.875rem",
        f13: "0.813rem",
        f12: "0.75rem",
        f11: "0.6875rem",
        f10: "0.625rem",
        f9: "0.5625rem",
        f8: "0.5rem",
        f7: "0.438rem",
        f6: "0.375rem",
        f1: "0.063rem",
      },
      animation: {
        marquee: 'marquee 25s linear infinite',
        marquee2: 'marquee2 25s linear infinite',
      },
      keyframes: {
        marquee: {
          '0%': { transform: 'translateX(0%)' },
          '100%': { transform: 'translateX(100%)' },
        },
        marquee2: {
          '0%': { transform: 'translateX(-100%)' },
          '100%': { transform: 'translateX(0%)' },
        },
      },
    }
  },
  plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
  content: [
    // this theme's phtml and layout XML files
    '../../**/*.phtml',
    '../../*/layout/*.xml',
    '../../*/page_layout/override/base/*.xml',
    // parent theme (automotive-en)
    '../../../automotive-en/**/*.phtml',
    '../../../automotive-en/*/layout/*.xml',
    '../../../automotive-en/*/page_layout/override/base/*.xml',
    // Hyva vendor theme
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/**/*.phtml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/layout/*.xml',
    '../../../../../../../vendor/hyva-themes/magento2-default-theme/*/page_layout/override/base/*.xml',
    // app/code phtml files
    '../../../../../../../app/code/**/*.phtml',
  ]
});
