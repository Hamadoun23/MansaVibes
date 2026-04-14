export default function registerOrderFormLines(Alpine) {
  Alpine.data('orderFormLines', (opts = {}) => ({
    uidSeq: 0,
    lines: [],
    templatePrices: {},
    templateNames: {},
    globalTemplateIds: [],
    templateIdsOrder: [],
    discountScope: 'none',
    discountPercent: 0,
    formAdvanceFcfa: 0,
    inventoryOptions: [],

    init() {
      this.templatePrices =
        opts.templatePrices && typeof opts.templatePrices === 'object' ? opts.templatePrices : {};
      this.templateNames =
        opts.templateNames && typeof opts.templateNames === 'object' ? opts.templateNames : {};
      this.templateIdsOrder = Array.isArray(opts.templateIdsOrder)
        ? opts.templateIdsOrder.map(String)
        : [];
      this.discountScope = String(opts.discountScope ?? 'none');
      if (this.discountScope === 'order') {
        this.discountScope = 'all';
      }
      this.discountPercent = Number(opts.discountPercent ?? 0) || 0;
      this.formAdvanceFcfa = Number(opts.formAdvanceFcfa ?? 0) || 0;
      this.inventoryOptions = Array.isArray(opts.inventoryOptions) ? opts.inventoryOptions : [];

      const list = Array.isArray(opts.initialLines) ? opts.initialLines : [];
      if (list.length > 0) {
        const raw = [...new Set(list.map((r) => String(r.measurement_form_template_id ?? '')).filter(Boolean))];
        this.globalTemplateIds = this.orderedSelectedIds(raw);
        list.forEach((r) => this.lines.push(this.normalizeLine(r)));
        this.rebuildLinesFromGlobal();
      }
    },

    orderedSelectedIds(selected) {
      const set = new Set(selected.map(String));
      const order =
        this.templateIdsOrder.length > 0 ? this.templateIdsOrder.map(String) : [...set];
      return order.filter((id) => set.has(id));
    },

    templateRefFcfa(line) {
      const id = String(line.measurement_form_template_id ?? '');
      if (!id) {
        return 0;
      }
      return Number(this.templatePrices[id]) || 0;
    },

    priceFcfa(line) {
      if (line.client_supplies_fabric) {
        const v = Number(line.fabric_price_fcfa);
        return Number.isFinite(v) ? Math.max(0, v) : 0;
      }
      return this.templateRefFcfa(line);
    },

    unitPriceCentsFromLine(line) {
      return Math.round(this.priceFcfa(line) * 100);
    },

    normalizeLine(r) {
      const tplId = String(r.measurement_form_template_id ?? '');
      const ref = tplId ? Number(this.templatePrices[tplId]) || 0 : 0;
      const fabric = Boolean(r.client_supplies_fabric);
      let fp = Number(r.fabric_price_fcfa);
      if (!Number.isFinite(fp)) {
        fp = ref;
      }
      fp = Math.max(0, fp);
      return {
        uid: ++this.uidSeq,
        measurement_form_template_id: tplId,
        description: String(r.description ?? ''),
        quantity: Number(r.quantity ?? 1) || 1,
        apply_discount: Boolean(r.apply_discount),
        client_supplies_fabric: fabric,
        fabric_price_fcfa: fp || ref,
        inventory_item_id: String(r.inventory_item_id ?? ''),
        inventory_characteristic_key: String(r.inventory_characteristic_key ?? ''),
        inventory_consumed_meters:
          r.inventory_consumed_meters !== undefined &&
          r.inventory_consumed_meters !== null &&
          r.inventory_consumed_meters !== ''
            ? String(r.inventory_consumed_meters)
            : '',
      };
    },

    inventoryMeta(invId) {
      const id = String(invId ?? '');
      if (!id) {
        return null;
      }
      return this.inventoryOptions.find((o) => String(o.id) === id) ?? null;
    },

    needsFabricStock(line) {
      if (line.client_supplies_fabric) {
        return false;
      }
      const m = this.inventoryMeta(line.inventory_item_id);
      return Boolean(m && m.numericRows && m.numericRows.length > 0);
    },

    fabricToggle(line) {
      if (line.client_supplies_fabric) {
        const ref = this.templateRefFcfa(line);
        if (!Number(line.fabric_price_fcfa)) {
          line.fabric_price_fcfa = ref;
        }
        line.inventory_characteristic_key = '';
        line.inventory_consumed_meters = '';
      } else {
        line.fabric_price_fcfa = this.templateRefFcfa(line);
      }
    },

    setClientFabricForAll() {
      this.lines.forEach((line) => {
        line.client_supplies_fabric = true;
        const ref = this.templateRefFcfa(line);
        if (!Number(line.fabric_price_fcfa)) {
          line.fabric_price_fcfa = ref;
        }
      });
    },

    clearClientFabricForAll() {
      this.lines.forEach((line) => {
        line.client_supplies_fabric = false;
        line.fabric_price_fcfa = this.templateRefFcfa(line);
      });
    },

    rebuildLinesFromGlobal() {
      const ids = this.orderedSelectedIds(this.globalTemplateIds);
      const prevById = new Map(this.lines.map((l) => [String(l.measurement_form_template_id), l]));
      const newLines = [];
      ids.forEach((id) => {
        const prev = prevById.get(id);
        newLines.push({
          uid: prev ? prev.uid : ++this.uidSeq,
          measurement_form_template_id: id,
          description: prev ? prev.description : String(this.templateNames[id] ?? ''),
          quantity: prev ? prev.quantity : 1,
          apply_discount: prev ? prev.apply_discount : false,
          client_supplies_fabric: prev ? prev.client_supplies_fabric : false,
          fabric_price_fcfa: prev
            ? prev.fabric_price_fcfa
            : (Number(this.templatePrices[id]) || 0),
          inventory_item_id: prev ? String(prev.inventory_item_id ?? '') : '',
          inventory_characteristic_key: prev ? String(prev.inventory_characteristic_key ?? '') : '',
          inventory_consumed_meters: prev ? String(prev.inventory_consumed_meters ?? '') : '',
        });
      });
      this.lines = newLines;
      this.globalTemplateIds = this.orderedSelectedIds(this.globalTemplateIds);
    },

    toggleGlobalTemplate(id, on) {
      id = String(id);
      const set = new Set(this.globalTemplateIds.map(String));
      if (on) {
        set.add(id);
      } else {
        set.delete(id);
      }
      this.globalTemplateIds = this.orderedSelectedIds([...set]);
      this.rebuildLinesFromGlobal();
    },

    removeLine(uid) {
      const line = this.lines.find((l) => l.uid === uid);
      const tid = line ? String(line.measurement_form_template_id ?? '') : '';
      if (tid) {
        const set = new Set(this.globalTemplateIds.map(String));
        set.delete(tid);
        this.globalTemplateIds = this.orderedSelectedIds([...set]);
        this.rebuildLinesFromGlobal();
        return;
      }
      this.lines = this.lines.filter((l) => l.uid !== uid);
    },

    lineGross(line) {
      const q = Number(line.quantity) || 0;
      return q * this.unitPriceCentsFromLine(line);
    },

    lineNet(line) {
      const g = this.lineGross(line);
      const p = Math.min(100, Math.max(0, Number(this.discountPercent) || 0));
      if (p === 0 || this.discountScope === 'none') {
        return g;
      }
      if (this.discountScope === 'all' || this.discountScope === 'order') {
        return g;
      }
      if (this.discountScope === 'lines' && line.apply_discount) {
        return Math.max(0, g - Math.round((g * p) / 100));
      }
      return g;
    },

    subtotalGross() {
      return this.lines.reduce((s, l) => s + this.lineGross(l), 0);
    },

    estimatedTotal() {
      const gross = this.subtotalGross();
      const p = Math.min(100, Math.max(0, Number(this.discountPercent) || 0));
      if (p === 0 || this.discountScope === 'none') {
        return gross;
      }
      if (this.discountScope === 'all' || this.discountScope === 'order') {
        return Math.max(0, Math.round((gross * (100 - p)) / 100));
      }
      return this.lines.reduce((s, l) => s + this.lineNet(l), 0);
    },
  }));
}
