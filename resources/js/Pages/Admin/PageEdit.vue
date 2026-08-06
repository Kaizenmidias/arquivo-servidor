<template>
  <AdminLayout>
    <template #pageTitle>Editar Página: {{ page?.titulo || 'Página' }}</template>
    
    <div class="flex items-center justify-between mb-6">
      <Link :href="`${adminBase}/pages`" class="text-blue-700 hover:text-blue-900 font-semibold">Voltar</Link>
      <div class="flex items-center gap-3">
        <button type="button" class="text-gray-700 hover:text-gray-900 font-semibold" @click="duplicate">Duplicar</button>
        <button type="button" class="text-red-600 hover:text-red-800 font-semibold" @click="remove">Excluir</button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">Conteúdo</h3>
        
        <div class="space-y-6">
          <div v-if="!isHome">
            <label class="block text-gray-700 mb-2 text-sm font-medium">Nome da página</label>
            <input type="text" v-model="form.titulo" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título da página">
            <div v-if="form.errors.titulo" class="text-sm text-red-600 mt-1">{{ form.errors.titulo }}</div>
          </div>
          
          <div v-if="showConteudo">
            <label class="block text-gray-700 mb-2 text-sm font-medium">Conteúdo</label>
            <textarea v-model="form.conteudo" rows="15" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Conteúdo da página..."></textarea>
            <div v-if="form.errors.conteudo" class="text-sm text-red-600 mt-1">{{ form.errors.conteudo }}</div>
          </div>

          <div v-if="isHome" class="border-t border-gray-200 pt-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">Banner da página de imóveis</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="space-y-5">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Título</label>
                  <input v-model="form.banner_title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Subtítulo</label>
                  <input v-model="form.banner_subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Título</label>
                    <div class="flex items-center gap-3">
                      <input type="color" v-model="form.banner_title_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                      <span class="text-gray-700 font-mono text-sm">{{ form.banner_title_color }}</span>
                    </div>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Subtítulo</label>
                    <div class="flex items-center gap-3">
                      <input type="color" v-model="form.banner_subtitle_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                      <span class="text-gray-700 font-mono text-sm">{{ form.banner_subtitle_color }}</span>
                    </div>
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Overlay</label>
                    <div class="flex items-center gap-3">
                      <input type="color" v-model="form.banner_overlay_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                      <span class="text-gray-700 font-mono text-sm">{{ form.banner_overlay_color }}</span>
                    </div>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Opacidade do Overlay (%)</label>
                    <input v-model.number="form.banner_overlay_opacity" type="number" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem do Banner</label>
                <input ref="bannerInputRef" type="file" accept="image/*" class="hidden" @change="onBannerSelected">
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="bannerInputRef?.click()">
                  <p class="text-gray-600">Clique para enviar</p>
                </div>
                <div v-if="bannerPreviewUrl" class="mt-4">
                  <img :src="bannerPreviewUrl" class="w-full h-48 object-cover rounded-xl border border-gray-200">
                  <button type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium" @click="clearBanner">Remover imagem</button>
                </div>
                <div v-if="form.errors.banner_image_file" class="text-sm text-red-600 mt-1">{{ form.errors.banner_image_file }}</div>
              </div>
            </div>
          </div>

          <div v-if="isHome" class="border-t border-gray-200 pt-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">NÃºmeros da Home</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="(item, idx) in form.page_data.home_stats" :key="idx" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Item {{ idx + 1 }}</div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-[0.8fr_1.2fr]">
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">NÃºmero</label>
                    <input v-model="item.value" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Ex.: 9+" />
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Texto</label>
                    <input v-model="item.label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Ex.: Oportunidades" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="isProperties" class="border-t border-gray-200 pt-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">Banner da página de imóveis</h4>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="space-y-5">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Título</label>
                  <input v-model="form.banner_title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Subtítulo</label>
                  <input v-model="form.banner_subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Título</label>
                    <div class="flex items-center gap-3">
                      <input type="color" v-model="form.banner_title_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                      <span class="text-gray-700 font-mono text-sm">{{ form.banner_title_color }}</span>
                    </div>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Subtítulo</label>
                    <div class="flex items-center gap-3">
                      <input type="color" v-model="form.banner_subtitle_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                      <span class="text-gray-700 font-mono text-sm">{{ form.banner_subtitle_color }}</span>
                    </div>
                  </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-end">
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Overlay</label>
                    <div class="flex items-center gap-3">
                      <input type="color" v-model="form.banner_overlay_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                      <span class="text-gray-700 font-mono text-sm">{{ form.banner_overlay_color }}</span>
                    </div>
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Opacidade do Overlay (%)</label>
                    <input v-model.number="form.banner_overlay_opacity" type="number" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem do Banner</label>
                <input ref="bannerInputRef" type="file" accept="image/*" class="hidden" @change="onBannerSelected">
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="bannerInputRef?.click()">
                  <p class="text-gray-600">Clique para enviar</p>
                </div>
                <div v-if="bannerPreviewUrl" class="mt-4">
                  <img :src="bannerPreviewUrl" class="w-full h-48 object-cover rounded-xl border border-gray-200">
                  <button type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium" @click="clearBanner">Remover imagem</button>
                </div>
                <div v-if="form.errors.banner_image_file" class="text-sm text-red-600 mt-1">{{ form.errors.banner_image_file }}</div>
              </div>
            </div>
          </div>

          <div v-if="isAbout" class="border-t border-gray-200 pt-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">Sobre Nós</h4>

            <div class="space-y-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                  <div class="text-sm font-semibold uppercase tracking-wide text-gray-500">Hero</div>
                  <input v-model="form.page_data.hero.subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Subtítulo" />
                  <input v-model="form.page_data.hero.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título" />
                  <textarea v-model="form.page_data.hero.text" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto"></textarea>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input v-model="form.page_data.hero.button_label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto do botão" />
                    <input v-model="form.page_data.hero.button_url" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Link do botão" />
                  </div>
                  <input ref="aboutHeroBgInputRef" type="file" accept="image/*" class="hidden" @change="onAboutHeroBgSelected" />
                  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="aboutHeroBgInputRef?.click()">
                    <p class="text-gray-600">Imagem do Hero</p>
                  </div>
                  <img v-if="aboutHeroBgPreview" :src="aboutHeroBgPreview" class="w-full h-40 object-cover rounded-lg border border-gray-200" />
                </div>

                <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                  <div class="text-sm font-semibold uppercase tracking-wide text-gray-500">Nossa História</div>
                  <input v-model="form.page_data.history.subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Subtítulo" />
                  <input v-model="form.page_data.history.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título" />
                  <textarea v-model="form.page_data.history.text" rows="7" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto"></textarea>
                  <input ref="aboutEssenceImgInputRef" type="file" accept="image/*" class="hidden" @change="onAboutEssenceImgSelected" />
                  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="aboutEssenceImgInputRef?.click()">
                    <p class="text-gray-600">Imagem da História</p>
                  </div>
                  <img v-if="aboutEssenceImgPreview" :src="aboutEssenceImgPreview" class="w-full h-40 object-cover rounded-lg border border-gray-200" />
                </div>
              </div>

              <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                  <div class="text-sm font-semibold uppercase tracking-wide text-gray-500">Números</div>
                  <button type="button" class="text-sm text-blue-700 hover:text-blue-900 font-medium" @click="addAboutNumber">Adicionar número</button>
                </div>
                <div class="space-y-4">
                  <div v-for="(item, idx) in form.page_data.numbers" :key="idx" class="rounded-lg border border-gray-200 bg-white p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                      <input v-model="item.number" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Número" />
                      <input v-model="item.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 md:col-span-1" placeholder="Título" />
                      <div class="space-y-3 md:col-span-1">
                        <input v-model="item.icon" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Ícone antigo (opcional)" />
                        <input :ref="(el) => setAboutNumberIconInputRef(el, idx)" type="file" accept="image/*" class="hidden" @change="(e) => onAboutNumberIconSelected(idx, e)" />
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="triggerAboutNumberIcon(idx)">
                          <p class="text-sm text-gray-600">Adicionar imagem do ícone</p>
                        </div>
                        <img v-if="item.icon_image" :src="item.icon_image" class="h-14 w-14 rounded-lg border border-gray-200 object-cover" alt="" />
                      </div>
                    </div>
                    <div class="mt-3 text-right">
                      <button type="button" class="text-sm text-red-600 hover:text-red-800 font-medium" @click="removeAboutNumber(idx)">Remover</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                  <div class="text-sm font-semibold uppercase tracking-wide text-gray-500">Especialista</div>
                  <input v-model="form.page_data.specialist.subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Pequeno título" />
                  <input v-model="form.page_data.specialist.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título principal" />
                  <textarea v-model="form.page_data.specialist.text" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto"></textarea>
                  <input v-model="form.page_data.specialist.name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Nome" />
                  <input v-model="form.page_data.specialist.role" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Cargo" />
                  <textarea v-model="form.page_data.specialist.card_description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Descrição do card"></textarea>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input v-model="form.page_data.specialist.button_label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto do botão" />
                    <input v-model="form.page_data.specialist.button_url" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Link do botão" />
                  </div>
                  <input ref="team1InputRef" type="file" accept="image/*" class="hidden" @change="onTeam1Selected" />
                  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="team1InputRef?.click()">
                    <p class="text-gray-600">Foto do especialista</p>
                  </div>
                  <img v-if="team1Preview" :src="team1Preview" class="w-full h-40 object-cover rounded-lg border border-gray-200" />
                </div>

                <div class="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                  <div class="text-sm font-semibold uppercase tracking-wide text-gray-500">Região</div>
                  <input v-model="form.page_data.region.subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Subtítulo" />
                  <input v-model="form.page_data.region.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título" />
                  <textarea v-model="form.page_data.region.text" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto"></textarea>
                  <div class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-sm font-medium text-gray-700">Lista de regiões</span>
                      <button type="button" class="text-sm text-blue-700 hover:text-blue-900 font-medium" @click="addRegionItem">Adicionar região</button>
                    </div>
                    <input v-for="(item, idx) in form.page_data.region.regions" :key="idx" v-model="form.page_data.region.regions[idx]" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Ex.: Alphaville" />
                  </div>
                  <input ref="regionImageInputRef" type="file" accept="image/*" class="hidden" @change="onRegionImageSelected" />
                  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="regionImageInputRef?.click()">
                    <p class="text-gray-600">Imagem da região</p>
                  </div>
                  <img v-if="regionImagePreview" :src="regionImagePreview" class="w-full h-40 object-cover rounded-lg border border-gray-200" />
                </div>
              </div>

              <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                  <div class="text-sm font-semibold uppercase tracking-wide text-gray-500">Valores</div>
                  <button type="button" class="text-sm text-blue-700 hover:text-blue-900 font-medium" @click="addAboutValue">Adicionar card</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div v-for="(item, idx) in form.page_data.values" :key="idx" class="rounded-lg border border-gray-200 bg-white p-4 space-y-3">
                    <input v-model="item.icon" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Ícone antigo" />
                    <input :ref="(el) => setAboutValueIconInputRef(el, idx)" type="file" accept="image/*" class="hidden" @change="(e) => onAboutValueIconSelected(idx, e)" />
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="triggerAboutValueIcon(idx)">
                      <p class="text-sm text-gray-600">Adicionar imagem do ícone</p>
                    </div>
                    <img v-if="item.icon_image" :src="item.icon_image" class="h-14 w-14 rounded-lg border border-gray-200 object-cover" alt="" />
                    <input v-model="item.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título" />
                    <textarea v-model="item.text" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto"></textarea>
                    <div class="text-right">
                      <button type="button" class="text-sm text-red-600 hover:text-red-800 font-medium" @click="removeAboutValue(idx)">Remover</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                <div class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">CTA Final</div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                  <input v-model="form.page_data.cta.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título" />
                  <input v-model="form.page_data.cta.text" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto" />
                  <input v-model="form.page_data.cta.button_1_label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto botão 1" />
                  <input v-model="form.page_data.cta.button_1_url" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Link botão 1" />
                  <input v-model="form.page_data.cta.button_2_label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto botão 2" />
                  <input v-model="form.page_data.cta.button_2_url" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Link botão 2" />
                </div>
                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem de fundo</label>
                  <input ref="ctaImageInputRef" type="file" accept="image/*" class="hidden" @change="onCtaImageSelected" />
                  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="ctaImageInputRef?.click()">
                    <p class="text-gray-600">Enviar imagem</p>
                  </div>
                  <img v-if="ctaImagePreview" :src="ctaImagePreview" class="w-full h-40 object-cover rounded-lg border border-gray-200 mt-3" />
                  </div>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do Overlay</label>
                      <div class="flex items-center gap-3">
                        <input type="color" v-model="form.page_data.cta.overlay_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                        <span class="text-gray-700 font-mono text-sm">{{ form.page_data.cta.overlay_color }}</span>
                      </div>
                    </div>
                    <div>
                      <label class="block text-gray-700 mb-2 text-sm font-medium">Opacidade do Overlay (%)</label>
                      <input v-model.number="form.page_data.cta.overlay_opacity" type="number" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="showContentMedia" class="border-t border-gray-200 pt-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">Imagens do Conteúdo</h4>
            <div>
              <input ref="contentMediaInputRef" type="file" accept="image/*" class="hidden" @change="onContentMediaSelected">
              <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="contentMediaInputRef?.click()">
                <p class="text-gray-600">Clique para enviar imagem para usar no conteúdo</p>
              </div>
              <div v-if="contentMediaError" class="text-sm text-red-600 mt-2">{{ contentMediaError }}</div>
            </div>
            <div v-if="uploadedMedia.length > 0" class="mt-4 grid grid-cols-1 gap-3">
              <div v-for="m in uploadedMedia" :key="m.url" class="flex items-center gap-3 border border-gray-200 rounded-lg p-3">
                <img :src="m.url" class="w-14 h-14 object-cover rounded border border-gray-200">
                <input type="text" readonly :value="m.url" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm" />
              </div>
            </div>
          </div>

          <div v-if="!isHome">
            <label class="block text-gray-700 mb-2 text-sm font-medium">Slug</label>
            <input type="text" v-model="form.slug" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="minha-pagina">
            <div v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</div>
          </div>

          <label class="flex items-center gap-2 text-sm text-gray-700">
            <input v-model="form.ativo" type="checkbox" class="rounded border-gray-300">
            Página ativa
          </label>
          <div v-if="form.errors.ativo" class="text-sm text-red-600 mt-1">{{ form.errors.ativo }}</div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow p-6 border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800 mb-6">SEO</h3>
        
        <div class="space-y-6">
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Meta Title</label>
            <input type="text" v-model="form.meta_title" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Meta título">
            <div v-if="form.errors.meta_title" class="text-sm text-red-600 mt-1">{{ form.errors.meta_title }}</div>
          </div>
          
          <div>
            <label class="block text-gray-700 mb-2 text-sm font-medium">Meta Description</label>
            <textarea v-model="form.meta_description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Meta descrição..."></textarea>
            <div v-if="form.errors.meta_description" class="text-sm text-red-600 mt-1">{{ form.errors.meta_description }}</div>
          </div>
        </div>
      </div>
      
      <div class="lg:col-span-3">
        <button type="button" :disabled="form.processing" class="bg-blue-900 hover:bg-blue-800 disabled:opacity-60 text-white px-8 py-3 rounded-lg font-semibold transition" @click="save">
          Salvar Alterações
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed, ref, watchEffect } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Shared/AdminLayout.vue';

const pageCtx = usePage();
const adminBase = computed(() => pageCtx.props?.paths?.admin || '/admin');

const props = defineProps({
  page: {
    type: Object,
    default: () => ({})
  },
  settings: {
    type: Object,
    default: () => ({})
  }
});

const template = computed(() => {
  if (props.page?.slug === 'home') return 'home';
  if (props.page?.slug === 'imoveis') return 'properties';
  if (props.page?.slug === 'sobre' || props.page?.slug === 'quem-somos') return 'about';
  if (props.page?.slug === 'contato') return 'contact';
  return props.page?.template || 'default';
});
const isHome = computed(() => template.value === 'home');
const isProperties = computed(() => template.value === 'properties');
const isAbout = computed(() => template.value === 'about');
const showConteudo = computed(() => !isHome.value && !isAbout.value);
const showContentMedia = computed(() => showConteudo.value);
const bannerSectionTitle = computed(() => (isHome.value ? 'Home (Hero)' : 'Banner'));
const bannerTitleLabel = computed(() => (isHome.value ? 'Título principal (H1)' : 'Título do banner'));
const bannerSubtitleLabel = computed(() => (isHome.value ? 'Subtítulo' : 'Subtítulo do banner'));
const legacyText = (...values) => values.map((value) => String(value || '').trim()).filter(Boolean).join(' ');

const homeDefaults = () => ({
  home_stats: [
    { value: '9+', label: 'Oportunidades' },
    { value: '3+', label: 'NegÃ³cios' },
    { value: '182+', label: 'CondomÃ­nios' },
    { value: '3+', label: 'Diferenciais' },
  ],
});

const mergeHomeData = (incoming) => {
  const base = homeDefaults();
  const src = incoming && typeof incoming === 'object' ? incoming : {};

  if (Array.isArray(src.home_stats)) {
    base.home_stats = base.home_stats.map((item, index) => ({ ...item, ...(src.home_stats[index] || {}) }));
  }

  return { ...src, home_stats: base.home_stats };
};

const aboutDefaults = () => ({
  hero: {
    subtitle: '',
    title: '',
    text: '',
    button_label: '',
    button_url: '',
    image: '',
  },
  history: {
    subtitle: '',
    title: '',
    text: '',
    image: '',
  },
  numbers: [
    { number: '', title: '', icon: '', icon_image: '' },
    { number: '', title: '', icon: '', icon_image: '' },
    { number: '', title: '', icon: '', icon_image: '' },
    { number: '', title: '', icon: '', icon_image: '' },
  ],
  specialist: {
    subtitle: '',
    title: '',
    text: '',
    name: '',
    role: '',
    card_description: '',
    image: '',
    button_label: '',
    button_url: '',
  },
  region: {
    subtitle: '',
    title: '',
    text: '',
    image: '',
    regions: [''],
  },
  values: [
    { icon: '', icon_image: '', title: '', text: '' },
    { icon: '', icon_image: '', title: '', text: '' },
    { icon: '', icon_image: '', title: '', text: '' },
    { icon: '', icon_image: '', title: '', text: '' },
  ],
  cta: {
    title: '',
    text: '',
    background_image: '',
    overlay_color: '#0f172a',
    overlay_opacity: 78,
    button_1_label: '',
    button_1_url: '',
    button_2_label: '',
    button_2_url: '',
  },
  // Legacy fields kept for backwards compatibility with older saved payloads.
  hero_title_primary: '',
  hero_title_secondary: '',
  hero_subtitle: '',
  hero_button_label: '',
  hero_button_url: '',
  hero_background_image: '',
  stats: [],
  essence: {},
  team: {},
  quote: {},
  pillars: [],
  territory: {},
});

const mergeAboutData = (incoming) => {
  const base = aboutDefaults();
  const src = incoming && typeof incoming === 'object' ? incoming : {};

  if (src.hero && typeof src.hero === 'object') {
    base.hero = { ...base.hero, ...src.hero };
  }
  if (src.history && typeof src.history === 'object') {
    base.history = { ...base.history, ...src.history };
  }
  if (Array.isArray(src.numbers)) {
    base.numbers = base.numbers.map((d, i) => ({ ...d, ...(src.numbers[i] || {}) }));
  }
  if (src.specialist && typeof src.specialist === 'object') {
    base.specialist = { ...base.specialist, ...src.specialist };
  }
  if (src.region && typeof src.region === 'object') {
    base.region = { ...base.region, ...src.region };
    if (Array.isArray(src.region.regions)) {
      const regs = src.region.regions.filter(Boolean).slice(0, 12);
      base.region.regions = regs.length > 0 ? regs : base.region.regions;
    }
  }
  if (Array.isArray(src.values)) {
    base.values = base.values.map((d, i) => ({ ...d, ...(src.values[i] || {}) }));
  }
  if (src.cta && typeof src.cta === 'object') {
    base.cta = { ...base.cta, ...src.cta };
  }

  if (src.hero_title_primary || src.hero_title_secondary || src.hero_subtitle || src.hero_button_label || src.hero_button_url || src.hero_background_image) {
    base.hero.title = legacyText(src.hero_title_primary, src.hero_title_secondary) || base.hero.title;
    base.hero.subtitle = src.hero_subtitle ?? base.hero.subtitle;
    base.hero.button_label = src.hero_button_label ?? base.hero.button_label;
    base.hero.button_url = src.hero_button_url ?? base.hero.button_url;
    base.hero.image = src.hero_background_image ?? base.hero.image;
  }

  base.hero_title_primary = src.hero_title_primary ?? base.hero_title_primary;
  base.hero_title_secondary = src.hero_title_secondary ?? base.hero_title_secondary;
  base.hero_subtitle = src.hero_subtitle ?? base.hero_subtitle;
  base.hero_button_label = src.hero_button_label ?? base.hero_button_label;
  base.hero_button_url = src.hero_button_url ?? base.hero_button_url;
  base.hero_background_image = src.hero_background_image ?? base.hero_background_image;

  if (Array.isArray(src.stats)) {
    base.stats = base.stats.map((d, i) => ({ ...d, ...(src.stats[i] || {}) }));
    base.numbers = base.stats.map((item) => ({ number: item.value || '', title: item.label || '', icon: '', icon_image: '' }));
  }

  if (src.essence && typeof src.essence === 'object') {
    base.essence = { ...base.essence, ...src.essence };
    if (Array.isArray(src.essence.bullets)) {
      base.essence.bullets = base.essence.bullets.map((d, i) => src.essence.bullets[i] ?? d);
    }
    base.history.subtitle = src.essence.kicker ?? base.history.subtitle;
    base.history.title = legacyText(src.essence.title_primary, src.essence.title_highlight) || base.history.title;
    base.history.text = legacyText(src.essence.text_1, src.essence.text_2) || base.history.text;
    base.history.image = src.essence.image ?? base.history.image;
  }

  if (src.team && typeof src.team === 'object') {
    base.team = { ...base.team, ...src.team };
    if (Array.isArray(src.team.members)) {
      base.team.members = base.team.members.map((d, i) => ({ ...d, ...(src.team.members[i] || {}) }));
    }
    base.specialist.subtitle = src.team.kicker ?? base.specialist.subtitle;
    base.specialist.title = src.team.title ?? base.specialist.title;
    base.specialist.text = src.team.subtitle ?? base.specialist.text;
    base.specialist.name = src.team.members?.[0]?.name ?? base.specialist.name;
    base.specialist.role = src.team.members?.[0]?.role ?? base.specialist.role;
    base.specialist.image = src.team.members?.[0]?.photo ?? base.specialist.image;
  }

  if (src.quote && typeof src.quote === 'object') {
    base.quote = { ...base.quote, ...src.quote };
  }

  if (Array.isArray(src.pillars)) {
    base.pillars = base.pillars.map((d, i) => ({ ...d, ...(src.pillars[i] || {}) }));
    base.values = base.pillars.map((item) => ({ icon: item.icon || '', title: item.title || '', text: item.description || '' }));
  }

  if (src.territory && typeof src.territory === 'object') {
    base.territory = { ...base.territory, ...src.territory };
    if (Array.isArray(src.territory.regions)) {
      const regs = src.territory.regions.filter(Boolean).slice(0, 10);
      base.territory.regions = regs.length > 0 ? regs : base.territory.regions;
      base.region.regions = base.territory.regions;
    }
    if (src.territory.images && typeof src.territory.images === 'object') {
      base.territory.images = { ...base.territory.images, ...src.territory.images };
    }
    base.region.subtitle = src.territory.kicker ?? base.region.subtitle;
    base.region.title = legacyText(src.territory.title, src.territory.title_highlight) || base.region.title;
    base.region.text = legacyText(src.territory.text_1, src.territory.text_2) || base.region.text;
    base.region.image = src.territory.images?.wide ?? base.region.image;
    base.cta.background_image = src.territory.images?.square ?? base.cta.background_image;
  }

  return base;
};

const form = useForm({
  titulo: props.page?.titulo || '',
  slug: props.page?.slug || '',
  template: template.value,
  conteudo: props.page?.conteudo || '',
  page_data: isAbout.value ? mergeAboutData(props.page?.data) : (isHome.value ? mergeHomeData(props.page?.data) : (props.page?.data || {})),
  banner_title: props.page?.banner_title || '',
  banner_subtitle: props.page?.banner_subtitle || '',
  banner_image: props.page?.banner_image || '',
  banner_title_color: props.page?.banner_title_color || '#ffffff',
  banner_subtitle_color: props.page?.banner_subtitle_color || '#ffffff',
  banner_overlay_color: props.page?.banner_overlay_color || '#0f172a',
  banner_overlay_opacity: Number(props.page?.banner_overlay_opacity ?? 70),
  banner_image_file: null,
  meta_title: props.page?.meta_title || '',
  meta_description: props.page?.meta_description || '',
  ativo: !!props.page?.ativo,
});

watchEffect(() => {
  if (isHome.value) {
    if (!form.page_data || typeof form.page_data !== 'object' || !Array.isArray(form.page_data.home_stats)) {
      form.page_data = mergeHomeData(form.page_data);
    }
    return;
  }

  if (!isAbout.value) return;
  if (!form.page_data || typeof form.page_data !== 'object') {
    form.page_data = mergeAboutData({});
    return;
  }
  if (!form.page_data.essence || !form.page_data.team || !Array.isArray(form.page_data.stats)) {
    form.page_data = mergeAboutData(form.page_data);
  }
});

const bannerInputRef = ref(null);
const bannerPreviewUrl = ref(form.banner_image || '');

const onBannerSelected = (e) => {
  const file = e.target.files?.[0] || null;
  form.banner_image_file = file;
  bannerPreviewUrl.value = file ? URL.createObjectURL(file) : (form.banner_image || '');
  if (bannerInputRef.value) bannerInputRef.value.value = '';
};

const clearBanner = () => {
  form.banner_image_file = null;
  form.banner_image = '';
  bannerPreviewUrl.value = '';
};

const contentMediaInputRef = ref(null);
const uploadedMedia = ref([]);
const contentMediaError = ref('');

const onContentMediaSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (contentMediaInputRef.value) contentMediaInputRef.value.value = '';
  if (!file) return;

  contentMediaError.value = '';
  const body = new FormData();
  body.append('file', file);

  try {
    const response = await window.axios.post(`${adminBase.value}/pages/${props.page.id}/media`, body, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    const url = response?.data?.url;
    if (url) {
      uploadedMedia.value = [{ url }, ...uploadedMedia.value];
    }
  } catch (err) {
    contentMediaError.value = 'Falha ao enviar imagem.';
  }
};

const aboutHeroBgInputRef = ref(null);
const aboutHeroBgPreview = ref(form.page_data?.hero?.image || form.page_data?.hero_background_image || '');
const aboutEssenceImgInputRef = ref(null);
const aboutEssenceImgPreview = ref(form.page_data?.history?.image || form.page_data?.essence?.image || '');
const team1InputRef = ref(null);
const team1Preview = ref(form.page_data?.specialist?.image || form.page_data?.team?.members?.[0]?.photo || '');
const ctaImageInputRef = ref(null);
const ctaImagePreview = ref(form.page_data?.cta?.background_image || '');
const aboutValueIconInputRefs = ref([]);
const regionImageInputRef = ref(null);
const regionImagePreview = ref(form.page_data?.region?.image || '');
const aboutNumberIconInputRefs = ref([]);

const uploadMedia = async (file) => {
  const body = new FormData();
  body.append('file', file);
  const response = await window.axios.post(`${adminBase.value}/pages/${props.page.id}/media`, body, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return response?.data?.url || '';
};

const onAboutHeroBgSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (aboutHeroBgInputRef.value) aboutHeroBgInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.hero.image = url;
    form.page_data.hero_background_image = url;
    aboutHeroBgPreview.value = url;
  }
};
const onAboutEssenceImgSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (aboutEssenceImgInputRef.value) aboutEssenceImgInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.history.image = url;
    form.page_data.essence.image = url;
    aboutEssenceImgPreview.value = url;
  }
};
const onTeam1Selected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (team1InputRef.value) team1InputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.specialist.image = url;
    form.page_data.team.members[0].photo = url;
    team1Preview.value = url;
  }
};
const clearTeam1 = () => {
  form.page_data.specialist.image = '';
  form.page_data.team.members[0].photo = '';
  team1Preview.value = '';
};

const addAboutNumber = () => {
  form.page_data.numbers.push({ number: '', title: '', icon: '', icon_image: '' });
};

const removeAboutNumber = (idx) => {
  form.page_data.numbers.splice(idx, 1);
  if (form.page_data.numbers.length === 0) {
    form.page_data.numbers.push({ number: '', title: '', icon: '', icon_image: '' });
  }
};

const addAboutValue = () => {
  form.page_data.values.push({ icon: '', icon_image: '', title: '', text: '' });
};

const removeAboutValue = (idx) => {
  form.page_data.values.splice(idx, 1);
  if (form.page_data.values.length === 0) {
    form.page_data.values.push({ icon: '', icon_image: '', title: '', text: '' });
  }
};

const addRegionItem = () => {
  form.page_data.region.regions.push('');
};

const onRegionImageSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (regionImageInputRef.value) regionImageInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.region.image = url;
    regionImagePreview.value = url;
  }
};

const onCtaImageSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (ctaImageInputRef.value) ctaImageInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.cta.background_image = url;
    ctaImagePreview.value = url;
  }
};

const setAboutValueIconInputRef = (el, idx) => {
  if (el) {
    aboutValueIconInputRefs.value[idx] = el;
  }
};

const triggerAboutValueIcon = (idx) => {
  aboutValueIconInputRefs.value[idx]?.click();
};

const onAboutValueIconSelected = async (idx, e) => {
  const file = e.target.files?.[0] || null;
  const input = aboutValueIconInputRefs.value[idx];
  if (input) input.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url && form.page_data.values[idx]) {
    form.page_data.values[idx].icon_image = url;
    form.page_data.values[idx].icon = '';
  }
};

const setAboutNumberIconInputRef = (el, idx) => {
  if (el) {
    aboutNumberIconInputRefs.value[idx] = el;
  }
};

const triggerAboutNumberIcon = (idx) => {
  aboutNumberIconInputRefs.value[idx]?.click();
};

const onAboutNumberIconSelected = async (idx, e) => {
  const file = e.target.files?.[0] || null;
  const input = aboutNumberIconInputRefs.value[idx];
  if (input) input.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url && form.page_data.numbers[idx]) {
    form.page_data.numbers[idx].icon_image = url;
    form.page_data.numbers[idx].icon = '';
  }
};

const save = () => {
  form
    .transform((data) => ({
      ...data,
      data: data.page_data,
      page_data: undefined,
      _method: 'put',
    }))
    .post(`${adminBase.value}/pages/${props.page.id}`, { forceFormData: true });
};

const duplicate = () => {
  router.post(`${adminBase.value}/pages/${props.page.id}/duplicate`);
};

const remove = () => {
  router.delete(`${adminBase.value}/pages/${props.page.id}`);
};
</script>
