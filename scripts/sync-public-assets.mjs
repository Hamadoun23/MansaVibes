import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.join(__dirname, '..');
const manifestPath = path.join(root, 'public', 'build', 'manifest.json');

if (!fs.existsSync(manifestPath)) {
  console.error('public/build/manifest.json introuvable. Lancez d’abord : npm run build');
  process.exit(1);
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const cssEntry = manifest['resources/css/app.css'];
const jsEntry = manifest['resources/js/app.js'];

if (!cssEntry?.file || !jsEntry?.file) {
  console.error('Manifest invalide : entrées CSS/JS manquantes.');
  process.exit(1);
}

for (const dir of ['public/css', 'public/js']) {
  fs.mkdirSync(path.join(root, dir), { recursive: true });
}

const cssFrom = path.join(root, 'public', 'build', cssEntry.file);
const jsFrom = path.join(root, 'public', 'build', jsEntry.file);
const cssTo = path.join(root, 'public', 'css', 'app.css');
const jsTo = path.join(root, 'public', 'js', 'app.js');

fs.copyFileSync(cssFrom, cssTo);
fs.copyFileSync(jsFrom, jsTo);

console.log('Assets copiés : public/css/app.css, public/js/app.js');
