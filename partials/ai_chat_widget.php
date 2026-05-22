<?php
declare(strict_types=1);

if (!defined('ORG_WEB_ROOT')) {
    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'org_database.php';
    define('ORG_WEB_ROOT', org_site_web_root());
}
$aiChatAssetBase = ORG_WEB_ROOT === '' ? '' : rtrim(ORG_WEB_ROOT, '/');
$aiChatScriptUrl = htmlspecialchars($aiChatAssetBase . '/assets/js/ai-chat.js?v=11', ENT_QUOTES, 'UTF-8');
$aiChatEndpointUrl = htmlspecialchars($aiChatAssetBase . '/ai_chat.php', ENT_QUOTES, 'UTF-8');
?>
<style>
    .ai-chat {
        --ai-chat-navy: #041e3f;
        --ai-chat-blue: #0a2f63;
        --ai-chat-accent: #1d6fd4;
        --ai-chat-glass: rgba(255, 255, 255, 0.12);
        --ai-chat-text: #f8fafc;
        --ai-chat-muted: rgba(248, 250, 252, 0.78);
        --ai-chat-shadow: 0 24px 48px rgba(2, 16, 40, 0.32), 0 8px 20px rgba(15, 76, 129, 0.22);
        --ai-chat-radius: 20px;
        position: fixed;
        right: clamp(1rem, 3vw, 1.5rem);
        bottom: clamp(1rem, 3vw, 1.5rem);
        z-index: 1080;
        font-family: var(--font-sans, 'Inter', system-ui, sans-serif);
        pointer-events: none;
    }

    .ai-chat__toggle,
    .ai-chat__panel {
        pointer-events: auto;
    }

    .ai-chat__toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.72rem 1rem 0.72rem 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        background: linear-gradient(135deg, #0b3f74 0%, #1a67b5 55%, #1d6fd4 100%);
        color: #fff;
        box-shadow: var(--ai-chat-shadow);
        cursor: pointer;
        transition: transform 0.28s ease, box-shadow 0.28s ease, opacity 0.28s ease;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .ai-chat__toggle:hover {
        transform: translateY(-2px);
        box-shadow: 0 28px 52px rgba(2, 16, 40, 0.36), 0 10px 24px rgba(29, 111, 212, 0.28);
    }

    .ai-chat__toggle:focus-visible {
        outline: 2px solid #f5d78e;
        outline-offset: 3px;
    }

    .ai-chat__toggle-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.16);
        font-size: 1.05rem;
    }

    .ai-chat__toggle-label {
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        line-height: 1.1;
        text-align: left;
    }

    .ai-chat__panel {
        position: absolute;
        right: 0;
        bottom: calc(100% + 0.85rem);
        width: min(380px, 92vw);
        max-height: min(560px, 80vh);
        display: flex;
        flex-direction: column;
        border-radius: var(--ai-chat-radius);
        border: 1px solid rgba(255, 255, 255, 0.18);
        background: linear-gradient(165deg, rgba(4, 30, 63, 0.94) 0%, rgba(10, 47, 99, 0.9) 48%, rgba(13, 61, 122, 0.92) 100%);
        box-shadow: var(--ai-chat-shadow);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(12px) scale(0.96);
        transform-origin: bottom right;
        transition: opacity 0.32s ease, transform 0.32s ease, visibility 0.32s ease;
    }

    .ai-chat.is-open .ai-chat__panel {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    .ai-chat.is-open .ai-chat__toggle {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(6px) scale(0.94);
    }

    .ai-chat__header {
        flex-shrink: 0;
        padding: 1rem 1rem 0.85rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), transparent);
    }

    .ai-chat__header-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .ai-chat__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--ai-chat-text);
        line-height: 1.3;
    }

    .ai-chat__subtitle {
        margin: 0.35rem 0 0;
        font-size: 0.8rem;
        color: var(--ai-chat-muted);
        line-height: 1.45;
    }

    .ai-chat__close {
        flex-shrink: 0;
        width: 2rem;
        height: 2rem;
        border: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .ai-chat__close:hover {
        background: rgba(255, 255, 255, 0.22);
    }

    .ai-chat__close:focus-visible {
        outline: 2px solid #f5d78e;
        outline-offset: 2px;
    }

    .ai-chat__messages {
        flex: 1 1 auto;
        min-height: 160px;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }

    .ai-chat__bubble {
        max-width: 92%;
        font-size: 0.86rem;
        line-height: 1.5;
        word-break: break-word;
    }

    .ai-chat__bubble--enter {
        animation: ai-chat-bubble-in 0.38s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    @keyframes ai-chat-bubble-in {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ai-chat__bubble-row {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .ai-chat__bubble--user .ai-chat__bubble-row {
        flex-direction: row-reverse;
    }

    .ai-chat__avatar {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background: linear-gradient(145deg, rgba(245, 215, 142, 0.35), rgba(29, 111, 212, 0.45));
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fde68a;
        font-size: 0.78rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .ai-chat__bubble-body {
        flex: 1 1 auto;
        min-width: 0;
    }

    .ai-chat__bubble--bot .ai-chat__bubble-inner {
        padding: 0.65rem 0.8rem;
        border-radius: 16px 16px 16px 6px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: var(--ai-chat-text);
    }

    .ai-chat__bubble--bot.ai-chat__bubble--empty .ai-chat__bubble-inner {
        border-color: rgba(245, 215, 142, 0.22);
        background: rgba(255, 255, 255, 0.1);
    }

    .ai-chat__bubble--user .ai-chat__bubble-inner {
        padding: 0.65rem 0.8rem;
        border-radius: 16px 16px 6px 16px;
        background: linear-gradient(135deg, #1a67b5, #1d6fd4);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .ai-chat__bubble-text {
        margin: 0;
    }

    .ai-chat__time {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.65rem;
        font-weight: 500;
        color: rgba(248, 250, 252, 0.48);
        letter-spacing: 0.02em;
    }

    .ai-chat__bubble--user .ai-chat__time {
        text-align: right;
    }

    .ai-chat__bubble--bot {
        align-self: flex-start;
    }

    .ai-chat__bubble--user {
        align-self: flex-end;
    }

    .ai-chat__bubble--typing .ai-chat__bubble-inner {
        display: inline-flex;
        align-items: center;
        min-height: 2.1rem;
        padding: 0.55rem 0.85rem;
    }

    .ai-chat__typing-dots {
        display: inline-flex;
        gap: 0.28rem;
        align-items: center;
        padding: 0.1rem 0;
    }

    .ai-chat__typing-dots span {
        width: 0.4rem;
        height: 0.4rem;
        border-radius: 50%;
        background: rgba(248, 250, 252, 0.9);
        animation: ai-chat-typing-pulse 1.4s ease-in-out infinite;
    }

    .ai-chat__typing-dots span:nth-child(1) {
        animation-delay: 0s;
    }

    .ai-chat__typing-dots span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .ai-chat__typing-dots span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes ai-chat-typing-pulse {
        0%, 100% {
            transform: translateY(0) scale(0.82);
            opacity: 0.35;
        }
        50% {
            transform: translateY(-4px) scale(1);
            opacity: 1;
        }
    }

    .ai-chat__results {
        margin: 0.65rem 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .ai-chat__result-card {
        padding: 0.52rem 0.58rem;
        border-radius: 11px;
        background: rgba(0, 0, 0, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: background 0.22s ease, border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
    }

    .ai-chat__result-card:hover {
        background: rgba(0, 0, 0, 0.22);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    }

    .ai-chat__result-badge {
        display: inline-block;
        margin-bottom: 0.35rem;
        padding: 0.18rem 0.5rem;
        border-radius: 999px;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        line-height: 1.25;
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .ai-chat__result-badge--dokumen {
        background: rgba(29, 111, 212, 0.35);
        color: #dbeafe;
    }

    .ai-chat__result-badge--layanan {
        background: rgba(13, 148, 136, 0.35);
        color: #ccfbf1;
    }

    .ai-chat__result-badge--pengumuman {
        background: rgba(180, 83, 9, 0.35);
        color: #fde68a;
    }

    .ai-chat__result-badge--berita {
        background: rgba(79, 70, 229, 0.35);
        color: #e0e7ff;
    }

    .ai-chat__result-badge--personel {
        background: rgba(100, 116, 139, 0.4);
        color: #f1f5f9;
    }

    .ai-chat__result-title {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--ai-chat-text);
        line-height: 1.4;
        margin-bottom: 0.2rem;
    }

    .ai-chat__result-desc {
        font-size: 0.75rem;
        color: var(--ai-chat-muted);
        line-height: 1.45;
        margin-bottom: 0.45rem;
    }

    .ai-chat__result-open {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        min-height: 1.55rem;
        padding: 0.18rem 0.55rem;
        border-radius: 7px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #0f172a;
        background: linear-gradient(135deg, #f5d78e, #e8c468);
        border: 1px solid rgba(255, 255, 255, 0.25);
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
    }

    .ai-chat__result-open:hover {
        color: #0f172a;
        filter: brightness(1.04);
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(245, 215, 142, 0.32);
    }

    .ai-chat__result-open:focus-visible {
        outline: 2px solid #f5d78e;
        outline-offset: 2px;
    }

    .ai-chat__send:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .ai-chat__composer {
        flex-shrink: 0;
        display: flex;
        gap: 0.5rem;
        padding: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.12);
    }

    .ai-chat__input {
        flex: 1 1 auto;
        min-width: 0;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        padding: 0.62rem 0.75rem;
        font-size: 0.86rem;
    }

    .ai-chat__input::placeholder {
        color: rgba(248, 250, 252, 0.55);
    }

    .ai-chat__input:focus {
        outline: none;
        border-color: rgba(245, 215, 142, 0.65);
        box-shadow: 0 0 0 3px rgba(245, 215, 142, 0.18);
    }

    .ai-chat__send {
        flex-shrink: 0;
        width: 2.65rem;
        height: 2.65rem;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #0f4c81, #1d6fd4);
        color: #fff;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .ai-chat__send:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(29, 111, 212, 0.35);
    }

    .ai-chat__send:focus-visible {
        outline: 2px solid #f5d78e;
        outline-offset: 2px;
    }

    .ai-chat__quick {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin: 0.15rem 0 0.35rem;
        padding: 0 0.15rem;
        max-width: 100%;
    }

    .ai-chat__quick[hidden] {
        display: none !important;
    }

    .ai-chat__suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-top: 0.55rem;
        padding-top: 0.45rem;
        border-top: 1px solid rgba(147, 197, 253, 0.22);
        max-width: 100%;
    }

    .ai-chat__chip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        padding: 0.32rem 0.62rem;
        border: 1px solid rgba(147, 197, 253, 0.35);
        border-radius: 999px;
        background: rgba(29, 111, 212, 0.22);
        color: #e0f2fe;
        font-size: 0.72rem;
        font-weight: 500;
        line-height: 1.25;
        cursor: pointer;
        transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .ai-chat__chip:hover {
        background: rgba(29, 111, 212, 0.34);
        border-color: rgba(191, 219, 254, 0.55);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18), 0 4px 14px rgba(15, 76, 129, 0.2);
        transform: translateY(-1px);
    }

    .ai-chat__chip:focus-visible {
        outline: 2px solid #f5d78e;
        outline-offset: 2px;
    }

    .ai-chat__chip:active {
        transform: translateY(0);
    }

    .ai-chat__chip:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    @media (max-width: 480px) {
        .ai-chat {
            right: 0.65rem;
            bottom: 0.65rem;
        }

        .ai-chat__panel {
            width: 92vw;
            right: 0;
            max-height: 80vh;
        }

        .ai-chat__messages {
            min-height: 140px;
        }

        .ai-chat__toggle-label {
            font-size: 0.72rem;
        }

        .ai-chat__quick,
        .ai-chat__suggestions {
            gap: 0.35rem;
        }

        .ai-chat__chip {
            font-size: 0.68rem;
            padding: 0.3rem 0.55rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ai-chat__panel,
        .ai-chat__toggle {
            transition: none;
        }

        .ai-chat__bubble--enter,
        .ai-chat__typing-dots span {
            animation: none;
        }

        .ai-chat__result-card:hover {
            transform: none;
        }
    }
</style>

<div id="ai-chat-widget" class="ai-chat" data-ai-chat-root data-ai-chat-endpoint="<?php echo $aiChatEndpointUrl; ?>">
    <button
        type="button"
        class="ai-chat__toggle"
        id="ai-chat-toggle"
        aria-label="Buka Asisten Smart Governance"
        aria-expanded="false"
        aria-controls="ai-chat-panel"
    >
        <span class="ai-chat__toggle-icon" aria-hidden="true"><i class="fa-solid fa-robot"></i></span>
        <span class="ai-chat__toggle-label">AI Assistant</span>
    </button>

    <section
        id="ai-chat-panel"
        class="ai-chat__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="ai-chat-title"
        aria-describedby="ai-chat-subtitle"
        aria-hidden="true"
        hidden
    >
        <header class="ai-chat__header">
            <div class="ai-chat__header-top">
                <div>
                    <h2 class="ai-chat__title" id="ai-chat-title">Asisten Smart Governance</h2>
                    <p class="ai-chat__subtitle" id="ai-chat-subtitle">Tanyakan dokumen, layanan, atau pengumuman</p>
                </div>
                <button
                    type="button"
                    class="ai-chat__close"
                    id="ai-chat-close"
                    aria-label="Tutup asisten chat"
                >
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <div class="ai-chat__messages" id="ai-chat-messages" role="log" aria-live="polite" aria-relevant="additions">
            <div class="ai-chat__bubble ai-chat__bubble--bot ai-chat__bubble--welcome" data-ai-chat-welcome>
                <div class="ai-chat__bubble-row">
                    <span class="ai-chat__avatar" aria-hidden="true"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                    <div class="ai-chat__bubble-body">
                        <div class="ai-chat__bubble-inner">
                            <p class="ai-chat__bubble-text">Halo, saya Asisten Smart Governance. Silakan tanyakan informasi dokumen, layanan, atau pengumuman.</p>
                            <time class="ai-chat__time" datetime="">Baru saja</time>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ai-chat__quick" id="ai-chat-quick" role="group" aria-label="Pertanyaan cepat">
                <button type="button" class="ai-chat__chip" data-ai-chat-question="Ada Perbup?">Ada Perbup?</button>
                <button type="button" class="ai-chat__chip" data-ai-chat-question="Layanan apa saja?">Layanan apa saja?</button>
                <button type="button" class="ai-chat__chip" data-ai-chat-question="Pengumuman terbaru">Pengumuman terbaru</button>
                <button type="button" class="ai-chat__chip" data-ai-chat-question="Dokumen SAKIP">Dokumen SAKIP</button>
                <button type="button" class="ai-chat__chip" data-ai-chat-question="Struktur Organisasi">Struktur Organisasi</button>
            </div>
        </div>

        <form class="ai-chat__composer" id="ai-chat-form" novalidate>
            <label class="visually-hidden" for="ai-chat-input">Pertanyaan untuk asisten</label>
            <input
                type="text"
                class="ai-chat__input"
                id="ai-chat-input"
                name="message"
                placeholder="Tanyakan Perbup, SOTK, layanan, atau pengumuman..."
                autocomplete="off"
                maxlength="300"
                aria-label="Ketik pertanyaan untuk Asisten Smart Governance"
            >
            <button type="submit" class="ai-chat__send" id="ai-chat-send" aria-label="Kirim pesan">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
    </section>
</div>
<?php if (defined('ORG_BERANDA_PAGE') && ORG_BERANDA_PAGE === true): ?>
<script>
(function () {
    'use strict';
    var aiUrl = <?php echo json_encode($aiChatAssetBase . '/assets/js/ai-chat.js?v=11', JSON_UNESCAPED_SLASHES); ?>;
    var loaded = false;
    window.orgBerandaLoadAiChat = function () {
        if (loaded) return;
        loaded = true;
        var s = document.createElement('script');
        s.src = aiUrl;
        s.defer = true;
        document.head.appendChild(s);
    };
    var toggle = document.getElementById('ai-chat-toggle');
    if (toggle) {
        toggle.addEventListener('click', window.orgBerandaLoadAiChat, { once: true, passive: true });
        toggle.addEventListener('focus', window.orgBerandaLoadAiChat, { once: true, passive: true });
    }
})();
</script>
<?php else: ?>
<script src="<?php echo $aiChatScriptUrl; ?>" defer></script>
<?php endif; ?>
