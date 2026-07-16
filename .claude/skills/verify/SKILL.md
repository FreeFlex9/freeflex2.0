---
name: verify
description: Build/launch/drive recipe for this Laravel + Inertia (Vue 3) app. Use when verifying a change end-to-end through the real HTTP surface.
---

# Verifying this app (Laravel + Inertia/Vue)

Stack: Laravel backend, Inertia.js server-driven routing, Vue 3 SPA pages.
Admin panel routes live under `/admin/*` and use the `admin` auth guard
(model `App\Models\Admin`, table `admins`, custom password column via
`getAuthPasswordName()` but `Auth::attempt` still takes the `password` key).

## Build

```bash
npx vite build --mode development
```

Fails loudly on Vue/JS syntax errors. Do this after any `.vue` change.

## Launch

```bash
php artisan serve --port=<free-port>
```

Run in background (`&`), give it ~2s, then `curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:<port>/admin/login` to confirm it's up (expect 200).

Stop it afterwards: find the PID via
`netstat -ano | grep <port> | grep LISTENING` then `taskkill //F //PID <pid>`
(Windows/Git Bash environment — no `pkill`).

## Driving admin-guarded routes

There's no known admin plaintext password (seeder only ships bcrypt hashes,
see `database/seeders/AdminSeeder.php`). To drive an authenticated admin
flow end-to-end:

1. Read the current hash so you can restore it exactly:
   `php artisan tinker --execute="echo DB::table('admins')->where('id',1)->value('password');"`
2. Temporarily overwrite it with a known password:
   `php artisan tinker --execute="DB::table('admins')->where('id',1)->update(['password' => Hash::make('temp_pw')]);"`
3. Login via curl with a cookie jar (get CSRF cookie first, urldecode it for the header):
   ```bash
   curl -s -c cookies.txt -b cookies.txt $BASE/admin/login -o /dev/null
   TOKEN=$(php -r "echo urldecode(\$argv[1]);" "$(grep XSRF-TOKEN cookies.txt | awk '{print $NF}')")
   curl -s -i -c cookies.txt -b cookies.txt $BASE/admin/login \
     -H "X-XSRF-TOKEN: $TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" \
     -X POST -d '{"email":"admin1@freeflex.com.br","password":"temp_pw"}'
   ```
   Expect `302` to `/admin`.
4. Hit any `/admin/*` route with the same cookie jar — **do not** send
   `X-Inertia: true` + a guessed `X-Inertia-Version` (causes a `409`
   version-conflict). Easiest: fetch the plain HTML page (no Inertia
   headers at all) and pull the embedded `data-page="..."` attribute:
   ```bash
   curl -s -b cookies.txt -c cookies.txt "$BASE/admin/usuarios?tipo=todos" -o resp.html
   php -r '
     $html = file_get_contents($argv[1]);
     preg_match("/data-page=\"([^\"]+)\"/", $html, $m);
     $d = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);
     print_r($d["props"]);
   ' resp.html
   ```
5. **Always restore the original password hash** afterwards (step 1's
   captured value) — this is shared dev DB state, not a throwaway fixture.

Use ports/paths outside the repo (the session scratchpad dir) for cookie
jars and response dumps so they never get committed.

## Gotchas

- Bash tool `/tmp` paths don't resolve for native `php.exe`/`curl.exe` the
  same way they do inside the bash session — write scratch files to the
  Windows scratchpad path instead so both bash and php/curl agree on it.
- `paginate()` on a page number beyond `last_page` returns an empty
  `data` array with `current_page` echoing the requested page — this is
  stock Laravel behavior, not a bug to chase.
