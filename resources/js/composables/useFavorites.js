import { computed, reactive } from 'vue';

const STORAGE_KEY = 'user_favorite_properties_session_v1';

const state = reactive({
  items: [],
  hydrated: false,
});

function normalizePhoto(photo) {
  if (typeof photo === 'string') {
    return {
      src: photo,
      srcset: null,
      sizes: '(max-width: 768px) 100vw, 600px',
    };
  }

  return {
    src: photo?.src || photo?.thumb || photo?.medium || photo?.full || '',
    srcset: photo?.srcset || null,
    sizes: photo?.sizes || '(max-width: 768px) 100vw, 600px',
  };
}

function normalizePriceRows(property) {
  const prices = Array.isArray(property?.prices) ? property.prices : [];
  const validRows = prices
    .filter((row) => Number(row?.value || 0) > 0)
    .map((row) => ({
      key: String(row?.key || 'default'),
      label: String(row?.label || 'Valor'),
      value: Number(row?.value || 0),
      suffix: String(row?.suffix || ''),
    }));

  if (validRows.length > 0) {
    return validRows;
  }

  if (Number(property?.price || 0) > 0) {
    return [{
      key: 'default',
      label: String(property?.type || 'Valor'),
      value: Number(property?.price || 0),
      suffix: String(property?.type || '').toLowerCase().includes('alugu') ? '/mês' : '',
    }];
  }

  return [];
}

function normalizeProperty(property) {
  const id = Number(property?.id || 0);

  if (!id) {
    return null;
  }

  return {
    id,
    slug: String(property?.slug || ''),
    url: String(property?.url || (property?.slug ? `/imoveis/${property.slug}` : '#')),
    code: String(property?.code || ''),
    title: String(property?.title || 'Imóvel'),
    location: String(property?.location || property?.address || ''),
    address: String(property?.address || property?.location || ''),
    type: String(property?.type || ''),
    price: Number(property?.price || 0),
    prices: normalizePriceRows(property),
    photo: normalizePhoto(property?.photo || property?.photos?.[0] || ''),
    businessLabels: Array.isArray(property?.businessLabels) ? property.businessLabels.map((item) => String(item || '')) : [],
    savedAt: new Date().toISOString(),
  };
}

function persist() {
  if (typeof window === 'undefined') {
    return;
  }

  window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state.items));
}

function hydrateFavorites(force = false) {
  if (state.hydrated && !force) {
    return;
  }

  state.hydrated = true;

  if (typeof window === 'undefined') {
    return;
  }

  try {
    const raw = window.sessionStorage.getItem(STORAGE_KEY);
    const parsed = raw ? JSON.parse(raw) : [];
    state.items = Array.isArray(parsed)
      ? parsed.map((item) => normalizeProperty(item)).filter(Boolean)
      : [];
  } catch {
    state.items = [];
  }
}

function isFavorite(propertyId) {
  const id = Number(propertyId || 0);
  return state.items.some((item) => item.id === id);
}

function addFavorite(property) {
  const normalized = normalizeProperty(property);

  if (!normalized) {
    return false;
  }

  const index = state.items.findIndex((item) => item.id === normalized.id);

  if (index !== -1) {
    state.items.splice(index, 1, normalized);
  } else {
    state.items.unshift(normalized);
  }

  persist();
  return true;
}

function removeFavorite(propertyId) {
  const id = Number(propertyId || 0);
  const index = state.items.findIndex((item) => item.id === id);

  if (index === -1) {
    return false;
  }

  state.items.splice(index, 1);
  persist();
  return true;
}

function toggleFavorite(property) {
  const id = Number(property?.id || 0);

  if (!id) {
    return false;
  }

  if (isFavorite(id)) {
    removeFavorite(id);
    return false;
  }

  addFavorite(property);
  return true;
}

const favoriteItems = computed(() => state.items);
const favoriteCount = computed(() => state.items.length);

export function useFavorites() {
  return {
    favoriteItems,
    favoriteCount,
    hydrateFavorites,
    isFavorite,
    addFavorite,
    removeFavorite,
    toggleFavorite,
  };
}
