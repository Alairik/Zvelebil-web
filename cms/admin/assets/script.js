// Auto-generate slug from title
document.addEventListener('DOMContentLoaded', function() {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput) {
        let slugManuallyEdited = slugInput.value !== '';

        slugInput.addEventListener('input', function() {
            slugManuallyEdited = true;
        });

        titleInput.addEventListener('input', function() {
            if (!slugManuallyEdited) {
                slugInput.value = generateSlug(titleInput.value);
            }
        });
    }

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Image preview
    const imageInput = document.getElementById('featured_image');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const preview = document.getElementById('image-preview');
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Editor tabs (WYSIWYG / Markdown)
    document.querySelectorAll('.editor-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            const target = this.dataset.tab;
            document.querySelectorAll('.editor-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.editor-pane').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById('pane-' + target).classList.add('active');

            if (target === 'preview') {
                updateMarkdownPreview();
            }
        });
    });
});

function generateSlug(text) {
    const map = {
        'á':'a','č':'c','ď':'d','é':'e','ě':'e','í':'i','ň':'n','ó':'o',
        'ř':'r','š':'s','ť':'t','ú':'u','ů':'u','ý':'y','ž':'z',
        'Á':'a','Č':'c','Ď':'d','É':'e','Ě':'e','Í':'i','Ň':'n','Ó':'o',
        'Ř':'r','Š':'s','Ť':'t','Ú':'u','Ů':'u','Ý':'y','Ž':'z'
    };
    return text.toLowerCase()
        .replace(/[áčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ]/g, c => map[c] || c)
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
}

function updateMarkdownPreview() {
    const content = document.getElementById('content');
    const preview = document.getElementById('markdown-preview');
    if (content && preview) {
        // Simple markdown to HTML (basic support)
        let md = content.value;
        md = md.replace(/^### (.*$)/gm, '<h3>$1</h3>');
        md = md.replace(/^## (.*$)/gm, '<h2>$1</h2>');
        md = md.replace(/^# (.*$)/gm, '<h1>$1</h1>');
        md = md.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        md = md.replace(/\*(.*?)\*/g, '<em>$1</em>');
        md = md.replace(/!\[(.*?)\]\((.*?)\)/g, '<img src="$2" alt="$1">');
        md = md.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2">$1</a>');
        md = md.replace(/\n\n/g, '</p><p>');
        md = '<p>' + md + '</p>';
        preview.innerHTML = md;
    }
}
