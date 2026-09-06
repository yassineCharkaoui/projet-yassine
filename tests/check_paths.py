"""Portable routing regression checks. Uses a temporary server and in-memory PDO doubles.
No application database is opened or modified. Run: python tests/check_paths.py
"""
from pathlib import Path
import http.client, json, os, shutil, socket, subprocess, tempfile, time

ROOT = Path(__file__).resolve().parents[1]
PHP = shutil.which('php')
assert PHP, 'PHP must be on PATH'
ENV = dict(os.environ)
ENV.pop('APP_BASE_PATH', None)

def php(code, binary=False):
    result = subprocess.run([PHP, '-d', 'display_errors=stderr', '-d', 'error_reporting=24575', '-r', code], cwd=ROOT, env=ENV, capture_output=True)
    assert result.returncode == 0, result.stderr.decode(errors='replace')
    assert not result.stderr, result.stderr.decode(errors='replace')
    return result.stdout if binary else result.stdout.decode()

def literal(value):
    return "'" + str(value).replace('\\', '/').replace("'", "\\'") + "'"

checks = 0
for prefix in ['', '/renamed', '/projet%20yassine', '/nested/a%20folder']:
    for entry in ['index.php', 'view/index.php', 'view/auth/login.php']:
        result = php("$_SERVER['SCRIPT_FILENAME']=" + literal(ROOT/entry) + ";$_SERVER['SCRIPT_NAME']=" + literal(prefix+'/'+entry) + ";require 'controller/config.php'; echo buildUrl('auth','processLogin',['q'=>'a & b']);")
        assert result == prefix+'/index.php?q=a%20%26%20b&controller=auth&action=processLogin', result
        checks += 1
assert php("putenv('APP_BASE_PATH=/alias folder/');require 'controller/config.php';echo appUrl('index.php');") == '/alias%20folder/index.php'
checks += 1

# Render missing views with empty and populated data, including HTML-sensitive values.
fixtures = {
 'client/medicaments/catalogue.php': ("$medicaments=[];$categories=[];", None),
 'client/medicaments/search.php': ("$medicaments=[];$categories=[];$_GET=['q'=>'aspirine','currency'=>'USD'];", None),
 'client/panier/cart.php': ("$panier=[];$total=0;$nombreArticles=0;", None),
 'client/ordonnances/soumettre.php': ("$medicaments=[];", None),
 'client/demandes/list.php': ("$demandes=[];", "$demandes=[['id_ordonnance_origine'=>1,'numero_ordonnance'=>'<test>','date_demande'=>'2026-01-01','statut'=>'en_attente','commentaire'=>'<test>']];"),
 'client/alertes.php': ("$alertesParOrdonnance=[];", "$alertesParOrdonnance=[1=>['ordonnance'=>['numero_ordonnance'=>'<test>'],'alertes'=>[['med1_nom'=>'<test>','med2_nom'=>'B','niveau_gravite'=>'modere','description'=>'<test>','recommandation'=>'<test>']]]];"),
 'responsable/utilisateurs/list.php': ("$utilisateurs=[];", "$utilisateurs=[['prenom'=>'<test>','nom'=>'Test','email'=>'test@example.test','role'=>'client']];"),
 'pharmacien/interactions/list.php': ("$interactions=[];", "$interactions=[['id_interaction'=>1,'med1_nom'=>'<test>','med2_nom'=>'B','niveau_gravite'=>'modere']];"),
 'pharmacien/interactions/view.php': (None, "$interaction=['med1_nom'=>'<test>','med2_nom'=>'B','niveau_gravite'=>'modere','description'=>'<test>'];"),
}
for view, variants in fixtures.items():
    for fixture in variants:
        if fixture is None: continue
        output=php("require 'controller/config.php';define('APP_ROUTED',true);$_SESSION=['user_id'=>1,'user_prenom'=>'Test','user_nom'=>'Test','user_role'=>'"+view.split('/')[0]+"'];"+fixture+"require "+literal('view/'+view)+";")
        assert '<html' in output and '<test>' not in output
        if '<test>' in fixture: assert '&lt;test&gt;' in output
        from html.parser import HTMLParser
        class Destinations(HTMLParser):
            def handle_starttag(self, tag, attrs):
                for key, value in attrs:
                    if key in ['href', 'action', 'src'] and value:
                        assert value.startswith(('/', '#', 'http:', 'https:', 'data:')), (view, key, value)
        Destinations().feed(output)
        checks += 1
pdf=php("require 'controller/PDFGenerator.php';PDFGenerator::downloadOrdonnancePDF(['numero_ordonnance'=>'TEST','date_prescription'=>'2026-01-01','medicaments'=>[]]);", binary=True)
assert pdf.startswith(b'%PDF-') and b'%%EOF' in pdf
checks += 1

# Actual HTTP headers and redirects, with real controllers but a fake PDO connection.
wrapper = r'''<?php
$root = __ROOT__;
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$prefix = '/test%20folder';
if (!str_starts_with($url, $prefix . '/')) { http_response_code(404); exit; }
$relative = rawurldecode(substr($url, strlen($prefix) + 1));
if (!in_array($relative, ['index.php','view/index.php','view/auth/login.php','view/auth/register.php','view/client/dashboard.php'], true)) { http_response_code(404); exit; }
$_SERVER['SCRIPT_NAME'] = $prefix . '/' . $relative;
$_SERVER['SCRIPT_FILENAME'] = $root . '/' . $relative;
require $root . '/controller/config.php';
class TestStatement extends PDOStatement {
    public function __construct(private string $sql) {}
    public function execute(?array $params = null): bool { return true; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $orientation = PDO::FETCH_ORI_NEXT, int $offset = 0): mixed {
        if (str_contains($this->sql, 'SELECT * FROM utilisateur')) return ['id_utilisateur'=>1,'role'=>$_SERVER['HTTP_X_TEST_ROLE'] ?? 'client','nom'=>'Test','prenom'=>'Test','email'=>'test@example.test','mot_de_passe'=>password_hash('password123',PASSWORD_DEFAULT)];
        return false;
    }
    public function fetchColumn(int $column = 0): mixed { return 0; }
}
class TestPDO extends PDO {
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false {
        if (isset($_SERVER['HTTP_X_TEST_FAILURE'])) throw new RuntimeException('Simulated internal error');
        return new TestStatement($query);
    }
    public function lastInsertId(?string $name = null): string|false { return '2'; }
}
(new ReflectionProperty(Config::class, 'pdo'))->setValue(null, new TestPDO());
require $_SERVER['SCRIPT_FILENAME'];
'''.replace('__ROOT__',literal(ROOT))
with tempfile.TemporaryDirectory(prefix='pharmacie-tests-') as temp:
    router=Path(temp)/'router.php'; router.write_text(wrapper,encoding='utf-8')
    with socket.socket() as sock:
        sock.bind(('127.0.0.1',0)); port=sock.getsockname()[1]
    log=open(Path(temp)/'server.log','w+')
    server=subprocess.Popen([PHP,'-d','display_errors=0','-S',f'127.0.0.1:{port}',str(router)],cwd=ROOT,env=ENV,stdout=log,stderr=log,creationflags=getattr(subprocess,'CREATE_NO_WINDOW',0))
    try:
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1',port),timeout=.1): break
            except OSError: time.sleep(.05)
        cookie=''
        def request(path, method='GET', body=None, headers=None):
            global cookie, checks
            conn=http.client.HTTPConnection('127.0.0.1',port,timeout=10)
            h={'Cookie':cookie}
            if body is not None: h['Content-Type']='application/x-www-form-urlencoded'
            h.update(headers or {})
            conn.request(method,'/test%20folder/'+path,body,h)
            response=conn.getresponse(); content=response.read().decode(); response_headers=dict(response.getheaders())
            if 'Set-Cookie' in response_headers: cookie=response_headers['Set-Cookie'].split(';')[0]
            conn.close(); checks+=1
            return response.status,response_headers,content
        for template in ['login','register']:
            status,h,_=request('view/auth/'+template+'.php'); assert status==302 and h['Location']=='/test%20folder/index.php?controller=auth&action='+template
        assert request('view/auth/login.php','POST','email=test')[0]==405
        assert request('view/client/dashboard.php')[0]==404
        for query in ['controller=invalid','controller=auth&action=__construct','controller[]=auth']:
            assert request('index.php?'+query)[0]==404
        status,h,_=request('view/index.php?controller=auth&action=processLogin','POST','email=test')
        assert status==307 and h['Location']=='/test%20folder/index.php?controller=auth&action=processLogin'
        import re
        status,h,page=request('index.php?controller=auth&action=login')
        assert status==200 and '/test%20folder/index.php?controller=auth&action=processLogin' in page
        token=re.search(r'name="csrf_token" value="([^"]+)"',page)[1]
        status,h,_=request('index.php?controller=auth&action=processLogin','POST','csrf_token=wrong&email=test@example.test&password=password123')
        assert status==302 and h['Location'].endswith('action=login')
        status,h,_=request('index.php?controller=auth&action=processLogin','POST',f'csrf_token={token}&email=test@example.test&password=wrong')
        assert status==302 and h['Location'].endswith('action=login')
        for role in ['client','pharmacien','responsable']:
            status,h,page=request('index.php?controller=auth&action=login')
            token=re.search(r'name="csrf_token" value="([^"]+)"',page)[1]
            status,h,_=request('index.php?controller=auth&action=processLogin','POST',f'csrf_token={token}&email=test@example.test&password=password123',{'X-Test-Role':role})
            assert status==302 and h['Location']==f'/test%20folder/index.php?controller={role}&action=dashboard'
            other='responsable' if role!='responsable' else 'client'
            assert request('index.php?controller='+other+'&action=dashboard')[0]==403
            status,h,_=request('index.php?controller=auth&action=logout'); assert status==302 and h['Location'].endswith('action=login')
        status,h,page=request('index.php?controller=auth&action=register')
        assert status==200
        token=re.search(r'name="csrf_token" value="([^"]+)"',page)[1]
        status,h,_=request('index.php?controller=auth&action=processRegister','POST',f'csrf_token={token}&nom=Test&prenom=Test&email=new@example.test&password=password123&confirm_password=password123')
        assert status==302 and h['Location'].endswith('action=login')
        assert request('index.php?controller=auth&action=processLogin','POST',f'csrf_token={token}&email=test@example.test&password=password123',{'X-Test-Failure':'1'})[0]==500
    finally:
        server.terminate(); server.wait(timeout=10); log.close()
print(f'Passed {checks} URL, view, PDF and HTTP checks (database mocked).')
