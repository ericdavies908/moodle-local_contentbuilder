// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Block builder UI — show/hide field groups per block type,
 * reveal next slot and scroll on "Add another block".
 *
 * @module     local_contentbuilder/blockbuilder
 * @copyright  2026 University of Glasgow LISU {@link https://www.gla.ac.uk}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {
    'use strict';

    /** @type {number} Maximum number of content blocks per push. */
    var MAX = 10;

    /**
     * Which field group divs are shown for each block type.
     * Keys match the values of the blocktype select element.
     * Values are arrays of group name suffixes matching cb-group-{slot}-{group} IDs.
     *
     * @type {Object.<string, string[]>}
     */
    var GROUPS = {
        'text':      ['heading', 'body'],
        'imagetext': ['heading', 'body', 'image', 'imagepos'],
        'imagefull': ['heading', 'image'],
        'banner':    ['image', 'heading', 'body'],
        'callout':   ['heading', 'callout', 'calloutstyle'],
        'quote':     ['quote'],
        'stats':     ['heading', 'stats'],
        'steps':     ['heading', 'steps'],
        'columns':   ['heading', 'columns'],
        'iconlist':  ['heading', 'iconlist'],
        'tabs':      ['heading', 'tabs'],
        'accordion': ['heading', 'accordion'],
        'flashcards':['heading', 'flashcards']
    };

    /** All known group names — used to hide non-applicable groups. */
    var ALL_GROUPS = [
        'heading', 'body', 'image', 'imagepos', 'callout', 'calloutstyle',
        'quote', 'stats', 'steps', 'columns', 'iconlist',
        'tabs', 'accordion', 'flashcards'
    ];

    /**
     * Read the current visible block count from the hidden form field.
     * Uses querySelector by name because Moodle does not guarantee an id
     * attribute on hidden form fields.
     *
     * @returns {number} Current block count, minimum 1.
     */
    function getCount() {
        var el = document.querySelector('input[name="cb_blockcount"]');
        return el ? (parseInt(el.value, 10) || 1) : 1;
    }

    /**
     * Write the current visible block count to the hidden form field.
     *
     * @param {number} n New count value.
     */
    function setCount(n) {
        var el = document.querySelector('input[name="cb_blockcount"]');
        if (el) {
            el.value = n;
        }
    }

    /**
     * Show or hide field groups inside a slot based on the selected block type.
     *
     * @param {number} slot Zero-based slot index.
     */
    function applyType(slot) {
        var sel = document.getElementById('blocktype_' + slot);
        if (!sel) {
            return;
        }
        var type    = sel.value || 'text';
        var visible = GROUPS[type] || GROUPS['text'];
        var all     = ALL_GROUPS;

        all.forEach(function(g) {
            var el = document.getElementById('cb-group-' + slot + '-' + g);
            if (el) {
                el.style.display = visible.indexOf(g) !== -1 ? '' : 'none';
            }
        });
    }

    /**
     * Attach a change listener to the block type selector for a slot,
     * then apply the current type immediately.
     *
     * @param {number} slot Zero-based slot index.
     */
    /**
     * Show or hide the third column editor based on colcount selector value.
     *
     * @param {number} slot Zero-based slot index.
     */
    function applyColCount(slot) {
        var sel = document.getElementById('colcount_' + slot);
        var col3 = document.getElementById('cb-col-' + slot + '-2');
        if (!sel || !col3) {
            return;
        }
        col3.style.display = sel.value === '3' ? '' : 'none';
    }

    /**
     * Reveal the next hidden tab row within a slot and update the add-tab button.
     *
     * @param {number} slot Zero-based slot index.
     */
    function addTab(slot) {
        var MAX_TABS = 6;
        var countEl = document.querySelector('input[name="tabcount[' + slot + ']"]');
        if (!countEl) { return; }
        var cur = parseInt(countEl.value, 10) || 2;
        if (cur >= MAX_TABS) { return; }
        var next = document.getElementById('cb-tab-' + slot + '-' + cur);
        if (!next) { return; }
        next.style.display = '';
        cur++;
        countEl.value = cur;
        var btn = document.getElementById('cb-add-tab-' + slot);
        if (btn) {
            btn.disabled = cur >= MAX_TABS;
            btn.textContent = cur >= MAX_TABS
                ? 'Maximum tabs reached (' + MAX_TABS + ')'
                : 'Add another tab (' + cur + ' of ' + MAX_TABS + ')';
        }
        var lbl = next.querySelector('input[type="text"]');
        if (lbl) { setTimeout(function() { lbl.focus(); }, 100); }
    }

    /**
     * Reveal the next hidden accordion item row within a slot and update the add button.
     *
     * @param {number} slot Zero-based slot index.
     */
    function addAccItem(slot) {
        var MAX_ACC = 8;
        var countEl = document.querySelector('input[name="acccount[' + slot + ']"]');
        if (!countEl) { return; }
        var cur = parseInt(countEl.value, 10) || 2;
        if (cur >= MAX_ACC) { return; }
        var next = document.getElementById('cb-acc-' + slot + '-' + cur);
        if (!next) { return; }
        next.style.display = '';
        cur++;
        countEl.value = cur;
        var btn = document.getElementById('cb-add-acc-' + slot);
        if (btn) {
            btn.disabled = cur >= MAX_ACC;
            btn.textContent = cur >= MAX_ACC
                ? 'Maximum items reached (' + MAX_ACC + ')'
                : 'Add another item (' + cur + ' of ' + MAX_ACC + ')';
        }
        var ttl = next.querySelector('input[type="text"]');
        if (ttl) { setTimeout(function() { ttl.focus(); }, 100); }
    }

    /**
     * Show or hide flashcard slot divs based on cardcount selector value.
     *
     * @param {number} slot Zero-based slot index.
     */
    function applyCardCount(slot) {
        var sel = document.getElementById('cardcount_' + slot);
        if (!sel) { return; }
        var count = parseInt(sel.value, 10) || 2;
        var c;
        for (c = 0; c < 3; c++) {
            var card = document.getElementById('cb-card-' + slot + '-' + c);
            if (card) {
                card.style.display = c < count ? '' : 'none';
            }
        }
    }

    function bindSlot(slot) {
        var sel = document.getElementById('blocktype_' + slot);
        if (sel && !sel._cb) {
            sel._cb = true;
            sel.addEventListener('change', function() {
                applyType(slot);
            });
        }
        applyType(slot);

        // Bind colcount selector for text columns block.
        var colsel = document.getElementById('colcount_' + slot);
        if (colsel && !colsel._cb) {
            colsel._cb = true;
            colsel.addEventListener('change', function() {
                applyColCount(slot);
            });
        }
        applyColCount(slot);

        // Bind add-tab button.
        var addtabbtn = document.getElementById('cb-add-tab-' + slot);
        if (addtabbtn && !addtabbtn._cb) {
            addtabbtn._cb = true;
            addtabbtn.addEventListener('click', function(e) {
                e.preventDefault();
                addTab(slot);
            });
        }

        // Bind add-accordion-item button.
        var addaccbtn = document.getElementById('cb-add-acc-' + slot);
        if (addaccbtn && !addaccbtn._cb) {
            addaccbtn._cb = true;
            addaccbtn.addEventListener('click', function(e) {
                e.preventDefault();
                addAccItem(slot);
            });
        }

        // Bind cardcount selector for flashcards block.
        var cardsel = document.getElementById('cardcount_' + slot);
        if (cardsel && !cardsel._cb) {
            cardsel._cb = true;
            cardsel.addEventListener('change', function() {
                applyCardCount(slot);
            });
        }
        applyCardCount(slot);
    }

    /**
     * Reveal the next hidden block slot, update the counter and button label,
     * then scroll to and focus the new slot.
     */
    function addBlock() {
        var cur = getCount();
        if (cur >= MAX) {
            return;
        }

        var next = document.getElementById('cb-slot-' + cur);
        if (!next) {
            return;
        }

        next.style.display = '';
        cur++;
        setCount(cur);

        var btn = document.getElementById('cb-add-block-btn');
        if (btn) {
            btn.disabled    = cur >= MAX;
            btn.textContent = cur >= MAX
                ? 'Maximum blocks reached (' + MAX + ')'
                : 'Add another block (' + cur + ' of ' + MAX + ')';
        }

        bindSlot(cur - 1);

        setTimeout(function() {
            next.scrollIntoView({behavior: 'smooth', block: 'start'});
            var h = next.querySelector('input[name^="heading"]');
            if (h) {
                setTimeout(function() {
                    h.focus();
                }, 400);
            }
        }, 100);
    }

    /**
     * Initialise: bind all currently visible slots and wire the add-block button.
     */
    function init() {
        var c = getCount();
        var i;
        for (i = 0; i < c; i++) {
            bindSlot(i);
        }

        var btn = document.getElementById('cb-add-block-btn');
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                addBlock();
            });
        }
    }

    return {
        init: init
    };
});
