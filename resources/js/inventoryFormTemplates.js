export default function registerInventoryFormTemplates(Alpine) {
  Alpine.data('inventoryFormTemplateCreateForm', (opts = {}) => ({
    uidSeq: 0,
    rows: [],
    templates: Array.isArray(opts.templates) ? opts.templates : [],
    copyFromId: opts.initialCopyFromId != null ? String(opts.initialCopyFromId) : '',
    formName: String(opts.formName ?? ''),
    formStockType: opts.formStockType != null ? String(opts.formStockType) : '',
    formNotes: String(opts.formNotes ?? ''),
    formSortOrder: Number(opts.formSortOrder ?? 0) || 0,
    formActive: opts.formActive !== undefined ? Boolean(opts.formActive) : true,

    init() {
      const list = Array.isArray(opts.initialRows) ? opts.initialRows : [];
      list.forEach((r) => {
        this.rows.push({
          uid: ++this.uidSeq,
          key: String(r.key ?? ''),
          label: String(r.label ?? ''),
          unit: String(r.unit ?? ''),
          type: r.type === 'text' ? 'text' : 'number',
        });
      });
    },

    addRow(type) {
      this.rows.push({
        uid: ++this.uidSeq,
        key: '',
        label: '',
        unit: type === 'number' ? 'cm' : '',
        type: type === 'text' ? 'text' : 'number',
      });
    },

    removeRow(uid) {
      this.rows = this.rows.filter((r) => r.uid !== uid);
    },

    onCopyFromChange() {
      const id = this.copyFromId;
      if (!id) {
        return;
      }
      const t = this.templates.find((x) => String(x.id) === String(id));
      if (!t) {
        return;
      }
      this.rows = [];
      this.uidSeq = 0;
      (t.fields || []).forEach((f) => {
        this.rows.push({
          uid: ++this.uidSeq,
          key: String(f.key ?? ''),
          label: String(f.label ?? ''),
          unit: String(f.unit ?? ''),
          type: f.type === 'text' ? 'text' : 'number',
        });
      });
      this.formName = `Copie — ${t.name || ''}`.trim();
      this.formStockType = t.applies_to_stock_type != null ? String(t.applies_to_stock_type) : '';
      this.formNotes = String(t.notes ?? '');
      this.formSortOrder = Number(t.sort_order) || 0;
      this.formActive = Boolean(t.is_active);
    },
  }));
}
