(function () {
    'use strict';

    var form = document.getElementById('cmd-form');
    if (!form) return;

    var btnTest = document.getElementById('btn-test');
    var panel = document.getElementById('test-panel');
    var btnRun = document.getElementById('btn-run');
    var input = document.getElementById('test-input');
    var output = document.getElementById('test-output');

    if (btnTest) {
        btnTest.addEventListener('click', function () {
            panel.hidden = !panel.hidden;
            if (!panel.hidden) input.focus();
        });
    }

    function run() {
        var script = form.querySelector('textarea[name="script"]').value;
        output.textContent = 'Executando…';

        fetch(form.dataset.testUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf: form.dataset.csrf,
                bot_id: form.dataset.botId,
                command_id: form.dataset.commandId,
                text: input.value,
                script: script
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok === false && data.error && !data.output) {
                output.textContent = '⚠ ' + data.error;
                return;
            }
            var lines = [];
            lines.push('⏱ ' + (data.ms != null ? data.ms + ' ms' : '') );
            lines.push('');
            lines.push('🤖 Resposta enviada ao usuário:');
            lines.push(data.output ? data.output : '(sem saída — nada foi enviado)');
            if (data.error) {
                lines.push('');
                lines.push('stderr:');
                lines.push(data.error);
            }
            output.textContent = lines.join('\n');
        })
        .catch(function (err) {
            output.textContent = 'Erro: ' + err;
        });
    }

    if (btnRun) btnRun.addEventListener('click', run);
    if (input) input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); run(); }
    });
})();
