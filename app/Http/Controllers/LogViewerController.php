<?php

namespace App\Http\Controllers;

use App\Support\AppLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogViewerController extends Controller
{
    private const MAX_BYTES = 400_000;

    public function index(Request $request)
    {
        $expected = (string) env('LOG_VIEWER_KEY', '');
        $given = (string) ($request->query('key') ?: $request->header('X-Log-Key', ''));

        if ($expected === '' || strlen($expected) < 8 || ! hash_equals($expected, $given)) {
            abort(Response::HTTP_FORBIDDEN, 'Cần ?key= đúng LOG_VIEWER_KEY (ít nhất 8 ký tự).');
        }

        $dir = AppLog::directory();
        $files = [];
        if (is_dir($dir)) {
            foreach (scandir($dir, SCANDIR_SORT_DESCENDING) ?: [] as $name) {
                if (preg_match('/^app-\d{4}-\d{2}-\d{2}\.log$/', $name)) {
                    $files[] = $name;
                }
            }
        }

        $selected = (string) $request->query('file', $files[0] ?? '');
        if ($selected !== '' && ! preg_match('/^app-\d{4}-\d{2}-\d{2}\.log$/', $selected)) {
            abort(Response::HTTP_FORBIDDEN, 'file không hợp lệ');
        }

        $level = strtolower((string) $request->query('level', 'all'));
        $q = trim((string) $request->query('q', ''));
        $entries = $this->readEntries($dir, $selected, $level, $q);

        return response()
            ->view('logs.app', [
                'files' => $files,
                'selected' => $selected,
                'level' => $level,
                'q' => $q,
                'entries' => $entries,
                'key' => $given,
                'dirLabel' => 'storage/logs/app',
            ])
            ->header('Cache-Control', 'no-store');
    }

    /**
     * @return list<array{ts:string,level:string,msg:string,url:string,method:string,ip:string,ctx:array}>
     */
    private function readEntries(string $dir, string $file, string $level, string $q): array
    {
        if ($file === '') {
            return [];
        }

        $path = $dir.DIRECTORY_SEPARATOR.$file;
        if (! is_file($path)) {
            return [];
        }

        $size = filesize($path);
        $start = $size !== false && $size > self::MAX_BYTES ? $size - self::MAX_BYTES : 0;
        $fh = fopen($path, 'r');
        if ($fh === false) {
            return [];
        }
        if ($start > 0) {
            fseek($fh, $start);
            fgets($fh);
        }
        $raw = stream_get_contents($fh);
        fclose($fh);

        $out = [];
        foreach (array_reverse(preg_split("/\r\n|\n|\r/", (string) $raw) ?: []) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $row = json_decode($line, true);
            if (! is_array($row)) {
                $row = ['ts' => '', 'level' => 'info', 'msg' => $line, 'url' => '', 'method' => '', 'ip' => '', 'ctx' => []];
            }
            $lv = strtolower((string) ($row['level'] ?? 'info'));
            if ($level !== 'all' && $lv !== $level) {
                continue;
            }
            $hay = strtolower(($row['msg'] ?? '').' '.($row['url'] ?? '').' '.json_encode($row['ctx'] ?? []));
            if ($q !== '' && ! str_contains($hay, strtolower($q))) {
                continue;
            }
            $out[] = [
                'ts' => (string) ($row['ts'] ?? ''),
                'level' => $lv,
                'msg' => (string) ($row['msg'] ?? ''),
                'url' => (string) ($row['url'] ?? ''),
                'method' => (string) ($row['method'] ?? ''),
                'ip' => (string) ($row['ip'] ?? ''),
                'ctx' => is_array($row['ctx'] ?? null) ? $row['ctx'] : [],
            ];
            if (count($out) >= 300) {
                break;
            }
        }

        return $out;
    }
}
