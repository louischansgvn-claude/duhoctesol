#!/usr/bin/env python3
"""Deploy duhoctesol qua FTP. Dùng: python3 deploy.py <cfgfile> <phase>
phase: login | images | css | html | seo | all
cfg: file kiểu curl -K, dòng:  user "USER:PASS"   (chmod 600)
Không bao giờ in password ra log."""
import os, sys, glob, subprocess, tempfile, time

# Windows: console mặc định cp1252, không in được tiếng Việt -> ép UTF-8
try:
    sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    sys.stderr.reconfigure(encoding='utf-8', errors='replace')
except Exception:
    pass

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
HOST = "ftp://pbf43-22360.azdigihost.com"
SKIP_PREFIX = ('.git', 'docs/', '.superpowers', 'scratchpad')


def slash(p):
    """Windows: glob trả '\\', nhưng curl config coi '\\' là escape và URL FTP
    bắt buộc dùng '/'. Chuẩn hoá hết về '/' (trên macOS/Linux là no-op)."""
    return p.replace('\\', '/')


def files_for(phase):
    os.chdir(ROOT)
    if phase == 'html':
        return [f for f in map(slash, glob.glob('**/index.html', recursive=True))
                if not f.startswith(SKIP_PREFIX)]
    if phase == 'css':
        return ['wp-content/themes/duy-study/assets/css/main.css']
    if phase == 'seo':
        return [f for f in ('robots.txt', 'sitemap.xml', 'llms.txt') if os.path.isfile(f)]
    if phase == 'images':
        return sorted(slash(f) for f in glob.glob('wp-content/uploads/**/*', recursive=True)
                      if os.path.isfile(f))
    raise SystemExit('phase?')

def upload(cfg, batch, label):
    fd, path = tempfile.mkstemp(suffix='.cfg'); os.close(fd)
    try:
        with open(cfg) as f: cred = f.read().strip()
        lines = [cred, 'ftp-create-dirs', 'connect-timeout = 25', 'retry = 3',
                 'retry-delay = 3', 'silent', 'show-error']
        for f in batch:
            lines.append(f'upload-file = "{slash(os.path.join(ROOT, f))}"')
            lines.append(f'url = "{HOST}/{f}"')
        open(path, 'w', encoding='utf-8').write('\n'.join(lines) + '\n')
        os.chmod(path, 0o600)
        t = time.time()
        r = subprocess.run(['curl', '-K', path], capture_output=True, text=True)
        err = '\n'.join(l for l in r.stderr.splitlines() if l.strip())[:800]
        print(f'  {label}: {len(batch)} file · exit={r.returncode} · {time.time()-t:.0f}s'
              + (f'\n    stderr: {err}' if err else ''))
        return r.returncode
    finally:
        if os.path.exists(path): os.remove(path)

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print(__doc__); raise SystemExit(2)
    cfg, phase = sys.argv[1], sys.argv[2]
    if phase == 'login':
        r = subprocess.run(['curl', '-sv', '--connect-timeout', '15', '-K', cfg, HOST + '/'],
                           capture_output=True, text=True)
        for l in r.stderr.splitlines():
            if l.startswith(('< 230', '< 530')) or 'denied' in l.lower(): print(' ', l)
        print('  stdout lines:', len(r.stdout.splitlines()))
        sys.exit(0)
    phases = ['images', 'css', 'html', 'seo'] if phase == 'all' else [phase]
    for p in phases:
        fs = files_for(p)
        print(f'--- {p}: {len(fs)} file ---')
        CH = 150 if p == 'images' else 400
        bad = 0
        for i in range(0, len(fs), CH):
            if upload(cfg, fs[i:i+CH], f'{p} {i+1}-{min(i+CH,len(fs))}') != 0: bad += 1
        print(f'--- {p} xong, batch lỗi: {bad} ---')
