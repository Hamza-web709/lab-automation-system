<?php foreach (get_flash() as $flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>" data-animate="fade-up">
        <span><?= e($flash['message']) ?></span>
        <button class="flash-close" type="button" aria-label="Close message" data-dismiss-flash>
            <i data-lucide="x"></i>
        </button>
    </div>
<?php endforeach; ?>
