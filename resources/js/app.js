import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';

function applyAppearance(settings) {
  if (!settings || typeof settings !== 'object') return;

  const root = document.documentElement;

  const primary = '#000000';
  const secondary = '#173b2f';
  const button = '#173b2f';
  const footerBg = '#000000';
  const fontFamily = "'Urbanist', system-ui, -apple-system, Segoe UI, sans-serif";
  const fontSizeText = Number(settings.font_size_text ?? 16);
  const fontSizeTitle = Number(settings.font_size_title ?? 40);

  root.style.setProperty('--site-primary', primary);
  root.style.setProperty('--site-secondary', secondary);
  root.style.setProperty('--site-button', button);
  root.style.setProperty('--site-footer-bg', footerBg);
  root.style.setProperty('--site-font-family', fontFamily);
  root.style.setProperty('--site-font-size-text', `${Number.isFinite(fontSizeText) ? fontSizeText : 16}px`);
  root.style.setProperty('--site-font-size-title', `${Number.isFinite(fontSizeTitle) ? fontSizeTitle : 40}px`);

  root.style.setProperty('--site-home-overlay-color', '#000000');
  if (settings.home_hero_overlay_opacity !== undefined && settings.home_hero_overlay_opacity !== null) {
    const raw = Number(settings.home_hero_overlay_opacity);
    const clamped = Math.max(0, Math.min(100, Number.isFinite(raw) ? raw : 70)) / 100;
    root.style.setProperty('--site-home-overlay-opacity', `${clamped}`);
  }

  if (settings.favicon_url) {
    let link = document.querySelector('link[rel="icon"]');
    if (!link) {
      link = document.createElement('link');
      link.setAttribute('rel', 'icon');
      document.head.appendChild(link);
    }
    link.setAttribute('href', settings.favicon_url);
  }
}

const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

createInertiaApp({
  resolve: (name) => {
    const page = pages[`./Pages/${name}.vue`];

    if (!page) {
      throw new Error(`Inertia page not found: ${name}`);
    }

    return page;
  },
  setup({ el, App, props, plugin }) {
    applyAppearance(props?.initialPage?.props?.settings);

    router.on('navigate', (event) => {
      applyAppearance(event?.detail?.page?.props?.settings);
    });

    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
});
