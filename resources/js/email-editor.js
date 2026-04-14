import Sortable from 'sortablejs';

/**
 * Email block editor Alpine.js component.
 *
 * Usage:  x-data="emailEditor({...})"
 */
export default function emailEditor(config) {
    return {
        /* ── State ── */
        blocks: config.blocks || [],
        selectedBlockId: null,
        subjectLine: config.subjectLine || '',
        previewText: config.previewText || '',
        headline: config.headline || '',
        ctaText: config.ctaText || '',
        campaignId: config.campaignId,
        bankImages: config.bankImages || [],
        hotelName: config.hotelName || '',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',

        saving: false,
        saved: false,
        refining: false,
        refiningBlockId: null,
        aiPrompt: '',
        globalAiPrompt: '',
        showPreview: false,
        editingBlockId: null,

        /* ── Sortable ── */
        sortableInstance: null,

        init() {
            this.$nextTick(() => this.initSortable());
        },

        initSortable() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;

            if (this.sortableInstance) {
                this.sortableInstance.destroy();
            }

            this.sortableInstance = new Sortable(canvas, {
                animation: 200,
                handle: '[data-drag-handle]',
                ghostClass: 'opacity-30',
                chosenClass: 'ring-2 ring-copper',
                onEnd: (evt) => {
                    const item = this.blocks.splice(evt.oldIndex, 1)[0];
                    this.blocks.splice(evt.newIndex, 0, item);
                },
            });
        },

        /* ── Block CRUD ── */
        addBlock(type, afterIndex = null) {
            const id = 'b' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
            const defaults = {
                'text-lead': { content: 'Escribe aquí el párrafo de apertura...' },
                'text-body': { content: 'Escribe aquí el contenido...' },
                'pull-quote': { content: 'Una frase memorable.' },
                'highlight-list': { items: ['Primer punto', 'Segundo punto', 'Tercer punto'] },
                'caption': { content: 'SECCIÓN' },
                'image': { image_id: 0, alt: '' },
                'signoff': { content: 'El equipo del ' + (this.hotelName || 'hotel') },
                'divider': {},
                'spacer': { height: 24 },
            };
            const block = { id, type, ...(defaults[type] || {}) };

            if (afterIndex !== null && afterIndex >= 0) {
                this.blocks.splice(afterIndex + 1, 0, block);
            } else {
                this.blocks.push(block);
            }

            this.$nextTick(() => {
                this.selectBlock(id);
                this.initSortable();
            });
        },

        removeBlock(id) {
            this.blocks = this.blocks.filter(b => b.id !== id);
            if (this.selectedBlockId === id) {
                this.selectedBlockId = null;
            }
            this.$nextTick(() => this.initSortable());
        },

        moveBlock(id, direction) {
            const idx = this.blocks.findIndex(b => b.id === id);
            if (idx === -1) return;
            const newIdx = idx + direction;
            if (newIdx < 0 || newIdx >= this.blocks.length) return;
            const item = this.blocks.splice(idx, 1)[0];
            this.blocks.splice(newIdx, 0, item);
        },

        duplicateBlock(id) {
            const idx = this.blocks.findIndex(b => b.id === id);
            if (idx === -1) return;
            const clone = JSON.parse(JSON.stringify(this.blocks[idx]));
            clone.id = 'b' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
            this.blocks.splice(idx + 1, 0, clone);
            this.$nextTick(() => {
                this.selectBlock(clone.id);
                this.initSortable();
            });
        },

        selectBlock(id) {
            this.selectedBlockId = id;
            this.aiPrompt = '';
        },

        get selectedBlock() {
            return this.blocks.find(b => b.id === this.selectedBlockId) || null;
        },

        get selectedBlockIndex() {
            return this.blocks.findIndex(b => b.id === this.selectedBlockId);
        },

        updateBlockField(id, field, value) {
            const block = this.blocks.find(b => b.id === id);
            if (block) {
                block[field] = value;
            }
        },

        updateListItem(id, index, value) {
            const block = this.blocks.find(b => b.id === id);
            if (block && block.items) {
                block.items[index] = value;
            }
        },

        addListItem(id) {
            const block = this.blocks.find(b => b.id === id);
            if (block && block.items && block.items.length < 5) {
                block.items.push('Nuevo punto');
            }
        },

        removeListItem(id, index) {
            const block = this.blocks.find(b => b.id === id);
            if (block && block.items && block.items.length > 1) {
                block.items.splice(index, 1);
            }
        },

        /* ── Save ── */
        async save() {
            this.saving = true;
            this.saved = false;
            try {
                const res = await fetch(`/campaigns/${this.campaignId}/editor`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        blocks: this.blocks,
                        subject_line: this.subjectLine,
                        preview_text: this.previewText,
                        headline: this.headline,
                        cta_text: this.ctaText,
                    }),
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Error al guardar');
                }

                this.saved = true;
                setTimeout(() => { this.saved = false; }, 2500);
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                this.saving = false;
            }
        },

        /* ── AI: Refine single block ── */
        async refineSelectedBlock(prompt) {
            if (!this.selectedBlock || this.refining) return;
            const idx = this.selectedBlockIndex;
            if (idx === -1) return;

            this.refining = true;
            this.refiningBlockId = this.selectedBlockId;
            try {
                const res = await fetch(`/campaigns/${this.campaignId}/editor/refine-block`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ block_index: idx, prompt }),
                });

                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Error al refinar');
                }

                if (data.block) {
                    this.blocks[idx] = { ...this.blocks[idx], ...data.block };
                }
                this.aiPrompt = '';
            } catch (e) {
                alert('Error IA: ' + e.message);
            } finally {
                this.refining = false;
                this.refiningBlockId = null;
            }
        },

        /* ── AI: Regenerate all ── */
        async regenerateAll(prompt) {
            if (this.refining) return;
            this.refining = true;
            try {
                const res = await fetch(`/campaigns/${this.campaignId}/editor/regenerate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt }),
                });

                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Error al regenerar');
                }

                if (data.blocks) {
                    this.blocks = data.blocks;
                }
                if (data.creative) {
                    this.subjectLine = data.creative.subject_line || this.subjectLine;
                    this.previewText = data.creative.preview_text || this.previewText;
                    this.headline = data.creative.headline || this.headline;
                    this.ctaText = data.creative.cta_text || this.ctaText;
                }
                this.globalAiPrompt = '';
                this.selectedBlockId = null;
                this.$nextTick(() => this.initSortable());
            } catch (e) {
                alert('Error IA: ' + e.message);
            } finally {
                this.refining = false;
            }
        },

        /* ── Quick AI actions per block type ── */
        quickActions(type) {
            const actions = {
                'text-lead': [
                    { label: 'Más conciso', prompt: 'Hazlo más corto y directo, máximo 2 frases.' },
                    { label: 'Más evocador', prompt: 'Hazlo más evocador con imágenes mentales concretas.' },
                    { label: 'Con dato concreto', prompt: 'Añade un dato concreto (número, hora, nombre de lugar).' },
                ],
                'text-body': [
                    { label: 'Más corto', prompt: 'Acorta este párrafo sin perder la idea principal.' },
                    { label: 'Más persuasivo', prompt: 'Hazlo más persuasivo, con más urgencia.' },
                    { label: 'Más detallado', prompt: 'Añade más detalles sensoriales concretos.' },
                    { label: 'Reescribir', prompt: 'Reescribe completamente este párrafo con un enfoque diferente.' },
                ],
                'pull-quote': [
                    { label: 'Otra frase', prompt: 'Genera una frase completamente diferente, más memorable.' },
                    { label: 'Más corta', prompt: 'Hazla más corta e impactante.' },
                ],
                'highlight-list': [
                    { label: 'Más concreto', prompt: 'Haz los items más concretos con datos y nombres propios.' },
                    { label: 'Más persuasivo', prompt: 'Hazlos más persuasivos, orientados a la acción.' },
                ],
                'signoff': [
                    { label: 'Más cálido', prompt: 'Hazlo más cálido y cercano.' },
                    { label: 'Más formal', prompt: 'Hazlo más formal y profesional.' },
                ],
                'caption': [
                    { label: 'Otra sección', prompt: 'Sugiere un nombre de sección diferente.' },
                ],
            };
            return actions[type] || [];
        },

        /* ── Block type metadata ── */
        blockTypeName(type) {
            const names = {
                'text-lead': 'Párrafo lead',
                'text-body': 'Párrafo body',
                'pull-quote': 'Pull-quote',
                'highlight-list': 'Lista highlights',
                'caption': 'Sección',
                'image': 'Imagen',
                'signoff': 'Firma',
                'divider': 'Separador',
                'spacer': 'Espacio',
            };
            return names[type] || type;
        },

        blockTypeIcon(type) {
            const icons = {
                'text-lead': 'T',
                'text-body': 'P',
                'pull-quote': '\u201C',
                'highlight-list': '\u2014',
                'caption': 'Aa',
                'image': '\u25A1',
                'signoff': '\u270D',
                'divider': '\u2726',
                'spacer': '\u2195',
            };
            return icons[type] || '?';
        },

        /* ── Preview ── */
        buildPreviewHtml() {
            const navy = '#1a1a2e';
            const copper = '#c8956c';
            const cream = '#faf8f5';

            let bodyHtml = '';
            for (const block of this.blocks) {
                bodyHtml += this.renderBlockToHtml(block);
            }

            return `
            <div style="max-width:640px;margin:0 auto;font-family:Georgia,'Times New Roman',serif;">
                <div style="background:${navy};color:${cream};padding:32px 40px;">
                    <p style="margin:0 0 12px;font-family:'Courier New',Courier,monospace;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:${copper};">${this.escHtml(this.hotelName)}</p>
                    <h1 style="margin:0;font-family:Georgia,serif;font-size:36px;line-height:1.1;color:${cream};">${this.escHtml(this.headline)}</h1>
                </div>
                <div style="padding:32px 40px;">
                    ${bodyHtml}
                </div>
                <div style="padding:0 40px 32px;text-align:center;">
                    <p style="margin:0 0 24px;text-align:center;font-family:Georgia,serif;font-size:14px;color:#e2d1c3;">&mdash; &#10022; &mdash;</p>
                    <a href="#" style="display:inline-block;background:${copper};color:${navy};padding:14px 32px;border-radius:999px;font-family:Georgia,serif;font-size:16px;font-weight:600;text-decoration:none;">${this.escHtml(this.ctaText)} &rarr;</a>
                </div>
            </div>`;
        },

        renderBlockToHtml(block) {
            switch (block.type) {
                case 'text-lead':
                    return `<p style="margin:0 0 24px;font-family:Georgia,'Times New Roman',serif;font-size:20px;line-height:1.5;color:#1a1a2e;">${this.escHtml(block.content || '')}</p>`;
                case 'text-body':
                    return `<p style="margin:0 0 20px;font-family:Georgia,'Times New Roman',serif;font-size:17px;line-height:1.7;color:#1a1a2eb3;">${this.escHtml(block.content || '')}</p>`;
                case 'pull-quote':
                    return `<blockquote style="margin:32px 0;padding:4px 0 4px 24px;border-left:2px solid #c8956c;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:22px;line-height:1.4;color:#1a1a2e;">&ldquo;${this.escHtml(block.content || '')}&rdquo;</blockquote>`;
                case 'highlight-list': {
                    let rows = (block.items || []).map((item, i, arr) => {
                        const pad = i < arr.length - 1 ? '0 0 12px 0' : '0';
                        return `<tr><td style="padding:${pad};font-family:Georgia,serif;font-size:17px;line-height:1.6;color:#1a1a2eb3;"><span style="color:#c8956c;">&mdash;&nbsp;</span>${this.escHtml(item)}</td></tr>`;
                    }).join('');
                    return `<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">${rows}</table>`;
                }
                case 'caption':
                    return `<p style="margin:24px 0 8px;font-family:'Courier New',Courier,monospace;font-size:11px;text-transform:uppercase;letter-spacing:2px;color:#1a1a2e66;">${this.escHtml(block.content || '')}</p>`;
                case 'image': {
                    if (!block.image_id) return '';
                    const img = this.bankImages.find(i => i.id === block.image_id);
                    const src = img ? img.url : `{{image:${block.image_id}}}`;
                    return `<img src="${src}" alt="${this.escHtml(block.alt || '')}" style="display:block;max-width:100%;height:auto;margin:0 0 20px;border-radius:8px;">`;
                }
                case 'signoff':
                    return `<p style="margin:28px 0 0;font-family:Georgia,'Times New Roman',serif;font-style:italic;font-size:15px;color:#1a1a2e66;">&mdash; ${this.escHtml(block.content || '')}</p>`;
                case 'divider':
                    return `<p style="margin:24px 0;text-align:center;font-family:Georgia,serif;font-size:14px;color:#e2d1c3;">&mdash; &#10022; &mdash;</p>`;
                case 'spacer':
                    return `<div style="height:${block.height || 24}px;"></div>`;
                default:
                    return '';
            }
        },

        escHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /* ── Palette block types ── */
        paletteTypes: [
            { type: 'text-lead', label: 'Párrafo lead', icon: 'T', desc: 'Apertura destacada (20px)' },
            { type: 'text-body', label: 'Párrafo body', icon: 'P', desc: 'Texto de contenido (17px)' },
            { type: 'pull-quote', label: 'Pull-quote', icon: '\u201C', desc: 'Cita editorial con borde copper' },
            { type: 'highlight-list', label: 'Lista highlights', icon: '\u2014', desc: 'Lista editorial (3 items)' },
            { type: 'caption', label: 'Sección', icon: 'Aa', desc: 'Label small-caps' },
            { type: 'image', label: 'Imagen', icon: '\u25A1', desc: 'Imagen del banco' },
            { type: 'signoff', label: 'Firma', icon: '\u270D', desc: 'Despedida editorial' },
            { type: 'divider', label: 'Separador', icon: '\u2726', desc: 'Divider sparkle' },
            { type: 'spacer', label: 'Espacio', icon: '\u2195', desc: 'Espacio vertical' },
        ],
    };
}
