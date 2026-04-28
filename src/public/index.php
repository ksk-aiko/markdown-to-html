<?php
declare(strict_types=1);

session_start();

$defaultMarkdown = <<<MD
# Sample Title

- item 1
- item 2

**bold text**
MD;

$maxLength = 20000;

$errorCode = $_POST['error'] ?? ($_GET['error'] ?? null);
$allowedErrorCodes = ['too_long', 'empty_markdown', 'invalid_csrf'];
$errorCode = in_array($errorCode, $allowedErrorCodes, true) ? $errorCode : null;

$errorMessage = null;
if ($errorCode === 'too_long') {
    $errorMessage = "Markdown must be {$maxLength} characters or less.";
} elseif ($errorCode === 'empty_markdown') {
    $errorMessage = "Markdown cannot be empty.";
} elseif ($errorCode === 'invalid_csrf') {
    $errorMessage = 'Session expired or invalid request.Please try again.';
} else {
    $errorMessage = null;
}

$markdown = $_POST['markdown'] ?? $defaultMarkdown;

$allowedModes = ['preview', 'download'];
$rawMode = $_POST['mode'] ?? 'preview';
$mode = in_array($rawMode, $allowedModes, true) ? $rawMode : 'preview';

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown to HTML</title>
    <style>
        :root {
            --page-max-width: 1380px; /* Changed: wider canvas like reference */
            --workspace-height: clamp(520px, calc(100dvh - 170px), 760px); /* Changed: viewport-safe fixed workspace height */
            --panel-bg: #f3f3f3; /* Added: reference-like gray */
            --line: #bdbdbd; /* Added: divider/border color */
        }

        html {
            overflow-y: scroll;
        }

        body {
            margin: 0;
            padding: 20px 16px;
            box-sizing: border-box;
            background: #efefef; /* Changed: page background tone */
            color: #222;
            font-family: sans-serif;
        }

        .page {
            max-width: var(--page-max-width);
            margin: 0 auto;
        }

        .workspace {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr)); /* Changed: equal width */
            height: var(--workspace-height); /* Added: unified tall height */
            min-height: 0; /* Added: allow children to shrink */
            border: 1px solid var(--line);
            background: #fff;
            overflow: hidden; /* Added: stop endless vertical expansion */
        }

        .pane {
            min-width: 0;
            min-height: 0; /* Added: needed for inner scroll containers */
            height: 100%;
            background: var(--panel-bg);
            box-sizing: border-box;
        }

        .pane-left {
            border-right: 1px solid var(--line); /* Added: center divider */
            padding: 10px;
            /* Added: label + editor area */
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 8px;
        }

        .pane-right {
            padding: 10px;
            display: grid;
            grid-template-rows: auto 1fr; /* Added: toolbar + preview */
            gap: 10px;
            min-height: 0; /* Added */
        }

        .toolbar {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .toolbar button,
        .toolbar .mode-select {
            height: 32px;
            padding: 0 10px;
            border: 1px solid #8f8f8f;
            background: #f7f7f7;
            border-radius: 3px;
            font-size: 14px;
        }

        #monaco-editor {
            width: 100%;
            height: 100%;
            min-height: 0; /* Added */
            border: 1px solid var(--line);
            border-radius: 0; /* Changed: square edges like reference */
            box-sizing: border-box;
            background: #fff;
        }

        #live-preview {
            width: 100%;
            height: 100%;
            min-height: 0; /* Added */
            border: 1px solid var(--line);
            border-radius: 0;
            box-sizing: border-box;
            background: #fff;
            overflow: auto;
            padding: 14px;
        }

        .is-monaco-enabled #markdown {
            display: none;
        }

        @media (max-width: 900px) {
            .workspace {
                grid-template-columns: 1fr;
                /* Changed: avoid oversized full viewport block on mobile */
                height: auto;
                max-height: none;
            }

            .pane-left {
                border-right: none;
                border-bottom: 1px solid var(--line);
                min-height: 320px;
            }

            .pane-right {
                min-height: 320px;
            }

            /* Added: mobile-safe panel height */
            #monaco-editor,
            #live-preview {
                height: 45vh;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <h1>Markdown to HTML Converter</h1>
        <?php if ($errorMessage !== null): ?>
            <p class="error-message"><?=  htmlspecialchars($errorMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php endif; ?>
        <form id="convert-form" action="/convert.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

            <div class="workspace">
                <section class="pane pane-left">
                    <label for="markdown">Markdown</label>
                    <div id="monaco-editor" aria-label="Markdown Editor"></div>
                    <textarea id="markdown" name="markdown" rows="12" cols="80" maxlength="<?= $maxLength ?>"><?= htmlspecialchars($markdown, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                </section>

                <section class="pane pane-right">
                    <div class="toolbar">
                        <!-- Changed: moved controls to top-right area -->
                        <select id="mode" name="mode" class="mode-select">
                            <option value="preview"<?= $mode === 'preview' ? ' selected' : '' ?>>Preview</option>
                            <option value="download"<?= $mode === 'download' ? ' selected' : '' ?>>Download</option>
                        </select>
                        <button type="submit">Convert</button>
                    </div>

                    <div id="live-preview" aria-live="polite"></div>
                </section>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/marked@12.0.2/lib/marked.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs/loader.js"></script>

    <script>
        (function () {
            var textarea = document.getElementById('markdown');
            var form = document.getElementById('convert-form');
            var mount = document.getElementById('monaco-editor');
            var livePreview = document.getElementById('live-preview');

            if (!window.require || !textarea || !form || !mount || !livePreview) {
                return;
            }

            document.body.classList.add('is-monaco-enabled');

            window.require.config({
                paths: {
                    vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.52.2/min/vs'
                }
            });

            window.require(['vs/editor/editor.main'], function () {
                var editor = monaco.editor.create(mount, {
                    value: textarea.value,
                    language: 'markdown',
                    theme: 'vs',
                    minimap: { enabled: false },
                    automaticLayout: true,
                });

                requestAnimationFrame(function () {
                    editor.layout();
                })

                function renderPreview() {
                    if (!window.marked || !window.DOMPurify) {
                        livePreview.textContent = 'Preview libraries are not loaded.';
                        return;
                    }
                    var raw = editor.getValue();
                    var parsed = marked.parse(raw);
                    livePreview.innerHTML = DOMPurify.sanitize(parsed);
                }

                var timer = null;
                editor.onDidChangeModelContent(function () {
                    if (timer) {
                        clearTimeout(timer);
                    }
                    timer = setTimeout(renderPreview, 120);
                });

                renderPreview();

                form.addEventListener('submit', function () {
                    textarea.value = editor.getValue();
                });
            });
        })();
    </script>
</body>
</html>