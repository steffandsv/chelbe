/**
 * Inline Editing JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initInlineEditing();
    initCustomTags();
    initCardExpand();
});

function initInlineEditing() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', async function() {
            const cardId = this.dataset.id;
            const url = this.dataset.url;
            const newStatus = this.value;
            
            await saveField(url, 'user_status', newStatus);
            
            const row = this.closest('.card-row');
            const defeatInput = row.querySelector('.defeat-input');
            
            if (newStatus === 'lost') {
                defeatInput?.classList.remove('hidden');
            } else {
                defeatInput?.classList.add('hidden');
            }
        });
    });
    
    document.querySelectorAll('.defeat-input').forEach(input => {
        let timeout;
        
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                saveField(this.dataset.url, 'defeat_reason', this.value);
            }, 500);
        });
        
        input.addEventListener('blur', function() {
            clearTimeout(timeout);
            saveField(this.dataset.url, 'defeat_reason', this.value);
        });
    });
}

async function saveField(url, field, value) {
    try {
        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ [field]: value })
        });
        return await response.json();
    } catch (error) {
        console.error('Error saving:', error);
        return { success: false };
    }
}

function initCustomTags() {
    document.querySelectorAll('.btn-add-tag').forEach(btn => {
        btn.addEventListener('click', function() {
            showAddTagPrompt(this.dataset.cardId, this);
        });
    });
    
    document.querySelectorAll('.remove-tag').forEach(btn => {
        btn.addEventListener('click', async function() {
            const tagId = this.dataset.id;
            await removeTag(tagId);
            this.closest('.tag').remove();
        });
    });
}

function showAddTagPrompt(cardId, btnElement) {
    document.querySelectorAll('.tag-prompt').forEach(p => p.remove());
    
    const prompt = document.createElement('div');
    prompt.className = 'tag-prompt';
    prompt.innerHTML = `
        <input type="text" class="tag-name-input" placeholder="Nome" style="width:80px;padding:0.25rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:4px;color:var(--text-primary);font-size:0.75rem;">
        <input type="text" class="tag-value-input" placeholder="Valor" style="width:60px;padding:0.25rem;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:4px;color:var(--text-primary);font-size:0.75rem;">
        <button class="btn-save-tag" style="padding:0.25rem 0.5rem;background:var(--accent-primary);border:none;border-radius:4px;color:white;cursor:pointer;font-size:0.75rem;">✓</button>
    `;
    prompt.style.cssText = 'display:flex;gap:0.25rem;margin-top:0.5rem;';
    
    btnElement.parentNode.appendChild(prompt);
    prompt.querySelector('.tag-name-input').focus();
    
    prompt.querySelector('.btn-save-tag').addEventListener('click', async () => {
        const name = prompt.querySelector('.tag-name-input').value.trim();
        const value = prompt.querySelector('.tag-value-input').value.trim();
        
        if (name) {
            await addTag(cardId, name, value, btnElement.parentNode);
        }
        prompt.remove();
    });
    
    prompt.querySelectorAll('input').forEach(input => {
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') prompt.querySelector('.btn-save-tag').click();
            if (e.key === 'Escape') prompt.remove();
        });
    });
}

async function addTag(cardId, name, value, container) {
    try {
        const response = await fetch(`/api/cards/${cardId}/tags`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ tag_name: name, tag_value: value || null })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const btn = container.querySelector('.btn-add-tag');
            const tagEl = document.createElement('span');
            tagEl.className = 'tag';
            tagEl.innerHTML = `
                ${escapeHtml(name)}${value ? ': ' + escapeHtml(value) : ''}
                <span class="remove-tag" data-id="${result.id}" title="Remover">×</span>
            `;
            tagEl.querySelector('.remove-tag').addEventListener('click', async function() {
                await removeTag(this.dataset.id);
                tagEl.remove();
            });
            container.insertBefore(tagEl, btn);
        }
    } catch (error) {
        console.error('Error adding tag:', error);
    }
}

async function removeTag(tagId) {
    try {
        await fetch(`/api/tags/${tagId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
    } catch (error) {
        console.error('Error removing tag:', error);
    }
}

function initCardExpand() {
    document.querySelectorAll('.btn-expand').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cardId = this.dataset.id;
            await loadCardDetails(cardId);
            openModal('cardModal');
        });
    });
}

async function loadCardDetails(cardId) {
    const modalBody = document.getElementById('cardModalBody');
    if (!modalBody) return;
    
    modalBody.innerHTML = '<p>Carregando...</p>';
    
    try {
        const response = await fetch(`/api/cards/${cardId}`);
        const card = await response.json();
        
        modalBody.innerHTML = `
            <h3 style="margin-bottom:1.5rem">${escapeHtml(card.title)}</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem">
                <div><small style="color:var(--text-muted)">Board</small><br>${escapeHtml(card.board_name || '-')}</div>
                <div><small style="color:var(--text-muted)">Lista</small><br>${escapeHtml(card.list_name || '-')}</div>
                <div><small style="color:var(--text-muted)">Analista</small><br>${escapeHtml(card.analyst || '-')}</div>
                <div><small style="color:var(--text-muted)">Data</small><br>${card.extracted_date || '-'}</div>
                <div><small style="color:var(--text-muted)">Portal</small><br>${escapeHtml(card.portal || '-')}</div>
                <div><small style="color:var(--text-muted)">Pregão</small><br>${escapeHtml(card.pregao_number || '-')}</div>
            </div>
            ${card.description ? `<div style="background:var(--bg-secondary);padding:1rem;border-radius:8px;font-size:0.875rem;white-space:pre-wrap;max-height:300px;overflow-y:auto">${escapeHtml(card.description)}</div>` : ''}
        `;
    } catch (error) {
        modalBody.innerHTML = `<p style="color:var(--text-muted)">Erro: ${error.message}</p>`;
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
