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

          <div class="border-t border-gray-200 pt-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">{{ bannerSectionTitle }}</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">{{ bannerTitleLabel }}</label>
                <input type="text" v-model="form.banner_title" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título">
                <div v-if="form.errors.banner_title" class="text-sm text-red-600 mt-1">{{ form.errors.banner_title }}</div>
              </div>
              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">{{ bannerSubtitleLabel }}</label>
                <textarea v-model="form.banner_subtitle" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Subtítulo..."></textarea>
                <div v-if="form.errors.banner_subtitle" class="text-sm text-red-600 mt-1">{{ form.errors.banner_subtitle }}</div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do título</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="form.banner_title_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700 font-mono text-sm">{{ form.banner_title_color }}</span>
                  </div>
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do subtítulo</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="form.banner_subtitle_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700 font-mono text-sm">{{ form.banner_subtitle_color }}</span>
                  </div>
                </div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Cor do overlay</label>
                  <div class="flex items-center gap-3">
                    <input type="color" v-model="form.banner_overlay_color" class="w-12 h-10 border-2 border-gray-300 rounded cursor-pointer">
                    <span class="text-gray-700 font-mono text-sm">{{ form.banner_overlay_color }}</span>
                  </div>
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Opacidade do overlay</label>
                  <input type="number" min="0" max="100" v-model.number="form.banner_overlay_opacity" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="70">
                </div>
              </div>

              <div>
                <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem do banner</label>
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
            <h4 class="text-base font-semibold text-gray-800 mb-4">Quem Somos</h4>
            <div class="grid grid-cols-1 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Título (linha 1)</label>
                  <input v-model="form.page_data.hero_title_primary" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Título (linha 2)</label>
                  <input v-model="form.page_data.hero_title_secondary" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Subtítulo</label>
                  <textarea v-model="form.page_data.hero_subtitle" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Botão (texto)</label>
                    <input v-model="form.page_data.hero_button_label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                  </div>
                  <div>
                    <label class="block text-gray-700 mb-2 text-sm font-medium">Botão (link)</label>
                    <input v-model="form.page_data.hero_button_url" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" />
                  </div>
                </div>
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem de fundo (upload)</label>
                  <input ref="aboutHeroBgInputRef" type="file" accept="image/*" class="hidden" @change="onAboutHeroBgSelected" />
                  <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="aboutHeroBgInputRef?.click()">
                    <p class="text-gray-600">Clique para enviar</p>
                  </div>
                  <div v-if="aboutHeroBgPreview" class="mt-3">
                    <img :src="aboutHeroBgPreview" class="w-full h-40 object-cover rounded-lg border border-gray-200" />
                    <button type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium" @click="clearAboutHeroBg">Remover imagem</button>
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Estatísticas</label>
                  <div class="grid grid-cols-2 gap-4">
                  <div v-for="(s, idx) in form.page_data.stats" :key="idx">
                      <input v-model="s.value" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Valor" />
                      <input v-model="s.label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 mt-2" placeholder="Label" />
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Essência</label>
                  <div class="space-y-3">
                    <input v-model="form.page_data.essence.kicker" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kicker" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <input v-model="form.page_data.essence.title_primary" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título 1" />
                      <input v-model="form.page_data.essence.title_highlight" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título 2" />
                    </div>
                    <textarea v-model="form.page_data.essence.text_1" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto 1"></textarea>
                    <textarea v-model="form.page_data.essence.text_2" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto 2"></textarea>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <input v-model="form.page_data.essence.bullets[0]" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Bullet 1" />
                      <input v-model="form.page_data.essence.bullets[1]" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Bullet 2" />
                      <input v-model="form.page_data.essence.bullets[2]" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Bullet 3" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <input v-model="form.page_data.essence.badge_value" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Badge valor" />
                      <input v-model="form.page_data.essence.badge_label" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Badge label" />
                    </div>
                    <div>
                      <label class="block text-gray-700 mb-2 text-sm font-medium">Imagem da essência (upload)</label>
                      <input ref="aboutEssenceImgInputRef" type="file" accept="image/*" class="hidden" @change="onAboutEssenceImgSelected" />
                      <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition cursor-pointer" @click="aboutEssenceImgInputRef?.click()">
                        <p class="text-gray-600">Clique para enviar</p>
                      </div>
                      <div v-if="aboutEssenceImgPreview" class="mt-3">
                        <img :src="aboutEssenceImgPreview" class="w-full h-40 object-cover rounded-lg border border-gray-200" />
                        <button type="button" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium" @click="clearAboutEssenceImg">Remover imagem</button>
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Nosso Time</label>
                  <div class="space-y-3">
                    <input v-model="form.page_data.team.kicker" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kicker" />
                    <input v-model="form.page_data.team.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título" />
                    <input v-model="form.page_data.team.subtitle" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Subtítulo" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div class="space-y-2">
                        <input v-model="form.page_data.team.members[0].name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Nome (1)" />
                        <input v-model="form.page_data.team.members[0].role" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Cargo (1)" />
                        <input ref="team1InputRef" type="file" accept="image/*" class="hidden" @change="onTeam1Selected" />
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="team1InputRef?.click()">
                          <p class="text-gray-600 text-sm">Foto (upload)</p>
                        </div>
                        <img v-if="team1Preview" :src="team1Preview" class="w-full h-28 object-cover rounded-lg border border-gray-200" />
                        <button v-if="team1Preview" type="button" class="text-sm text-red-600 hover:text-red-800 font-medium" @click="clearTeam1">Remover foto</button>
                      </div>
                      <div class="space-y-2">
                        <input v-model="form.page_data.team.members[1].name" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Nome (2)" />
                        <input v-model="form.page_data.team.members[1].role" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Cargo (2)" />
                        <input ref="team2InputRef" type="file" accept="image/*" class="hidden" @change="onTeam2Selected" />
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="team2InputRef?.click()">
                          <p class="text-gray-600 text-sm">Foto (upload)</p>
                        </div>
                        <img v-if="team2Preview" :src="team2Preview" class="w-full h-28 object-cover rounded-lg border border-gray-200" />
                        <button v-if="team2Preview" type="button" class="text-sm text-red-600 hover:text-red-800 font-medium" @click="clearTeam2">Remover foto</button>
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Citação</label>
                  <div class="space-y-3">
                    <textarea v-model="form.page_data.quote.text" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto da citação"></textarea>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <input v-model="form.page_data.quote.author" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Autor" />
                      <input v-model="form.page_data.quote.author_role" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Cargo (opcional)" />
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Nossos Pilares (4)</label>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div v-for="(p, idx) in form.page_data.pillars" :key="idx" class="border border-gray-200 rounded-xl p-4 bg-gray-50">
                      <div class="text-xs text-gray-500 font-semibold mb-2">Pilar {{ idx + 1 }}</div>
                      <input v-model="p.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título" />
                      <textarea v-model="p.description" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 mt-2" placeholder="Descrição"></textarea>
                      <div class="mt-3">
                        <div class="text-sm text-gray-700 font-medium mb-2">Ícone (upload)</div>
                        <input
                          :ref="(el) => (pillarIconInputRefs[idx] = el)"
                          type="file"
                          accept=".jpg,.jpeg,.png,.svg,.webp,image/jpeg,image/png,image/svg+xml,image/webp"
                          class="hidden"
                          @change="(e) => onPillarIconSelected(idx, e)"
                        />
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="pickPillarIcon(idx)">
                          <p class="text-gray-600 text-sm">Enviar</p>
                        </div>
                        <div v-if="p.icon" class="mt-2 flex items-center gap-3">
                          <img :src="p.icon" class="w-12 h-12 object-contain rounded border border-gray-200 bg-white" />
                          <button type="button" class="text-sm text-red-600 hover:text-red-800 font-medium" @click="clearPillarIcon(idx)">
                            Remover ícone
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div>
                  <label class="block text-gray-700 mb-2 text-sm font-medium">Nosso Território</label>
                  <div class="space-y-3">
                    <input v-model="form.page_data.territory.kicker" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kicker (ex.: Nosso território)" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <input v-model="form.page_data.territory.title" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título (parte 1)" />
                      <input v-model="form.page_data.territory.title_highlight" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Título (destaque)" />
                    </div>
                    <textarea v-model="form.page_data.territory.text_1" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto 1"></textarea>
                    <textarea v-model="form.page_data.territory.text_2" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Texto 2"></textarea>
                    <div>
                      <div class="text-sm text-gray-700 font-medium mb-2">Regiões de atuação</div>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <input v-for="(r, idx) in form.page_data.territory.regions" :key="idx" v-model="form.page_data.territory.regions[idx]" type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Ex.: Alphaville" />
                      </div>
                      <button type="button" class="mt-2 text-sm text-blue-700 hover:text-blue-900 font-medium" @click="addTerritoryRegion">Adicionar região</button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                      <div>
                        <div class="text-sm text-gray-700 font-medium mb-2">Imagem principal (vertical)</div>
                        <input ref="territoryMainInputRef" type="file" accept="image/*" class="hidden" @change="onTerritoryMainSelected" />
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="territoryMainInputRef?.click()">
                          <p class="text-gray-600 text-sm">Enviar</p>
                        </div>
                        <img v-if="territoryMainPreview" :src="territoryMainPreview" class="w-full h-28 object-cover rounded-lg border border-gray-200 mt-2" />
                      </div>
                      <div>
                        <div class="text-sm text-gray-700 font-medium mb-2">Imagem quadrada</div>
                        <input ref="territorySquareInputRef" type="file" accept="image/*" class="hidden" @change="onTerritorySquareSelected" />
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="territorySquareInputRef?.click()">
                          <p class="text-gray-600 text-sm">Enviar</p>
                        </div>
                        <img v-if="territorySquarePreview" :src="territorySquarePreview" class="w-full h-28 object-cover rounded-lg border border-gray-200 mt-2" />
                      </div>
                      <div>
                        <div class="text-sm text-gray-700 font-medium mb-2">Imagem horizontal</div>
                        <input ref="territoryWideInputRef" type="file" accept="image/*" class="hidden" @change="onTerritoryWideSelected" />
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-blue-400 transition cursor-pointer" @click="territoryWideInputRef?.click()">
                          <p class="text-gray-600 text-sm">Enviar</p>
                        </div>
                        <img v-if="territoryWidePreview" :src="territoryWidePreview" class="w-full h-28 object-cover rounded-lg border border-gray-200 mt-2" />
                      </div>
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
  }
});

const template = computed(() => {
  if (props.page?.slug === 'home') return 'home';
  if (props.page?.slug === 'sobre' || props.page?.slug === 'quem-somos') return 'about';
  if (props.page?.slug === 'contato') return 'contact';
  return props.page?.template || 'default';
});
const isHome = computed(() => template.value === 'home');
const isAbout = computed(() => template.value === 'about');
const showConteudo = computed(() => !isHome.value && !isAbout.value);
const showContentMedia = computed(() => showConteudo.value);
const bannerSectionTitle = computed(() => (isHome.value ? 'Home (Hero)' : 'Banner'));
const bannerTitleLabel = computed(() => (isHome.value ? 'Título principal (H1)' : 'Título do banner'));
const bannerSubtitleLabel = computed(() => (isHome.value ? 'Subtítulo' : 'Subtítulo do banner'));

const aboutDefaults = () => ({
  hero_title_primary: '',
  hero_title_secondary: '',
  hero_subtitle: '',
  hero_button_label: '',
  hero_button_url: '',
  hero_background_image: '',
  stats: [
    { value: '', label: '' },
    { value: '', label: '' },
    { value: '', label: '' },
    { value: '', label: '' },
  ],
  essence: {
    kicker: '',
    title_primary: '',
    title_highlight: '',
    text_1: '',
    text_2: '',
    bullets: ['', '', ''],
    badge_value: '',
    badge_label: '',
    image: '',
  },
  team: {
    kicker: '',
    title: '',
    subtitle: '',
    members: [
      { name: '', role: '', photo: '' },
      { name: '', role: '', photo: '' },
    ],
  },
  quote: {
    text: '',
    author: '',
    author_role: '',
  },
  pillars: [
    { title: '', description: '', icon: '' },
    { title: '', description: '', icon: '' },
    { title: '', description: '', icon: '' },
    { title: '', description: '', icon: '' },
  ],
  territory: {
    kicker: '',
    title: '',
    title_highlight: '',
    text_1: '',
    text_2: '',
    regions: [''],
    images: {
      main: '',
      square: '',
      wide: '',
    },
  },
});

const mergeAboutData = (incoming) => {
  const base = aboutDefaults();
  const src = incoming && typeof incoming === 'object' ? incoming : {};

  base.hero_title_primary = src.hero_title_primary ?? base.hero_title_primary;
  base.hero_title_secondary = src.hero_title_secondary ?? base.hero_title_secondary;
  base.hero_subtitle = src.hero_subtitle ?? base.hero_subtitle;
  base.hero_button_label = src.hero_button_label ?? base.hero_button_label;
  base.hero_button_url = src.hero_button_url ?? base.hero_button_url;
  base.hero_background_image = src.hero_background_image ?? base.hero_background_image;

  if (Array.isArray(src.stats)) {
    base.stats = base.stats.map((d, i) => ({ ...d, ...(src.stats[i] || {}) }));
  }

  if (src.essence && typeof src.essence === 'object') {
    base.essence = { ...base.essence, ...src.essence };
    if (Array.isArray(src.essence.bullets)) {
      base.essence.bullets = base.essence.bullets.map((d, i) => src.essence.bullets[i] ?? d);
    }
  }

  if (src.team && typeof src.team === 'object') {
    base.team = { ...base.team, ...src.team };
    if (Array.isArray(src.team.members)) {
      base.team.members = base.team.members.map((d, i) => ({ ...d, ...(src.team.members[i] || {}) }));
    }
  }

  if (src.quote && typeof src.quote === 'object') {
    base.quote = { ...base.quote, ...src.quote };
  }

  if (Array.isArray(src.pillars)) {
    base.pillars = base.pillars.map((d, i) => ({ ...d, ...(src.pillars[i] || {}) }));
  }

  if (src.territory && typeof src.territory === 'object') {
    base.territory = { ...base.territory, ...src.territory };
    if (Array.isArray(src.territory.regions)) {
      const regs = src.territory.regions.filter(Boolean).slice(0, 10);
      base.territory.regions = regs.length > 0 ? regs : base.territory.regions;
    }
    if (src.territory.images && typeof src.territory.images === 'object') {
      base.territory.images = { ...base.territory.images, ...src.territory.images };
    }
  }

  return base;
};

const form = useForm({
  titulo: props.page?.titulo || '',
  slug: props.page?.slug || '',
  template: template.value,
  conteudo: props.page?.conteudo || '',
  page_data: isAbout.value ? mergeAboutData(props.page?.data) : (props.page?.data || {}),
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
const aboutHeroBgPreview = ref(form.page_data?.hero_background_image || '');
const aboutEssenceImgInputRef = ref(null);
const aboutEssenceImgPreview = ref(form.page_data?.essence?.image || '');
const team1InputRef = ref(null);
const team1Preview = ref(form.page_data?.team?.members?.[0]?.photo || '');
const team2InputRef = ref(null);
const team2Preview = ref(form.page_data?.team?.members?.[1]?.photo || '');
const pillarIconInputRefs = [];
const territoryMainInputRef = ref(null);
const territorySquareInputRef = ref(null);
const territoryWideInputRef = ref(null);
const territoryMainPreview = ref(form.page_data?.territory?.images?.main || '');
const territorySquarePreview = ref(form.page_data?.territory?.images?.square || '');
const territoryWidePreview = ref(form.page_data?.territory?.images?.wide || '');

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
    form.page_data.hero_background_image = url;
    aboutHeroBgPreview.value = url;
  }
};
const clearAboutHeroBg = () => {
  form.page_data.hero_background_image = '';
  aboutHeroBgPreview.value = '';
};

const onAboutEssenceImgSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (aboutEssenceImgInputRef.value) aboutEssenceImgInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.essence.image = url;
    aboutEssenceImgPreview.value = url;
  }
};
const clearAboutEssenceImg = () => {
  form.page_data.essence.image = '';
  aboutEssenceImgPreview.value = '';
};

const onTeam1Selected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (team1InputRef.value) team1InputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.team.members[0].photo = url;
    team1Preview.value = url;
  }
};
const clearTeam1 = () => {
  form.page_data.team.members[0].photo = '';
  team1Preview.value = '';
};

const onTeam2Selected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (team2InputRef.value) team2InputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.team.members[1].photo = url;
    team2Preview.value = url;
  }
};
const clearTeam2 = () => {
  form.page_data.team.members[1].photo = '';
  team2Preview.value = '';
};

const pickPillarIcon = (idx) => {
  pillarIconInputRefs[idx]?.click();
};

const onPillarIconSelected = async (idx, e) => {
  const file = e?.target?.files?.[0] || null;
  if (pillarIconInputRefs[idx]) pillarIconInputRefs[idx].value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.pillars[idx].icon = url;
  }
};

const clearPillarIcon = (idx) => {
  form.page_data.pillars[idx].icon = '';
};

const addTerritoryRegion = () => {
  if (!Array.isArray(form.page_data.territory.regions)) {
    form.page_data.territory.regions = [''];
    return;
  }
  if (form.page_data.territory.regions.length >= 10) return;
  form.page_data.territory.regions.push('');
};

const onTerritoryMainSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (territoryMainInputRef.value) territoryMainInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.territory.images.main = url;
    territoryMainPreview.value = url;
  }
};

const onTerritorySquareSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (territorySquareInputRef.value) territorySquareInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.territory.images.square = url;
    territorySquarePreview.value = url;
  }
};

const onTerritoryWideSelected = async (e) => {
  const file = e.target.files?.[0] || null;
  if (territoryWideInputRef.value) territoryWideInputRef.value.value = '';
  if (!file) return;
  const url = await uploadMedia(file);
  if (url) {
    form.page_data.territory.images.wide = url;
    territoryWidePreview.value = url;
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
