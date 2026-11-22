

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

document.querySelectorAll('.lapmarket-native-select').forEach(select => {
    const wrapper = document.createElement('div');
    wrapper.className = 'lapmarket-select';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'lapmarket-select-trigger';
    trigger.setAttribute('aria-expanded', 'false');
    trigger.innerHTML = '<span></span><i aria-hidden="true"></i>';
    wrapper.appendChild(trigger);

    const menu = document.createElement('div');
    menu.className = 'lapmarket-select-menu';
    menu.setAttribute('role', 'listbox');
    Array.from(select.options).forEach(option => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'lapmarket-select-option';
        item.textContent = option.textContent;
        item.dataset.value = option.value;
        item.setAttribute('role', 'option');
        item.addEventListener('click', () => {
            select.value = option.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            closeMenu();
        });
        menu.appendChild(item);
    });
    wrapper.appendChild(menu);

    const sync = () => {
        const selected = select.options[select.selectedIndex];
        trigger.querySelector('span').textContent = selected?.textContent ?? '';
        menu.querySelectorAll('.lapmarket-select-option').forEach(item => {
            const active = item.dataset.value === select.value;
            item.classList.toggle('is-selected', active);
            item.setAttribute('aria-selected', active ? 'true' : 'false');
        });
    };
    const closeMenu = () => {
        wrapper.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    };
    trigger.addEventListener('click', () => {
        const open = wrapper.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    select.addEventListener('change', sync);
    sync();
});

document.addEventListener('click', event => {
    document.querySelectorAll('.lapmarket-select.is-open').forEach(wrapper => {
        if (!wrapper.contains(event.target)) {
            wrapper.classList.remove('is-open');
            wrapper.querySelector('.lapmarket-select-trigger')?.setAttribute('aria-expanded', 'false');
        }
    });
});

document.addEventListener('submit', event => {
    if (event.defaultPrevented || !(event.target instanceof HTMLFormElement)) {
        return;
    }

    const buttons = document.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');
    buttons.forEach(button => {
        if (button.form !== event.target) {
            return;
        }

        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('button[aria-busy="true"], input[aria-busy="true"]').forEach(button => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    });
});
