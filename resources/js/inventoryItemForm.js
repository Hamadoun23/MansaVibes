export default function registerInventoryItemForm(Alpine) {
  Alpine.data('inventoryItemSchemaLines', (opts = {}) => ({
    uidSeq: 0,
    rows: [],

    init() {
      const list = Array.isArray(opts.initialRows) ? opts.initialRows : [];
      list.forEach((r) => {
        this.rows.push({
          uid: ++this.uidSeq,
          key: String(r.key ?? ''),
          label: String(r.label ?? ''),
          type: r.type === 'number' ? 'number' : 'text',
        });
      });
    },

    addRow(type) {
      this.rows.push({
        uid: ++this.uidSeq,
        key: '',
        label: '',
        type: type === 'number' ? 'number' : 'text',
      });
    },

    removeRow(uid) {
      this.rows = this.rows.filter((r) => r.uid !== uid);
    },
  }));

  Alpine.data('inventoryItemCustomLines', (opts = {}) => ({
    uidSeq: 0,
    rows: [],
    articleName: '',

    init() {
      this.articleName = String(opts.initialName ?? '');
      const list = Array.isArray(opts.initialRows) ? opts.initialRows : [];
      list.forEach((r) => {
        this.rows.push({
          uid: ++this.uidSeq,
          label: String(r.label ?? ''),
          type: r.type === 'number' ? 'number' : 'text',
          value: String(r.value ?? ''),
        });
      });
    },

    normalizeForFabricRule(s) {
      return String(s ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/\p{M}/gu, '');
    },

    get fabricMetersMode() {
      const n = this.normalizeForFabricRule(this.articleName);
      if (!n.includes('tissu')) {
        return false;
      }
      return n.includes('leg') || n.includes('lege') || n.includes('leger');
    },

    get numericTotalMeters() {
      let t = 0;
      for (const r of this.rows) {
        if (r.type !== 'number') {
          continue;
        }
        const v = String(r.value ?? '')
          .trim()
          .replace(',', '.');
        if (v === '') {
          continue;
        }
        const n = parseFloat(v, 10);
        if (!Number.isNaN(n)) {
          t += n;
        }
      }
      return Math.round(t * 1000) / 1000;
    },

    get numericTotalMetersDisplay() {
      return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
      }).format(this.numericTotalMeters);
    },

    addRow(type) {
      this.rows.push({
        uid: ++this.uidSeq,
        label: '',
        type: type === 'number' ? 'number' : 'text',
        value: '',
      });
    },

    removeRow(uid) {
      this.rows = this.rows.filter((r) => r.uid !== uid);
    },
  }));
}
