(function (w, d) {
    w.Tollwerk.Observer.register('a[href][data-hit-area]', a => {
        const hitAreaID = a.dataset.hitArea;
        const hitArea = hitAreaID ? d.getElementById(hitAreaID) : null;
        if (hitArea) {
            hitArea.classList.add('is-hit-area');
            hitArea.addEventListener('click', e => {
                e.preventDefault();
                d.location = a.href;
            }, true);
            a.addEventListener('focus', () => hitArea.classList.add('hit-area-focused'), true);
            a.addEventListener('blur', () => hitArea.classList.remove('hit-area-focused'), true);
        }
    });

})(window, document);
