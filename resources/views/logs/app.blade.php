<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TS Screen — logs/app</title>
    <style>
        :root { color-scheme: dark; }
        body { font: 14px/1.45 ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; margin: 0; background: #111; color: #e8e8e8; }
        header { padding: 16px 20px; background: #1c1c1c; border-bottom: 1px solid #333; }
        h1 { font-size: 16px; margin: 0 0 8px; font-weight: 600; }
        .meta { color: #9aa; font-size: 12px; }
        form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 12px; }
        select, input[type=search] { background: #0d0d0d; color: #eee; border: 1px solid #444; padding: 6px 8px; border-radius: 4px; }
        button { background: #2a6; color: #041; border: 0; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; }
        main { padding: 12px 20px 40px; }
        .empty { color: #888; padding: 24px 0; }
        article { border-bottom: 1px solid #2a2a2a; padding: 10px 0; }
        .lvl { display: inline-block; min-width: 64px; font-weight: 700; text-transform: uppercase; font-size: 11px; }
        .error { color: #f66; }
        .warning { color: #fc6; }
        .info { color: #6cf; }
        .ts { color: #8a8; }
        .url { color: #aaa; word-break: break-all; font-size: 12px; }
        .msg { margin: 4px 0; }
        pre { margin: 6px 0 0; white-space: pre-wrap; word-break: break-word; color: #bbb; font-size: 12px; }
    </style>
</head>
<body>
<header>
    <h1>Logs ứng dụng</h1>
    <div class="meta">{{ $dirLabel }} — lỗi upload, quota, exception 500. Mọi hosting: xem khi video/ảnh bị từ chối hoặc server quá tải.</div>
    <form method="get" action="{{ url('/logs/app') }}">
        <input type="hidden" name="key" value="{{ $key }}">
        <label>File
            <select name="file">
                @forelse ($files as $f)
                    <option value="{{ $f }}" @selected($f === $selected)>{{ $f }}</option>
                @empty
                    <option value="">(chưa có log)</option>
                @endforelse
            </select>
        </label>
        <label>Mức
            <select name="level">
                <option value="all" @selected($level === 'all')>Tất cả</option>
                <option value="error" @selected($level === 'error')>error</option>
                <option value="warning" @selected($level === 'warning')>warning</option>
                <option value="info" @selected($level === 'info')>info</option>
            </select>
        </label>
        <input type="search" name="q" value="{{ $q }}" placeholder="Tìm url / nội dung">
        <button type="submit">Xem</button>
    </form>
</header>
<main>
    @forelse ($entries as $e)
        <article>
            <span class="lvl {{ $e['level'] }}">{{ $e['level'] }}</span>
            <span class="ts">{{ $e['ts'] }}</span>
            @if ($e['method'] || $e['url'])
                <div class="url">{{ $e['method'] }} {{ $e['url'] }} @if($e['ip']) · {{ $e['ip'] }} @endif</div>
            @endif
            <div class="msg">{{ $e['msg'] }}</div>
            @if ($e['ctx'] !== [])
                <pre>{{ json_encode($e['ctx'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) }}</pre>
            @endif
        </article>
    @empty
        <p class="empty">Chưa có dòng log khớp bộ lọc. Khi upload lỗi / exception, AppLog ghi vào đây.</p>
    @endforelse
</main>
</body>
</html>
