/**
 * Emoji Picker (Dropdown) can work as global singleton (one dropdown for all inputs on the page)
 * or with separate instances (and settings) for each input.
 *
 * @author Wolfgang Stöttinger
 */
import $ from 'jquery';
import EmojiArea from 'EmojiArea';
import Emoji from 'EmojiUtil';

export default class EmojiPicker {

  constructor(options = EmojiArea.DEFAULTS) {
    this.o = options;
    const $body = $(document.body);
    $body.on('keydown', (e) => { if (e.keyCode === KEY_ESC || e.keyCode === KEY_TAB) this.hide(); });
    $body.on('click', () => { this.hide(); });
    $(window).on('resize', () => { if (this.$p.is(':visible')) { this.reposition(); }});

    this.$p = $('<div>')
      .addClass('emoji-picker')
      .attr('data-picker-type', options.type) // $.data() here not possible, doesn't change dom
      .on('mouseup click', (e) => e.stopPropagation() && false)
      .hide()
      .appendTo($body);

    const tabs = this.loadPicker();
    setTimeout(this.loadEmojis.bind(this, tabs), 100);
  }

  loadPicker() {
    const ul = $('<ul>')
      .addClass('emoji-selector nav nav-tabs');
    const tabs = $('<div>')
      .addClass('tab-content');

    for (let g = 0; g < Emoji.groups.length; g++) {
      const group = Emoji.groups[g];
      const id = 'group_' + group.name;
      const gid = '#' + id;

      const a = $('<a>')
        .html(EmojiArea.createEmoji(group.name, this.o))
        .data('toggle', 'tab')
        .attr('href', gid);

      ul.append($('<li>').append(a));

      const tab = $('<div>')
        .attr('id', id)
        .addClass('emoji-group tab-pane')
        .data('group', group.name);

      a.on('click', (e) => {
        $('.tab-pane').not(tab).hide().removeClass('active');
        tab.addClass('active').show();
        e.preventDefault();
      });
      tabs.append(tab);
    }

    tabs.find('.tab-pane').not(':first-child').hide().removeClass('active');

    this.$p.append(ul).append(tabs);
    return tabs.children();
  }

  loadEmojis(tabs) {
    for (let g = 0; g < Emoji.groups.length; g++) {
      const group = Emoji.groups[g];
      const tab = tabs[g];
      for (let e = 0; e < group.items.length; e++) {
        const emojiId = group.items[e];
        if (Emoji.data.hasOwnProperty(emojiId)) {
          const word = Emoji.data[emojiId][Emoji.EMOJI_ALIASES] || '';
          const emojiElem = $('<a>')
            .data('emoji', word)
            .html(EmojiArea.createEmoji(word, this.o))
            .on('click', () => {this.insertEmoji(word)});
          $(tab).append(emojiElem);
        }
      }
    }
  }

  insertEmoji(emoji) {
    if (typeof this.cb === 'function')
      this.cb(emoji, this.o);
    this.hide();
  }

  reposition(anchor, options) {
    if (!anchor || anchor.length === 0)
      return;

    const $anchor = $(anchor);
    const anchorOffset = $anchor.offset();
    anchorOffset.right = anchorOffset.left + anchor.outerWidth() - this.$p.outerWidth();
    this.$p.css({
      top: anchorOffset.top + anchor.outerHeight() + (options.anchorOffsetY || 0),
      left: anchorOffset[options.anchorAlignment] + (options.anchorOffsetX || 0),
    });
  };

  show(insertCallback, anchor, options) {
    this.cb = insertCallback;
    this.reposition(anchor, options);
    this.$p.attr('data-picker-type', options.type); // $.data() here not possible, doesn't change dom
    this.$p.show();
    return this;
  }

  hide() {
    this.$p.hide();
  }

  isVisible() {
    return this.$p.is(':visible');
  }
}

EmojiPicker.globalPicker = null;

EmojiPicker.show = function (insertCallback, anchor, options = EmojiArea.DEFAULTS) {
  let picker = EmojiPicker.globalPicker;
  if (!options.globalPicker)
    picker = new EmojiPicker(options);
  if (!picker)
    picker = EmojiPicker.globalPicker = new EmojiPicker(options);
  picker.show(insertCallback, anchor, options);
  return picker;
};

EmojiPicker.isVisible = function () {
  return EmojiPicker.globalPicker && EmojiPicker.globalPicker.isVisible();
};

EmojiPicker.hide = function () {
  !EmojiPicker.globalPicker || EmojiPicker.globalPicker.hide();
};

const KEY_ESC = 27;
const KEY_TAB = 9;