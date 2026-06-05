import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

function applyAppearance(settings) {
  if (!settings || typeof settings !== 'object') return;

  const root = document.documentElement;

  const primary = settings.primary_color || '#1e3a8a';
  const secondary = settings.secondary_color || '#f97316';
  const button = settings.button_color || secondary;
  const footerBg = settings.footer_bg_color || '#111827';
  const fontFamily = settings.font_family || 'Instrument Sans, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif';
  const fontSizeText = Number(settings.font_size_text ?? 16);
  const fontSizeTitle = Number(settings.font_size_title ?? 40);

  root.style.setProperty('--site-primary', primary);
  root.style.setProperty('--site-secondary', secondary);
  root.style.setProperty('--site-button', button);
  root.style.setProperty('--site-footer-bg', footerBg);
  root.style.setProperty('--site-font-family', fontFamily);
  root.style.setProperty('--site-font-size-text', `${Number.isFinite(fontSizeText) ? fontSizeText : 16}px`);
  root.style.setProperty('--site-font-size-title', `${Number.isFinite(fontSizeTitle) ? fontSizeTitle : 40}px`);

  if (settings.home_hero_overlay_color) {
    root.style.setProperty('--site-home-overlay-color', settings.home_hero_overlay_color);
  }
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

createInertiaApp({
  resolve: (name) => {
    return resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
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
