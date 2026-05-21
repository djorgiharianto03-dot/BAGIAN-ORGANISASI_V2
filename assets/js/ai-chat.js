(function () {
    'use strict';

    var root = document.querySelector('[data-ai-chat-root]');
    if (!root) {
        return;
    }

    var toggleBtn = document.getElementById('ai-chat-toggle');
    var panel = document.getElementById('ai-chat-panel');
    var closeBtn = document.getElementById('ai-chat-close');
    var form = document.getElementById('ai-chat-form');
    var input = document.getElementById('ai-chat-input');
    var sendBtn = document.getElementById('ai-chat-send');
    var messages = document.getElementById('ai-chat-messages');
    var quickChips = document.getElementById('ai-chat-quick');
    var endpoint = root.getAttribute('data-ai-chat-endpoint') || 'ai_chat.php';

    if (!toggleBtn || !panel || !closeBtn || !form || !input || !messages) {
        return;
    }

    var isOpen = false;
    var isSending = false;
    var MSG_CONNECTION_ERROR = 'Maaf, terjadi gangguan koneksi. Silakan coba lagi.';

    function scrollToLatest() {
        messages.scrollTop = messages.scrollHeight;
    }

    function setPanelOpen(open) {
        isOpen = !!open;
        root.classList.toggle('is-open', isOpen);
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

        if (isOpen) {
            panel.removeAttribute('hidden');
            window.setTimeout(function () {
                input.focus();
            }, 120);
        } else {
            panel.setAttribute('hidden', '');
            toggleBtn.focus();
        }
    }

    function openChat() {
        setPanelOpen(true);
    }

    function closeChat() {
        setPanelOpen(false);
    }

    function setComposerDisabled(disabled) {
        input.disabled = disabled;
        if (sendBtn) {
            sendBtn.disabled = disabled;
        }
        if (quickChips) {
            quickChips.querySelectorAll('.ai-chat__chip').forEach(function (chip) {
                chip.disabled = disabled;
            });
        }
        messages.querySelectorAll('.ai-chat__suggestions .ai-chat__chip').forEach(function (chip) {
            chip.disabled = disabled;
        });
    }

    function hideQuickChips() {
        if (quickChips) {
            quickChips.setAttribute('hidden', '');
        }
    }

    function bindChipActivation(chip, onActivate) {
        chip.addEventListener('click', onActivate);
        chip.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                onActivate();
            }
        });
    }

    function bindQuickChips() {
        if (!quickChips) {
            return;
        }
        quickChips.querySelectorAll('[data-ai-chat-question]').forEach(function (chip) {
            bindChipActivation(chip, function () {
                var question = (chip.getAttribute('data-ai-chat-question') || '').trim();
                if (!question || isSending) {
                    return;
                }
                sendMessage(question);
            });
        });
    }

    function removeTypingIndicator() {
        var typing = messages.querySelector('[data-ai-chat-typing]');
        if (typing) {
            typing.remove();
        }
    }

    function createAvatarElement() {
        var avatar = document.createElement('span');
        avatar.className = 'ai-chat__avatar';
        avatar.setAttribute('aria-hidden', 'true');
        avatar.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i>';
        return avatar;
    }

    function formatTimeLabel() {
        return 'Baru saja';
    }

    function appendTimestamp(parent, alignUser) {
        var time = document.createElement('time');
        time.className = 'ai-chat__time';
        time.setAttribute('datetime', new Date().toISOString());
        time.textContent = formatTimeLabel();
        if (alignUser) {
            time.style.textAlign = 'right';
        }
        parent.appendChild(time);
    }

    function animateBubbleEnter(bubble) {
        window.requestAnimationFrame(function () {
            bubble.classList.add('ai-chat__bubble--enter');
        });
    }

    function isEmptyStateAnswer(text) {
        var lower = String(text || '').toLowerCase();
        return lower.indexOf('belum menemukan') !== -1;
    }

    function showTypingIndicator() {
        removeTypingIndicator();

        var bubble = document.createElement('div');
        bubble.className = 'ai-chat__bubble ai-chat__bubble--bot ai-chat__bubble--typing';
        bubble.setAttribute('data-ai-chat-typing', '');
        bubble.setAttribute('aria-live', 'polite');
        bubble.setAttribute('aria-label', 'Asisten sedang mengetik');

        var row = document.createElement('div');
        row.className = 'ai-chat__bubble-row';
        row.appendChild(createAvatarElement());

        var body = document.createElement('div');
        body.className = 'ai-chat__bubble-body';

        var inner = document.createElement('div');
        inner.className = 'ai-chat__bubble-inner';

        var dots = document.createElement('span');
        dots.className = 'ai-chat__typing-dots';
        dots.setAttribute('aria-hidden', 'true');
        dots.innerHTML = '<span></span><span></span><span></span>';
        inner.appendChild(dots);

        body.appendChild(inner);
        row.appendChild(body);
        bubble.appendChild(row);

        messages.appendChild(bubble);
        animateBubbleEnter(bubble);
        scrollToLatest();
    }

    function badgeClassForType(type) {
        var key = String(type || '').trim().toLowerCase();
        if (key === 'dokumen') {
            return 'ai-chat__result-badge--dokumen';
        }
        if (key === 'layanan') {
            return 'ai-chat__result-badge--layanan';
        }
        if (key === 'berita') {
            return 'ai-chat__result-badge--berita';
        }
        if (key === 'personel') {
            return 'ai-chat__result-badge--personel';
        }

        return 'ai-chat__result-badge--pengumuman';
    }

    function appendResultItems(container, results) {
        if (!Array.isArray(results) || !results.length) {
            return;
        }

        var list = document.createElement('ul');
        list.className = 'ai-chat__results';

        results.forEach(function (item) {
            if (!item || typeof item !== 'object') {
                return;
            }

            var type = typeof item.type === 'string' ? item.type.trim() : 'Informasi';
            var title = typeof item.title === 'string' ? item.title.trim() : '';
            var description = typeof item.description === 'string' ? item.description.trim() : '';
            var link = typeof item.link === 'string' ? item.link.trim() : '';

            if (title === '' && link === '') {
                return;
            }
            if (title === '') {
                title = link;
            }
            if (link === '') {
                return;
            }

            var li = document.createElement('li');
            li.className = 'ai-chat__result-card';

            var badge = document.createElement('span');
            badge.className = 'ai-chat__result-badge ' + badgeClassForType(type);
            badge.textContent = type || 'Informasi';
            li.appendChild(badge);

            var titleEl = document.createElement('div');
            titleEl.className = 'ai-chat__result-title';
            titleEl.textContent = title;
            li.appendChild(titleEl);

            if (description !== '') {
                var descEl = document.createElement('div');
                descEl.className = 'ai-chat__result-desc';
                descEl.textContent = description;
                li.appendChild(descEl);
            }

            var openLink = document.createElement('a');
            openLink.className = 'ai-chat__result-open';
            openLink.href = link;
            openLink.textContent = 'Buka';
            openLink.setAttribute('aria-label', 'Buka ' + type + ': ' + title);
            openLink.innerHTML = 'Buka <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true" style="font-size:0.62em"></i>';
            li.appendChild(openLink);

            list.appendChild(li);
        });

        if (list.childNodes.length) {
            container.appendChild(list);
        }
    }

    function appendSuggestionChips(container, suggestions) {
        if (!Array.isArray(suggestions) || !suggestions.length) {
            return;
        }

        var wrap = document.createElement('div');
        wrap.className = 'ai-chat__suggestions';
        wrap.setAttribute('role', 'group');
        wrap.setAttribute('aria-label', 'Saran pertanyaan lanjutan');

        suggestions.forEach(function (label) {
            if (typeof label !== 'string') {
                return;
            }
            var question = label.trim();
            if (question === '') {
                return;
            }

            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'ai-chat__chip';
            chip.setAttribute('data-ai-chat-question', question);
            chip.textContent = question;
            bindChipActivation(chip, function () {
                if (isSending) {
                    return;
                }
                sendMessage(question);
            });
            wrap.appendChild(chip);
        });

        if (wrap.childNodes.length) {
            container.appendChild(wrap);
        }
    }

    function buildUserBubble(text) {
        var bubble = document.createElement('div');
        bubble.className = 'ai-chat__bubble ai-chat__bubble--user';

        var row = document.createElement('div');
        row.className = 'ai-chat__bubble-row';

        var body = document.createElement('div');
        body.className = 'ai-chat__bubble-body';

        var inner = document.createElement('div');
        inner.className = 'ai-chat__bubble-inner';

        var textEl = document.createElement('p');
        textEl.className = 'ai-chat__bubble-text';
        textEl.textContent = text;
        inner.appendChild(textEl);

        appendTimestamp(inner, true);

        body.appendChild(inner);
        row.appendChild(body);
        bubble.appendChild(row);

        return bubble;
    }

    function buildBotBubble(text, results, suggestions) {
        var bubble = document.createElement('div');
        bubble.className = 'ai-chat__bubble ai-chat__bubble--bot';

        if (isEmptyStateAnswer(text) && (!results || !results.length)) {
            bubble.classList.add('ai-chat__bubble--empty');
        }

        var row = document.createElement('div');
        row.className = 'ai-chat__bubble-row';
        row.appendChild(createAvatarElement());

        var body = document.createElement('div');
        body.className = 'ai-chat__bubble-body';

        var inner = document.createElement('div');
        inner.className = 'ai-chat__bubble-inner';

        var textEl = document.createElement('p');
        textEl.className = 'ai-chat__bubble-text';
        textEl.textContent = text;
        inner.appendChild(textEl);

        appendResultItems(inner, results);
        appendSuggestionChips(inner, suggestions);
        appendTimestamp(inner, false);

        body.appendChild(inner);
        row.appendChild(body);
        bubble.appendChild(row);

        return bubble;
    }

    function appendBubble(text, role, results, suggestions) {
        var bubble = role === 'user'
            ? buildUserBubble(text)
            : buildBotBubble(text, results, suggestions);

        messages.appendChild(bubble);
        animateBubbleEnter(bubble);
        scrollToLatest();
    }

    function requestSearch(userMessage) {
        return fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                message: userMessage,
            }),
        }).then(function (response) {
            return response.text().then(function (text) {
                var data = null;
                try {
                    data = text ? JSON.parse(text) : null;
                } catch (e) {
                    data = null;
                }
                return { ok: response.ok, data: data };
            });
        });
    }

    function sendMessage(optionalText) {
        var userMessage = '';
        if (typeof optionalText === 'string' && optionalText.trim() !== '') {
            userMessage = optionalText.trim();
        } else {
            userMessage = (input.value || '').trim();
        }
        if (!userMessage || isSending) {
            return;
        }

        hideQuickChips();
        appendBubble(userMessage, 'user');
        input.value = '';
        isSending = true;
        setComposerDisabled(true);
        showTypingIndicator();

        requestSearch(userMessage)
            .then(function (res) {
                removeTypingIndicator();

                var payload = res.data;
                if (!payload || typeof payload.answer !== 'string') {
                    appendBubble(MSG_CONNECTION_ERROR, 'bot');
                    return;
                }

                var results = Array.isArray(payload.results) ? payload.results : [];
                var suggestions = Array.isArray(payload.suggestions) ? payload.suggestions : [];
                appendBubble(payload.answer, 'bot', results, suggestions);
            })
            .catch(function () {
                removeTypingIndicator();
                appendBubble(MSG_CONNECTION_ERROR, 'bot');
            })
            .finally(function () {
                isSending = false;
                setComposerDisabled(false);
                input.focus();
                scrollToLatest();
            });
    }

    toggleBtn.addEventListener('click', function () {
        if (isOpen) {
            closeChat();
        } else {
            openChat();
        }
    });

    closeBtn.addEventListener('click', closeChat);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) {
            closeChat();
        }
    });

    bindQuickChips();
    scrollToLatest();
})();
